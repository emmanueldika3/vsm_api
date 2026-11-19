<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matchsitems', function (Blueprint $table) {
            // Ajoute la clé étrangère optionnelle vers la table events
            $table->foreignId('event_id')->nullable()->after('season_id')->constrained('events')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('matchsitems', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};