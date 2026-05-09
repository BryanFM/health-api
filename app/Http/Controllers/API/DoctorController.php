<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Services\DoctorService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Doctors", description="Doctor management endpoints")
 */
class DoctorController extends Controller
{
    use ApiResponse;

    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * @OA\Get(
     *     path="/api/doctors",
     *     tags={"Doctors"},
     *     summary="List all doctors",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="specialty_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="is_available", in="query", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="List of doctors")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('doctors.index');

        $doctors = $this->doctorService->list(
            $request->only(['search', 'specialty_id', 'is_available']),
            (int) $request->get('per_page', 15)
        );

        return $this->paginated($doctors);
    }

    /**
     * @OA\Post(
     *     path="/api/doctors",
     *     tags={"Doctors"},
     *     summary="Create a new doctor",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","specialty_id","license_number"},
     *             @OA\Property(property="name", type="string", example="Dr. Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="smith@example.com"),
     *             @OA\Property(property="phone", type="string", example="555-0200"),
     *             @OA\Property(property="password", type="string", example="Password1"),
     *             @OA\Property(property="specialty_id", type="integer", example=1),
     *             @OA\Property(property="license_number", type="string", example="MD-00123"),
     *             @OA\Property(property="experience_years", type="integer", example=8),
     *             @OA\Property(property="bio", type="string", example="Specialist in general medicine."),
     *             @OA\Property(property="is_available", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Doctor created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $doctor = $this->doctorService->create($request->validated());

        return $this->created($doctor->load(['user', 'specialty']), 'Doctor created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/doctors/{id}",
     *     tags={"Doctors"},
     *     summary="Get a doctor by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Doctor details")
     * )
     */
    public function show(Doctor $doctor): JsonResponse
    {
        $this->authorize('doctors.show');

        return $this->success($this->doctorService->findOrFail($doctor->id));
    }

    /**
     * @OA\Put(
     *     path="/api/doctors/{id}",
     *     tags={"Doctors"},
     *     summary="Update a doctor",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="specialty_id", type="integer"),
     *             @OA\Property(property="license_number", type="string"),
     *             @OA\Property(property="experience_years", type="integer"),
     *             @OA\Property(property="bio", type="string"),
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Doctor updated")
     * )
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        $updated = $this->doctorService->update($doctor, $request->validated());

        return $this->success($updated, 'Doctor updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/doctors/{id}",
     *     tags={"Doctors"},
     *     summary="Delete a doctor",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Doctor deleted")
     * )
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        $this->authorize('doctors.delete');

        $this->doctorService->delete($doctor);

        return $this->noContent('Doctor deleted successfully');
    }
}
