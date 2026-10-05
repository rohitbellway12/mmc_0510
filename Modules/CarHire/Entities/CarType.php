<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;

class CarType extends Model
{
    protected $fillable = ['name', 'slug', 'status'];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }
}

