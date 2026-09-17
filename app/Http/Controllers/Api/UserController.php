<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/users",
        summary: "Liste des membres VSM (avec filtres et compteur)",
        security: [["bearerAuth" => []]],
        tags: ["Membres"],
        parameters: [
            new OA\Parameter(
                name: "is_active",
                in: "query",
                required: false,
                description: "Filtrer les membres par statut d'activité (true pour membres actifs)",
                schema: new OA\Schema(type: "boolean", example: true)
            ),
            new OA\Parameter(
                name: "role",
                in: "query",
                required: false,
                description: "Filtrer par rôle (president, admin, treasurer, coach, player)",
                schema: new OA\Schema(type: "string", example: "player")
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                required: false,
                description: "Rechercher par nom, email ou téléphone",
                schema: new OA\Schema(type: "string", example: "Dika")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des utilisateurs récupérée avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Emmanuel Dika"),
                                    new OA\Property(property: "email", type: "string", example: "admin@vsm.com"),
                                    new OA\Property(property: "phone", type: "string", example: "690000000"),
                                    new OA\Property(property: "role", type: "string", example: "president"),
                                    new OA\Property(property: "position", type: "string", example: "Milieu"),
                                    new OA\Property(property: "jersey_number", type: "integer", example: 10),
                                    new OA\Property(property: "is_active", type: "boolean", example: true),
                                    new OA\Property(property: "photo_url", type: "string", nullable: true)
                                ]
                            )
                        ),
                        new OA\Property(property: "count", type: "integer", example: 8)
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filtre par statut d'activité (ex: ?is_active=true)
        if ($request->has('is_active')) {
            $isActive = filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        // Filtre par rôle (ex: ?role=player)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Recherche textuelle (ex: ?search=Dika)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $users,
            'count'  => $users->count()
        ], 200);
    }

    #[OA\Get(
        path: "/users/{id}",
        summary: "Détails d'un membre",
        security: [["bearerAuth" => []]],
        tags: ["Membres"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du membre",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Membre trouvé",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Emmanuel Dika"),
                                new OA\Property(property: "email", type: "string", example: "dika@vsm.com"),
                                new OA\Property(property: "phone", type: "string", example: "+237600000000"),
                                new OA\Property(property: "role", type: "string", example: "player"),
                                new OA\Property(property: "jersey_number", type: "integer", nullable: true, example: 10),
                                new OA\Property(property: "position", type: "string", nullable: true, example: "Attaquant"),
                                new OA\Property(property: "is_active", type: "boolean", example: true),
                                new OA\Property(property: "photo_url", type: "string", nullable: true, example: "https://vsm.com/storage/avatars/avatar.jpg")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Membre introuvable")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Membre introuvable'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    #[OA\Post(
        path: "/users",
        summary: "Créer un membre (Admin)",
        security: [["bearerAuth" => []]],
        tags: ["Membres"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "role"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Samuel Etoo"),
                    new OA\Property(property: "email", type: "string", example: "etoo@vsm.com"),
                    new OA\Property(property: "phone", type: "string", nullable: true, example: "+237690000000"),
                    new OA\Property(property: "password", type: "string", example: "Secret123!"),
                    new OA\Property(property: "role", type: "string", enum: ["admin", "treasurer", "coach", "player", "president"], example: "player"),
                    new OA\Property(property: "jersey_number", type: "integer", nullable: true, example: 9),
                    new OA\Property(property: "position", type: "string", nullable: true, example: "Attaquant")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Membre créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Nouveau membre créé avec succès."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 2),
                                new OA\Property(property: "name", type: "string", example: "Samuel Etoo"),
                                new OA\Property(property: "email", type: "string", example: "etoo@vsm.com"),
                                new OA\Property(property: "role", type: "string", example: "player"),
                                new OA\Property(property: "is_active", type: "boolean", example: true)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 422, description: "Données invalides")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,treasurer,coach,player,president',
            'jersey_number' => 'nullable|integer',
            'position' => 'nullable|string|max:100',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $user = User::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Nouveau membre créé avec succès.',
            'data' => $user
        ], 201);
    }

    #[OA\Put(
        path: "/users/{id}",
        summary: "Mettre à jour un membre",
        security: [["bearerAuth" => []]],
        tags: ["Membres"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Emmanuel Dika"),
                    new OA\Property(property: "email", type: "string", example: "dika@vsm.com"),
                    new OA\Property(property: "phone", type: "string", nullable: true, example: "+237699999999"),
                    new OA\Property(property: "password", type: "string", nullable: true, example: "NewPassword123!"),
                    new OA\Property(property: "role", type: "string", enum: ["admin", "treasurer", "coach", "player", "president"], nullable: true, example: "player"),
                    new OA\Property(property: "jersey_number", type: "integer", nullable: true, example: 10),
                    new OA\Property(property: "position", type: "string", nullable: true, example: "Milieu"),
                    new OA\Property(property: "is_active", type: "boolean", nullable: true, example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Membre mis à jour",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Informations du membre mises à jour."),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Membre introuvable"),
            new OA\Response(response: 422, description: "Données de mise à jour invalides")
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Membre introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
            'role' => 'nullable|string|in:admin,treasurer,coach,player,president',
            'jersey_number' => 'nullable|integer',
            'position' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Informations du membre mises à jour.',
            'data' => $user
        ]);
    }

    #[OA\Delete(
        path: "/users/{id}",
        summary: "Supprimer / Désactiver un membre",
        security: [["bearerAuth" => []]],
        tags: ["Membres"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Membre supprimé",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Membre supprimé du club avec succès.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 404, description: "Membre introuvable")
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Membre introuvable'
            ], 404);
        }

        if ($user->photo_url) {
            $path = parse_url($user->photo_url, PHP_URL_PATH);
            $relativePath = str_replace('/storage/', '', $path);
            Storage::disk('public')->delete($relativePath);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Membre supprimé du club avec succès.'
        ]);
    }

    #[OA\Post(
        path: "/user/photo",
        summary: "Mise à jour de la photo de profil du membre connecté",
        security: [["bearerAuth" => []]],
        tags: ["Membres"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["photo"],
                    properties: [
                        new OA\Property(property: "photo", type: "string", format: "binary", description: "Fichier image (JPG, PNG, WEBP, max 2Mo)")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Photo mise à jour",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Photo de profil mise à jour avec succès."),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 422, description: "Fichier invalide")
        ]
    )]
    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('photo')) {
            if ($user->photo_url) {
                $oldPath = parse_url($user->photo_url, PHP_URL_PATH);
                $relativeOldPath = str_replace('/storage/', '', $oldPath);
                Storage::disk('public')->delete($relativeOldPath);
            }

            $path = $request->file('photo')->store('avatars', 'public');
            $user->photo_url = asset('storage/' . $path);
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Photo de profil mise à jour avec succès.',
            'data' => $user,
        ]);
    }
}