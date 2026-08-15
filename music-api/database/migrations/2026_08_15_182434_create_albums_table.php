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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('title',200);
            $table->enum('album_type',['album','single','ep','compilation']);
            $table->date('release_date')->nullable()->index('idx_albums_release_date');
            $table->string('cover_image_url',500)->nullable();

            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE albums
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (to_tsvector('english', title)) STORED
        SQL);

        DB::statement('CREATE INDEX idx_albums_search ON albums USING GIN (search_vector)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
