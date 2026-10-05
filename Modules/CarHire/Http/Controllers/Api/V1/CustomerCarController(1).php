<?php

namespace Modules\CarHire\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Modules\CarHire\Entities\Car;
use Modules\CarHire\Entities\CarBooking;
use Modules\CategoryManagement\Entities\Category;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\UserManagement\Entities\User;
use Modules\PaymentModule\Library\Payment;
use Modules\PaymentModule\Library\Payer;
use Modules\PaymentModule\Library\Receiver;
use Modules\PaymentModule\Traits\Payment as PaymentTrait;
use Illuminate\Support\Facades\DB;

class CustomerCarController extends Controller
{
    private Car $car;
    private CarBooking $carBooking;

    public function __construct(Car $car, CarBooking $carBooking)
    {
        $this->car = $car;
        $this->carBooking = $carBooking;
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|uuid',
            'limit' => 'numeric|min:1|max:200',
            'offset' => 'numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);

        $cars = $this->car->with(['type', 'category'])
            ->where('category_id', $request->category_id)
            ->where('status', 1)
            ->latest()
            ->paginate($limit, ['*'], 'offset', $offset)
            ->withPath('');

        $cars->getCollection()->transform(function ($car) {
            $car->image_full_paths = $this->getImageFullPaths($car->images);
            return $car;
        });

        return response()->json(response_formatter(DEFAULT_200, $cars), 200);
    }

