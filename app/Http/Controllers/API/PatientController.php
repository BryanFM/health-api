<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Models\Patient;
use App\Services\PatientService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Patients", description="Patient management endpoints")
 */
class PatientController extends Controller
{
    use ApiResponse;

    private PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * @OA\Get(
     *     path="/api/patients",
     *     tags={"Patients"},
     *     summary="List all patients",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="gender", in="query", @OA\Schema(type="string", enum={"male","female","other"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of patients")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('patients.index');

        $patients = $this->patientService->list(
            $request->only(['search', 'gender', 'blood_type']),
            (int) $request->get('per_page', 15)
        );

        return $this->paginated($patients);
    }

    /**
     * @OA\Post(
     *     path="/api/patients",
     *     tags={"Patients"},
     *     summary="Create a new patient",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","date_of_birth","gender"},
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="phone", type="string", example="555-0100"),
     *             @OA\Property(property="password", type="string", example="Password1"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1990-06-15"),
     *             @OA\Property(property="gender", type="string", enum={"male","female","other"}, example="female"),
     *             @OA\Property(property="blood_type", type="string", enum={"A+","A-","B+","B-","AB+","AB-","O+","O-"}, example="O+"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="emergency_contact_name", type="string", example="John Doe"),
     *             @OA\Property(property="emergency_contact_phone", type="string", example="555-0199"),
     *             @OA\Property(property="allergies", type="string", example="Penicillin")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Patient created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = $this->patientService->create($request->validated());

        return $this->created($patient->load('user'), 'Patient created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/patients/{id}",
     *     tags={"Patients"},
     *     summary="Get a patient by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Patient details"),
     *     @OA\Response(response=404, description="Patient not found")
     * )
     */
    public function show(Patient $patient): JsonResponse
    {
        $this->authorize('patients.show');

        return $this->success($this->patientService->findOrFail($patient->id));
    }

    /**
     * @OA\Put(
     *     path="/api/patients/{id}",
     *     tags={"Patients"},
     *     summary="Update a patient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="date_of_birth", type="string", format="date"),
     *             @OA\Property(property="gender", type="string", enum={"male","female","other"}),
     *             @OA\Property(property="blood_type", type="string", enum={"A+","A-","B+","B-","AB+","AB-","O+","O-"}),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="emergency_contact_name", type="string"),
     *             @OA\Property(property="emergency_contact_phone", type="string"),
     *             @OA\Property(property="allergies", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Patient updated")
     * )
     */
    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        $updated = $this->patientService->update($patient, $request->validated());

        return $this->success($updated, 'Patient updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/patients/{id}",
     *     tags={"Patients"},
     *     summary="Delete a patient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Patient deleted")
     * )
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $this->authorize('patients.delete');

        $this->patientService->delete($patient);

        return $this->noContent('Patient deleted successfully');
    }
}
