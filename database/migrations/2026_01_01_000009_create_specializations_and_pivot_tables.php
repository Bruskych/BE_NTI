<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('call_specialization', function (Blueprint $table) {
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained()->cascadeOnDelete();
            $table->primary(['call_id', 'specialization_id']);
        });

        Schema::create('challenge_specialization', function (Blueprint $table) {
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained()->cascadeOnDelete();
            $table->primary(['challenge_id', 'specialization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_specialization');
        Schema::dropIfExists('call_specialization');
        Schema::dropIfExists('specializations');
    }
};
