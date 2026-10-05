<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;

class Tyre extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'status' => 'integer',
        'images' => 'array',
    ];

    protected $fillable = [
        'provider_id',
        'category_id',
        'brand',
        'model',
        'tyre_type',
        'season',
        'vehicle_type',
        'width',
        'profile',
        'rim_size',
        'speed_rating',
        'load_index',
        'size',
        'price',
        'stock',
        'images',
        'status',
    ];

    protected $appends = ['image_full_paths'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function getImageFullPathsAttribute()
    {
        $images = $this->images ?? [];
        $defaultPath = asset('public/assets/admin-module/img/placeholder.png');

        if (empty($images)) {
            if (request()->is('api/*')) {
                return [];
            }
            return [$defaultPath];
        }

        $path = 'tyre/';
        return array_map(function ($image) use ($path, $defaultPath) {
            return getSingleImageFullPath(imagePath: $path . $image, s3Storage: null, defaultPath: $defaultPath);
        }, $images);
    }
}
