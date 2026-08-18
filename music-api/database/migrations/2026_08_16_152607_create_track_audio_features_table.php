<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_audio_features', function (Blueprint $table) {
            $table->foreignId('track_id')
                ->primary()
                ->constrained('tracks')
                ->cascadeOnDelete();

            $table->decimal('tempo_bpm', 6, 2)->nullable();
            $table->decimal('energy', 4, 3)->nullable();
            $table->decimal('danceability', 4, 3)->nullable();
            $table->decimal('valence', 4, 3)->nullable()->comment('musical positivity');
            $table->decimal('acousticness', 4, 3)->nullable();
            $table->decimal('instrumentalness', 4, 3)->nullable();
            $table->decimal('speechiness', 4, 3)->nullable();
            $table->decimal('liveness', 4, 3)->nullable();
            $table->decimal('loudness_db', 5, 2)->nullable();
            $table->smallInteger('key_signature')->nullable();
            $table->smallInteger('mode')->nullable()->comment('0 = minor, 1 = major');
            $table->smallInteger('time_signature')->nullable();
        });

        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_tempo_bpm CHECK (tempo_bpm > 0)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_energy CHECK (energy BETWEEN 0 AND 1)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_danceability CHECK (danceability BETWEEN 0 AND 1)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_valence CHECK (valence BETWEEN 0 AND 1)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_acousticness CHECK (acousticness BETWEEN 0 AND 1)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_instrumentalness CHECK (instrumentalness BETWEEN 0 AND 1)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_speechiness CHECK (speechiness BETWEEN 0 AND 1)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_liveness CHECK (liveness BETWEEN 0 AND 1)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_key_signature CHECK (key_signature BETWEEN 0 AND 11)');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_mode CHECK (mode IN (0, 1))');
        DB::statement('ALTER TABLE track_audio_features ADD CONSTRAINT chk_time_signature CHECK (time_signature BETWEEN 1 AND 12)');
    }

    public function down(): void
    {
        Schema::dropIfExists('track_audio_features');
    }
};