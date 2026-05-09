<?php

use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\MedicalRecordController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\SpecialtyController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::apiResource('patients', PatientController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('specialties', SpecialtyController::class);

    // Appointments
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');
    Route::apiResource('appointments', AppointmentController::class);

    // Medical Records
    Route::get('patients/{patient}/medical-records', [MedicalRecordController::class, 'indexForPatient'])
        ->name('patients.medical-records.index');
    Route::apiResource('medical-records', MedicalRecordController::class);
});
