<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

class TestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/test-swagger",
     *     summary="Route de test Swagger",
     *     tags=["Test"],
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function test()
    {
        return response()->json(['message' => 'ok']);
    }
}