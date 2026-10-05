<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;

class CarDocument extends Model
{
    protected $fillable = ['car_id', 'doc_type', 'file_path', 'expiry_date'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}

