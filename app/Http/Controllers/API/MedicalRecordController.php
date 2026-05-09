<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalRecord\StoreMedicalRecordRequest;
use App\Http\Requests\MedicalRecord\UpdateMedicalRecordRequest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\MedicalRecordService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Medical Records", description="Clinical history management")
 */
class MedicalRecordController extends Controller
{
    use ApiResponse;

    private MedicalRecordService $medicalRecordService;

    public function __construct(MedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }

    /**
     * @OA\Get(
     *     path="/api/medical-records",
     *     tags={"Medical Records"},
     *     summary="List medical records",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="patient_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="doctor_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="record_type", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of medical records")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('medical-records.index');

        $records = $this->medicalRecordService->list(
            $request->only(['patient_id', 'doctor_id', 'record_type']),
            (int) $request->get('per_page', 15),
            $request->user()
        );

        return $this->paginated($records);
    }

    /**
     * @OA\Get(
     *     path="/api/patients/{patient}/medical-records",
     *     tags={"Medical Records"},
     *     summary="List medical records for a specific patient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Patient medical records")
     * )
     */
    public function indexForPatient(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('medical-records.index');

        $records = $this->medicalRecordService->listForPatient(
            $patient->id,
            $request->only(['record_type']),
            (int) $request->get('per_page', 15),
            $request->user()
        );

        return $this->paginated($records);
    }

    /**
     * @OA\Post(
     *     path="/api/medical-records",
     *     tags={"Medical Records"},
     *     summary="Create a medical record",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id","doctor_id","diagnosis","treatment"},
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="doctor_id", type="integer", example=1),
     *             @OA\Property(property="appointment_id", type="integer", example=1),
     *             @OA\Property(property="diagnosis", type="string", example="Hypertension stage 1"),
     *             @OA\Property(property="treatment", type="string", example="Lifestyle changes and low-sodium diet"),
     *             @OA\Property(property="prescription", type="string", example="Lisinopril 10mg daily"),
     *             @OA\Property(property="notes", type="string", example="Follow-up in 3 months"),
     *             @OA\Property(property="record_type", type="string", enum={"consultation","lab_result","imaging","surgery","follow_up","other"}, example="consultation"),
     *             @OA\Property(property="recorded_at", type="string", format="date-time", example="2026-05-09 09:00:00")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Medical record created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreMedicalRecordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['recorded_at'] = $data['recorded_at'] ?? now();

        $record = $this->medicalRecordService->create($data);

        return $this->created(
            $record->load(['patient.user', 'doctor.user', 'appointment']),
            'Medical record created successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/medical-records/{id}",
     *     tags={"Medical Records"},
     *     summary="Get a medical record by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Medical record details")
     * )
     */
    public function show(Request $request, MedicalRecord $medicalRecord): JsonResponse
    {
        $this->authorize('medical-records.show');

        return $this->success($this->medicalRecordService->findOrFail($medicalRecord->id, $request->user()));
    }

    /**
     * @OA\Put(
     *     path="/api/medical-records/{id}",
     *     tags={"Medical Records"},
     *     summary="Update a medical record",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="diagnosis", type="string"),
     *             @OA\Property(property="treatment", type="string"),
     *             @OA\Property(property="prescription", type="string"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="record_type", type="string", enum={"consultation","lab_result","imaging","surgery","follow_up","other"}),
     *             @OA\Property(property="recorded_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Medical record updated")
     * )
     */
    public function update(UpdateMedicalRecordRequest $request, MedicalRecord $medicalRecord): JsonResponse
    {
        $updated = $this->medicalRecordService->update($medicalRecord, $request->validated());

        return $this->success($updated, 'Medical record updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/medical-records/{id}",
     *     tags={"Medical Records"},
     *     summary="Delete a medical record",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Medical record deleted")
     * )
     */
    public function destroy(MedicalRecord $medicalRecord): JsonResponse
    {
        $this->authorize('medical-records.delete');

        $this->medicalRecordService->delete($medicalRecord);

        return $this->noContent('Medical record deleted successfully');
    }
}
