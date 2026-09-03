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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            
            // Rôle dans le club (admin, president, coach, treasurer, player)
            $table->enum('role', ['admin', 'president', 'coach', 'treasurer', 'player'])->default('player');
            
            // Statut du compte (pending, active, rejected, suspended)
            $table->string('status')->default('pending');
            $table->boolean('is_active')->default(false);
            
            // Profil sportif sur le terrain
            $table->string('position')->nullable(); // ex: Gardien, Défenseur, Milieu, Attaquant
            $table->unsignedSmallInteger('jersey_number')->nullable();
            $table->string('photo_url')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};