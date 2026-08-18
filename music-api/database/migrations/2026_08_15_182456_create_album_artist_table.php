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
        Schema::create('album_artist', function (Blueprint $table) {
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['primary', 'featured', 'various'])->default('primary');
            $table->primary(['album_id', 'artist_id', 'role']);

            $table->index('artist_id', 'idx_album_artists_artist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('album_artist');
    }
};
