<?php

namespace Modules\BookingModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class SlotBooking extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'booking_id',
        'provider_time_slot_id',
        'booking_date'
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    /**
     * Get the booking that owns the slot booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * Get the time slot for this booking
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(\Modules\ProviderManagement\Entities\ProviderTimeSlot::class, 'provider_time_slot_id');
    }
}
