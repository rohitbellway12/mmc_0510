<?php

namespace Modules\BookingModule\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingEstimate;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\ProviderServicePrice;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;

class BookingEstimateController extends Controller
{
    /**
     * Display a listing of estimates for provider
     */
    public function index(Request $request): Renderable
    {
        $providerId = $request->user()->provider->id;

        $queryParams = [
            'status' => $request->get('status', 'all'),
            'search' => $request->get('search', ''),
        ];

        $estimates = BookingEstimate::where('provider_id', $providerId)
            ->when($queryParams['status'] != 'all', function ($q) use ($queryParams) {
                $q->where('status', $queryParams['status']);
            })
            ->when(!empty($queryParams['search']), function ($q) use ($queryParams) {
                $search = $queryParams['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('readable_id', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('car_registration_number', 'like', "%{$search}%");
                });
            })
            ->with(['service', 'category', 'customer', 'booking'])
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        $statusCounts = [
            'all' => BookingEstimate::where('provider_id', $providerId)->count(),
            'pending' => BookingEstimate::where('provider_id', $providerId)->where('status', 'pending')->count(),
            'accepted' => BookingEstimate::where('provider_id', $providerId)->where('status', 'accepted')->count(),
            'canceled' => BookingEstimate::where('provider_id', $providerId)->where('status', 'canceled')->count(),
        ];

