<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CarHire\Database\Factories\FuleTypeFactory;

class FuelTypes extends Model
{
    use HasFactory;

    protected $table = 'fuel_types';
    /**
     * The attributes that are mass assignable.
    */
    protected $fillable = ['name','status'];

    // protected static function newFactory(): FuleTypeFactory
    // {
    //     // return FuleTypeFactory::new();
    // }
}
