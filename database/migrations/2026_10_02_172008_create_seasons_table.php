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
        Schema::create('seasons', function (Blueprint $table) {
           $table->id();
            $table->string('name'); // Ex: "Saison 2026-2027"
            $table->decimal('adhesion_fee', 10, 2)->default(3000.00); // Montant adhésion
            $table->decimal('annual_fee', 10, 2)->default(24000.00); // Montant cotisation annuelle
            $table->boolean('is_active')->default(false); // Saison en cours ou non
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
