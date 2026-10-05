<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderServicePrice;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;

class ProviderServicePriceController extends Controller
{
    private ProviderServicePrice $providerServicePrice;
    private Service $service;
    private SubscribedService $subscribedService;
    private Variation $variation;

    public function __construct(
        ProviderServicePrice $providerServicePrice,
        Service $service,
        SubscribedService $subscribedService,
        Variation $variation
    ) {
        $this->providerServicePrice = $providerServicePrice;
        $this->service = $service;
        $this->subscribedService = $subscribedService;
        $this->variation = $variation;
    }

    /**
     * Display a listing of services with pricing options
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
    {
        $provider = $request->user()->provider;
        $search = $request->has('search') ? $request['search'] : '';

        // Get subscribed services for this provider
        $subscribedServiceIds = $this->subscribedService
            ->where('provider_id', $provider->id)
            ->ofStatus(1)
            ->pluck('service_id')
            ->toArray();

        // Get services in subscribed categories
        $services = $this->service
            ->with(['category', 'subCategory', 'variations' => function ($query) use ($provider) {
                $query->where('zone_id', $provider->zone_id);
            }])
            ->whereIn('id', $subscribedServiceIds)
            ->where('is_active', 1)
            ->when($request->has('search') && !empty($search), function ($query) use ($search) {
                $keys = explode(' ', $search);
                foreach ($keys as $key) {
                    $query->where('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->orderBy('name')
            ->paginate(pagination_limit())
            ->appends(['search' => $search]);

        // Get provider's custom prices
        $customPrices = $this->providerServicePrice
            ->where('provider_id', $provider->id)
            ->where('zone_id', $provider->zone_id)
            ->get()
            ->keyBy(function ($item) {
                return $item->service_id . '_' . $item->variation_id;
            });

        return view('providermanagement::provider.service-pricing', compact('services', 'customPrices', 'search'));
    }

    /**
     * Store or update provider's custom price for a service
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|uuid|exists:services,id',
            'variation_id' => 'required|exists:variations,id',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $request->user()->provider;

        // Check if custom price already exists
        $providerPrice = $this->providerServicePrice
            ->where('provider_id', $provider->id)
            ->where('service_id', $request->service_id)
            ->where('variation_id', $request->variation_id)
            ->where('zone_id', $provider->zone_id)
            ->first();

        if ($providerPrice) {
            // Update existing price
            $providerPrice->price = $request->price;
            $providerPrice->is_active = 1;
            $providerPrice->save();
        } else {
            // Create new custom price
            $this->providerServicePrice->create([
                'provider_id' => $provider->id,
                'service_id' => $request->service_id,
                'variation_id' => $request->variation_id,
                'zone_id' => $provider->zone_id,
                'price' => $request->price,
                'is_active' => 1,
            ]);
        }

        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
    }

    /**
     * Update status of custom pricing (enable/disable)
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|uuid|exists:provider_service_prices,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerPrice = $this->providerServicePrice->find($request->id);

        // Verify that this price belongs to the authenticated provider
        if ($providerPrice->provider_id !== $request->user()->provider->id) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $providerPrice->is_active = $request->status;
        $providerPrice->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Reset custom price (remove it, revert to admin base price)
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $providerPrice = $this->providerServicePrice->find($id);

        if (!$providerPrice) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        // Verify that this price belongs to the authenticated provider
        if ($providerPrice->provider_id !== $request->user()->provider->id) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $providerPrice->delete();

        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }

    /**
     * Bulk update prices
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function bulkUpdate(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'prices' => 'required|array',
            'prices.*.service_id' => 'required|uuid|exists:services,id',
            'prices.*.variation_id' => 'required|exists:variations,id',
            'prices.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $request->user()->provider;

        foreach ($request->prices as $priceData) {
            $providerPrice = $this->providerServicePrice
                ->where('provider_id', $provider->id)
                ->where('service_id', $priceData['service_id'])
                ->where('variation_id', $priceData['variation_id'])
                ->where('zone_id', $provider->zone_id)
                ->first();

            if ($providerPrice) {
                $providerPrice->price = $priceData['price'];
                $providerPrice->is_active = 1;
                $providerPrice->save();
            } else {
                $this->providerServicePrice->create([
                    'provider_id' => $provider->id,
                    'service_id' => $priceData['service_id'],
                    'variation_id' => $priceData['variation_id'],
                    'zone_id' => $provider->zone_id,
                    'price' => $priceData['price'],
                    'is_active' => 1,
                ]);
            }
        }

        Toastr::success(translate('Prices updated successfully'));
        return back();
    }
}
