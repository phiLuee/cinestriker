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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating'); // 1 bis 5
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'movie_id']); // 1 Bewertung pro User pro Film
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
