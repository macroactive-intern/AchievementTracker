<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type');
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index('player_id');
            $table->index('action_type');
            $table->index('occurred_at');
            $table->index(['player_id', 'action_type']);
            $table->index(['player_id', 'action_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_actions');
    }
};
