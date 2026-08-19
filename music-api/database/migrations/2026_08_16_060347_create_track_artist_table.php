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
        Schema::create('track_artist', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->enum('role',['primary','featured','producer','composer','remixer']);
            $table->primary(['track_id', 'artist_id', 'role']);

           $table->index('artist_id', 'idx_track_artists_artist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_artist');
    }
};
