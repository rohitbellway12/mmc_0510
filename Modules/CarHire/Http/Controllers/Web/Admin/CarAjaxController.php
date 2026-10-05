<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CarHire\Entities\CarBrands;
use Modules\CarHire\Entities\CarModels;
use Modules\CarHire\Entities\CarYears;

class CarAjaxController extends Controller
{


    public function getBrandsByType($typeId)
    {
        $brands = CarBrands::where('car_type_id', $typeId)
            ->where('status', 1)
            ->get();

        return response()->json($brands);
    }


    public function getModelsByBrand($brandId)
    {
        //dd('sjkdbjs');
        //dd($brandId);
        $models = CarModels::where('brand_id', $brandId)
            ->where('status', 1)
            ->select('id', 'name')
            ->get();
        //dd($models);
        return response()->json($models);
    }

    public function getYearsByModel($modelId)
    {
        $years = CarYears::where('model_id', $modelId)
            ->where('status', 1)
            ->select('id', 'year')
            ->get();
        return response()->json($years);
    }

}
