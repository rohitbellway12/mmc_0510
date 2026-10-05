<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ServiceManagement\Entities\Tyre;
use Illuminate\Support\Carbon;

class TyreController extends Controller
{
    /**
     * List all tyres for authenticated provider.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'provider_not_found', 'message' => translate('Provider not found')]]), 404);
        }

        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'limit' => 'nullable|numeric',
            'offset' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $search = $request->search;

        $tyres = Tyre::where('provider_id', $provider->id)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('brand', 'LIKE', '%' . $search . '%')
                        ->orWhere('model', 'LIKE', '%' . $search . '%')
                        ->orWhere('size', 'LIKE', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($request->limit ?? 10, ['*'], 'page', $request->offset ?? 1);

        return response()->json(response_formatter(DEFAULT_200, $tyres), 200);
    }

    /**
     * Store a new tyre in stock.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'provider_not_found', 'message' => translate('Provider not found')]]), 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|uuid',
            'brand' => 'required|string',
            'model' => 'nullable|string',
            'tyre_type' => 'nullable|string',
            'season' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'width' => 'nullable|string',
            'profile' => 'nullable|string',
            'rim_size' => 'nullable|string',
            'speed_rating' => 'nullable|string',
            'load_index' => 'nullable|string',
            'size' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $size = $request->size;
        if (empty($size) && $request->filled(['width', 'profile', 'rim_size'])) {
            $size = $request->width . '/' . $request->profile . ' ' . $request->rim_size;
            if ($request->filled('load_index') || $request->filled('speed_rating')) {
                $size .= ' ' . $request->load_index . $request->speed_rating;
            }
        }

        $tyre = new Tyre();
        $tyre->provider_id = $provider->id;
        $tyre->category_id = $request->category_id;
        $tyre->brand = $request->brand;
        $tyre->model = $request->model;
        $tyre->tyre_type = $request->tyre_type ?? 'tubeless';
        $tyre->season = $request->season ?? 'all_season';
        $tyre->vehicle_type = $request->vehicle_type ?? 'passenger_car';
        $tyre->width = $request->width;
        $tyre->profile = $request->profile;
        $tyre->rim_size = $request->rim_size;
        $tyre->speed_rating = $request->speed_rating;
        $tyre->load_index = $request->load_index;
        $tyre->size = $size ?? 'Standard';
        $tyre->price = $request->price;
        $tyre->stock = $request->stock;
        $tyre->status = 1;

        if ($request->has('images') && is_array($request->images)) {
            $images = [];
            foreach ($request->images as $image) {
                if (!empty($image)) {
                    if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                        $data = base64_decode(substr($image, strpos($image, ',') + 1));
                        $ext = strtolower($type[1]);
                        if (!in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                            $ext = 'png';
                        }
                        $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $ext;
                        \Illuminate\Support\Facades\Storage::disk(getDisk())->put('tyre/' . $imageName, $data);
                        $images[] = $imageName;
                    } else {
                        $images[] = file_uploader('tyre/', 'png', $image);
                    }
                }
            }
            $tyre->images = $images;
        }

        $tyre->save();

        return response()->json(response_formatter(DEFAULT_200, $tyre), 200);
    }

    /**
     * Show tyre details for provider.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'provider_not_found', 'message' => translate('Provider not found')]]), 404);
        }

        $tyre = Tyre::where('id', $id)->where('provider_id', $provider->id)->first();
        if (!$tyre) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $tyre), 200);
    }

    /**
     * Update existing tyre record.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'provider_not_found', 'message' => translate('Provider not found')]]), 404);
        }

        $tyre = Tyre::where('id', $id)->where('provider_id', $provider->id)->first();
        if (!$tyre) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|uuid',
            'brand' => 'required|string',
            'model' => 'nullable|string',
            'tyre_type' => 'nullable|string',
            'season' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'width' => 'nullable|string',
            'profile' => 'nullable|string',
            'rim_size' => 'nullable|string',
            'speed_rating' => 'nullable|string',
            'load_index' => 'nullable|string',
            'size' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $size = $request->size;
        if (empty($size) && $request->filled(['width', 'profile', 'rim_size'])) {
            $size = $request->width . '/' . $request->profile . ' ' . $request->rim_size;
            if ($request->filled('load_index') || $request->filled('speed_rating')) {
                $size .= ' ' . $request->load_index . $request->speed_rating;
            }
        }

        if ($request->has('category_id')) $tyre->category_id = $request->category_id;
        $tyre->brand = $request->brand;
        $tyre->model = $request->model;
        $tyre->tyre_type = $request->tyre_type ?? $tyre->tyre_type;
        $tyre->season = $request->season ?? $tyre->season;
        $tyre->vehicle_type = $request->vehicle_type ?? $tyre->vehicle_type;
        $tyre->width = $request->width;
        $tyre->profile = $request->profile;
        $tyre->rim_size = $request->rim_size;
        $tyre->speed_rating = $request->speed_rating;
        $tyre->load_index = $request->load_index;
        $tyre->size = $size ?? $tyre->size;
        $tyre->price = $request->price;
        $tyre->stock = $request->stock;

        if ($request->has('images') && is_array($request->images)) {
            $images = $tyre->images ?? [];
            foreach ($request->images as $image) {
                if (!empty($image)) {
                    if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                        $data = base64_decode(substr($image, strpos($image, ',') + 1));
                        $ext = strtolower($type[1]);
                        if (!in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                            $ext = 'png';
                        }
                        $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $ext;
                        \Illuminate\Support\Facades\Storage::disk(getDisk())->put('tyre/' . $imageName, $data);
                        $images[] = $imageName;
                    } else {
                        $images[] = file_uploader('tyre/', 'png', $image);
                    }
                }
            }
            $tyre->images = $images;
        }

        $tyre->save();

        return response()->json(response_formatter(DEFAULT_200, $tyre), 200);
    }

    /**
     * Delete tyre from stock.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'provider_not_found', 'message' => translate('Provider not found')]]), 404);
        }

        $tyre = Tyre::where('id', $id)->where('provider_id', $provider->id)->first();
        if (!$tyre) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $tyre->delete();
        return response()->json(response_formatter(DEFAULT_200), 200);
    }

    /**
     * Toggle tyre status.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function statusUpdate(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'provider_not_found', 'message' => translate('Provider not found')]]), 404);
        }

        $tyre = Tyre::where('id', $id)->where('provider_id', $provider->id)->first();
        if (!$tyre) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $tyre->status = !$tyre->status;
        $tyre->save();

        return response()->json(response_formatter(DEFAULT_200, $tyre), 200);
    }
}
