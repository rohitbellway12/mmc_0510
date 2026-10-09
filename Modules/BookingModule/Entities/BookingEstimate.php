<?php

namespace Modules\BookingModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;

class BookingEstimate extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'booking_estimates';

    protected $fillable = [
        'readable_id',
        'module_type',
        'provider_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'service_id',
        'category_id',
        'sub_category_id',
        'zone_id',
        'car_id',
        'car_model',
        'car_registration_number',
        'car_image',
        'damage_description',
        'service_schedule',
        'start_date',
        'end_date',
        'pickup_time',
        'drop_time',
        'pickup_type',
        'pricing_type',
        'pickup_location',
        'drop_location',
        'pickup_coordinates',
        'drop_coordinates',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'service_type',
        'price',
        'rent_amount',
        'delivery_fee',
        'security_deposit',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'status',
        'booking_id',
        'car_booking_id',
        'link_token',
        'expired_at',
    ];

    protected $casts = [
        'price' => 'float',
        'rent_amount' => 'float',
        'delivery_fee' => 'float',
        'security_deposit' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'service_schedule' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'pickup_coordinates' => 'array',
        'drop_coordinates' => 'array',
        'delivery_latitude' => 'float',
        'delivery_longitude' => 'float',
        'expired_at' => 'datetime',
    ];

    protected $appends = [
        'deep_link_url',
        'web_url',
        'car_image_full_path',
        'car_images_full_path',
        'shareable_message',
        'whatsapp_share_url',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function question_answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BookingQuestionAnswer::class, 'booking_estimate_id')->with('question');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(\Modules\CarHire\Entities\Car::class, 'car_id');
    }

    public function carBooking(): BelongsTo
    {
        return $this->belongsTo(\Modules\CarHire\Entities\CarBooking::class, 'car_booking_id');
    }

    public function getDeepLinkUrlAttribute(): string
    {
        $scheme = env('CUSTOMER_APP_SCHEME', 'mmc');
        return $scheme . '://estimate/' . $this->link_token;
    }

    public function getWebUrlAttribute(): string
    {
        $frontendUrl = env('FRONTEND_URL');
        if (!empty($frontendUrl)) {
            return rtrim($frontendUrl, '/') . '/estimate/' . $this->link_token;
        }
        return url('/estimate/' . $this->link_token);
    }

    public function getShareableMessageAttribute(): string
    {
        $customerName = !empty($this->customer_name) ? trim($this->customer_name) : 'Customer';
        $quoteId = !empty($this->readable_id) ? $this->readable_id : $this->id;
        $isCar = in_array($this->module_type, ['car_hire', 'chauffeur']);
        $itemTitle = $isCar 
            ? ($this->car_model ?? 'Vehicle Rental') 
            : ($this->service?->name ?? ($this->category?->name ?? 'Service'));
        
        $totalAmount = $this->total_amount ?? $this->price ?? 0;
        $formattedPrice = function_exists('with_currency_symbol') 
            ? with_currency_symbol($totalAmount) 
            : '£' . number_format($totalAmount, 2);

        $url = $this->web_url;
        $companyName = 'MMC Club';

        return "Hello {$customerName},\n\nHere is your quotation #{$quoteId} for {$itemTitle}.\nQuoted Price: {$formattedPrice}\n\nView and confirm your quotation here:\n{$url}\n\nThank you,\n{$companyName}";
    }

    public function getWhatsappShareUrlAttribute(): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$this->customer_phone);
        $encodedMsg = urlencode($this->shareable_message);
        return "https://api.whatsapp.com/send?phone={$cleanPhone}&text={$encodedMsg}";
    }

    public function getCarImagesAttribute(): array
    {
        $raw = $this->attributes['car_image'] ?? null;
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        if (str_contains($raw, ',')) {
            return array_map('trim', explode(',', $raw));
        }
        return [$raw];
    }

    public function getCarImagesFullPathAttribute(): array
    {
        $images = $this->car_images;
        return array_map(function ($img) {
            return asset('storage/app/public/estimate/car/' . $img);
        }, $images);
    }

    public function getCarImageFullPathAttribute(): ?string
    {
        $fullPaths = $this->car_images_full_path;
        return $fullPaths[0] ?? null;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->readable_id)) {
                $lastRecord = self::orderBy('created_at', 'desc')->first();
                $model->readable_id = $lastRecord ? ($lastRecord->readable_id + 1) : 10001;
            }
            if (empty($model->link_token)) {
                $model->link_token = bin2hex(random_bytes(24));
            }
        });
    }
}
