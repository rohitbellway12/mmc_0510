<?php

namespace Modules\BidModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BidModule\Entities\PostBid;

class BidAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public PostBid $postBid;

    public function __construct(PostBid $postBid)
    {
        $this->postBid = $postBid;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $customerName = $this->postBid->post?->customer?->first_name . ' ' . $this->postBid->post?->customer?->last_name;
        $offeredPrice = with_currency_symbol($this->postBid->offered_price);
        $subject = "Quotation Offer Accepted ({$offeredPrice}) by {$customerName}";

        return $this->subject($subject)
            ->view('bidmodule::mail-templates.bid-accepted', [
                'postBid' => $this->postBid
            ]);
    }
}
