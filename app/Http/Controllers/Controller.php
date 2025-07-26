<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Reservations API - Documentation",
 * description="Detailed API description for the space reservation system, by José González.",
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="API Server"
 * )
 * * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * description="Enter token in format (Bearer <token>)"
 * )
 * * @OA\Schema(
 * schema="ValidationError",
 * type="object",
 * title="Validation Error",
 * properties={
 * @OA\Property(property="message", type="string", example="The given data was invalid."),
 * @OA\Property(property="errors", type="object")
 * }
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}