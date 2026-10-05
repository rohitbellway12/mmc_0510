<?php

namespace Modules\CarHire\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CarHire\Entities\CarType;
use Illuminate\Support\Str;

class CarTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'SUV',
            'Sedan',
            'Hatchback',
            'Coupe',
            'Convertible',
            'Wagon'
        ];

        foreach ($types as $type) {
            CarType::create([
                'name'   => $type,
                'slug'   => Str::slug($type),
                'status' => 1,
            ]);
        }
    }
}
