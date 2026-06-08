<?php

namespace App\Actions;

use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Models\Team;
use App\Models\Application;
use App\Models\GdprConsent;
use App\Models\Organization;
use App\Models\Notification;
use App\Services\EmailConfirmationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegisterUserAction
{
    // Spec 6.2: GDPR consent must be captured at registration time
    private const REQUIRED_CONSENT_TYPES = ['privacy_policy', 'terms_of_service'];

    // Spec 6.2: "registrácia e-mailom s overením adresy" — namespaces the verification
    // code so it can't collide with other EmailConfirmationService purposes (e.g. document access)
    public const EMAIL_VERIFICATION_PURPOSE = 'email_verification';

    public function __construct(private EmailConfirmationService $confirmation)
    {
    }

    public function execute(array $data, ?string $ipAddress = null): User
    {
        return DB::transaction(function () use ($data, $ipAddress) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $this->recordGdprConsent($user, $data, $ipAddress);
            $this->sendEmailVerificationCode($user);

            $user->notificationPreference()->create([
                'email_enabled'           => true,
                'system_enabled'          => true,
                'marketing_enabled'       => false,
                'deadline_alerts_enabled' => true,
            ]);

            $user->assignRole('visitor');

            if ($data['role'] === 'student') {
                $user->studentProfile()->create();
                $this->createStudentTeam($user);
            }
            elseif ($data['role'] === 'company') {
                $this->createCompanyRegistration($user, $data);
            }

            return $user;
        });
    }

    /**
     * Spec 6.2: "registrácia e-mailom s overením adresy" — sends a one-time code
     * (Redis-backed via EmailConfirmationService, same mechanism as document access codes)
     * that the user must submit to POST /auth/email/verify before email_verified_at is set.
     */
    private function sendEmailVerificationCode(User $user): void
    {
        $code = $this->confirmation->generateCode($user->email, [], self::EMAIL_VERIFICATION_PURPOSE);

        Mail::to($user->email)->queue(new EmailVerificationMail($user->name, $code, EmailConfirmationService::DEFAULT_EXPIRES_IN));
    }

    private function recordGdprConsent(User $user, array $data, ?string $ipAddress): void
    {
        $version = $data['consent_version'] ?? '1.0';
        $acceptedAt = now();

        foreach (self::REQUIRED_CONSENT_TYPES as $consentType) {
            GdprConsent::create([
                'user_id'      => $user->id,
                'consent_type' => $consentType,
                'version'      => $version,
                'accepted_at'  => $acceptedAt,
                'ip_address'   => $ipAddress,
                'created_at'   => $acceptedAt,
            ]);
        }
    }

    private function createStudentTeam(User $user): void
    {
        $team = Team::create([
            'name'      => 'Team ' . $user->name,
            'leader_id' => $user->id,
            'status'    => 'active',
        ]);

        $team->members()->attach($user->id, ['role' => 'leader', 'joined_at' => now()]);

        Application::create([
            'program_id' => 1,
            'team_id'    => $team->id,
            'status'     => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    private function createCompanyRegistration(User $user, array $data): void
    {
        $organization = Organization::create([
            'name'          => $data['company_name'],
            'tax_id'        => $data['company_tax_id'],
            'sector'        => $data['sector'],
            'website_link'  => $data['website_link'],
            'description'   => $data['description'],
            'status'        => 'inactive',
        ]);

        $user->organizations()->attach($organization->id, ['role' => 'owner']);

        $team = Team::create([
            'name'      => 'Team ' . $organization->name,
            'leader_id' => $user->id,
            'status'    => 'pending',
        ]);

        $team->members()->attach($user->id, ['role' => 'leader', 'joined_at' => now()]);

        Application::create([
            'program_id' => 1,
            'organization_id' => $organization->id,
            'team_id'    => $team->id,
            'status'     => 'submitted',
            'submitted_at' => now(),
        ]);

        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'company_registration_submitted',
            'message'   => 'Your company registration request has been submitted.',
        ]);
    }
}
