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
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name',150);
            $table->text('bio')->nullable();
            $table->char('country_code',2)->nullable()->index('idx_artists_country');
            $table->smallInteger('formed_year')->nullable();
            $table->string('image_url',500)->nullable();
            $table->jsonb('external_ids')->nullable();
            $table->timestampsTz();
        });
        
        DB::statement(<<<'SQL'
            ALTER TABLE artists
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (to_tsvector('english', name)) STORED
        SQL);

        DB::statement('CREATE INDEX idx_artists_search ON artists USING GIN (search_vector)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};