        return view('bookingmodule::provider.estimate.index', compact('estimates', 'queryParams', 'statusCounts'));
    }

    /**
     * Show create estimate form
     */
    public function create(Request $request): Renderable
    {
        $provider = $request->user()->provider;

        // Fetch specifically subscribed services for this provider from available services
        $subscribedServices = SubscribedService::where('provider_id', $provider->id)
            ->where('is_subscribed', 1)
            ->whereNotNull('service_id')
            ->get();

        $subscribedServiceIds = $subscribedServices->pluck('service_id')->toArray();

        // If provider subscribed to specific services, only show those.
        // Otherwise fallback to category-level subscriptions if any exist without service_id.
        if (!empty($subscribedServiceIds)) {
            $servicesQuery = Service::whereIn('id', $subscribedServiceIds);
        } else {
            $subscribedCategoryIds = SubscribedService::where('provider_id', $provider->id)
                ->where('is_subscribed', 1)
                ->pluck('category_id')
                ->toArray();
            $servicesQuery = Service::whereIn('category_id', $subscribedCategoryIds);
        }

        $services = $servicesQuery
            ->where('is_active', 1)
            ->with(['category', 'variations' => function ($q) use ($provider) {
                $q->where('zone_id', $provider->zone_id);
            }])
            ->get();

        $subscribedServicePrices = $subscribedServices->pluck('service_price', 'service_id')->toArray();
        $customPrices = ProviderServicePrice::where('provider_id', $provider->id)
            ->where('zone_id', $provider->zone_id)
            ->whereIn('service_id', $subscribedServiceIds)
            ->get()
            ->keyBy('service_id');

        // Set effective provider price on each service
        foreach ($services as $service) {
            $configuredPrice = null;
            if (isset($subscribedServicePrices[$service->id]) && floatval($subscribedServicePrices[$service->id]) > 0) {
                $configuredPrice = floatval($subscribedServicePrices[$service->id]);
            } elseif (isset($customPrices[$service->id]) && floatval($customPrices[$service->id]->service_price) > 0) {
                $configuredPrice = floatval($customPrices[$service->id]->service_price);
            }

            $service->configured_price = $configuredPrice;
            $service->default_catalog_price = floatval($service->variations->first()?->price ?? 0);
            $service->effective_price = $configuredPrice ?? $service->default_catalog_price;
        }

        $categoryIds = $services->pluck('category_id')->unique()->toArray();
        $categories = Category::whereIn('id', $categoryIds)->where('is_active', 1)->get();

        $customers = User::where('user_type', 'customer')->orderBy('first_name', 'asc')->take(500)->get(['id', 'first_name', 'last_name', 'phone', 'email']);

        // Fetch provider's fleet cars for Car Hire & Chauffeur
        $cars = \Modules\CarHire\Entities\Car::where('provider_id', $provider->id)
            ->where('status', 1)
            ->with(['type', 'category'])
            ->get();

        return view('bookingmodule::provider.estimate.create', compact('categories', 'services', 'customers', 'provider', 'cars'));
    }

    /**
     * Store new estimate
     */
    public function store(Request $request): RedirectResponse
    {
        $moduleType = $request->get('module_type', 'general');

        if ($moduleType === 'car_hire' || $moduleType === 'chauffeur') {
            $request->validate([
                'customer_name' => 'required|string|max:191',
                'customer_phone' => 'required|string|max:30',
                'customer_email' => 'nullable|email|max:191',
                'car_id' => 'required|exists:cars,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'pickup_time' => 'required',
                'drop_time' => 'required',
                'pickup_type' => 'required|in:self,delivery,chauffeur',
                'pickup_location' => 'required_if:pickup_type,chauffeur',
                'drop_location' => 'required_if:pickup_type,chauffeur',
                'delivery_address' => 'required_if:pickup_type,delivery',
                'price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $provider = $request->user()->provider;
            $car = \Modules\CarHire\Entities\Car::with(['category', 'type'])->findOrFail($request->car_id);

            // Check if customer exists in users table
            $existingCustomer = User::where('user_type', 'customer')
                ->where(function ($q) use ($request) {
                    $q->where('phone', $request->customer_phone);
                    if (!empty($request->customer_email)) {
                        $q->orWhere('email', $request->customer_email);
                    }
                })
                ->first();

            $estimate = new BookingEstimate();
            $estimate->provider_id = $provider->id;
            $estimate->customer_id = $existingCustomer?->id;
            $estimate->customer_name = $request->customer_name;
            $estimate->customer_phone = $request->customer_phone;
            $estimate->customer_email = $request->customer_email;
            $estimate->customer_address = $request->pickup_type == 'delivery' ? $request->delivery_address : ($request->customer_address ?? $request->pickup_location);
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
            $estimate->pickup_type = $request->pickup_type;
            $estimate->pickup_location = $request->pickup_location;
            $estimate->drop_location = $request->drop_location;
            $estimate->delivery_address = $request->delivery_address;
            $estimate->service_schedule = \Carbon\Carbon::parse($request->start_date . ' ' . $request->pickup_time);
            $estimate->service_type = 'fixed_price';
            $estimate->price = floatval($request->price);
            $estimate->tax_amount = 0;
            $estimate->discount_amount = 0;
            $estimate->total_amount = floatval($request->price);
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
                    $pushNotification->notification_type = 'estimate';
                    $pushNotification->reference_id = $estimate->id;
                    $pushNotification->booking_status = $estimate->status;
                    $pushNotification->is_active = 1;
                    $pushNotification->save();

                    $pushNotificationUser = new \Modules\PromotionManagement\Entities\PushNotificationUser();
                    $pushNotificationUser->push_notification_id = $pushNotification->id;
                    $pushNotificationUser->user_id = $existingCustomer->id;
                    $pushNotificationUser->save();
                } catch (\Exception $e) {
                    info("Web estimate notification error: " . $e->getMessage());
                }
            }

            // Send Email to Customer
            $customerEmail = $estimate->customer_email ?? $existingCustomer?->email;
            if (!empty($customerEmail)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \Modules\BookingModule\Emails\EstimateCreatedMail($estimate));
                } catch (\Exception $e) {
                    info("Web estimate email failed for {$customerEmail}: " . $e->getMessage());
                }
            }

            Toastr::success(translate('Car Hire / Chauffeur Quotation generated successfully!'));
            return redirect()->route('provider.estimate.details', [$estimate->id])->with('newly_created', true);
        }

        // Regular Garage / Automotive service
        $request->validate([
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

        $provider = $request->user()->provider;

        $service = Service::with('category', 'variations')->findOrFail($request->service_id);
        $isQuotation = (bool)($service->is_quotation_based ?? false);
        $serviceType = $isQuotation ? 'quotation_based' : 'fixed_price';

        if ($isQuotation && (!$request->filled('price') || floatval($request->price) <= 0)) {
            Toastr::error(translate('Please specify a price for quotation-based service'));
            return back()->withInput();
        }

        $defaultPrice = $service->variations->first()?->price ?? 0;
        $price = $request->filled('price') && floatval($request->price) > 0 ? floatval($request->price) : floatval($defaultPrice);

        // Check if customer exists in users table
        $existingCustomer = User::where('user_type', 'customer')
            ->where(function ($q) use ($request) {
                $q->where('phone', $request->customer_phone);
                if (!empty($request->customer_email)) {
                    $q->orWhere('email', $request->customer_email);
                }
            })
            ->first();

        // Car images (supports multiple car_images[] or single car_image)
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
                $pushNotification->notification_type = 'estimate';
                $pushNotification->reference_id = $estimate->id;
                $pushNotification->booking_status = $estimate->status;
                $pushNotification->is_active = 1;
                $pushNotification->save();

                $pushNotificationUser = new \Modules\PromotionManagement\Entities\PushNotificationUser();
                $pushNotificationUser->push_notification_id = $pushNotification->id;
                $pushNotificationUser->user_id = $existingCustomer->id;
                $pushNotificationUser->save();
            } catch (\Exception $e) {
                info("Web estimate notification error: " . $e->getMessage());
            }
        }

        // Send Email to Customer
        $customerEmail = $estimate->customer_email ?? $existingCustomer?->email;
        if (!empty($customerEmail)) {
            try {
                \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \Modules\BookingModule\Emails\EstimateCreatedMail($estimate));
            } catch (\Exception $e) {
                info("Web estimate email failed for {$customerEmail}: " . $e->getMessage());
            }
        }

        Toastr::success(translate('Booking estimate / Quotation generated successfully!'));
        return redirect()->route('provider.estimate.details', [$estimate->id])->with('newly_created', true);
    }

    /**
     * Show estimate details
     */
    public function show(Request $request, string $id): Renderable
    {
        $providerId = $request->user()->provider->id;

        $estimate = BookingEstimate::where('provider_id', $providerId)
            ->where('id', $id)
            ->with(['service', 'category', 'customer', 'booking', 'car', 'carBooking'])
            ->firstOrFail();

        return view('bookingmodule::provider.estimate.details', compact('estimate'));
    }

    /**
     * Cancel an estimate
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $providerId = $request->user()->provider->id;

        $estimate = BookingEstimate::where('provider_id', $providerId)
            ->where('id', $id)
            ->firstOrFail();

        if ($estimate->status != 'pending') {
            Toastr::error(translate('Only pending quotations can be canceled.'));
            return back();
        }

        $estimate->status = 'canceled';
        $estimate->save();

        Toastr::success(translate('Quotation canceled successfully!'));
        return back();
    }

    /**
     * Public Customer Web View for Deep Link Fallback (e.g. https://mmcclub.co.uk/estimate/{token})
     */
    public function customerView(string $token)
    {
        $estimate = BookingEstimate::where('link_token', $token)
            ->orWhere('id', $token)
            ->firstOrFail();

        $frontendUrl = env('FRONTEND_URL');
        if (!empty($frontendUrl)) {
            return redirect()->to(rtrim($frontendUrl, '/') . '/estimate/' . $estimate->link_token);
        }

        $estimate->load(['service', 'category', 'provider.owner', 'booking', 'car', 'carBooking']);

        return view('bookingmodule::customer.estimate.view', compact('estimate'));
    }

    /**
     * Customer accepts estimate from public Web view
     */
    public function customerAcceptWeb(Request $request, string $token): RedirectResponse
    {
        $estimate = BookingEstimate::where('link_token', $token)
            ->orWhere('id', $token)
            ->with(['service', 'provider.owner', 'car'])
            ->firstOrFail();

        if ($estimate->status == 'accepted' || !empty($estimate->booking_id)) {
            Toastr::info(translate('This quotation has already been accepted!'));
            return back();
        }

        if ($estimate->status == 'canceled') {
            Toastr::error(translate('This quotation was canceled by the provider.'));
            return back();
        }

        // Customer ID
        $customerId = null;
        if (auth()->check() && auth()->user()->user_type == 'customer') {
            $customerId = auth()->user()->id;
        } elseif (!empty($estimate->customer_id)) {
            $customerId = $estimate->customer_id;
        } else {
            $existingUser = User::where('user_type', 'customer')->where('phone', $estimate->customer_phone)->first();
            if ($existingUser) {
                $customerId = $existingUser->id;
            } else {
                $newUser = new User();
                $nameParts = explode(' ', $estimate->customer_name, 2);
                $newUser->first_name = $nameParts[0] ?? $estimate->customer_name;
                $newUser->last_name = $nameParts[1] ?? '';
                $newUser->phone = $estimate->customer_phone;
                $newUser->email = $estimate->customer_email;
                $newUser->user_type = 'customer';
                $newUser->is_active = 1;
                $newUser->password = bcrypt('12345678');
                $newUser->save();
                $customerId = $newUser->id;
            }
        }

        DB::transaction(function () use ($estimate, $customerId) {
            $isCarBooking = ($estimate->module_type === 'car_hire' || $estimate->module_type === 'chauffeur');

            $booking = new Booking();
            $booking->customer_id = $customerId;
            $booking->provider_id = $estimate->provider_id;
            $booking->category_id = $estimate->category_id;
            $booking->sub_category_id = $estimate->sub_category_id;
            $booking->zone_id = $estimate->zone_id;
            $booking->booking_status = 'accepted';
            $booking->is_paid = 0;
            $booking->payment_method = 'cash_after_service';
            $booking->transaction_id = 'cash-payment';
            $booking->total_booking_amount = $estimate->total_amount;
            $booking->total_tax_amount = $estimate->tax_amount;
            $booking->total_discount_amount = $estimate->discount_amount;
            $booking->service_schedule = $estimate->service_schedule ?? now()->addDay();
            $booking->booking_otp = rand(100000, 999999);
            $booking->is_guest = 0;
            $booking->car_model = $estimate->car_model;
            $booking->car_registration_number = $estimate->car_registration_number;
            $booking->damage_description = $estimate->damage_description;
            if ($estimate->car_image) {
                $booking->evidence_photos = [$estimate->car_image];
            }
            $booking->notes = $estimate->notes;
            if ($isCarBooking) {
                $booking->service_address_location = $estimate->pickup_type === 'delivery' ? $estimate->delivery_address : ($estimate->pickup_location ?? $estimate->customer_address);
            }
            $booking->save();

            $detail = new BookingDetail();
            $detail->booking_id = $booking->id;
            $detail->service_id = $estimate->service_id;
            $detail->service_name = $isCarBooking ? ($estimate->car_model . ' (' . ucfirst($estimate->pickup_type ?? 'hire') . ')') : ($estimate->service?->name ?? translate('Custom Service'));
            $detail->service_cost = $estimate->price;
            $detail->quantity = 1;
            $detail->tax_amount = $estimate->tax_amount;
            $detail->total_cost = $estimate->total_amount;
            $detail->save();

            BookingScheduleHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => 'customer',
                'schedule' => $booking->service_schedule,
            ]);
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => 'customer',
                'booking_status' => 'accepted',
            ]);

            // If Car Hire or Chauffeur, create CarBooking
            if ($isCarBooking && !empty($estimate->car_id)) {
                $pickupTime24 = $estimate->pickup_time ? date("H:i:s", strtotime($estimate->pickup_time)) : '10:00:00';
                $dropTime24 = $estimate->drop_time ? date("H:i:s", strtotime($estimate->drop_time)) : '18:00:00';

                $carBooking = new \Modules\CarHire\Entities\CarBooking();
                $carBooking->booking_id = $booking->id;
                $carBooking->car_id = $estimate->car_id;
                $carBooking->user_id = $customerId;
                $carBooking->start_date = $estimate->start_date ?? date('Y-m-d');
                $carBooking->end_date = $estimate->end_date ?? date('Y-m-d');
                $carBooking->pickup_type = $estimate->pickup_type ?? 'self';
                $carBooking->pickup_time = $pickupTime24;
                $carBooking->drop_time = $dropTime24;
                $carBooking->pickup_location = $estimate->pickup_location;
                $carBooking->drop_location = $estimate->drop_location;
                $carBooking->pickup_coordinates = $estimate->pickup_coordinates;
                $carBooking->drop_coordinates = $estimate->drop_coordinates;
                $carBooking->delivery_address = $estimate->delivery_address;
                $carBooking->delivery_latitude = $estimate->delivery_latitude;
                $carBooking->delivery_longitude = $estimate->delivery_longitude;
                $carBooking->description = $estimate->notes;
                $carBooking->total_amount = $estimate->total_amount;
                $carBooking->payment_method = 'cash_after_service';
                $carBooking->payment_status = 'unpaid';
                $carBooking->booking_status = 'pending';
                $carBooking->is_paid = 0;
                $carBooking->save();

                $estimate->car_booking_id = $carBooking->id;
            }

            $estimate->status = 'accepted';
            $estimate->booking_id = $booking->id;
            $estimate->customer_id = $customerId;
            $estimate->save();
        });

        // Notify provider
        try {
            $fcmToken = $estimate->provider?->owner?->fcm_token;
            $title = translate("Quotation Accepted!");
            $description = translate("Customer {$estimate->customer_name} accepted quotation #{$estimate->readable_id}");
            if ($fcmToken) {
                device_notification($fcmToken, $title, $description, null, $estimate->booking_id, 'booking');
            }
            if ($estimate->provider?->owner?->id) {
                $pushNotification = new \Modules\PromotionManagement\Entities\PushNotification();
                $pushNotification->title = $title;
                $pushNotification->description = $description;
                $pushNotification->zone_ids = [$estimate->zone_id ?? config('zone_id')];
                $pushNotification->to_users = ['provider-admin'];
                $pushNotification->is_active = 1;
                $pushNotification->save();

                $pushNotificationUser = new \Modules\PromotionManagement\Entities\PushNotificationUser();
                $pushNotificationUser->push_notification_id = $pushNotification->id;
                $pushNotificationUser->user_id = $estimate->provider->owner->id;
                $pushNotificationUser->save();
            }

            $providerEmail = $estimate->provider?->owner?->email;
            if (!empty($providerEmail)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($providerEmail)->send(new \Modules\BookingModule\Emails\EstimateAcceptedMail($estimate));
                } catch (\Exception $e) {
                    info("Web estimate accept email failed for provider: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            info("Web estimate accept notification error: " . $e->getMessage());
        }

        Toastr::success(translate('Quotation accepted and booking created successfully!'));
        return back();
    }
}
