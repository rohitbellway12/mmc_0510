<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\BusinessSettingsModule\Entities\Storage;

class PushNotification extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'zone_ids' => 'array',
        'to_users' => 'array',
        'is_active' => 'integer',
    ];

    protected $appends = ['cover_image_full_path'];

    protected $fillable = [
        'id', 'title', 'description', 'to_users', 'zone_ids', 'cover_image', 'is_active',
        'notification_type', 'booking_id', 'booking_status', 'reference_id'
    ];

    public function getNotificationTypeAttribute($value)
    {
        if (!empty($value) && $value !== 'general') {
            return $value;
        }
        $title = strtolower($this->title ?? '');
        $desc = strtolower($this->description ?? '');
        if (str_contains($title, 'booking') || str_contains($desc, 'booking #')) {
            return 'booking';
        }
        if (str_contains($title, 'bid') || str_contains($desc, 'provider has submitted a price quotation') || str_contains($desc, 'bid')) {
            return 'post_bid';
        }
        if (str_contains($title, 'quotation') || str_contains($desc, 'quotation')) {
            return 'estimate';
        }
        return $value ?: 'general';
    }

    public function getBookingStatusAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        $desc = strtolower($this->description ?? '');
        $title = strtolower($this->title ?? '');
        if (str_contains($desc, 'completed') || str_contains($title, 'completed') || str_contains($desc, 'complete')) {
            return 'completed';
        }
        if (str_contains($desc, 'ongoing') || str_contains($title, 'ongoing')) {
            return 'ongoing';
        }
        if (str_contains($desc, 'accepted') || str_contains($title, 'accepted')) {
            return 'accepted';
        }
        if (str_contains($desc, 'canceled') || str_contains($title, 'canceled') || str_contains($desc, 'cancelled')) {
            return 'canceled';
        }
        return $value;
    }

    public function pushNotificationUser(): hasOne
    {
        return $this->hasOne(PushNotificationUser::class, 'push_notification_id', 'id');
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }

    public function getCoverImageFullPathAttribute()
    {
        $image = $this->cover_image;
        $defaultPath = request()->is('*/edit/*') ? asset('public/assets/admin-module/img/media/banner-upload-file.png') : asset('public/assets/admin-module/img/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;
        $path = 'push-notification/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }


    public function scopeOfStatus($query, $status)
    {
        $query->where('is_active', '=', $status);
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            $storageType = getDisk();
            if ($model->isDirty('cover_image') && $storageType != 'public') {
                saveSingleImageDataToStorage(model: $model, modelColumn: 'cover_image', storageType: $storageType);
            }
        });
    }
}
