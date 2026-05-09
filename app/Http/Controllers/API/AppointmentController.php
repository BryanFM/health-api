<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Appointments", description="Appointment management endpoints")
 */
class AppointmentController extends Controller
{
    use ApiResponse;

    private AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * @OA\Get(
     *     path="/api/appointments",
     *     tags={"Appointments"},
     *     summary="List appointments",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="patient_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="doctor_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="List of appointments")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('appointments.index');

        $appointments = $this->appointmentService->list(
            $request->only(['patient_id', 'doctor_id', 'status', 'date', 'upcoming']),
            (int) $request->get('per_page', 15),
            $request->user()
        );

        return $this->paginated($appointments);
    }

    /**
     * @OA\Post(
     *     path="/api/appointments",
     *     tags={"Appointments"},
     *     summary="Create an appointment",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id","doctor_id","scheduled_at","reason"},
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="doctor_id", type="integer", example=1),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time", example="2026-06-01 10:00:00"),
     *             @OA\Property(property="duration_minutes", type="integer", enum={15,30,45,60}, example=30),
     *             @OA\Property(property="reason", type="string", example="Routine check-up"),
     *             @OA\Property(property="notes", type="string", example="Patient has history of hypertension")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Appointment created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->appointmentService->create($request->validated());

        return $this->created(
            $appointment->load(['patient.user', 'doctor.user', 'doctor.specialty']),
            'Appointment created successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/appointments/{id}",
     *     tags={"Appointments"},
     *     summary="Get an appointment by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Appointment details")
     * )
     */
    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('appointments.show');

        return $this->success($this->appointmentService->findOrFail($appointment->id, $request->user()));
    }

    /**
     * @OA\Put(
     *     path="/api/appointments/{id}",
     *     tags={"Appointments"},
     *     summary="Update an appointment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="scheduled_at", type="string", format="date-time", example="2026-06-01 11:00:00"),
     *             @OA\Property(property="duration_minutes", type="integer", enum={15,30,45,60}),
     *             @OA\Property(property="status", type="string", enum={"pending","confirmed","completed","no_show"}),
     *             @OA\Property(property="reason", type="string"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Appointment updated")
     * )
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $updated = $this->appointmentService->update($appointment, $request->validated());

        return $this->success($updated, 'Appointment updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/appointments/{id}/cancel",
     *     tags={"Appointments"},
     *     summary="Cancel an appointment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cancellation_reason"},
     *             @OA\Property(property="cancellation_reason", type="string", example="Doctor unavailable")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Appointment cancelled")
     * )
     */
    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $cancelled = $this->appointmentService->cancel($appointment, $request->validated()['cancellation_reason']);

        return $this->success($cancelled, 'Appointment cancelled successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/appointments/{id}",
     *     tags={"Appointments"},
     *     summary="Delete an appointment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Appointment deleted")
     * )
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorize('appointments.delete');

        $this->appointmentService->delete($appointment);

        return $this->noContent('Appointment deleted successfully');
    }
}
