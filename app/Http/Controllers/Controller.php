<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Health API",
 *     version="1.0.0",
 *     description="REST API for a Health Management System. Includes authentication, patient management, doctors, appointments, and clinical history.",
 *     @OA\Contact(email="admin@healthapi.com"),
 *     @OA\License(name="MIT")
 * )
 *
 * @OA\Server(url="http://127.0.0.1:8000", description="Local server")
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Sanctum token"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
