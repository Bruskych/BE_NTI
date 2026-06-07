<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\ApplicationPairingSubmission;
use App\Models\ApplicationHistory;
use App\Models\ApplicationAnswer;
use App\Models\Application;
use App\Models\Challenge;
use App\Models\FormField;
use App\Models\Program;
use App\Models\Call;
use App\Models\Team;
use App\Models\User;

class ApplicationSeeder extends Seeder
{
    /**
     * Форма заявок
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ApplicationPairingSubmission::truncate();
        ApplicationAnswer::truncate();
        ApplicationHistory::truncate();
        Application::truncate();
        Schema::enableForeignKeyConstraints();

        $programGrant = Program::where('type', 'grant')->first();
        $programPractice = Program::where('type', 'practice')->first();
        $teams = Team::all();
        $user = User::first();

        if ($teams->isEmpty()) {
            return;
        }

        // ------------------------------
        // Ручное создание
        // ------------------------------

        // Программа А: Гранты (Статика)
        if ($programGrant) {
            $staticCall = Call::where('program_id', $programGrant->id)->first();
            $staticTeam = $teams->first();

            if ($staticCall && $staticTeam) {
                $staticAppA = Application::create([
                    'program_id'        => $programGrant->id,
                    'call_id'           => $staticCall->id,
                    'challenge_id'      => null,
                    'organization_id'   => null,
                    'team_id'           => $staticTeam->id,
                    'status'            => Application::STATUS_SUBMITTED,
                    'submitted_at'      => now()->subDays(3),
                    'total_score'       => 85.50,
                    'decision_comment'  => 'Static application for testing Program A',
                ]);

                ApplicationHistory::create([
                    'application_id'    => $staticAppA->id,
                    'old_status'        => null,
                    'new_status'        => Application::STATUS_SUBMITTED,
                    'changed_by'        => $user?->id,
                    'comment'           => 'Initial submission',
                    'created_at'        => now()->subDays(3),
                ]);

                $staticFields = FormField::where('program_id', $programGrant->id)
                    ->where(fn($q) => $q->where('call_id', $staticCall->id)->orWhereNull('call_id'))
                    ->get();

                foreach ($staticFields as $field) {
                    ApplicationAnswer::create([
                        'application_id'    => $staticAppA->id,
                        'field_id'          => $field->id,
                        'value_text'        => $field->type === 'select' ? 'Intermediate' : 'Ответ на поле ' . $field->name,
                        'value_json'        => null,
                        'file_path'         => $field->type === 'file' ? 'uploads/answers/static_doc.pdf' : null,
                    ]);
                }
            }
        }

        // Программа Б: Практика (Статика)
        if ($programPractice) {
            $staticChallenge = Challenge::where('program_id', $programPractice->id)->first();
            $staticTeam = $teams->first();

            if ($staticChallenge && $staticTeam) {
                $staticAppB = Application::create([
                    'program_id'        => $programPractice->id,
                    'call_id'           => null,
                    'challenge_id'      => $staticChallenge->id,
                    'organization_id'   => $staticChallenge->organization_id,
                    'team_id'           => $staticTeam->id,
                    'status'            => Application::STATUS_ACTIVE,
                    'submitted_at'      => now()->subDays(7),
                    'total_score'       => 94.00,
                    'decision_comment'  => 'Static application for testing Program B',
                ]);

                ApplicationHistory::create([
                    'application_id'    => $staticAppB->id,
                    'old_status'        => null,
                    'new_status'        => Application::STATUS_ACTIVE,
                    'changed_by'        => $user?->id,
                    'comment'           => 'Approved for the project',
                    'created_at'        => now()->subDays(7),
                ]);

                ApplicationPairingSubmission::create([
                    'application_id'    => $staticAppB->id,
                    'type'              => 'cv',
                    'file_path'         => 'uploads/pairing/static_cv.pdf',
                    'notes'             => 'Leader resume',
                ]);

                ApplicationPairingSubmission::create([
                    'application_id' => $staticAppB->id,
                    'type' => 'solution_proposal',
                    'file_path' => 'uploads/pairing/static_proposal.pdf',
                    'notes' => 'Technical proposal',
                ]);
            }
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        // Программа А: Гранты (Фабрики)
        if ($programGrant) {
            $calls = Call::where('program_id', $programGrant->id)->get();

            foreach ($calls as $call) {
                $selectedTeams = $teams->random(min(2, $teams->count()));

                foreach ($selectedTeams as $team) {
                    if (isset($staticAppA) && $team->id === $staticAppA->team_id && $call->id === $staticAppA->call_id) {
                        continue;
                    }

                    $app = Application::factory()->create([
                        'program_id'        => $programGrant->id,
                        'call_id'           => $call->id,
                        'challenge_id'      => null,
                        'organization_id'   => null,
                        'team_id'           => $team->id,
                    ]);

                    ApplicationHistory::factory()->create([
                        'application_id'    => $app->id,
                        'old_status'        => null,
                        'new_status'        => $app->status,
                        'changed_by'        => $user?->id,
                    ]);

                    $fields = FormField::where('program_id', $programGrant->id)
                        ->where(fn($q) => $q->where('call_id', $call->id)->orWhereNull('call_id'))
                        ->get();

                    foreach ($fields as $field) {
                        $valueText = 'Sample answer for ' . $field->name;
                        $valueJson = null;
                        $filePath = null;

                        if ($field->type === 'select') {
                            $valueText = is_array($field->options_json) ? fake()->randomElement($field->options_json) : 'Default';
                        } elseif ($field->type === 'file') {
                            $valueText = null;
                            $filePath = 'uploads/answers/' . fake()->uuid() . '.pdf';
                        }

                        ApplicationAnswer::create([
                            'application_id'    => $app->id,
                            'field_id'          => $field->id,
                            'value_text'        => $valueText,
                            'value_json'        => $valueJson,
                            'file_path'         => $filePath,
                        ]);
                    }
                }
            }
        }

        // Программа Б: Практика (Фабрики)
        if ($programPractice) {
            $challenges = Challenge::where('program_id', $programPractice->id)->get();

            foreach ($challenges as $challenge) {
                $selectedTeams = $teams->random(min(2, $teams->count()));

                foreach ($selectedTeams as $team) {
                    if (isset($staticAppB) && $team->id === $staticAppB->team_id && $challenge->id === $staticAppB->challenge_id) {
                        continue;
                    }

                    $app = Application::factory()->create([
                        'program_id'        => $programPractice->id,
                        'call_id'           => null,
                        'challenge_id'      => $challenge->id,
                        'organization_id'   => $challenge->organization_id,
                        'team_id'           => $team->id,
                    ]);

                    ApplicationHistory::factory()->create([
                        'application_id'    => $app->id,
                        'old_status'        => null,
                        'new_status'        => $app->status,
                        'changed_by'        => $user?->id,
                    ]);

                    ApplicationPairingSubmission::factory()->create([
                        'application_id'    => $app->id,
                        'type'              => 'cv',
                    ]);

                    ApplicationPairingSubmission::factory()->create([
                        'application_id'    => $app->id,
                        'type'              => 'solution_proposal',
                    ]);
                }
            }
        }
    }
}
