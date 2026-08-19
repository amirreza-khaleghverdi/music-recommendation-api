<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---------------------------------------------------------------
        // Framework scaffolding (Laravel defaults)
        // ---------------------------------------------------------------

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('display_name', 100)->nullable();
            $table->rememberToken();
            $table->timestampsTz();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedSmallInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('connection');
            $table->string('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();

            $table->index(['connection', 'queue', 'failed_at']);
        });

        // ---------------------------------------------------------------
        // Catalog cluster: artists, albums, tracks, genres
        // ---------------------------------------------------------------

        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('bio')->nullable();
            $table->char('country_code', 2)->nullable()->index('idx_artists_country');
            $table->smallInteger('formed_year')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->jsonb('external_ids')->nullable();
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE artists
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (to_tsvector('english', name)) STORED
        SQL);

        DB::statement('CREATE INDEX idx_artists_search ON artists USING GIN (search_vector)');

        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->enum('album_type', ['album', 'single', 'ep', 'compilation']);
            $table->date('release_date')->nullable()->index('idx_albums_release_date');
            $table->string('cover_image_url', 500)->nullable();

            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE albums
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (to_tsvector('english', title)) STORED
        SQL);

        DB::statement('CREATE INDEX idx_albums_search ON albums USING GIN (search_vector)');

        Schema::create('album_artist', function (Blueprint $table) {
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['primary', 'featured', 'various'])->default('primary');
            $table->primary(['album_id', 'artist_id', 'role']);

            $table->index('artist_id', 'idx_album_artists_artist');
        });

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->smallInteger('track_number')->nullable();
            $table->smallInteger('disc_number')->default(1);
            $table->integer('duration_ms');
            $table->string('isrc', 15)->nullable()->unique();
            $table->boolean('explicit')->default(false);
            $table->jsonb('external_ids')->nullable();

            $table->timestampsTz();
            $table->index('album_id', 'idx_tracks_album');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE tracks
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (to_tsvector('english', title)) STORED
        SQL);

        DB::statement('CREATE INDEX idx_tracks_search ON tracks USING GIN (search_vector)');

        Schema::create('track_artist', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['primary', 'featured', 'producer', 'composer', 'remixer']);
            $table->primary(['track_id', 'artist_id', 'role']);

            $table->index('artist_id', 'idx_track_artists_artist');
        });

        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->foreignId('parent_genre_id')->nullable()->constrained('genres')->cascadeOnDelete();

            $table->index('parent_genre_id', 'idx_genres_parent');
        });

        Schema::create('track_genre', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->primary(['track_id', 'genre_id']);

            $table->index('genre_id', 'idx_track_genre_genre');
        });

        // ---------------------------------------------------------------
        // Behavioral / time-series cluster
        // ---------------------------------------------------------------

        Schema::create('artist_follows', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('followed_at')->useCurrent();

            $table->primary(['user_id', 'artist_id']);

            $table->index('artist_id', 'idx_artist_follows_artist');
        });

        Schema::create('track_likes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('liked_at')->useCurrent();

            $table->primary(['user_id', 'track_id']);

            $table->index('track_id', 'idx_track_likes_track');
        });

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

        // ---------------------------------------------------------------
        // Playlists cluster
        // ---------------------------------------------------------------

        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('cover_image', 500)->nullable();

            $table->timestampsTz();
            $table->unique(['user_id', 'title']);
        });

        Schema::create('playlist_track', function (Blueprint $table) {
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->integer('position');
            $table->timestampTz('added_at')->useCurrent();

            $table->primary(['playlist_id', 'track_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_track');
        Schema::dropIfExists('playlists');
        Schema::dropIfExists('track_audio_features');
        Schema::dropIfExists('listen_events');
        Schema::dropIfExists('track_likes');
        Schema::dropIfExists('artist_follows');
        Schema::dropIfExists('track_genre');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('track_artist');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('album_artist');
        Schema::dropIfExists('albums');
        Schema::dropIfExists('artists');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};