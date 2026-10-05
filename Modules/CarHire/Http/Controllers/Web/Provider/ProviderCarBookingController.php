<?php

namespace Modules\CarHire\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CarHire\Entities\CarBooking;
use Modules\CarHire\Entities\Car;
use Illuminate\Support\Facades\DB;

class ProviderCarBookingController extends Controller
{
    private CarBooking $carBooking;
    private Car $car;

    public function __construct(CarBooking $carBooking, Car $car)
    {
        $this->carBooking = $carBooking;
        $this->car = $car;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $bookingStatus = $request->input('booking_status', 'all');
        $search = $request->input('search');
        $providerId = auth()->user()->provider->id;

        $bookings = $this->carBooking->with(['car', 'user'])
            ->whereHas('car', function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->when($bookingStatus != 'all', function ($query) use ($bookingStatus) {
                return $query->where('booking_status', $bookingStatus);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('id', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(pagination_limit());

        return view('carhire::provider.booking.list', compact('bookings', 'bookingStatus'));
    }

    /**
     * Show the specified resource.
     * @param string $id
     * @return Renderable
     */
    public function details(string $id): Renderable
    {
        $providerId = auth()->user()->provider->id;
        $booking = $this->carBooking->with(['car', 'user'])
            ->whereHas('car', function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->findOrFail($id);

        return view('carhire::provider.booking.details', compact('booking'));
    }

    /**
     * Update the booking status.
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function statusUpdate(Request $request, string $id)
    {
        $request->validate([
            'booking_status' => 'required|in:pending,accepted,ongoing,completed,canceled',
        ]);

        $providerId = auth()->user()->provider->id;
        $booking = $this->carBooking->whereHas('car', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->findOrFail($id);

        $booking->booking_status = $request->booking_status;
        $booking->save();

        // Ensure transactions are placed on completion (extra safety).
        // Duplicate prevention is handled inside the transaction functions.
        if ($booking->booking_status === 'completed') {
            if ($booking->payment_method === 'cash_after_service') {
                $booking->is_paid = 1;
                $booking->payment_status = 'paid';
                $booking->save();
                placeCarBookingTransactionForCompletedCas($booking);
            } elseif ($booking->is_paid) {
                placeCarBookingTransactionForCompleted($booking);
            }
        }

        Toastr::success(translate('Booking status updated successfully'));
        return back();
    }
}
