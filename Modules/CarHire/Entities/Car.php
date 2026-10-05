<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CategoryManagement\Entities\Category;

/**
 * Modules\CarHire\Entities\Car
 *
 * @property int $id
 * @property int $provider_id
 * @property int $category_id
 * @property int $car_type_id
 * @property string $service_category
 * @property string $brand
 * @property string $model
 * @property string $year
 * @property string $manufacture_year
 * @property string $registration_number
 * @property string $fuel_type
 * @property string $transmission_type
 * @property string $transmission
 * @property int $seating_capacity
 * @property int $air_conditioning
 * @property float $daily_rate
 * @property float $hourly_rate
 * @property string $pricing_type
 * @property float $security_deposit
 * @property string $mileage_limit
 * @property float $extra_mileage_charge
 * @property string $fuel_policy
 * @property float $delivery_fee
 * @property int $min_driver_age
 * @property string $service_type
 * @property int $min_booking_hours
 * @property int $luggage_capacity
 * @property string $chauffeur_tier
 * @property array $amenities
 * @property string $preferred_areas
 * @property string $description
 * @property array $images
 * @property array $features
 * @property string $postcode
 * @property string $address
 * @property string $available_for
 * @property string $terms_conditions
 * @property int $status
 * @property array $coordinates
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Car extends Model
{
    protected $fillable = [
        'category_id',
        'car_type_id',
        'brand',
        'model',
        'year',
        'fuel_type',
        'transmission_type',
        'transmission',
        'seating_capacity',
        'daily_rate',
        'hourly_rate',
        'description',
        'images',
        'features',
        'status',
        'coordinates',
        'provider_id',
        'registration_number',
        'air_conditioning',
        'service_type',
        'available_hours_start',
        'available_hours_end',
        'preferred_areas',
        'driving_license',
        'vehicle_registration',
        'insurance_documents',
        'mot_certificate',
        'service_category',
        'pricing_type',
        'manufacture_year',
        'security_deposit',
        'postcode',
        'address',
        'available_for',
        'terms_conditions',
        'mileage_limit',
        'extra_mileage_charge',
        'fuel_policy',
        'delivery_fee',
        'min_driver_age',
        'min_booking_hours',
        'luggage_capacity',
        'amenities',
        'chauffeur_tier',
    ];

    protected $casts = [
        'images' => 'array',
        'coordinates' => 'json',
        'features' => 'array',
        'amenities' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(\Modules\ProviderManagement\Entities\Provider::class);
    }

    public function type()
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function bookings()
    {
        return $this->hasMany(CarBooking::class);
    }

    public function documents()
    {
        return $this->hasMany(CarDocument::class);
    }
}

