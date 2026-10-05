<?php

namespace Modules\BidModule\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BidModule\Entities\PostBid;

class BidOfferMail extends Mailable
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
        $providerName = $this->postBid->provider?->company_name ?? 'Service Provider';
        $offeredPrice = with_currency_symbol($this->postBid->offered_price);
        $subject = "New Quotation Offer ({$offeredPrice}) from {$providerName}";

        return $this->subject($subject)
            ->view('bidmodule::mail-templates.bid-offer', [
                'postBid' => $this->postBid
            ]);
    }
}
