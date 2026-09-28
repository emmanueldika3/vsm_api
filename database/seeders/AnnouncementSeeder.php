<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un utilisateur administrateur ou le premier utilisateur disponible
        $author = User::first() ?? User::factory()->create([
            'name' => 'Admin Club',
            'email' => 'admin@club.com',
        ]);

        $announcements = [
            [
                'author_id' => $author->id,
                'title' => 'Changement d’horaire pour l’entraînement de ce mercredi',
                'content' => 'En raison des conditions météo prévues en soirée, la séance d’entraînement de ce mercredi est avancée à 18h00 sur le terrain synthétique. Merci d’être ponctuels.',
                'category' => 'training',
                'target_audience' => 'all',
                'is_urgent' => false,
                'created_at' => now()->subDays(5),
            ],
            [
                'author_id' => $author->id,
                'title' => 'Convocation Match Amical - Dimanche 15h00',
                'content' => 'Rassemblement général au stade à 14h00 pour le match amical contre l’équipe Vétérans B. N’oubliez pas vos équipements complets (maillots, protège-tibias et gourdes).',
                'category' => 'match',
                'target_audience' => 'all',
                'is_urgent' => false,
                'created_at' => now()->subDays(3),
            ],
            [
                'author_id' => $author->id,
                'title' => 'Ordre du jour : Réunion extraordinaire du Bureau',
                'content' => 'Une réunion restreinte du bureau aura lieu ce vendredi à 19h00 dans la salle de réunion du complexe. Sujets : Bilan financier du mois et cotisations en retard.',
                'category' => 'meeting',
                'target_audience' => 'board',
                'is_urgent' => false,
                'created_at' => now()->subDays(2),
            ],
            [
                'author_id' => $author->id,
                'title' => 'URGENT : Annulation du match de cet après-midi !',
                'content' => 'Suite à un arrêté municipal de dernière minute fermant l’accès aux pelouses, la rencontre de ce jour est officielle reportée. Merci d’informer rapidement vos coéquipiers.',
                'category' => 'match',
                'target_audience' => 'all',
                'is_urgent' => true,
                'created_at' => now()->subHours(4),
            ],
            [
                'author_id' => $author->id,
                'title' => 'Renouvellement des licences et cotisations annuelles',
                'content' => 'Pensez à régulariser vos dossiers de licence auprès du secrétariat avant la fin de la semaine. Tout dossier incomplet ne permettra pas d’être inscrit sur les feuilles de match.',
                'category' => 'general',
                'target_audience' => 'all',
                'is_urgent' => false,
                'created_at' => now()->subHours(1),
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }
    }
}