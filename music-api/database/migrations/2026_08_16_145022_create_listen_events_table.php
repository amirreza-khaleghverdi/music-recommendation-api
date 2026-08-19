<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('listen_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('played_at');
            $table->integer('ms_played');
            $table->enum('context_type', ['playlist', 'album', 'artist', 'search', 'radio', 'recommendation'])->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->string('device_type', 30)->nullable();
            $table->uuid('session_id')->nullable();

            $table->index('user_id');
            $table->index('track_id');
            $table->index('played_at');
            $table->index(['context_type', 'context_id']);
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listen_events');
    }
};
