<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('study_program')->nullable();
            $table->integer('year')->nullable();
            $table->json('skills_json')->nullable();
            $table->string('cv_path', 500)->nullable();
            $table->decimal('avg_grade', 4, 2)->nullable();
            $table->boolean('has_carried_subjects')->default(false);
            $table->timestamp('eligibility_confirmed_at')->nullable();
            $table->string('eligibility_document_path', 500)->nullable();
            $table->string('academic_documents_path', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