    public function show($id): JsonResponse
    {
        $car = $this->car->with(['type', 'category', 'provider'])->where('status', 1)->find($id);

        if (!$car) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $car->image_full_paths = $this->getImageFullPaths($car->images);

        return response()->json(response_formatter(DEFAULT_200, $car), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_time' => 'required',
            'drop_time' => 'required',
            'pickup_type' => 'required|in:self,delivery,chauffeur',

            'pickup_location' => 'required_if:pickup_type,chauffeur',
            'pickup_coordinates' => 'required_if:pickup_type,chauffeur',
            'drop_location' => 'required_if:pickup_type,chauffeur',
            'drop_coordinates' => 'required_if:pickup_type,chauffeur',

            'delivery_address' => 'required_if:pickup_type,delivery',
            'delivery_latitude' => 'required_if:pickup_type,delivery|numeric|between:-90,90',
            'delivery_longitude' => 'required_if:pickup_type,delivery|numeric|between:-180,180',

            'payment_method' => 'required|in:cash_after_service,wallet_payment,digital_payment',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        // Validate JSON coordinates for chauffeur
        if ($request->pickup_type == 'chauffeur') {
            try {
                $pickupCoordinates = is_string($request->pickup_coordinates)
                    ? json_decode($request->pickup_coordinates, true)
                    : $request->pickup_coordinates;

                $dropCoordinates = is_string($request->drop_coordinates)
                    ? json_decode($request->drop_coordinates, true)
                    : $request->drop_coordinates;

                if (!isset($pickupCoordinates['latitude'], $pickupCoordinates['longitude'])) {
                    return response()->json([
                        'response_code' => 'default_400',
                        'message' => 'Invalid pickup coordinates format. Must contain latitude and longitude.'
                    ], 400);
                }

                if (!isset($dropCoordinates['latitude'], $dropCoordinates['longitude'])) {
                    return response()->json([
                        'response_code' => 'default_400',
                        'message' => 'Invalid drop coordinates format. Must contain latitude and longitude.'
                    ], 400);
                }

                // Update request with validated coordinates
                $request->merge([
                    'pickup_coordinates' => $pickupCoordinates,
                    'drop_coordinates' => $dropCoordinates
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'response_code' => 'default_400',
                    'message' => 'Invalid coordinates JSON format: ' . $e->getMessage()
                ], 400);
            }
        }

        $car = $this->car->find($request->car_id);
        if (!$car) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        // Convert time format from 12-hour to 24-hour format for database storage
        $pickupTime24 = date("H:i:s", strtotime($request->pickup_time));
        $dropTime24 = date("H:i:s", strtotime($request->drop_time));

        // Calculate total amount
        $startDate = \Carbon\Carbon::parse($request->start_date . ' ' . $request->pickup_time);
        $endDate = \Carbon\Carbon::parse($request->end_date . ' ' . $request->drop_time);

        // Calculate hours and days
        $totalHours = $startDate->diffInHours($endDate);
        $totalDays = (int) $startDate->diffInDays($endDate);

        Log::info('Car Booking Calculation Debug', [
            'car_id' => $car->id,
            'pricing_type' => $car->pricing_type,
            'hourly_rate' => $car->hourly_rate,
            'daily_rate' => $car->daily_rate,
            'totalHours' => $totalHours,
            'totalDays' => $totalDays
        ]);

        // Determine pricing based on car's pricing_type
        if ($car->pricing_type == 'hourly') {
            // Hourly pricing only
            $hours = max(1, $totalHours);
            $totalAmount = $hours * $car->hourly_rate;
            Log::info('Using HOURLY pricing', ['hours' => $hours, 'totalAmount' => $totalAmount]);
        } elseif ($car->pricing_type == 'daily') {
            // Daily pricing only
            $days = $totalDays;
            if ($totalHours % 24 != 0) {
                $days += 1;
            }
            if ($days == 0) {
                $days = 1;
            }
            $totalAmount = $days * $car->daily_rate;
            Log::info('Using DAILY pricing', ['days' => $days, 'totalAmount' => $totalAmount]);
        } else {
            // Both hourly and daily
            if ($totalHours <= 8) {
                $hours = max(1, $totalHours);
                $totalAmount = $hours * $car->hourly_rate;
                Log::info('Using HOURLY pricing (both type, <=8h)', ['hours' => $hours, 'totalAmount' => $totalAmount]);
            } else {
                $days = $totalDays;
                if ($totalHours % 24 != 0) {
                    $days += 1;
                }
                if ($days == 0) {
                    $days = 1;
                }
                $totalAmount = $days * $car->daily_rate;
                Log::info('Using DAILY pricing (both type, >8h)', ['days' => $days, 'totalAmount' => $totalAmount]);
            }
        }

        $booking = DB::transaction(function () use ($request, $totalAmount, $car, $pickupTime24, $dropTime24) {
            $bookingData = [
                'car_id' => $request->car_id,
                'user_id' => auth('api')->id(),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pickup_type' => $request->pickup_type,
                'pickup_time' => $pickupTime24,
                'drop_time' => $dropTime24,
                'description' => $request->description,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => ($request->payment_method == 'cash_after_service') ? 'unpaid' : 'pending',
                'booking_status' => 'pending',
                'is_paid' => 0,
            ];

            // Handle different pickup types
            if ($request->pickup_type == 'chauffeur') {
                $bookingData['pickup_location'] = $request->pickup_location;
                $bookingData['drop_location'] = $request->drop_location;
                $bookingData['pickup_coordinates'] = $request->pickup_coordinates;
                $bookingData['drop_coordinates'] = $request->drop_coordinates;
            } elseif ($request->pickup_type == 'delivery') {
                $bookingData['delivery_address'] = $request->delivery_address;
                $bookingData['delivery_latitude'] = $request->delivery_latitude;
                $bookingData['delivery_longitude'] = $request->delivery_longitude;
            }

            $booking = $this->carBooking->create($bookingData);

            // ===== Mirror into regular booking system (so reports/commission use regular flow) =====
            $provider = $car->provider;
            $regularBooking = Booking::create([
                'customer_id' => auth('api')->id(),
                'provider_id' => $car->provider_id,
                'zone_id' => $provider?->zone_id,
                'booking_status' => 'pending',
                'is_paid' => 0,
                'payment_method' => $request->payment_method,
                'transaction_id' => null,
                'total_booking_amount' => $totalAmount,
                'total_tax_amount' => 0,
                'total_discount_amount' => 0,
                'total_campaign_discount_amount' => 0,
                'total_coupon_discount_amount' => 0,
                'service_schedule' => \Carbon\Carbon::parse($request->start_date . ' ' . $request->pickup_time),
                'service_address_id' => null,
                'category_id' => $car->category_id,
                'sub_category_id' => null,
                'service_address_location' => $request->pickup_type === 'delivery' ? $request->delivery_address : ($request->pickup_location ?? null),
                'notes' => $request->description,
                // store some car meta for visibility
                'car_registration_number' => $car->registration_number ?? null,
                'car_model' => $car->model ?? null,
            ]);

            // Minimal booking detail so regular booking details page has something to show
            $bookingDetail = BookingDetail::create([
                'booking_id' => $regularBooking->id,
                'service_id' => null,
                'variant_key' => null,
                'service_cost' => $totalAmount,
                'quantity' => 1,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_cost' => $totalAmount,
            ]);

            // Required for commission calculation logic on completion
            BookingDetailsAmount::create([
                'booking_details_id' => $bookingDetail->id,
                'booking_id' => $regularBooking->id,
                'service_unit_cost' => $totalAmount,
                'discount_by_admin' => 0,
                'discount_by_provider' => 0,
                'coupon_discount_by_admin' => 0,
                'coupon_discount_by_provider' => 0,
                'campaign_discount_by_admin' => 0,
                'campaign_discount_by_provider' => 0,
                'admin_commission' => 0,
            ]);

            $booking->booking_id = $regularBooking->id;
            $booking->save();

            // Wallet payment handling
            if ($request->payment_method == 'wallet_payment') {
                /** @var \Modules\UserManagement\Entities\User $user */
                $user = auth('api')->user();
                if ($user->wallet_balance < $totalAmount) {
                    throw new \Exception('Insufficient wallet balance');
                }

                $admin_user = User::where('user_type', 'admin')->first();
                $admin_user_id = $admin_user ? $admin_user->id : null;

                if ($admin_user_id) {
                    $admin_account = \Modules\TransactionModule\Entities\Account::where('user_id', $admin_user_id)->first();
                    if ($admin_account) {
                        $admin_account->balance_pending += $totalAmount;
                        $admin_account->save();
                    }

                    \Modules\TransactionModule\Entities\Transaction::create([
                        'booking_id' => 0,
                        'car_booking_id' => $booking->id,
                        'trx_type' => 'car_booking_amount',
                        'debit' => 0,
                        'credit' => $totalAmount,
                        'balance' => $admin_account ? $admin_account->balance_pending : 0,
                        'from_user_id' => $user->id,
                        'to_user_id' => $admin_user_id,
                        'to_user_account' => 'admin_balance'
                    ]);
                }

                $user->wallet_balance -= $totalAmount;
                $user->save();

                \Modules\TransactionModule\Entities\Transaction::create([
                    'booking_id' => 0,
                    'car_booking_id' => $booking->id,
                    'trx_type' => 'car_booking_wallet_payment',
                    'debit' => $totalAmount,
                    'credit' => 0,
                    'balance' => $user->wallet_balance,
                    'from_user_id' => $user->id,
                    'to_user_id' => $user->id,
                    'to_user_account' => 'user_wallet'
                ]);

                $booking->update([
                    'is_paid' => 1,
                    'payment_status' => 'paid',
                    'transaction_id' => 'wallet_payment_' . time()
                ]);

                // mirror payment fields into regular booking too
                if (!empty($booking->booking_id)) {
                    $linkedBooking = Booking::find($booking->booking_id);
                    if ($linkedBooking) {
                        $linkedBooking->is_paid = 1;
                        $linkedBooking->payment_method = 'wallet_payment';
                        $linkedBooking->transaction_id = $booking->transaction_id;
                        $linkedBooking->save();
                    }
                }
            }

            return $booking;
        });

        // Send notifications after booking is created
        $this->sendCarBookingNotifications($booking);

        if ($request->payment_method != 'cash_after_service' && $request->payment_method != 'wallet_payment') {
            // Digital Payment
            $user = auth('api')->user();
            $payer = new Payer($user->first_name . ' ' . $user->last_name, $user->email, $user->phone, '');
            $payment_info = new Payment(
                success_hook: 'car_booking_payment_success',
                failure_hook: 'car_booking_payment_fail',
                currency_code: currency_code(),
                payment_method: $request->payment_method,
                payment_platform: $request->payment_platform ?? 'app',
                payer_id: $user->id,
                receiver_id: null,
                additional_data: ['car_booking_id' => $booking->id],
                payment_amount: $totalAmount,
                external_redirect_link: $request->callback ?? null,
                attribute: 'car_booking_id',
                attribute_id: $booking->id
            );

            $receiver_info = new Receiver('Admin', 'example.png');
            $redirect_link = PaymentTrait::generate_link($payer, $payment_info, $receiver_info);

            return response()->json(response_formatter(BOOKING_PLACE_SUCCESS_200, [
                'booking' => $booking,
                'redirect_link' => $redirect_link
            ]), 200);
        }

        return response()->json(response_formatter(BOOKING_PLACE_SUCCESS_200, $booking), 200);
    }

    public function getCarTypes(): JsonResponse
    {
        $types = \Modules\CarHire\Entities\CarType::all();
        return response()->json(response_formatter(DEFAULT_200, $types), 200);
    }

    public function bookChauffeur(Request $request): JsonResponse
    {
        $request->merge(['pickup_type' => 'chauffeur']);

        return $this->store($request);
    }

    public function searchChauffeurCars(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'car_type_id' => 'required|exists:car_types,id',
            'date' => 'required|date|after_or_equal:today',
            'limit' => 'numeric|min:1|max:200',
            'offset' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);

        $categoryId = 'fd6a04cf-3803-4a9b-a830-6cfa9c3c48d4';

        $cars = $this->car->with(['type', 'category', 'provider'])
            ->where('category_id', $categoryId)
            ->where('car_type_id', $request->car_type_id)
            // ->where('preferred_areas', 'like', '%' . $request->postcode . '%')
            ->where('status', 1)
            ->latest()
            ->paginate($limit, ['*'], 'offset', $offset)
            ->withPath('');

        $cars->getCollection()->transform(function ($car) {
            $car->image_full_paths = $this->getImageFullPaths($car->images);
            return $car;
        });

        return response()->json(response_formatter(DEFAULT_200, $cars), 200);
    }


