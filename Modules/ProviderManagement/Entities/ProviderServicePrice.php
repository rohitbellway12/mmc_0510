<?php

namespace Modules\ProviderManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ZoneManagement\Entities\Zone;

class ProviderServicePrice extends Model
{
    use HasFactory, HasUuid;

    protected $casts = [
        'price' => 'float',
        'is_active' => 'integer'
    ];

    protected $fillable = [
        'provider_id',
        'service_id',
        'variation_id',
        'zone_id',
        'price',
        'is_active'
    ];

    /**
     * Scope to filter active prices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope to filter by provider
     */
    public function scopeOfProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope to filter by service
     */
    public function scopeOfService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope to filter by zone
     */
    public function scopeOfZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Relationship with Provider
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /**
     * Relationship with Service
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Relationship with Variation
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(Variation::class, 'variation_id');
    }

    /**
     * Relationship with Zone
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
}
