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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->string('title',200);
            $table->smallInteger('track_number')->nullable();
            $table->smallInteger('disc_number')->default(1);
            $table->integer('duration_ms');
            $table->string('isrc',15)->nullable()->unique();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
