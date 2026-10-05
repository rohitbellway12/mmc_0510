<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\CarHire\Database\Factories\TransmissionsFactory;

class Transmissions extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name','status'];

    // protected static function newFactory(): TransmissionsFactory
    // {
    //     // return TransmissionsFactory::new();
    // }
}
