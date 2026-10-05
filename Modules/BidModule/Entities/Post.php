<?php

namespace Modules\BidModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookingModule\Entities\Booking;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;

class Post extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [];

    protected $appends = ['car_image_full_path', 'car_images_full_path'];

    protected static function newFactory()
    {
        return \Modules\BidModule\Database\factories\PostFactory::new();
    }

    /** Relations */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sub_category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id')->withoutGlobalScopes();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(PostBid::class, 'post_id', 'id');
    }

    public function addition_instructions(): HasMany
    {
        return $this->hasMany(PostAdditionalInstruction::class, 'post_id', 'id');
    }

    public function question_answers(): HasMany
    {
        return $this->hasMany(\Modules\BookingModule\Entities\BookingQuestionAnswer::class, 'post_id', 'id')->with('question');
    }

    public function postDeleteNote(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PostAdditionalInformation::class, 'post_id')->where('key', 'post_delete_note');
    }

    public function ignored_posts(): HasMany
    {
        return $this->hasMany(IgnoredPost::class, 'post_id', 'id');
    }

    public function service_address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'service_address_id');
    }

    public function post_providers(): HasMany
    {
        return $this->hasMany(PostProvider::class, 'post_id', 'id');
    }

    public function targeted_providers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\ProviderManagement\Entities\Provider::class,
            'post_providers',
            'post_id',
            'provider_id'
        );
    }

    public function post_services(): HasMany
    {
        return $this->hasMany(PostService::class, 'post_id', 'id');
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'post_services',
            'post_id',
            'service_id'
        );
    }

    public function getCarImagesAttribute(): array
    {
        $raw = $this->attributes['car_image'] ?? null;
        if (empty($raw)) {
            return [];
        }
        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [$raw];
    }

    public function getCarImageFullPathAttribute(): ?string
    {
        $images = $this->car_images;
        if (empty($images)) {
            return null;
        }
        return asset('storage/app/public/post/car/' . $images[0]);
    }

    public function getCarImagesFullPathAttribute(): array
    {
        $images = $this->car_images;
        $fullPaths = [];
        foreach ($images as $img) {
            $fullPaths[] = asset('storage/app/public/post/car/' . $img);
        }
        return $fullPaths;
    }
}
