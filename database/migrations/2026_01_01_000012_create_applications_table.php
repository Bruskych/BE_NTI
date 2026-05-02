<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stavový automat (sekcia 7.4):
        // draft → submitted → verified → in_evaluation → needs_supplement
        // → approved / rejected → onboarding → active → suspended → archived
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained();
            $table->foreignId('call_id')->nullable()->constrained()->nullOnDelete();       // Program A
            $table->foreignId('challenge_id')->nullable()->constrained()->nullOnDelete();  // Program B
            $table->foreignId('team_id')->constrained();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 100)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->decimal('total_score', 6, 2)->nullable();
            $table->text('decision_comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('application_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('old_status', 100)->nullable();
            $table->string('new_status', 100)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('application_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_fields');
            $table->text('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Program B párovanie: CV, motivačný list, návrh riešenia
        Schema::create('application_pairing_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['cv', 'motivation_letter', 'solution_proposal', 'other']);
            $table->string('file_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_pairing_submissions');
        Schema::dropIfExists('application_answers');
        Schema::dropIfExists('application_history');
        Schema::dropIfExists('applications');
    }
};
