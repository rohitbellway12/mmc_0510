<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CarHire\Database\Factories\FeaturesFactory;

class Features extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'status'];

    // protected static function newFactory(): FeaturesFactory
    // {
    //     // return FeaturesFactory::new();
    // }
}
