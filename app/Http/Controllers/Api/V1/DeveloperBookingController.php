<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BookingController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeveloperBookingController extends Controller
{
    public function __construct(protected BookingController $bookingController)
    {
    }

    public function availability(Request $request, string $slug)
    {
        $request->headers->set('Accept', 'application/json');

        return $this->bookingController->getAvailableSlots($request, $slug);
    }

    public function createBooking(Request $request, string $slug)
    {
        $request->headers->set('Accept', 'application/json');

        return $this->bookingController->createBooking($request, $slug);
    }
}
