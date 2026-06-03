<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Application;
use App\Models\Team;
use App\Models\Program;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users',
            'password'         => 'required|string|min:8|confirmed',
            'role'             => 'required|string|in:student,company,mentor',
            'company_name'     => 'required_if:role,company|string|max:255',
            'company_tax_id'   => 'required_if:role,company|string|regex:/^\d{8,10}$/',
            'sector'           => 'required_if:role,company|string|max:255',
            'website_link'     => 'required_if:role,company|url|max:500',
            'description'      => 'required_if:role,company|string|max:2000',
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                if ($request->role === 'student') {
                    $user->assignRole('visitor');
                    $user->studentProfile()->create();

                    $team = Team::create([
                        'name'      => 'Team ' . $user->name,
                        'leader_id' => $user->id,
                        'status'    => 'active',
                    ]);

                    DB::table('team_user')->insert([
                        'team_id'   => $team->id,
                        'user_id'   => $user->id,
                        'role'      => 'leader',
                        'joined_at' => now(),
                    ]);

                    Application::create([
                        'program_id'      => 1,
                        'organization_id' => null,
                        'team_id'         => $team->id,
                        'status'          => 'submitted',
                        'submitted_at'    => now(),
                        'total_score'     => 0.00,
                    ]);
                }
                elseif ($request->role === 'company') {
                    $user->assignRole('visitor');

                    $organization = Organization::create([
                        'name'          => $request->company_name,
                        'tax_id'        => $request->company_tax_id,
                        'sector'        => $request->sector,
                        'website_link'  => $request->website_link,
                        'description'   => $request->description,
                        'status'          => 'inactive',
                    ]);

                    DB::table('organization_user')->insert([
                        'organization_id' => $organization->id,
                        'user_id'         => $user->id,
                        'role'            => 'owner',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    $team = Team::create([
                        'name'      => 'Team ' . $organization->name,
                        'leader_id' => $user->id,
                        'status'    => 'pending',
                    ]);

                    DB::table('team_user')->insert([
                        'team_id'   => $team->id,
                        'user_id'   => $user->id,
                        'role'      => 'leader',
                        'joined_at' => now(),
                    ]);

                    Application::create([
                        'program_id'      => 1,
                        'organization_id' => $organization->id,
                        'team_id'         => $team->id,
                        'status'          => 'submitted',
                        'submitted_at'    => now(),
                        'total_score'     => 0.00,
                    ]);

                    Notification::create([
                        'user_id'   => $user->id,
                        'type'      => 'company_registration_submitted',
                        'channel'   => 'system',
                        'title'     => 'Company registration submitted',
                        'message'   => 'Your company registration request has been submitted for administrator verification.',
                        'data_json' => json_encode(['organization_id' => $organization->id]),
                    ]);
                }

                $token = $user->createToken('auth_token')->plainTextToken;

                return [
                    'token'         => $token,
                    'user'          => $user->load('roles'),
                    'notifications' => $user->notifications()->latest()->get()
                ];
            });

            return response()->json([
                'message'       => 'Registration successful!',
                'token'         => $result['token'],
                'user'          => $result['user'],
                'notifications' => $result['notifications']
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token'         => $token,
            'user'          => $user->load('roles'),
            'notifications' => $user->notifications()->latest()->get(),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user->load('roles');

        return response()->json([
            'user'          => $user,
            'notifications' => $user->notifications()->latest()->get()
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Logged out'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'User with this email address does not exist.'
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'A password reset link has been sent to your email address.'
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to send password reset email.'
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Password Reset Link Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
