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
    
    // Rôle dans le club
    $table->enum('role', ['admin', 'president', 'coach', 'treasurer', 'player'])->default('player');
    
    // Statut unique du compte
    $table->string('status')->default('pending'); // 'pending', 'active', 'rejected', 'suspended'
    
    // Profil sportif
    $table->string('position')->nullable();
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