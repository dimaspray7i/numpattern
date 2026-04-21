<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('current_score')->default(0);
            $table->integer('lives')->default(3);
            $table->integer('correct_streak')->default(0);
            $table->integer('wrong_streak')->default(0);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');
            $table->integer('current_answer')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};