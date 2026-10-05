<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CarHire\Database\Factories\CarYearsFactory;

class CarYears extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
   protected $fillable = ['model_id','year','status'];

    // protected static function newFactory(): CarYearsFactory
    // {
    //     // return CarYearsFactory::new();
    // }

     public function model()
    {
        return $this->belongsTo(CarModels::class, 'model_id');
    }

    // Accessor to get brand_id through model
    public function getBrandIdAttribute()
    {
        return $this->model->brand_id ?? null;
    }

    // Accessor to get car_type_id through model and brand
    public function getCarTypeIdAttribute()
    {
        return $this->model->brand->car_type_id ?? null;
    }
}
