<?php

namespace Modules\BookingModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ServiceManagement\Entities\Service;

class BookingRepeatDetails extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = ['booking_repeat_id', 'booking_id', 'service_id', 'tyre_id', 'service_name', 'variant_key', 'quantity', 'service_cost', 'discount_amount', 'campaign_discount_amount', 'overall_coupon_discount_amount', 'tax_amount', 'total_cost'];

    protected static function newFactory()
    {
        return \Modules\BookingModule\Database\factories\BookingRepeatDetailsFactory::new();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function tyre(): BelongsTo
    {
        return $this->belongsTo(\Modules\ServiceManagement\Entities\Tyre::class, 'tyre_id');
    }

    public function repeat(): BelongsTo
    {
        return $this->belongsTo(BookingRepeat::class, 'booking_repeat_id');
    }
}
