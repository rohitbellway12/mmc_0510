<?php

namespace Modules\UserManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BusinessSettingsModule\Entities\Storage;

class UserCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'manufacture_year',
        'registration_number',
        'car_image'
    ];

    protected $appends = ['car_image_full_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'car_image');
    }

    public function getCarImageFullPathAttribute()
    {
        $image = $this->car_image;
        $defaultPath = asset('public/assets/admin-module/img/media/provider-id.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;
        $path = 'user/car/';

        $imagePath = $path . $image;

        return getSingleImageFullPath($imagePath, $s3Storage, $defaultPath);
    }
}
