<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained();
            // null = platné pre celý program; not null = špecifické pre danú výzvu
            $table->foreignId('call_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('label')->nullable();
            $table->string('type', 100)->nullable();
            $table->boolean('required')->default(false);
            $table->json('options_json')->nullable();
            $table->text('validation_rules')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
