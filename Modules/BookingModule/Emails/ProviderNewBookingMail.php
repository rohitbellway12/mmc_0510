<?php

namespace Modules\BookingModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BookingModule\Entities\Booking;

class ProviderNewBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $bookingCode = $this->booking->readable_id ?? $this->booking->id;
        $subject = "New Booking Received #{$bookingCode}";

        return $this->subject($subject)
            ->view('bookingmodule::mail-templates.provider-new-booking', [
                'booking' => $this->booking
            ]);
    }
}
