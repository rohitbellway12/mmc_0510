<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CarHire\Entities\CarBooking;
use Modules\CarHire\Entities\Car;
use Illuminate\Support\Facades\DB;

class AdminCarBookingController extends Controller
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

        $bookings = $this->carBooking->with(['car.provider', 'user'])
            ->when($bookingStatus != 'all', function ($query) use ($bookingStatus) {
                return $query->where('booking_status', $bookingStatus);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('id', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(pagination_limit());

        return view('carhire::admin.booking.list', compact('bookings', 'bookingStatus'));
    }

    /**
     * Show the specified resource.
     * @param string $id
     * @return Renderable
     */
    public function details(string $id): Renderable
    {
        $booking = $this->carBooking->with(['car.provider', 'user'])->findOrFail($id);

        return view('carhire::admin.booking.details', compact('booking'));
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

        $booking = $this->carBooking->findOrFail($id);
        $booking->booking_status = $request->booking_status;
        $booking->save();

        Toastr::success(translate('Booking status updated successfully'));
        return back();
    }
}
