<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "VSM PK11 API Documentation",
    description: "API de gestion des membres et finances du club VSM PK11"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Serveur local de développement"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Entrez votre jeton d'authentification Sanctum sous la forme: Bearer {token}"
)]
abstract class Controller
{
    //
}