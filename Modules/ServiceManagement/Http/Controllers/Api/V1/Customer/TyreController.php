<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ServiceManagement\Entities\Tyre;

class TyreController extends Controller
{
    /**
     * List all tyres with filters.
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|uuid',
            'brand' => 'nullable|string',
            'size' => 'nullable|string',
            'tyre_type' => 'nullable|string',
            'season' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'width' => 'nullable|string',
            'profile' => 'nullable|string',
            'rim_size' => 'nullable|string',
            'limit' => 'nullable|numeric',
            'offset' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $tyres = Tyre::with(['provider.subscribed_services', 'category'])
            ->where('status', 1)
            ->when($request->has('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->has('brand'), function ($query) use ($request) {
                $query->where('brand', 'LIKE', '%' . $request->brand . '%');
            })
            ->when($request->has('size'), function ($query) use ($request) {
                $query->where('size', 'LIKE', '%' . $request->size . '%');
            })
            ->when($request->has('tyre_type'), function ($query) use ($request) {
                $query->where('tyre_type', $request->tyre_type);
            })
            ->when($request->has('season'), function ($query) use ($request) {
                $query->where('season', $request->season);
            })
            ->when($request->has('vehicle_type'), function ($query) use ($request) {
                $query->where('vehicle_type', $request->vehicle_type);
            })
            ->when($request->has('width'), function ($query) use ($request) {
                $query->where('width', $request->width);
            })
            ->when($request->has('profile'), function ($query) use ($request) {
                $query->where('profile', $request->profile);
            })
            ->when($request->has('rim_size'), function ($query) use ($request) {
                $query->where('rim_size', 'LIKE', '%' . $request->rim_size . '%');
            })
            ->latest()
            ->paginate($request->limit ?? 10, ['*'], 'page', $request->offset ?? 1);

        $tyres->getCollection()->transform(function ($tyre) {
            // Priority 1: Pick a service that has 'Replacement' in the name
            $subscribedService = \Modules\ProviderManagement\Entities\SubscribedService::where('provider_id', $tyre->provider_id)
                ->where('category_id', $tyre->category_id)
                ->where('is_subscribed', 1)
                ->whereHas('service', function ($q) {
                    $q->where('name', 'LIKE', '%Replacement%');
                })
                ->first();

            // Priority 2: Fallback to the first available subscribed service in category
            if (!$subscribedService) {
                $subscribedService = \Modules\ProviderManagement\Entities\SubscribedService::where('provider_id', $tyre->provider_id)
                    ->where('category_id', $tyre->category_id)
                    ->where('is_subscribed', 1)
                    ->first();
            }

            $tyre->service_id = $subscribedService ? $subscribedService->service_id : null;
            return $tyre;
        });

        return response()->json(response_formatter(DEFAULT_200, $tyres), 200);
    }

    /**
     * Get tyre details by ID.
     * @param string $id
     * @return JsonResponse
     */
    public function details(string $id): JsonResponse
    {
        $tyre = Tyre::with(['provider', 'category'])->where('status', 1)->find($id);

        if (!$tyre) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        // Priority 1: Pick a service that has 'Replacement' in the name
        $subscribedService = \Modules\ProviderManagement\Entities\SubscribedService::where('provider_id', $tyre->provider_id)
            ->where('category_id', $tyre->category_id)
            ->where('is_subscribed', 1)
            ->whereHas('service', function ($q) {
                $q->where('name', 'LIKE', '%Replacement%');
            })
            ->first();

        // Priority 2: Fallback to the first available subscribed service in category
        if (!$subscribedService) {
            $subscribedService = \Modules\ProviderManagement\Entities\SubscribedService::where('provider_id', $tyre->provider_id)
                ->where('category_id', $tyre->category_id)
                ->where('is_subscribed', 1)
                ->first();
        }

        $tyre->service_id = $subscribedService ? $subscribedService->service_id : null;

        return response()->json(response_formatter(DEFAULT_200, $tyre), 200);
    }
}
