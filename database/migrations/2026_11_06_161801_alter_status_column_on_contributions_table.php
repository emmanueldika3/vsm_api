<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            // On élargit ou transforme la colonne status en string pour accepter 'unpaid', 'partial', 'paid', etc.
            $table->string('status', 20)->default('unpaid')->change();
        });
    }

    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            // Retour en arrière si besoin
            $table->string('status', 10)->default('pending')->change();
        });
    }
};