    private function getImageFullPaths($images): array
    {
        $fullPaths = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                $fullPaths[] = asset('storage/app/public/car/' . $image);
            }
        }
        return $fullPaths;
    }

    /**
     * Send notifications to provider/admin for new car booking
     * @param $booking
     * @return void
     */
    protected function sendCarBookingNotifications($booking)
    {
        // 1. Firebase Topic Notification
        $bookingNotification = (int) (business_config('booking_notification', 'business_information'))?->live_values;
        $bookingNotificationType = (business_config('booking_notification_type', 'business_information'))?->live_values;

        if ($bookingNotification && $bookingNotificationType == 'firebase') {
            try {
                // Get zone_id from related Booking or Car->Provider relationship
                $zoneId = $booking->booking ? $booking->booking->zone_id : ($booking->car->provider->zone_id ?? null);

                // Get provider_id
                $providerId = $booking->car->provider_id ?? null;

                if ($zoneId && $providerId) {
                    $topic = "demandium_provider_{$zoneId}_{$providerId}_booking_message";
                    topic_notification($topic, 'new car booking request', '', 'def.png', null);
                }
            } catch (\Exception $e) {
                // Log exception if needed, or ignore to not break flow
                info("Starting notification error: " . $e->getMessage());
            }
        }

        // 2. Device Notification (FCM) to Provider
        $bookingNotificationStatus = business_config('booking', 'notification_settings')->live_values;
        // Check if push_notification_booking is enabled
        if (isset($bookingNotificationStatus) && isset($bookingNotificationStatus['push_notification_booking']) && $bookingNotificationStatus['push_notification_booking']) {
            $provider = $booking->car->provider;
            if ($provider) {
                $fcmToken = $provider->owner->fcm_token ?? null;
                $languageKey = $provider->owner->current_language_key ?? 'en';

                $title = get_push_notification_message('new_service_request_arrived', 'provider_notification', $languageKey);

                if ($title && $fcmToken && sendDeviceNotificationPermission($provider->id)) {
                    device_notification($fcmToken, $title, null, null, $booking->id, 'car_booking');
                }
            }
        }
    }


}
