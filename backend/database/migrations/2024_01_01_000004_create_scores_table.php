<?php
// database/migrations/2024_01_01_000003_create_scores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->timestamp('created_at')->useCurrent();

            // Indexes for leaderboard queries
            $table->index(['score']);                    // sort by score
            $table->index(['user_id']);                 // user history
            $table->index(['difficulty', 'score']);     // filtered leaderboard
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
