<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MemberController extends Controller
{
    #[OA\Get(
        path: '/api/member/profile',
        summary: 'Récupère le profil du membre connecté',
        description: 'Retourne les informations nécessaires pour le composant MemberProfileCard de l\'application mobile.',
        security: [['sanctum' => []]],
        tags: ['Membre & Profil'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil du membre récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'full_name', type: 'string', example: 'Emmanuel Dika'),
                                new OA\Property(property: 'position', type: 'string', example: 'Milieu Offensif'),
                                new OA\Property(property: 'role_title', type: 'string', example: 'Administrateur'),
                                new OA\Property(property: 'photo_url', type: 'string', example: 'https://vsm-pk11.cm/storage/avatars/dika.jpg', nullable: true)
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié')
        ]
    )]
    public function profile(Request $request)
    {
        // ...
    }
}