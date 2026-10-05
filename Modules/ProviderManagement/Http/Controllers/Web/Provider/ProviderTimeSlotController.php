<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Entities\ProviderTimeSlot;

class ProviderTimeSlotController extends Controller
{
    protected ProviderTimeSlot $timeSlot;

    public function __construct(ProviderTimeSlot $timeSlot)
    {
        $this->timeSlot = $timeSlot;
    }

    /**
     * Display listing of provider's time slots
     */
    public function index(Request $request): View|Factory|Application
    {
        $search = $request->has('search') ? $request['search'] : '';
        $providerId = $request->user()->provider->id;

        $timeSlots = $this->timeSlot
            ->where('provider_id', $providerId)
            ->when($search, function ($query) use ($search) {
                $query->where('day_of_week', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(10);

        return view('providermanagement::provider.time-slots', compact('timeSlots', 'search'));
    }

    /**
     * Store a new time slot
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_bookings' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        try {
            $providerId = $request->user()->provider->id;

            // Check for duplicate
            $exists = $this->timeSlot
                ->where('provider_id', $providerId)
                ->where('day_of_week', $request->day_of_week)
                ->where('start_time', $request->start_time)
                ->where('end_time', $request->end_time)
                ->exists();

            if ($exists) {
                return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'duplicate', 'message' => translate('Time slot already exists')]]), 400);
            }

            $this->timeSlot->create([
                'provider_id' => $providerId,
                'day_of_week' => $request->day_of_week,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'max_bookings' => $request->max_bookings,
                'is_active' => 1,
            ]);

            return response()->json(response_formatter(DEFAULT_STORE_200), 200);
        } catch (\Exception $e) {
            return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'exception', 'message' => $e->getMessage()]]), 400);
        }
    }

    /**
     * Update time slot status
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|uuid',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->user()->provider->id;
        $timeSlot = $this->timeSlot->where('id', $request->id)->where('provider_id', $providerId)->first();

        if (!$timeSlot) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $timeSlot->is_active = $request->status;
        $timeSlot->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Delete time slot
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $providerId = $request->user()->provider->id;
        $timeSlot = $this->timeSlot->where('id', $id)->where('provider_id', $providerId)->first();

        if (!$timeSlot) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $timeSlot->delete();
        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }
}
