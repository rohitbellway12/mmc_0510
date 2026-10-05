<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;

class CarBrands extends Model
{
    protected $table = 'car_brands';

    protected $fillable = ['car_type_id', 'name', 'slug', 'status'];

    public function carType()
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }

    public function models()
    {
        return $this->hasMany(CarModels::class, 'brand_id');
    }
    public function cars()
    {
        return $this->hasMany(Car::class, 'brand_id');
    }
}

