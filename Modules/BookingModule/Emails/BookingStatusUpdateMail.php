<?php

namespace Modules\BookingModule\Emails;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BookingModule\Entities\Booking;

class BookingStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $bookingStatus;

    public function __construct(Booking $booking, string $status = '')
    {
        $this->booking = $booking;
        $this->bookingStatus = !empty($status) ? $status : ($booking->booking_status ?? 'updated');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $readableId = $this->booking->readable_id ?? $this->booking->id;
        $statusLabels = [
            'pending' => 'Pending Confirmation',
            'accepted' => 'Accepted',
            'ongoing' => 'In Progress (Ongoing)',
            'completed' => 'Completed',
            'canceled' => 'Canceled',
        ];
        $statusLabel = $statusLabels[$this->bookingStatus] ?? ucfirst($this->bookingStatus);

        $subject = "Booking #{$readableId} Update: {$statusLabel}";

        $mail = $this->subject($subject)
            ->view('bookingmodule::mail-templates.booking-status-updated', [
                'booking' => $this->booking,
                'status' => $this->bookingStatus,
                'statusLabel' => $statusLabel
            ]);

        // Attach invoice if booking is completed or accepted
        try {
            $bookingWithDetails = Booking::with([
                'detail.service' => fn($q) => $q->withTrashed(),
                'detail.variation',
                'customer',
                'provider.owner',
                'serviceman.user'
            ])->find($this->booking->id) ?? $this->booking;

            $pdf = PDF::loadView('bookingmodule::admin.booking.invoice', ['booking' => $bookingWithDetails]);
            if ($pdf) {
                $mail->attachData($pdf->output(), "Booking-Invoice-#{$readableId}.pdf");
            }
        } catch (\Exception $e) {
            info("PDF Invoice generation skipped for email: " . $e->getMessage());
        }

        return $mail;
    }
}
