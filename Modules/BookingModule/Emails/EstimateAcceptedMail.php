<?php

namespace Modules\BookingModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BookingModule\Entities\BookingEstimate;

class EstimateAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public BookingEstimate $estimate;

    public function __construct(BookingEstimate $estimate)
    {
        $this->estimate = $estimate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $estimateId = $this->estimate->readable_id ?? $this->estimate->id;
        $customerName = $this->estimate->customer_name;
        $subject = "Quotation #{$estimateId} Accepted by Customer {$customerName}";

        return $this->subject($subject)
            ->view('bookingmodule::mail-templates.estimate-accepted', [
                'estimate' => $this->estimate
            ]);
    }
}
