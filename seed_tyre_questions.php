<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\ProviderManagement\Entities\ProviderQuestion;

$category_id = '5d98d5c9-509e-4ab7-859d-806174384e27';

$questions = [
    [
        'question_text' => 'Assistance Type',
        'question_type' => 'select',
        'options' => 'Mobile Tyre Service, Recovery Truck',
        'is_required' => 1,
        'display_order' => 1
    ],
    [
        'question_text' => 'Tyre Size (e.g., 205/55 R16)',
        'question_type' => 'text',
        'options' => null,
        'is_required' => 1,
        'display_order' => 2
    ],
    [
        'question_text' => 'Vehicle Model & Year',
        'question_type' => 'text',
        'options' => null,
        'is_required' => 1,
        'display_order' => 3
    ],
    [
        'question_text' => 'Current Situation',
        'question_type' => 'select',
        'options' => 'Puncture, Burst Tyre, New Tyre',
        'is_required' => 1,
        'display_order' => 4
    ],
    [
        'question_text' => 'Number of Tyres',
        'question_type' => 'select',
        'options' => '1, 2, 3, 4',
        'is_required' => 1,
        'display_order' => 5
    ],
    [
        'question_text' => 'Upload Photo of Tyre',
        'question_type' => 'file',
        'options' => null,
        'is_required' => 1,
        'display_order' => 6
    ],
];

foreach ($questions as $q) {
    ProviderQuestion::create(array_merge($q, [
        'provider_id' => null,
        'category_id' => $category_id,
        'is_active' => 1
    ]));
}

echo "Questions seeded successfully for Tyre Fitter!";
