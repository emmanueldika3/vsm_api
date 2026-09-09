<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Vérifier si le mode maintenance est activé
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Charger l'autoloader de Composer
require __DIR__.'/../vendor/autoload.php';

// Bootstrap de l'application Laravel et traitement de la requête
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());