<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationMail;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        // RegisterUserAction::createStudentTeam() submits an application against program_id=1
        Program::create(['name' => 'NTI Practice', 'type' => 'practice', 'is_active' => true]);
    }

    private function registerStudent(string $email = 'petra@example.com', string $name = 'Petra Student'): User
    {
        $this->postJson('/api/auth/register', [
            'name'                  => $name,
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'gdpr_consent'          => true,
            'role'                  => 'student',
        ])->assertStatus(201);

        return User::where('email', $email)->firstOrFail();
    }

    /**
     * Spec 6.2: "registrácia e-mailom s overením adresy" — registration must
     * trigger an e-mail verification code that the user later confirms.
     */
    public function test_registration_queues_an_email_verification_code()
    {
        Mail::fake();

        $user = $this->registerStudent();
        $this->assertNull($user->email_verified_at);

        Mail::assertQueued(EmailVerificationMail::class, fn (EmailVerificationMail $mail) => $mail->hasTo($user->email));
    }

    public function test_user_can_verify_email_with_the_code_from_the_mail()
    {
        Mail::fake();

        $user = $this->registerStudent();

        $code = null;
        Mail::assertQueued(EmailVerificationMail::class, function (EmailVerificationMail $mail) use (&$code) {
            $code = $mail->confirmationCode;
            return true;
        });

        $this->actingAs($user)
            ->postJson('/api/auth/email/verify', ['code' => $code])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Email verified successfully.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verifying_email_with_invalid_code_fails()
    {
        Mail::fake();

        $user = $this->registerStudent();

        $this->actingAs($user)
            ->postJson('/api/auth/email/verify', ['code' => 'WRONGCODE'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_request_a_new_verification_code()
    {
        Mail::fake();

        $user = $this->registerStudent();

        $this->actingAs($user)
            ->postJson('/api/auth/email/resend')
            ->assertStatus(200)
            ->assertJsonPath('message', 'Verification code resent.');

        Mail::assertQueued(EmailVerificationMail::class, 2);
    }
}
