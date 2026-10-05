<?php

namespace Modules\BookingModule\Http\Controllers\Api\V1\Customer;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\BidModule\Entities\PostBid;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\BookingOfflinePayment;
use Modules\BookingModule\Entities\BookingPartialPayment;
use Modules\BookingModule\Entities\BookingRepeat;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\UserManagement\Entities\User;
use Modules\BookingModule\Entities\Booking;
use Modules\PaymentModule\Entities\OfflinePayment;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\CustomerModule\Traits\CustomerAddressTrait;
use Modules\PaymentModule\Traits\PaymentHelperTrait;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BidModule\Http\Controllers\APi\V1\Customer\PostBidController;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderTimeSlot;
use Modules\ProviderManagement\Entities\ProviderQuestion;
use Modules\BookingModule\Entities\SlotBooking;
use Modules\PaymentModule\Traits\Payment as PaymentTrait;
use Modules\PaymentModule\Library\Payment as Payment;
use Modules\PaymentModule\Library\Payer;
use Modules\PaymentModule\Library\Receiver;

class BookingController extends Controller
{
    use BookingTrait, CustomerAddressTrait, PaymentHelperTrait;

    private Booking $booking;
    private BookingStatusHistory $bookingStatusHistory;

    protected OfflinePayment $offlinePayment;
    private BookingRepeat $bookingRepeat;
    private bool $isCustomerLoggedIn;
    private mixed $customerUserId;

    public function __construct(Booking $booking, BookingStatusHistory $bookingStatusHistory, Request $request, OfflinePayment $offlinePayment, BookingRepeat $bookingRepeat)
    {
        $this->booking = $booking;
        $this->bookingStatusHistory = $bookingStatusHistory;
        $this->offlinePayment = $offlinePayment;
        $this->bookingRepeat = $bookingRepeat;

        $this->isCustomerLoggedIn = (bool) auth('api')->user();
        $this->customerUserId = $this->isCustomerLoggedIn ? auth('api')->user()->id : $request['guest_id'];
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function placeRequest(Request $request): JsonResponse
    {

        $serviceAtProviderPlace = (int) ((business_config('service_at_provider_place', 'provider_config'))->live_values ?? 0);

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:' . implode(',', array_column(PAYMENT_METHODS, 'key')),
            'zone_id' => 'required|uuid',
            'service_schedule' => 'required_if:service_type,regular|nullable|date',
            'service_address_id' => is_null($request['service_address']) ? 'required' : 'nullable',

            'post_id' => 'nullable|uuid',
            'provider_id' => 'nullable|uuid',

            'guest_id' => $this->isCustomerLoggedIn ? 'nullable' : 'required|uuid',
            'service_address' => is_null($request['service_address_id']) && $request['service_location'] == 'customer' ? [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail($attribute . ' must be a valid JSON string.');
                        return;
                    }

                    if (is_null($decoded['lat']) || $decoded['lat'] == '')
                        $fail($attribute . ' must contain "lat" properties.');
                    if (is_null($decoded['lon']) || $decoded['lon'] == '')
                        $fail($attribute . ' must contain "lon" properties.');
                    if (is_null($decoded['address']) || $decoded['address'] == '')
                        $fail($attribute . ' must contain "address" properties.');
                    if (is_null($decoded['contact_person_name']) || $decoded['contact_person_name'] == '')
                        $fail($attribute . ' must contain "contact_person_name" properties.');
                    if (is_null($decoded['contact_person_number']) || $decoded['contact_person_number'] == '')
                        $fail($attribute . ' must contain "contact_person_number" properties.');
                    if (is_null($decoded['address_label']) || $decoded['address_label'] == '')
                        $fail($attribute . ' must contain "address_label" properties.');
                },
            ] : '',

