<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Specialty\StoreSpecialtyRequest;
use App\Http\Requests\Specialty\UpdateSpecialtyRequest;
use App\Models\Specialty;
use App\Services\SpecialtyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Specialties", description="Medical specialty management")
 */
class SpecialtyController extends Controller
{
    use ApiResponse;

    private SpecialtyService $specialtyService;

    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }

    /**
     * @OA\Get(
     *     path="/api/specialties",
     *     tags={"Specialties"},
     *     summary="List all specialties",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of specialties")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('specialties.index');

        $specialties = $this->specialtyService->list(
            $request->only(['search', 'is_active']),
            (int) $request->get('per_page', 15)
        );

        return $this->paginated($specialties);
    }

    /**
     * @OA\Post(
     *     path="/api/specialties",
     *     tags={"Specialties"},
     *     summary="Create a specialty",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Neurology"),
     *             @OA\Property(property="description", type="string", example="Disorders of the nervous system."),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Specialty created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreSpecialtyRequest $request): JsonResponse
    {
        $specialty = $this->specialtyService->create($request->validated());

        return $this->created($specialty, 'Specialty created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/specialties/{id}",
     *     tags={"Specialties"},
     *     summary="Get a specialty by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Specialty details")
     * )
     */
    public function show(Specialty $specialty): JsonResponse
    {
        $this->authorize('specialties.show');

        return $this->success($this->specialtyService->findOrFail($specialty->id));
    }

    /**
     * @OA\Put(
     *     path="/api/specialties/{id}",
     *     tags={"Specialties"},
     *     summary="Update a specialty",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Specialty updated")
     * )
     */
    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): JsonResponse
    {
        $updated = $this->specialtyService->update($specialty, $request->validated());

        return $this->success($updated, 'Specialty updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/specialties/{id}",
     *     tags={"Specialties"},
     *     summary="Delete a specialty",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Specialty deleted")
     * )
     */
    public function destroy(Specialty $specialty): JsonResponse
    {
        $this->authorize('specialties.delete');

        $this->specialtyService->delete($specialty);

        return $this->noContent('Specialty deleted successfully');
    }
}
