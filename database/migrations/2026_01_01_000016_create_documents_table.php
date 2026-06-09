<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы документов: documents и milestone_documents.
 * Документы могут быть привязаны к заявке, проекту или этапу разработки.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы documents и сводную milestone_documents.
     */
    public function up(): void
    {
        // Документы платформы (контракты, отчёты, спецификации, паспорта)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->integer('version')->default(1);
            $table->enum('classification', ['public', 'internal', 'confidential'])->default('internal');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Сводная таблица: документы, прикреплённые к этапу разработки
        Schema::create('milestone_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Удаляет таблицы milestone_documents и documents.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestone_documents');
        Schema::dropIfExists('documents');
    }
};
