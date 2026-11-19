<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchsitems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained('seasons')->onDelete('set null');
            $table->enum('type', ['match', 'training'])->default('match');
            $table->string('opponent')->nullable();
            $table->dateTime('match_date');
            $table->integer('vsm_score')->default(0);
            $table->integer('opponent_score')->default(0);
            $table->string('location')->default('Stade Yoro (PK11)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchsitems');
    }
};