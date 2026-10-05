<?php

namespace Modules\BookingModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingIgnore extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'provider_id',
        'reason',
    ];

    public function provider(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\ProviderManagement\Entities\Provider::class, 'provider_id');
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
    
    protected static function newFactory()
    {
        return \Modules\BookingModule\Database\factories\BookingIgnoreFactory::new();
    }
}