            'is_partial' => 'nullable|in:0,1',
            'service_location' => 'required|in:customer,provider',
            'booking_type' => 'nullable|in:normal,emergency',
            'selected_slot_id' => 'nullable|uuid',
            'answers' => 'nullable|array',
            'damage_description' => 'nullable|string',
            'car_registration_number' => 'nullable|string',
            'car_model' => 'nullable|string',
            'car_manufacture_year' => 'nullable|string|max:10',
            'car_color' => 'nullable|string',
            'special_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'postcode' => 'nullable|string',
            'car_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            function ($attribute, $value, $fail) use ($serviceAtProviderPlace) {
                if ($value == 'provider' && $serviceAtProviderPlace != 1) {
                    $fail('The selected service location cannot be "provider" because the service is not available at the provider’s place.');
                }
            },
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $newUserInfo = null;
        // Additional validation and register for new_user_info
        if ($request->has('new_user_info') && !empty($request->get('new_user_info')) && !$this->isCustomerLoggedIn) {
            $newUserInfo = json_decode($request['new_user_info'], true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($newUserInfo)) {
                return response()->json(response_formatter(DEFAULT_400, null, 'Invalid new_user_info format'), 400);
            }

            $newUserValidator = Validator::make($newUserInfo, [
                'first_name' => 'required',
                'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
                'password' => 'required|min:8',
            ]);

            if ($newUserValidator->fails()) {
                return response()->json(response_formatter(DEFAULT_400, null, error_processor($newUserValidator)), 400);
            }
        }

        $customerUserId = $this->customerUserId;

        if (is_null($request['service_address_id'])) {
            $request['service_address_id'] = $this->add_address(json_decode($request['service_address']), null, !$this->isCustomerLoggedIn, $request->service_location);
        }

        $minimumBookingAmount = (float) (business_config('min_booking_amount', 'booking_setup'))?->live_values;
        $totalBookingAmount = cart_total($customerUserId) + getServiceFee();

        if (!isset($request['post_id']) && $minimumBookingAmount > 0 && $totalBookingAmount < $minimumBookingAmount) {
            return response()->json(response_formatter(MINIMUM_BOOKING_AMOUNT_200), 200);
        }

        $additionalData = [
            'booking_type' => $request->booking_type,
            'selected_slot_id' => $request->selected_slot_id,
            'answers' => $request->answers,
            'damage_description' => $request->damage_description,
            'car_registration_number' => $request->car_registration_number,
            'car_model' => $request->car_model,
            'car_manufacture_year' => $request->car_manufacture_year,
            'car_color' => $request->car_color,
            'special_conditions' => $request->special_conditions,
            'notes' => $request->notes,
            'postcode' => $request->postcode,
            'car_image' => $request->file('car_image'),
        ];

        if ($request['payment_method'] == 'wallet_payment') {
            if (!isset($request['post_id'])) {
                $response = $this->placeBookingRequest(userId: $customerUserId, request: $request, transactionId: 'wallet_payment', newUserInfo: $newUserInfo, additionalData: $additionalData);
            } else {
                $postBid = PostBid::with(['post'])
                    ->where('post_id', $request['post_id'])
                    ->where('provider_id', $request['provider_id'])
                    ->first();

                $data = [
                    'post_id' => $request['post_id'],
                    'payment_method' => $request['payment_method'],
                    'zone_id' => $request['zone_id'],
                    'service_tax' => $postBid?->post?->service?->tax,
                    'provider_id' => $postBid->provider_id,
                    'price' => $postBid->offered_price,
                    'service_schedule' => !is_null($request['booking_schedule']) ? $request['booking_schedule'] : $postBid->post->booking_schedule,
                    'service_id' => $postBid->post->service_id,
                    'category_id' => $postBid->post->category_id,
                    'sub_category_id' => $postBid->post->category_id,
                    'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $postBid->post->service_address_id,
                    'is_partial' => $request['is_partial']
                ];

                $user = User::find($customerUserId);
                $tax = !is_null($data['service_tax']) ? round((($data['price'] * $data['service_tax']) / 100) * 1, 2) : 0;
                if (isset($user) && $user->wallet_balance < ($postBid->offered_price + $tax)) {
                    return response()->json(response_formatter(INSUFFICIENT_WALLET_BALANCE_400), 400);
                }

                $response = $this->placeBookingRequestForBidding($customerUserId, $request, 'wallet_payment', $data);

                if ($response['flag'] == 'success') {
                    PostBidController::acceptPostBidOffer($postBid->id, $response['booking_id']);
                }
            }

        } elseif ($request['payment_method'] == 'offline_payment') {
            if (!isset($request['post_id'])) {
                $response = $this->placeBookingRequest($customerUserId, $request, 'offline-payment', newUserInfo: $newUserInfo, isGuest: !$this->isCustomerLoggedIn, additionalData: $additionalData);
            } else {
                $postBid = PostBid::with(['post'])
                    ->where('post_id', $request['post_id'])
                    ->where('provider_id', $request['provider_id'])
                    ->first();

                $data = [
                    'post_id' => $request['post_id'],
                    'payment_method' => $request['payment_method'],
                    'zone_id' => $request['zone_id'],
                    'service_tax' => $postBid?->post?->service?->tax,
                    'provider_id' => $postBid->provider_id,
                    'price' => $postBid->offered_price,
                    'service_schedule' => !is_null($request['booking_schedule']) ? $request['booking_schedule'] : $postBid->post->booking_schedule,
                    'service_id' => $postBid->post->service_id,
                    'category_id' => $postBid->post->category_id,
                    'sub_category_id' => $postBid->post->category_id,
                    'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $postBid->post->service_address_id,
                    'is_partial' => $request['is_partial']
                ];

                $response = $this->placeBookingRequestForBidding($customerUserId, $request, 'offline_payment', $data);

                if ($response['flag'] == 'success') {
                    PostBidController::acceptPostBidOffer($postBid->id, $response['booking_id']);
                }
            }
        } elseif (in_array($request['payment_method'], array_column(DIGITAL_PAYMENT_METHODS, 'key'))) {
            // Register new user from guest info
            if (!empty($newUserInfo) && !$this->isCustomerLoggedIn) {
                $address = UserAddress::find($request['service_address_id']);
                $customer = collect([
                    'first_name' => $address['contact_person_name'] ?? 'Guest',
                    'last_name' => '',
                    'phone' => $address['contact_person_number'] ?? '',
                    'email' => '',
                ]);
            } else {
                $user = User::find($customerUserId);
                if (!$user) {
                    return response()->json(response_formatter(DEFAULT_401, null, 'User not found or token expired.'), 401);
                }
                $customer = collect([
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                ]);
            }

            $query_params = array_merge($validator->validated(), [
                'service_address_id' => $request['service_address_id'],
                'customer' => base64_encode($customer),
                'access_token' => base64_encode($customerUserId)
            ]);

            if ($newUserInfo != null) {
                $query_params['register_new_customer'] = 1;
                $query_params = array_merge($query_params, $newUserInfo);
            }

            if (isset($request['post_id']) && !empty($request['post_id'])) {
                $postBid = PostBid::where('post_id', $request['post_id'])
                    ->where('provider_id', $request['provider_id'])
                    ->first();
                if (!$postBid) {
                    $postBid = PostBid::where('post_id', $request['post_id'])->first();
                }
                if (!$postBid) {
                    $post = \Modules\BidModule\Entities\Post::find($request['post_id']);
                    if (!$post) {
                        return response()->json(response_formatter(DEFAULT_404, null, 'Post not found'), 404);
                    }
                }
            }

            try {
                $total_booking_amount = $this->find_total_Booking_amount($customerUserId, $request['post_id'], $request['provider_id']);
                $user = User::find($customerUserId);
                $customer_wallet_balance = $user ? $user->wallet_balance : 0;
                $amount_to_pay = $request['is_partial'] ? round($total_booking_amount * 0.25, 2) : $total_booking_amount;

                $payer = new Payer($customer['first_name'] . ' ' . $customer['last_name'], $customer['email'], $customer['phone'], '');
                $payment_info = new Payment(
                    success_hook: 'digital_payment_success',
                    failure_hook: 'digital_payment_fail',
                    currency_code: currency_code(),
                    payment_method: $request->payment_method,
                    payment_platform: $request->payment_platform ?? 'app',
                    payer_id: $customerUserId,
                    receiver_id: null,
                    additional_data: $query_params,
                    payment_amount: $amount_to_pay,
                    external_redirect_link: $request['callback'] ?? null,
                    attribute: 'booking_id',
                    attribute_id: time()
                );

                $receiver_info = new Receiver('Admin', 'example.png');
                $redirect_link = PaymentTrait::generate_link($payer, $payment_info, $receiver_info);

                return response()->json(response_formatter(DEFAULT_200, [
                    'redirect_link' => $redirect_link,
                    'amount' => $amount_to_pay
                ]), 200);
            } catch (\Exception $e) {
                Log::info($e->getMessage());
                return response()->json(response_formatter(DEFAULT_400, null, $e->getMessage()), 400);
            }

        } else {
            if (isset($request['post_id'])) {
                $postBid = PostBid::with(['post'])
                    ->where('post_id', $request['post_id'])
                    ->where('provider_id', $request['provider_id'])
                    ->first();

                if (!$postBid) {
                    return response()->json(response_formatter(DEFAULT_404, null, 'Post bid offer not found'), 404);
                }

                $data = [
                    'post_id' => $request['post_id'],
                    'payment_method' => $request['payment_method'],
                    'zone_id' => $request['zone_id'],
                    'service_tax' => $postBid?->post?->service?->tax,
                    'provider_id' => $postBid->provider_id,
                    'price' => $postBid->offered_price,
                    'service_schedule' => !is_null($request['service_schedule'] ?? $request['booking_schedule']) ? ($request['service_schedule'] ?? $request['booking_schedule']) : $postBid->post->booking_schedule,
                    'service_id' => $postBid->post->service_id,
                    'category_id' => $postBid->post->category_id,
                    'sub_category_id' => $postBid->post->sub_category_id ?? $postBid->post->category_id,
                    'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $postBid->post->service_address_id,
                    'is_partial' => $request['is_partial'] ?? 0
                ];

                $response = $this->placeBookingRequestForBidding($customerUserId, $request, 'cash-payment', $data);

                if ($response['flag'] == 'success') {
                    PostBidController::acceptPostBidOffer($postBid->id, $response['booking_id']);
                }
            } elseif ($request['service_type'] == 'repeat') {
                $response = $this->placeRepeatBookingRequest($customerUserId, $request, 'cash-payment', newUserInfo: $newUserInfo, isGuest: !$this->isCustomerLoggedIn);
            } else {
                $response = $this->placeBookingRequest($customerUserId, $request, 'cash-payment', newUserInfo: $newUserInfo, isGuest: !$this->isCustomerLoggedIn, additionalData: $additionalData);
            }
        }

        if ($response['flag'] == 'success') {
            return response()->json(response_formatter(BOOKING_PLACE_SUCCESS_200, $response), 200);
        } else {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200), 200);
        }
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    // public function index(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [
    //         'limit' => 'required|numeric|min:1|max:200',
    //         'offset' => 'required|numeric|min:1|max:100000',
    //         'booking_status' => 'required|in:all,' . implode(',', array_column(BOOKING_STATUSES, 'key')),
    //         'service_type' => 'required|in:all,regular,repeat',
    //         'string' => 'string'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
    //     }

    //     $bookings = $this->booking
    //         ->with(['customer', 'repeat', 'customizeBooking'])
    //         ->where(['customer_id' => $request->user()->id])
    //         ->search(base64_decode($request['string']), ['readable_id'])
    //         ->when($request['booking_status'] != 'all', function ($query) use ($request) {
    //             return $query->ofBookingStatus($request['booking_status']);
    //         })
    //         ->when($request['service_type'] != 'all', function ($query) use ($request) {
    //             return $query->ofRepeatBookingStatus($request['service_type'] === 'repeat' ? 1 : ($request['service_type'] === 'regular' ? 0 : null));
    //         })
    //         ->latest()
    //         ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
    //         ->withPath('');

    //     foreach ($bookings as $booking) {
    //         if ($booking->repeat->isNotEmpty()) {
    //             $sortedRepeats = $booking->repeat->sortBy(function ($repeat) {
    //                 $parts = explode('-', $repeat->readable_id);
    //                 $suffix = end($parts);
    //                 return $this->readableIdToNumber($suffix);
    //             });
    //             $booking->repeats = $sortedRepeats->values()->toArray();
    //         }
    //         $booking->is_customize_booking = $booking->customizeBooking ? 1 : 0;

    //         unset($booking->repeat);
    //         unset($booking->customizeBooking);
    //     }

    //     return response()->json(response_formatter(DEFAULT_200, $bookings), 200);
    // }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'booking_status' => 'required|in:all,' . implode(',', array_column(BOOKING_STATUSES, 'key')),
            'service_type' => 'required|in:all,regular,repeat',
            'string' => 'string',
            'booking_type' => 'sometimes|in:all,regular,car' // New parameter to filter by booking type
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $bookingType = $request->input('booking_type', 'all');
        $result = [];

        if ($bookingType === 'all' || $bookingType === 'regular') {
            // Get regular bookings
            $regularBookings = $this->booking
                ->with(['customer', 'repeat', 'customizeBooking', 'booking_partial_payments'])
                ->where(['customer_id' => $request->user()->id])
                ->search(base64_decode($request['string']), ['readable_id'])
                ->when($request['booking_status'] != 'all', function ($query) use ($request) {
                    return $query->ofBookingStatus($request['booking_status']);
                })
                ->when($request['service_type'] != 'all', function ($query) use ($request) {
                    return $query->ofRepeatBookingStatus($request['service_type'] === 'repeat' ? 1 : ($request['service_type'] === 'regular' ? 0 : null));
                })
                ->latest()
                ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
                ->withPath('');

            foreach ($regularBookings as $booking) {
                if ($booking->repeat->isNotEmpty()) {
                    $sortedRepeats = $booking->repeat->sortBy(function ($repeat) {
                        $parts = explode('-', $repeat->readable_id);
                        $suffix = end($parts);
                        return $this->readableIdToNumber($suffix);
                    });
                    $booking->repeats = $sortedRepeats->values()->toArray();
                }
                $booking->is_customize_booking = $booking->customizeBooking ? 1 : 0;
                $booking->booking_type = 'regular';

                // Partial payment details for mobile app
                if ($booking->booking_partial_payments && $booking->booking_partial_payments->isNotEmpty()) {
                    $latestPartial = $booking->booking_partial_payments->sortByDesc('created_at')->first();
                    $totalPaid = (float) $booking->booking_partial_payments->sum('paid_amount');
                    $dueAmount = (float) ($latestPartial?->due_amount ?? 0);

                    $booking->is_partial = 1;
                    $booking->paid_amount = $totalPaid;
                    $booking->due_amount = $dueAmount;
                    $booking->partial_paid_amount = $totalPaid;
                    $booking->partial_due_amount = $dueAmount;
                } else {
                    $booking->is_partial = 0;
                    $isPaid = (int) $booking->is_paid;
                    $total = (float) $booking->total_booking_amount;
                    $booking->paid_amount = $isPaid ? $total : 0.0;
                    $booking->due_amount = $isPaid ? 0.0 : $total;
                    $booking->partial_paid_amount = $booking->paid_amount;
                    $booking->partial_due_amount = $booking->due_amount;
                }

                unset($booking->repeat);
                unset($booking->customizeBooking);
            }

            $result['regular_bookings'] = $regularBookings;
        }

        if ($bookingType === 'all' || $bookingType === 'car') {
            // Get car bookings
            $carBookings = \Modules\CarHire\Entities\CarBooking::with(['car', 'user'])
                ->where(['user_id' => $request->user()->id])
                ->when($request['booking_status'] != 'all', function ($query) use ($request) {
                    return $query->where('booking_status', $request['booking_status']);
                })
                ->when($request['string'], function ($query) use ($request) {
                    return $query->where('id', 'like', '%' . base64_decode($request['string']) . '%');
                })
                ->latest()
                ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
                ->withPath('');

            foreach ($carBookings as $booking) {
                $booking->booking_type = 'car';
                // Add any car-specific transformations here
            }

            $result['car_bookings'] = $carBookings;
        }

        return response()->json(response_formatter(DEFAULT_200, $result), 200);
    }



    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $booking = $this->booking
            ->where(['customer_id' => $request->user()->id])
            ->with([
                'detail.service',
                'schedule_histories.user',
                'status_histories.user',
                'customer',
                'provider',
                'category',
                'subCategory:id,name',
                'serviceman.user',
                'booking_partial_payments',
                'repeat.scheduleHistories',
                'repeat.repeatHistories'
            ])
            ->where(['id' => $id])
            ->first();

        if (isset($booking)) {
            $offlinePayment = $booking->booking_offline_payments?->first();

            if ($offlinePayment) {
                $booking->booking_offline_payment_method = $offlinePayment->method_name;
                $booking->booking_offline_payment = collect($offlinePayment->customer_information)->map(function ($value, $key) {
                    return ["key" => $key, "value" => $value];
                })->values()->all();

                $booking->offline_payment_id = $offlinePayment->offline_payment_id ?? null;
                $booking->offline_payment_status = $offlinePayment->payment_status ?? null;
                $booking->offline_payment_denied_note = $offlinePayment->denied_note ?? null;
            }

            $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

            unset($booking->booking_offline_payments, $booking->service_address_location);

            if (isset($booking->provider)) {
                $booking->provider->chatEligibility = chatEligibility($booking->provider_id);
            }

            if ($booking->repeat->isNotEmpty()) {
                $repeatHistoryCollection = $booking->repeat->flatMap(function ($repeat) {
                    return $repeat->repeatHistories->map(function ($history) {
                        $history->log_details = json_decode($history->log_details);
                        return $history;
                    });
                });

                $booking['repeatHistory'] = $repeatHistoryCollection->toArray();
                $sortedRepeats = $booking->repeat->sortBy(function ($repeat) {
                    $parts = explode('-', $repeat->readable_id);
                    $suffix = end($parts);
                    $repeat['service_address'] = json_decode($repeat->service_address_location);
                    unset($repeat->service_address_location);
                    return $this->readableIdToNumber($suffix);
                });
                $booking['repeats'] = $sortedRepeats->values()->toArray();

                $nextService = collect($booking['repeats'])->firstWhere('booking_status', 'accepted');
                if (!$nextService) {
                    $nextService = collect($booking['repeats'])->firstWhere('booking_status', 'pending');
                }

                $booking['nextService'] = $nextService;
                $booking['time'] = max(collect($booking['repeats'])->pluck('service_schedule')->flatten()->toArray());
                $booking['startDate'] = min(collect($booking['repeats'])->pluck('service_schedule')->flatten()->toArray());
                $booking['endDate'] = max(collect($booking['repeats'])->pluck('service_schedule')->flatten()->toArray());
                $booking['totalCount'] = count($booking['repeats']);
                $booking['bookingType'] = $booking['repeats'][0]['booking_type'];

                if ($booking['bookingType'] == 'weekly') {
                    $booking['weekNames'] = collect($booking['repeats'])
                        ->pluck('service_schedule')
                        ->map(function ($schedule) {
                            return \Carbon\Carbon::parse($schedule)->format('l');
                        })
                        ->unique()
                        ->sort()
                        ->values()
                        ->toArray();
                }

                $booking['completedCount'] = collect($booking['repeats'])->where('booking_status', 'completed')->count();
                $booking['canceledCount'] = collect($booking['repeats'])->where('booking_status', 'canceled')->count();

                unset($booking->repeat);
                $booking['repeats'] = array_map(function ($repeat) {
                    if (isset($repeat['repeat_histories'])) {
                        unset($repeat['repeat_histories']);
                    }
                    return $repeat;
                }, $booking['repeats']);
            }

            $booking->is_customize_booking = $booking->customizeBooking ? 1 : 0;
            unset($booking->customizeBooking);

            // Partial payment details for mobile app
            if ($booking->booking_partial_payments && $booking->booking_partial_payments->isNotEmpty()) {
                $latestPartial = $booking->booking_partial_payments->sortByDesc('created_at')->first();
                $totalPaid = (float) $booking->booking_partial_payments->sum('paid_amount');
                $dueAmount = (float) ($latestPartial?->due_amount ?? 0);

                $booking->is_partial = 1;
                $booking->paid_amount = $totalPaid;
                $booking->due_amount = $dueAmount;
                $booking->partial_paid_amount = $totalPaid;
                $booking->partial_due_amount = $dueAmount;
            } else {
                $booking->is_partial = 0;
                $isPaid = (int) $booking->is_paid;
                $total = (float) $booking->total_booking_amount;
                $booking->paid_amount = $isPaid ? $total : 0.0;
                $booking->due_amount = $isPaid ? 0.0 : $total;
                $booking->partial_paid_amount = $booking->paid_amount;
                $booking->partial_due_amount = $booking->due_amount;
            }

            return response()->json(response_formatter(DEFAULT_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function singleDetails(Request $request, string $id): JsonResponse
    {
        $booking = $this->bookingRepeat->with([
            'detail.service',
            'scheduleHistories.user',
            'statusHistories.user',
            'booking.customer',
            'provider',
            'serviceman.user'
        ])->where(['id' => $id])->first();

        $booking->booking->service_address = $booking->booking->service_address_location != null ? json_decode($booking->booking->service_address_location) : $booking->booking->service_address;

        if (isset($booking)) {
            if (isset($booking->provider)) {
                $booking->provider->chatEligibility = chatEligibility($booking->provider_id);
            }
            return response()->json(response_formatter(DEFAULT_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }
    /**
     * Show the specified resource.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function track(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking
            ->with(['detail.service', 'schedule_histories.user', 'status_histories.user', 'customer', 'provider', 'zone', 'serviceman.user'])
            ->where(['readable_id' => $id])
            ->whereHas('service_address', fn($query) => $query->where('contact_person_number', $request['phone']))
            ->first();

        $booking->service_address = $booking->service_address_location != null ? json_decode($booking->service_address_location) : $booking->service_address;

        unset($booking->service_address_location);

        if (isset($booking))
            return response()->json(response_formatter(DEFAULT_200, $booking), 200);

        return response()->json(response_formatter(DEFAULT_404, $booking), 404);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $booking_id
     * @return JsonResponse
     */
    public function statusUpdate(Request $request, string $booking_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_status' => 'required|in:canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $booking_id)->where('customer_id', $request->user()->id)->first();

        if (isset($booking)) {

            if ($booking->booking_status == 'accepted' && $request['booking_status'] == 'canceled') {
                return response()->json(response_formatter(BOOKING_ALREADY_ACCEPTED), 200);
            }

            if ($booking->booking_status == 'ongoing' && $request['booking_status'] == 'canceled') {
                return response()->json(response_formatter(BOOKING_ALREADY_ONGOING), 200);
            }

            if ($booking->booking_status == 'completed' && $request['booking_status'] == 'canceled') {
                return response()->json(response_formatter(BOOKING_ALREADY_COMPLETED), 200);
            }

            $booking->booking_status = $request['booking_status'];

            $bookingStatusHistory = $this->bookingStatusHistory;
            $bookingStatusHistory->booking_id = $booking_id;
            $bookingStatusHistory->changed_by = $request->user()->id;
            $bookingStatusHistory->booking_status = $request['booking_status'];

            DB::transaction(function () use ($bookingStatusHistory, $booking, $request) {
                $booking->save();
                $bookingStatusHistory->save();

                if ($request['booking_status'] == 'canceled' && $booking->repeat->isNotEmpty()) {
                    foreach ($booking->repeat as $repeat) {
                        $repeat->booking_status = 'canceled';
                        $repeat->setAttribute('skipNotification', false);
                        unset($repeat->skipNotification);
                        $repeat->save();

                        $repeatBookingStatusHistory = new $this->bookingStatusHistory;
                        $repeatBookingStatusHistory->booking_id = 0;
                        $repeatBookingStatusHistory->booking_repeat_id = $repeat->id;
                        $repeatBookingStatusHistory->changed_by = $request->user()->id;
                        $repeatBookingStatusHistory->booking_status = 'canceled';
                        $repeatBookingStatusHistory->save();
                    }
                }
            });

            return response()->json(response_formatter(BOOKING_STATUS_UPDATE_SUCCESS_200, $booking), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * @param Request $request
     * @param string $repeatId
     * @return JsonResponse
     */
    public function singleBookingCancel(Request $request, string $repeatId): JsonResponse
    {
        $customerId = $request->user()->id;
        $repeat = $this->bookingRepeat->where('id', $repeatId)->first();
        $bookingId = $repeat->booking_id;
        $booking = $this->booking->where('id', $bookingId)->where('customer_id', $customerId)->first();

        if ($booking && $repeat) {
            $statusCheck = $repeat->booking_status == 'canceled';
            if ($statusCheck) {
                return response()->json(response_formatter(BOOKING_ALREADY_CANCELED_200), 200);
            }

            DB::transaction(function () use ($repeat) {
                $repeat->booking_status = 'canceled';
                $repeat->save();
            });

            return response()->json(response_formatter(DEFAULT_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeOfflinePaymentData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'offline_payment_id' => 'required',
            'customer_information' => 'required',
            'booking_id' => 'required',
            'is_partial' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        // Retrieve booking
        $booking = $this->booking->find($request->booking_id);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $offlinePaymentData = $this->offlinePayment->find($request['offline_payment_id']);
        if (!$offlinePaymentData) {
            return response()->json(response_formatter(DEFAULT_400, null, 'Invalid offline payment ID.'), 400);
        }

        $fields = array_column($offlinePaymentData->customer_information, 'field_name');
        $customerInformation = (array) json_decode(base64_decode($request['customer_information']))[0];

        foreach ($fields as $field) {
            if (!key_exists($field, $customerInformation)) {
                return response()->json(response_formatter(DEFAULT_400, $fields, null), 400);
            }
        }

        // Handle partial payment if applicable
        if ($request->is_partial) {
            $user = auth('api')->user();
            $walletBalance = $user->wallet_balance;

            if ($walletBalance <= 0 || $walletBalance >= $booking->total_booking_amount) {
                return response()->json(response_formatter(DEFAULT_400, null, 'Invalid partial payment data.'), 400);
            }

            $paidAmount = $walletBalance;
            $dueAmount = $booking->total_booking_amount - $paidAmount;

            // Save wallet payment
            BookingPartialPayment::create([
                'booking_id' => $booking->id,
                'paid_with' => 'wallet',
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
            ]);

            // Save remaining payment
            BookingPartialPayment::create([
                'booking_id' => $booking->id,
                'paid_with' => 'offline_payment',
                'paid_amount' => $dueAmount,
                'due_amount' => 0,
            ]);

            placeBookingTransactionForPartialDigital($booking);
        }

        // Check if the booking_id already exists
        $existingPayment = BookingOfflinePayment::where('booking_id', $request->booking_id)->first();

        $customerInformation = (array) json_decode(base64_decode($request['customer_information']))[0];

        if ($existingPayment) {
            // If it exists, update with new data
            $existingPayment->offline_payment_id = $request['offline_payment_id'];
            $existingPayment->method_name = OfflinePayment::find($request['offline_payment_id'])?->method_name;
            $existingPayment->customer_information = $customerInformation;
            $existingPayment->payment_status = 'pending';
            $existingPayment->save();
        } else {
            // If no existing record, create a new one
            $bookingOfflinePayment = new BookingOfflinePayment();
            $bookingOfflinePayment->booking_id = $request->booking_id;
            $bookingOfflinePayment->offline_payment_id = $request['offline_payment_id'];
            $bookingOfflinePayment->method_name = OfflinePayment::find($request['offline_payment_id'])?->method_name;
            $bookingOfflinePayment->customer_information = $customerInformation;
            $bookingOfflinePayment->payment_status = 'pending';
            $bookingOfflinePayment->save();
        }

        $booking->update(['payment_method' => 'offline_payment']);

        return response()->json(response_formatter(OFFLINE_PAYMENT_SUCCESS_200), 200);
    }

    public function switchPaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required',
            'payment_method' => 'required',
            'offline_payment_id' => 'required_if:payment_method,offline_payment',
            'customer_information' => 'required_if:payment_method,offline_payment',
            'is_partial' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        // Retrieve booking
        $booking = $this->booking->find($request->booking_id);
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        // Handle partial payment if applicable
        if ($request->is_partial) {
            $user = auth('api')->user();
            $walletBalance = $user->wallet_balance;

            if ($walletBalance <= 0 || $walletBalance >= $booking->total_booking_amount) {
                return response()->json(response_formatter(DEFAULT_400, null, 'Invalid partial payment data.'), 400);
            }

            $paidAmount = $walletBalance;
            $dueAmount = $booking->total_booking_amount - $paidAmount;

            // Save wallet payment
            BookingPartialPayment::create([
                'booking_id' => $booking->id,
                'paid_with' => 'wallet',
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
            ]);

            // Save remaining payment
            BookingPartialPayment::create([
                'booking_id' => $booking->id,
                'paid_with' => 'digital',
                'paid_amount' => $dueAmount,
                'due_amount' => 0,
            ]);
        }

        // Handle payment method updates
        if ($request->payment_method == 'cash_after_service') {
            $booking->update(['payment_method' => 'cash_after_service', 'transaction_id' => 'cash-payment', 'is_verified' => 1]);
            if ($booking->booking_partial_payments->isNotEmpty()) {
                // Delete rows where `paid_with` is not 'wallet'
                $booking->booking_partial_payments()
                    ->where('paid_with', '!=', 'wallet')
                    ->delete();
            }
            if ($request->is_partial) {
                placeBookingTransactionForPartialCas($booking);
            }

        } elseif ($request->payment_method == 'wallet_payment') {
            $booking->update(['payment_method' => 'wallet_payment', 'transaction_id' => 'wallet-payment']);
            placeBookingTransactionForWalletPayment($booking);

        } elseif (in_array($request->payment_method, array_column(PAYMENT_METHODS, 'key'))) {
            // Digital Payment (e.g., Stripe)
            $user = auth('api')->user();
            $payer = new Payer($user->first_name . ' ' . $user->last_name, $user->email, $user->phone, '');

            // Check if it's a Car Hire booking
            $car_booking = \Modules\CarHire\Entities\CarBooking::where('booking_id', $booking->id)->first();

            $attribute = 'booking_id';
            $attribute_id = $booking->readable_id;
            $amount = $booking->total_booking_amount;

            if ($request->is_partial) {
                $user = auth('api')->user();
                $amount = $booking->total_booking_amount - $user->wallet_balance;
            }

            $success_hook = 'digital_payment_success';
            $failure_hook = 'digital_payment_fail';

            if ($car_booking) {
                $attribute = 'car_booking_id';
                $attribute_id = $car_booking->id;
                $amount = $car_booking->total_amount;
                $success_hook = 'car_booking_payment_success';
                $failure_hook = 'car_booking_payment_fail';
            }

            $payment_info = new Payment(
                success_hook: $success_hook,
                failure_hook: $failure_hook,
                currency_code: currency_code(),
                payment_method: $request->payment_method,
                payment_platform: $request->payment_platform ?? 'app',
                payer_id: $user->id,
                receiver_id: null,
                additional_data: [
                    'booking_id' => $booking->id,
                    'callback' => $request->callback
                ],
                payment_amount: $amount,
                external_redirect_link: $request->callback ?? null,
                attribute: $attribute,
                attribute_id: $attribute_id
            );

            $receiver_info = new Receiver('Admin', 'example.png');
            $redirect_link = PaymentTrait::generate_link($payer, $payment_info, $receiver_info);

            return response()->json(response_formatter(DEFAULT_200, [
                'redirect_link' => $redirect_link,
                'amount' => $amount
            ]), 200);

        } else {
            return response()->json(response_formatter(DEFAULT_400, null, 'Invalid payment method.'), 400);
        }

        return response()->json(response_formatter(PAYMENT_METHOD_UPDATE_200), 200);
    }

    public function digitalPaymentBookingResponse(Request $request): JsonResponse|array
    {
        if ($request->has('token')) {
            $token_data = base64_decode($request->token);
            $output = [];
            parse_str(str_replace('&&', '&', $token_data), $output);
            $request->merge([
                'transaction_id' => $output['transaction_reference'] ?? null,
                'attribute_id' => $output['attribute_id'] ?? null,
            ]);
        }

        $payment_info = null;
        if ($request->has('transaction_id') && !empty($request->transaction_id)) {
            $payment_info = PaymentRequest::where('transaction_id', $request->transaction_id)->first();
        }

        if (!$payment_info && $request->has('attribute_id') && !empty($request->attribute_id)) {
            $payment_info = PaymentRequest::where('attribute_id', $request->attribute_id)->first();
        }

        if (!$payment_info && $request->has('payment_id') && !empty($request->payment_id)) {
            $payment_info = PaymentRequest::where('id', $request->payment_id)->first();
        }

        if (!$payment_info) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $additional_data = json_decode($payment_info->additional_data, true);

        $booking_repeat_id = $additional_data['booking_repeat_id'] ?? null;
        $register_new_customer = $additional_data['register_new_customer'] ?? 0;
        $new_user_phone = $register_new_customer == 1 ? $additional_data['phone'] : null;

        $booking = null;
        $booking_id = null;
        $car_booking = null;
        if (isset($payment_info) && $payment_info->attribute_id != null) {
            if ($payment_info->attribute === 'booking' || $payment_info->attribute === 'booking_id') {
                $booking = Booking::where('readable_id', $payment_info->attribute_id)
                    ->orWhere('id', $payment_info->attribute_id)
                    ->first();
                $booking_id = $booking ? $booking->id : null;
            } elseif ($payment_info->attribute === 'car_booking_id') {
                $car_booking = \Modules\CarHire\Entities\CarBooking::find($payment_info->attribute_id);
                if ($car_booking) {
                    $booking_id = $car_booking->booking_id; // Linked regular booking
                    $booking = Booking::find($booking_id);
                }
            }
        }

        // Fallback: If booking was not found by attribute_id (e.g. timestamp attribute_id), search by transaction_id or customer_id
        if (!$booking && $payment_info) {
            $customer_id = $payment_info->payer_id;
            $booking = Booking::where('transaction_id', $payment_info->transaction_id)->first();
            if (!$booking && !empty($customer_id)) {
                $booking = Booking::where('customer_id', $customer_id)->latest()->first();
            }
            if ($booking) {
                $booking_id = $booking->id;
                $payment_info->attribute = 'booking';
                $payment_info->attribute_id = $booking->readable_id;
                $payment_info->save();
            }
        }

        $loginToken = null;
        if ($register_new_customer == 1 && $new_user_phone != null) {
            $user = new User();
            $user->first_name = $additional_data['first_name'];
            $user->last_name = '';
            $user->phone = $additional_data['phone'];
            $user->password = bcrypt($additional_data['password']);
            $user->user_type = 'customer';
            $user->is_active = 1;
            $user->save();

            if ($user && $booking) {
                $booking->customer_id = $user->id;
                $booking->is_guest = 0;
                $booking->save();
            }

            $loginToken = $user->createToken('CUSTOMER_PANEL_ACCESS')->accessToken;
        }

        $response = [
            'booking_id' => $booking_id,
            'booking_repeat_id' => $booking_repeat_id,
            'new_user_phone' => $new_user_phone,
            'login_token' => $loginToken,
        ];

        return response()->json(response_formatter(DEFAULT_200, $response), 200);

    }

    /**
     * Get available time slots for a provider on a specific date
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|uuid',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = Provider::where('id', $request['provider_id'])
            ->orWhere('user_id', $request['provider_id'])
            ->first();

        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l'));

        // Get slots for this day
        $slots = ProviderTimeSlot::where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', 1)
            ->get();

        $availableSlots = [];

        foreach ($slots as $slot) {
            // Check booked count for this slot on this date
            $bookedCount = SlotBooking::where('provider_time_slot_id', $slot->id)
                ->where('booking_date', $request->date)
                ->count();

            if ($bookedCount < $slot->max_bookings) {
                $availableSlots[] = $slot;
            }
        }

        return response()->json(response_formatter(DEFAULT_200, $availableSlots), 200);
    }

    /**
     * Get custom questions for a provider
     * @param Request $request
     * @return JsonResponse
     */
    public function getProviderQuestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'nullable|uuid',
            'category_id' => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = null;
        if (!empty($request['provider_id'])) {
            $provider = Provider::where('id', $request['provider_id'])
                ->orWhere('user_id', $request['provider_id'])
                ->first();
            $providerId = $provider?->id;
        }

        $questions = ProviderQuestion::where('is_active', 1)
            ->when(!empty($providerId), function ($query) use ($providerId) {
                $query->where(function ($q) use ($providerId) {
                    $q->whereNull('provider_id')
                        ->orWhere('provider_id', $providerId);
                });
            }, function ($query) {
                $query->whereNull('provider_id');
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereNull('category_id')
                        ->orWhere('category_id', $request->category_id);
                });
            })
            ->orderBy('display_order')
            ->get();

        return response()->json(response_formatter(DEFAULT_200, $questions), 200);
    }


public function withCarDetails(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'limit' => 'required|numeric|min:1|max:200',
        'offset' => 'required|numeric|min:1|max:100000',
        'booking_status' => 'required|in:all,pending,accepted,ongoing,completed,cancelled,rejected',
        'string' => 'string',
    ]);

    if ($validator->fails()) {
        return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
    }

    // Get car bookings with only required fields
    $carBookings = \Modules\CarHire\Entities\CarBooking::with([
        'car' => function($query) {
            $query->select('id', 'brand', 'model', 'registration_number', 'manufacture_year', 'seating_capacity');
        },
        'user' => function($query) {
            $query->select('id', 'first_name', 'last_name', 'email', 'phone', 'profile_image');
        }
    ])
    ->where(['user_id' => $request->user()->id])
    ->when($request['booking_status'] != 'all', function ($query) use ($request) {
        return $query->where('booking_status', $request['booking_status']);
    })
    ->when($request['string'], function ($query) use ($request) {
        return $query->where(function($q) use ($request) {
            $q->where('id', 'like', '%' . base64_decode($request['string']) . '%')
              ->orWhere('booking_id', 'like', '%' . base64_decode($request['string']) . '%');
        });
    })
    ->latest()
    ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
    ->withPath('');

    // Transform the data
    $carBookings->getCollection()->transform(function ($booking) {
        return [
            'id' => $booking->id,
            'booking_id' => $booking->booking_id,
            'start_date' => $booking->start_date,
            'end_date' => $booking->end_date,
            'pickup_location' => $booking->pickup_location,
            'pickup_coordinates' => $booking->pickup_coordinates,
            'drop_location' => $booking->drop_location,
            'drop_coordinates' => $booking->drop_coordinates,
            'pickup_type' => $booking->pickup_type,
            'pickup_time' => $booking->pickup_time,
            'drop_time' => $booking->drop_time,
            'total_amount' => $booking->total_amount,
            'payment_method' => $booking->payment_method,
            'payment_status' => $booking->payment_status,
            'booking_status' => $booking->booking_status,
            'created_at' => $booking->created_at,
            'booking_type' => 'car',
            'car' => $booking->car ? [
                'brand' => $booking->car->brand ?? 'N/A',
                'model' => $booking->car->model ?? 'N/A',
                'registration_number' => $booking->car->registration_number ?? 'N/A',
                'manufacture_year' => $booking->car->manufacture_year ?? 'N/A',
                'seating_capacity' => $booking->car->seating_capacity ?? 'N/A'
            ] : null,
            'user' => $booking->user ? [
                'id' => $booking->user->id,
                'first_name' => $booking->user->first_name,
                'last_name' => $booking->user->last_name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone,
                'profile_image' => $booking->user->profile_image
            ] : null,
        ];
    });

    return response()->json(response_formatter(DEFAULT_200, $carBookings), 200);
}









}
