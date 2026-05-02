<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained();
            $table->foreignId('organization_id')->constrained();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->longText('technical_specification')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->foreignId('product_owner_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->dateTime('deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'pairing', 'assigned', 'active', 'closed'])
                  ->default('draft');
            $table->integer('max_applications')->default(3);
            $table->integer('backlog_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
