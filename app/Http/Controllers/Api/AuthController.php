<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Connexion de l'utilisateur / membre VSM
     */
    #[OA\Post(
        path: "/api/login",
        summary: "Connexion d'un utilisateur",
        tags: ["Authentification"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["phone", "password"],
                properties: [
                    new OA\Property(property: "phone", type: "string", example: "+237690000000"),
                    new OA\Property(property: "password", type: "string", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "1|laravel_sanctum_token..."),
                        new OA\Property(property: "user", ref: "#/components/schemas/UserModel")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Identifiants invalides")
        ]
    )]
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $credentials['phone'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
    #[OA\Get(
        path: '/api/user/role',
        summary: 'Récupère le rôle et les libellés de l\'utilisateur connecté',
        description: 'Retourne le rôle principal de l\'utilisateur authentifié ainsi que les titres d\'en-tête associés.',
        security: [['sanctum' => []]],
        tags: ['Authentification & Profil'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Rôle récupéré avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'role', type: 'string', example: 'admin'),
                                new OA\Property(property: 'role_label', type: 'string', example: 'Administration Générale'),
                                new OA\Property(property: 'sub_title', type: 'string', example: 'Bureau Exécutif • PK11')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Non authentifié (Token Sanctum manquant ou invalide)'
            )
        ]
    )]
    public function userRole(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'role' => $user->role, // Ex: 'admin', 'coach', 'treasurer', 'player'
                'role_label' => match($user->role) {
                    'admin' => 'Administration Générale',
                    'coach' => 'Direction Technique',
                    'treasurer' => 'Trésorerie Générale',
                    default => 'Espace Membre',
                },
                'sub_title' => 'Bureau Exécutif • PK11',
            ],
        ]);
    }

    /**
     * Déconnexion de l'utilisateur
     */
    #[OA\Post(
        path: "/api/logout",
        summary: "Déconnexion de l'utilisateur",
        security: [["sanctum" => []]],
        tags: ["Authentification"],
        responses: [
            new OA\Response(response: 200, description: "Déconnexion réussie")
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }
}