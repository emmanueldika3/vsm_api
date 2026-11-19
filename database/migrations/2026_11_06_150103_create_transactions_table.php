<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Lien vers l'obligation (la contribution concernée)
            $table->foreignId('contribution_id')
                  ->constrained('contributions')
                  ->onDelete('cascade');
            
            // Lien direct vers le membre (pour l'historique rapide)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Montant de la tranche versée
            $table->decimal('amount', 12, 2);
            
            // Mode de paiement (ex: 'cash', 'mobile_money', etc.)
            $table->string('payment_method')->default('cash');
            
            // Référence optionnelle (numéro de reçu ou transaction Mobile Money)
            $table->string('reference')->nullable();
            
            // ID du trésorier/administrateur qui a enregistré l'encaissement
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->foreign('executed_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};