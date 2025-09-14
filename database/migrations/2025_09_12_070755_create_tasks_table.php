<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', TaskStatus::values())->default(TaskStatus::PENDING)->index();
            $table->enum('priority', TaskPriority::values())->default(TaskPriority::MEDIUM)->index();
            $table->date('due_date')->nullable()->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->integer('version')->default(1);
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('created_at');
            $table->fullText(['title', 'description']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
