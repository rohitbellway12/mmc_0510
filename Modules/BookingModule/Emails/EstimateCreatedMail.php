<?php

namespace Modules\BookingModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BookingModule\Entities\BookingEstimate;

class EstimateCreatedMail extends Mailable
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
        $providerName = $this->estimate->provider?->company_name ?? 'Service Provider';
        $subject = "New Quotation / Estimate #{$estimateId} from {$providerName}";

        return $this->subject($subject)
            ->view('bookingmodule::mail-templates.estimate-created', [
                'estimate' => $this->estimate
            ]);
    }
}
