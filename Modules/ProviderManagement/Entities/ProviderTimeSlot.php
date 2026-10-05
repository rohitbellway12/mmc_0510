<?php

namespace Modules\ProviderManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class ProviderTimeSlot extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'provider_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'max_bookings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_bookings' => 'integer',
    ];

    /**
     * Get the provider that owns the time slot
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /**
     * Get all slot bookings for this time slot
     */
    public function slotBookings(): HasMany
    {
        return $this->hasMany(\Modules\BookingModule\Entities\SlotBooking::class, 'provider_time_slot_id');
    }

    /**
     * Scope to get only active slots
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope to get slots for a specific day
     */
    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }

    /**
     * Scope to get slots for a provider
     */
    public function scopeOfProvider($query, string $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Check if slot is available for a specific date
     */
    public function isAvailableOn($date)
    {
        $bookedCount = $this->slotBookings()
            ->where('booking_date', $date)
            ->count();

        return $bookedCount < $this->max_bookings;
    }
}
