<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CarHire\Database\Factories\CarModelsFactory;

class CarModels extends Model
{
    use HasFactory;

    protected $table = 'car_models';
    /**
     * The attributes that are mass assignable.
     */
     protected $fillable = ['brand_id','name','slug','status'];

    // protected static function newFactory(): CarModelsFactory
    // {
    //     // return CarModelsFactory::new();
    // }
    public function brand()
    {
        return $this->belongsTo(CarBrands::class);
    }
    public function carType()
    {
        return $this->belongsTo(CarType::class);
    }



}
