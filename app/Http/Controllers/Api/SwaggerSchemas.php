<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserModel",
    title: "User Model",
    description: "Schéma OpenAPI pour l'utilisateur VSM"
)]
class SwaggerSchemas
{
    #[OA\Property(property: "id", type: "integer", example: 1)]
    public int $id;

    #[OA\Property(property: "name", type: "string", example: "Membre VSM")]
    public string $name;

    #[OA\Property(property: "email", type: "string", example: "membre@vsm.cm")]
    public string $email;

    #[OA\Property(property: "role", type: "string", example: "president")]
    public string $role;
}