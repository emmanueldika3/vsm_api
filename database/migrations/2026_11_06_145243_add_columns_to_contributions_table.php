<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            // 1. Liaison avec la saison
            $table->foreignId('season_id')
                  ->nullable() // Mets nullable au cas où tu as déjà des lignes existantes sans saison
                  ->after('user_id')
                  ->constrained('seasons')
                  ->onDelete('cascade');

            // 2. Libellé de la contribution (ex: "Cotisation Annuelle", "Frais de Réinscription")
            $table->string('title')->after('season_id')->default('Cotisation');

            // 3. Montant total attendu (si tu veux le différencier ou le compléter avec ton champ 'amount')
            $table->decimal('amount_expected', 12, 2)->after('title')->default(0.00);

            // 4. Montant cumulé déjà payé (essentiel pour suivre les tranches/transactions)
            $table->decimal('amount_paid', 12, 2)->after('amount_expected')->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropForeign(['season_id']);
            $table->dropColumn(['season_id', 'title', 'amount_expected', 'amount_paid']);
        });
    }
};