<?php

namespace Modules\BookingModule\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\BookingEstimate;
use Modules\ProviderManagement\Entities\ProviderServicePrice;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Modules\UserManagement\Entities\User;

class BookingEstimateController extends Controller
{
    /**
     * Search customers for autocomplete
     */
    public function searchCustomer(Request $request): JsonResponse
    {
        $query = $request->get('query', '');

        $customers = User::where('user_type', 'customer')
            ->when(!empty($query), function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('phone', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                });
            })
            ->latest()
            ->take(15)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        return response()->json(response_formatter(DEFAULT_200, $customers), 200);
    }

    /**
     * Get provider subscribed services with quotation/fixed-price info
     */
    public function getServices(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        // Specifically subscribed services from available services
        $subscribedServices = SubscribedService::where('provider_id', $provider->id)
            ->where('is_subscribed', 1)
            ->whereNotNull('service_id')
            ->get();

        $subscribedServiceIds = $subscribedServices->pluck('service_id')->toArray();

        if (!empty($subscribedServiceIds)) {
            $servicesQuery = Service::whereIn('id', $subscribedServiceIds);
        } else {
            $subscribedCategoryIds = SubscribedService::where('provider_id', $provider->id)
                ->where('is_subscribed', 1)
                ->pluck('category_id')
                ->toArray();
            $servicesQuery = Service::whereIn('category_id', $subscribedCategoryIds);
        }

        $subscribedServicePrices = $subscribedServices->pluck('service_price', 'service_id')->toArray();
        $customPrices = ProviderServicePrice::where('provider_id', $provider->id)
            ->where('zone_id', $provider->zone_id)
            ->whereIn('service_id', $subscribedServiceIds)
            ->get()
            ->keyBy('service_id');

        $services = $servicesQuery
            ->where('is_active', 1)
            ->with(['category', 'variations' => function ($q) use ($provider) {
                $q->where('zone_id', $provider->zone_id);
            }])
            ->get()
            ->map(function ($service) use ($subscribedServicePrices, $customPrices) {
                $isQuotation = (bool)($service->is_quotation_based ?? false);
                $firstVariation = $service->variations->first();
                $defaultCatalogPrice = $firstVariation ? floatval($firstVariation->price) : 0.0;

                $configuredPrice = null;
                if (isset($subscribedServicePrices[$service->id]) && floatval($subscribedServicePrices[$service->id]) > 0) {
                    $configuredPrice = floatval($subscribedServicePrices[$service->id]);
                } elseif (isset($customPrices[$service->id]) && floatval($customPrices[$service->id]->service_price) > 0) {
                    $configuredPrice = floatval($customPrices[$service->id]->service_price);
                }

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category_id' => $service->category_id,
                    'category_name' => $service->category?->name,
                    'sub_category_id' => $service->sub_category_id,
                    'is_quotation_based' => $isQuotation,
                    'service_type' => $isQuotation ? 'quotation_based' : 'fixed_price',
                    'default_price' => $defaultCatalogPrice,
                    'configured_price' => $configuredPrice,
                    'effective_price' => $configuredPrice ?? $defaultCatalogPrice,
                    'thumbnail_full_path' => $service->thumbnail_full_path,
                ];
            });

        return response()->json(response_formatter(DEFAULT_200, $services), 200);
    }

    /**
     * Get provider fleet cars for Car Hire & Chauffeur quotations
     */
    public function getCars(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $cars = \Modules\CarHire\Entities\Car::where('provider_id', $provider->id)
            ->where('status', 1)
            ->with(['type', 'category'])
            ->get()
            ->map(function ($car) {
                $firstImage = !empty($car->images) && is_array($car->images) ? ($car->images[0] ?? null) : null;
                return [
                    'id' => $car->id,
                    'brand' => $car->brand,
                    'model' => $car->model,
                    'year' => $car->year,
                    'registration_number' => $car->registration_number,
                    'car_type_id' => $car->car_type_id,
                    'car_type_name' => $car->type?->name,
                    'service_category' => $car->service_category,
                    'service_type' => $car->service_type,
                    'pricing_type' => $car->pricing_type,
                    'daily_rate' => floatval($car->daily_rate ?? $car->daily_rent ?? 0),
                    'hourly_rate' => floatval($car->hourly_rate ?? 0),
                    'delivery_fee' => floatval($car->delivery_fee ?? 0),
                    'security_deposit' => floatval($car->security_deposit ?? 0),
                    'mileage_limit' => $car->mileage_limit ?? 'Unlimited',
                    'fuel_policy' => $car->fuel_policy ?? 'Full to Full',
                    'min_driver_age' => intval($car->min_driver_age ?? 21),
                    'min_booking_hours' => intval($car->min_booking_hours ?? 1),
                    'luggage_capacity' => intval($car->luggage_capacity ?? 0),
                    'chauffeur_tier' => $car->chauffeur_tier,
                    'amenities' => $car->amenities,
                    'image_url' => $firstImage ? asset('storage/app/public/car/' . $firstImage) : null,
                ];
            });

        return response()->json(response_formatter(DEFAULT_200, $cars), 200);
    }

    /**
     * Provider creates a Booking Estimate / Quotation for a Customer
     */
    public function store(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $moduleType = $request->get('module_type', 'general');

        if ($moduleType === 'car_hire' || $moduleType === 'chauffeur') {
            $rules = [
                'customer_name' => 'required|string|max:191',
                'customer_phone' => 'required|string|max:30',
                'customer_email' => 'nullable|email|max:191',
                'car_id' => 'required|exists:cars,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'pickup_time' => 'required',
                'drop_time' => 'required',
                'pricing_type' => 'nullable|in:daily,hourly,auto',
                'price' => 'required|numeric|min:0',
                'rent_amount' => 'nullable|numeric|min:0',
                'delivery_fee' => 'nullable|numeric|min:0',
                'security_deposit' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ];

            if ($moduleType === 'chauffeur') {
                $rules['pickup_location'] = 'required|string|max:255';
                $rules['drop_location'] = 'required|string|max:255';
            } else {
                $rules['pickup_type'] = 'required|in:self,delivery,chauffeur';
                $rules['delivery_address'] = 'required_if:pickup_type,delivery';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
            }

            $car = \Modules\CarHire\Entities\Car::with(['category', 'type'])->find($request->car_id);
            if (!$car || $car->provider_id != $provider->id) {
                return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'car', 'message' => translate('Car not found in provider fleet')]]), 404);
            }

            $existingCustomer = User::where('user_type', 'customer')
                ->where(function ($q) use ($request) {
                    $q->where('phone', $request->customer_phone);
                    if (!empty($request->customer_email)) {
                        $q->orWhere('email', $request->customer_email);
                    }
                })
                ->first();

            $pickupType = $moduleType === 'chauffeur' ? 'chauffeur' : $request->get('pickup_type', 'self');
            $pricingType = $request->get('pricing_type', 'daily');
            if ($pricingType === 'auto' || empty($pricingType)) {
                $pricingType = $car->pricing_type ?? 'daily';
            }

            $estimate = new BookingEstimate();
            $estimate->provider_id = $provider->id;
            $estimate->customer_id = $existingCustomer?->id;
            $estimate->customer_name = $request->customer_name;
            $estimate->customer_phone = $request->customer_phone;
            $estimate->customer_email = $request->customer_email;
            $estimate->customer_address = $pickupType == 'delivery' ? $request->delivery_address : ($request->customer_address ?? $request->pickup_location);
            $estimate->module_type = $moduleType;
            $estimate->car_id = $car->id;
            $estimate->service_id = null;
            $estimate->category_id = $car->category_id;
            $estimate->zone_id = $provider->zone_id;
            $estimate->car_model = trim($car->brand . ' ' . $car->model . ' (' . $car->year . ')');
            $estimate->car_registration_number = $car->registration_number;
            $estimate->car_image = !empty($car->images) && is_array($car->images) ? ($car->images[0] ?? null) : null;
            $estimate->start_date = $request->start_date;
            $estimate->end_date = $request->end_date;
            $estimate->pickup_time = $request->pickup_time;
            $estimate->drop_time = $request->drop_time;
            $estimate->pickup_type = $pickupType;
            $estimate->pricing_type = $pricingType;
            $estimate->pickup_location = $request->pickup_location;
            $estimate->drop_location = $request->drop_location;
            if ($request->has('pickup_coordinates')) {
                $estimate->pickup_coordinates = is_array($request->pickup_coordinates) ? $request->pickup_coordinates : json_decode($request->pickup_coordinates, true);
            }
            if ($request->has('drop_coordinates')) {
                $estimate->drop_coordinates = is_array($request->drop_coordinates) ? $request->drop_coordinates : json_decode($request->drop_coordinates, true);
            }
            $estimate->delivery_address = $request->delivery_address;
            if ($request->has('delivery_latitude')) {
                $estimate->delivery_latitude = $request->delivery_latitude;
            }
            if ($request->has('delivery_longitude')) {
                $estimate->delivery_longitude = $request->delivery_longitude;
            }

            $securityDeposit = ($moduleType === 'car_hire' && !empty($car->security_deposit)) ? floatval($car->security_deposit) : 0;
            if ($request->filled('security_deposit')) {
                $securityDeposit = floatval($request->security_deposit);
            }

            $deliveryFee = ($pickupType === 'delivery' && !empty($car->delivery_fee)) ? floatval($car->delivery_fee) : 0;
            if ($request->filled('delivery_fee')) {
                $deliveryFee = floatval($request->delivery_fee);
            }

            $totalPrice = floatval($request->price);
            $rentAmount = max(0, $totalPrice - $deliveryFee - $securityDeposit);
            if ($request->filled('rent_amount') && floatval($request->rent_amount) > 0) {
                $rentAmount = floatval($request->rent_amount);
            }

            $estimate->service_schedule = \Carbon\Carbon::parse($request->start_date . ' ' . $request->pickup_time);
            $estimate->service_type = 'fixed_price';
            $estimate->rent_amount = $rentAmount;
            $estimate->delivery_fee = $deliveryFee;
            $estimate->security_deposit = $securityDeposit;
            $estimate->price = $totalPrice;
            $estimate->tax_amount = 0;
            $estimate->discount_amount = 0;
            $estimate->total_amount = $totalPrice;
            $estimate->notes = $request->notes;
            $estimate->status = 'pending';
            $estimate->expired_at = now()->addDays(7);
            $estimate->save();

            if ($existingCustomer) {
                try {
                    $title = translate('New Quotation Received');
                    $description = translate('Provider ') . $provider->company_name . translate(' has sent you a new quotation of ') . with_currency_symbol($estimate->total_amount) . '.';
                    if (!empty($existingCustomer->fcm_token)) {
                        device_notification($existingCustomer->fcm_token, $title, $description, null, $estimate->id, 'estimate');
                    }
                    $pushNotification = new \Modules\PromotionManagement\Entities\PushNotification();
                    $pushNotification->title = $title;
                    $pushNotification->description = $description;
                    $pushNotification->zone_ids = [$provider->zone_id];
                    $pushNotification->to_users = ['customer'];
                    $pushNotification->is_active = 1;
                    $pushNotification->save();

                    $pushNotificationUser = new \Modules\PromotionManagement\Entities\PushNotificationUser();
                    $pushNotificationUser->push_notification_id = $pushNotification->id;
                    $pushNotificationUser->user_id = $existingCustomer->id;
                    $pushNotificationUser->save();
                } catch (\Exception $e) {
                    info("API estimate notification error: " . $e->getMessage());
                }
            }

            // Send Email to Customer
            $customerEmail = $estimate->customer_email ?? $existingCustomer?->email;
            if (!empty($customerEmail)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \Modules\BookingModule\Emails\EstimateCreatedMail($estimate));
                } catch (\Exception $e) {
                    info("API estimate email failed for {$customerEmail}: " . $e->getMessage());
                }
            }

            $estimate->load(['car', 'category', 'provider.owner']);

            return response()->json(response_formatter([
                'response_code' => 'estimate_created_200',
                'message' => translate('Car Hire / Chauffeur quotation created successfully! Share the link with the customer.'),
            ], [
                'estimate' => $estimate,
                'share_link' => $estimate->web_url,
                'deep_link' => $estimate->deep_link_url,
                'shareable_message' => $estimate->shareable_message,
                'whatsapp_share_url' => $estimate->whatsapp_share_url,
            ]), 200);
        }

        // Regular Garage / Automotive service
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:191',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:191',
            'customer_address' => 'nullable|string',
            'service_id' => 'required|uuid',
            'service_schedule' => 'required|date',
            'price' => 'nullable|numeric|min:0',
            'car_model' => 'nullable|string|max:191',
            'car_registration_number' => 'nullable|string|max:191',
            'damage_description' => 'nullable|string',
            'notes' => 'nullable|string',
            'car_image' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $service = Service::with('category', 'variations')->find($request->service_id);
        if (!$service) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'service', 'message' => translate('Service not found')]]), 404);
        }

        $isQuotation = (bool)($service->is_quotation_based ?? false);
        $serviceType = $isQuotation ? 'quotation_based' : 'fixed_price';

        // Determine price
        if ($isQuotation) {
            if (!$request->filled('price') || floatval($request->price) <= 0) {
                return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'price', 'message' => translate('Price is required for quotation-based service')]]), 400);
            }
            $price = floatval($request->price);
        } else {
            $defaultPrice = $service->variations->first()?->price ?? 0;
            $price = $request->filled('price') && floatval($request->price) > 0 ? floatval($request->price) : floatval($defaultPrice);
        }

        // Check if customer exists in users table
        $existingCustomer = User::where('user_type', 'customer')
            ->where(function ($q) use ($request) {
                $q->where('phone', $request->customer_phone);
                if (!empty($request->customer_email)) {
                    $q->orWhere('email', $request->customer_email);
                }
            })
            ->first();

        // Handle car images (supports multiple car_images[] or single car_image)
        $carImageName = null;
        $carImages = [];
        if ($request->hasFile('car_images')) {
            foreach ($request->file('car_images') as $imgFile) {
                if ($imgFile) {
                    $carImages[] = file_uploader('estimate/car/', 'png', $imgFile);
                }
            }
        } elseif ($request->hasFile('car_image')) {
            $carImages[] = file_uploader('estimate/car/', 'png', $request->file('car_image'));
        }

        if (!empty($carImages)) {
            $carImageName = count($carImages) === 1 ? $carImages[0] : json_encode($carImages);
        }

        $estimate = new BookingEstimate();
        $estimate->provider_id = $provider->id;
        $estimate->customer_id = $existingCustomer?->id;
        $estimate->customer_name = $request->customer_name;
        $estimate->customer_phone = $request->customer_phone;
        $estimate->customer_email = $request->customer_email;
        $estimate->customer_address = $request->customer_address;
        $estimate->module_type = 'general';
        $estimate->service_id = $service->id;
        $estimate->category_id = $service->category_id;
        $estimate->sub_category_id = $service->sub_category_id;
        $estimate->zone_id = $provider->zone_id;
        $estimate->car_model = $request->car_model;
        $estimate->car_registration_number = $request->car_registration_number;
        $estimate->car_image = $carImageName;
        $estimate->damage_description = $request->damage_description;
        $estimate->service_schedule = $request->service_schedule;
        $estimate->service_type = $serviceType;
        $estimate->price = $price;
        $estimate->tax_amount = 0;
        $estimate->discount_amount = 0;
        $estimate->total_amount = $price;
        $estimate->notes = $request->notes;
        $estimate->status = 'pending';
        $estimate->expired_at = now()->addDays(7);
        $estimate->save();

        // Save Category Question Answers
        $answers = $request->answers ?? $request->question_answers ?? [];
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        if (is_array($answers) && count($answers) > 0) {
            foreach ($answers as $questionId => $answer) {
                $qId = null;
                $ansVal = '';
                if (is_array($answer) && isset($answer['provider_question_id'])) {
                    $qId = $answer['provider_question_id'];
                    $ansVal = is_array($answer['answer_value'] ?? null) ? implode(', ', $answer['answer_value']) : ($answer['answer_value'] ?? '');
                } elseif (is_array($answer) && isset($answer['question_id'])) {
                    $qId = $answer['question_id'];
                    $ansVal = is_array($answer['answer'] ?? null) ? implode(', ', $answer['answer']) : ($answer['answer'] ?? '');
                } else {
                    $qId = $questionId;
                    $ansVal = is_array($answer) ? implode(', ', $answer) : (string)$answer;
                }

                if (!empty($qId)) {
                    \Modules\BookingModule\Entities\BookingQuestionAnswer::create([
                        'booking_estimate_id' => $estimate->id,
                        'provider_question_id' => $qId,
                        'answer_value' => $ansVal
                    ]);
                }
            }
        }

        if ($existingCustomer) {
            try {
                $title = translate('New Quotation Received');
                $description = translate('Provider ') . $provider->company_name . translate(' has sent you a new quotation of ') . with_currency_symbol($estimate->total_amount) . '.';
                if (!empty($existingCustomer->fcm_token)) {
                    device_notification($existingCustomer->fcm_token, $title, $description, null, $estimate->id, 'estimate');
                }
                $pushNotification = new \Modules\PromotionManagement\Entities\PushNotification();
                $pushNotification->title = $title;
                $pushNotification->description = $description;
                $pushNotification->zone_ids = [$provider->zone_id];
                $pushNotification->to_users = ['customer'];
                $pushNotification->is_active = 1;
                $pushNotification->save();

                $pushNotificationUser = new \Modules\PromotionManagement\Entities\PushNotificationUser();
                $pushNotificationUser->push_notification_id = $pushNotification->id;
                $pushNotificationUser->user_id = $existingCustomer->id;
                $pushNotificationUser->save();
            } catch (\Exception $e) {
                info("API estimate notification error: " . $e->getMessage());
            }
        }

        // Send Email to Customer
        $customerEmail = $estimate->customer_email ?? $existingCustomer?->email;
        if (!empty($customerEmail)) {
            try {
                \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \Modules\BookingModule\Emails\EstimateCreatedMail($estimate));
            } catch (\Exception $e) {
                info("API estimate email failed for {$customerEmail}: " . $e->getMessage());
            }
        }

        $estimate->load(['service', 'category', 'provider.owner', 'question_answers.question']);

        return response()->json(response_formatter([
            'response_code' => 'estimate_created_200',
            'message' => translate('Quotation / Booking estimate created successfully! Share the link with the customer.'),
        ], [
            'estimate' => $estimate,
            'share_link' => $estimate->web_url,
            'deep_link' => $estimate->deep_link_url,
            'shareable_message' => $estimate->shareable_message,
            'whatsapp_share_url' => $estimate->whatsapp_share_url,
        ]), 200);
    }

    /**
     * List all estimates created by provider
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $estimates = BookingEstimate::where('provider_id', $provider->id)
            ->when($request->filled('status') && $request->status != 'all', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('readable_id', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('car_registration_number', 'like', "%{$search}%");
                });
            })
            ->with(['service', 'category', 'booking', 'car', 'carBooking', 'question_answers.question'])
            ->latest()
            ->paginate($request->get('limit', 15), ['*'], 'offset', $request->get('offset', 1));

        return response()->json(response_formatter(DEFAULT_200, $estimates), 200);
    }

    /**
     * View single estimate details
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $estimate = BookingEstimate::where('provider_id', $provider->id)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('readable_id', $id);
            })
            ->with(['service', 'category', 'customer', 'booking', 'car', 'carBooking', 'question_answers.question'])
            ->first();

        if (!$estimate) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $estimate), 200);
    }

    /**
     * Cancel / Withdraw an estimate
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $estimate = BookingEstimate::where('provider_id', $provider->id)
            ->where('id', $id)
            ->first();

        if (!$estimate) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        if ($estimate->status != 'pending') {
            return response()->json(response_formatter([
                'response_code' => 'cannot_cancel_400',
                'message' => translate('Only pending estimates can be canceled.'),
            ]), 400);
        }

        $estimate->status = 'canceled';
        $estimate->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200, $estimate), 200);
    }
}
