<?php

namespace Modules\BookingModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\BookingModule\Entities\BookingEstimate;
use Modules\BookingModule\Entities\BookingPartialPayment;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\PaymentModule\Library\Payer;
use Modules\PaymentModule\Library\Payment;
use Modules\PaymentModule\Library\Receiver;
use Modules\PaymentModule\Traits\Payment as PaymentTrait;
use Modules\UserManagement\Entities\User;

class BookingEstimateController extends Controller
{
    use PaymentTrait;
    /**
     * Get estimate details by token for Customer App or Web
     */
    public function details(Request $request, string $token): JsonResponse
    {
        $cleanId = preg_replace('/[^0-9]/', '', (string)$token);

        $estimate = BookingEstimate::where('link_token', $token)
            ->orWhere('id', $token)
            ->when(!empty($cleanId), function ($q) use ($cleanId) {
                $q->orWhere('readable_id', $cleanId);
            })
            ->with([
                'service',
                'category',
                'provider.owner',
                'provider.zone',
                'booking',
                'car',
                'carBooking'
            ])
            ->first();

        if (!$estimate) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'estimate', 'message' => translate('Quotation / Estimate not found')]]), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $estimate), 200);
    }

    /**
     * Customer accepts estimate and converts it to an official Booking
     */
    public function accept(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'link_token' => 'required_without:estimate_id|string',
            'estimate_id' => 'required_without:link_token',
            'payment_method' => 'nullable|string',
            'is_partial' => 'nullable|in:0,1',
            'callback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $token = $request->link_token ?? $request->estimate_id;
        $cleanId = preg_replace('/[^0-9]/', '', (string)$token);

        $estimate = BookingEstimate::where('link_token', $token)
            ->orWhere('id', $token)
            ->when(!empty($cleanId), function ($q) use ($cleanId) {
                $q->orWhere('readable_id', $cleanId);
            })
            ->with(['service', 'provider.owner'])
            ->first();

        if (!$estimate) {
            return response()->json(response_formatter(DEFAULT_404, null, [['error_code' => 'estimate', 'message' => translate('Quotation not found')]]), 404);
        }

        if ($estimate->status == 'accepted' || !empty($estimate->booking_id)) {
            return response()->json(response_formatter([
                'response_code' => 'already_accepted_200',
                'message' => translate('This quotation has already been accepted and booked.'),
            ], [
                'booking_id' => $estimate->booking_id,
            ]), 200);
        }

        if ($estimate->status == 'canceled') {
            return response()->json(response_formatter([
                'response_code' => 'estimate_canceled_400',
                'message' => translate('This quotation was canceled by the provider.'),
            ]), 400);
        }

        // Determine customer ID: logged in user, or find by phone/email, or create guest customer
        $customerId = null;
        if (auth('api')->check()) {
            $customerId = auth('api')->user()->id;
        } elseif (!empty($estimate->customer_id)) {
            $customerId = $estimate->customer_id;
        } else {
            $existingUser = User::where('user_type', 'customer')
                ->where('phone', $estimate->customer_phone)
                ->first();

            if ($existingUser) {
                $customerId = $existingUser->id;
            } else {
                // Auto create customer user
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

        // Payment and partial calculations
        $totalAmount = (float)$estimate->total_amount;
        $isPartial = (isset($request['is_partial']) && (int)$request['is_partial'] === 1) ? 1 : 0;
        $amountToPay = $isPartial ? round($totalAmount * 0.25, 2) : $totalAmount;
        $dueAmount = $isPartial ? round($totalAmount - $amountToPay, 2) : 0;
        $paymentMethod = $request->get('payment_method', 'cash_after_service');

        $isCarBooking = ($estimate->module_type === 'car_hire' || $estimate->module_type === 'chauffeur');

        // Convert to Booking in a transaction
        $booking = null;
        $carBooking = null;

        try {
            DB::beginTransaction();

            $booking = new Booking();
            $booking->customer_id = $customerId;
            $booking->provider_id = $estimate->provider_id;
            $booking->category_id = $estimate->category_id;
            $booking->sub_category_id = $estimate->sub_category_id;
            $booking->zone_id = $estimate->zone_id;
            $booking->booking_status = 'accepted';
            $booking->is_paid = 0;
            $booking->payment_method = $paymentMethod;
            $booking->transaction_id = $request->get('transaction_id', ($paymentMethod === 'cash_after_service' ? 'cash-payment' : null));
            $booking->total_booking_amount = $totalAmount;
            $booking->total_tax_amount = $estimate->tax_amount;
            $booking->total_discount_amount = $estimate->discount_amount;
            $booking->service_schedule = $estimate->service_schedule ?? now()->addDay();
            $booking->booking_otp = rand(100000, 999999);
            $booking->is_guest = 0;
            $booking->car_model = $estimate->car_model;
            $booking->car_registration_number = $estimate->car_registration_number;
            $booking->damage_description = $estimate->damage_description;
            if ($estimate->car_image) {
                $decodedPhotos = is_array($estimate->car_image) ? $estimate->car_image : json_decode($estimate->car_image, true);
                $photoList = is_array($decodedPhotos) ? $decodedPhotos : [$estimate->car_image];
                $booking->evidence_photos = $photoList;

                // Sync images to storage/app/public/booking/ so standard booking views find them
                foreach ($photoList as $img) {
                    $sourcePath = storage_path('app/public/estimate/car/' . $img);
                    $destPath = storage_path('app/public/booking/' . $img);
                    if (file_exists($sourcePath) && !file_exists($destPath)) {
                        @copy($sourcePath, $destPath);
                    }
                }
            }
            $booking->notes = $estimate->notes;

            // Address handling for both Car Hire and General Services
            $userAddress = \Modules\UserManagement\Entities\UserAddress::where('user_id', $customerId)->latest()->first();
            $addressText = $isCarBooking
                ? ($estimate->pickup_type === 'delivery' ? $estimate->delivery_address : ($estimate->pickup_location ?? $estimate->customer_address))
                : ($estimate->customer_address ?? ($userAddress?->address ?? null));

            if ($userAddress && (empty($addressText) || $addressText === $userAddress->address)) {
                $booking->service_address_id = $userAddress->id;
                $booking->service_address_location = json_encode($userAddress);
            } elseif ($addressText) {
                if ($userAddress) {
                    $booking->service_address_id = $userAddress->id;
                }
                $booking->service_address_location = json_encode([
                    'id' => $userAddress?->id,
                    'address' => $addressText,
                    'contact_person_name' => $estimate->customer_name ?: ($userAddress?->contact_person_name ?: null),
                    'contact_person_number' => $estimate->customer_phone ?: ($userAddress?->contact_person_number ?: null),
                    'lat' => $estimate->pickup_coordinates['latitude'] ?? ($estimate->delivery_latitude ?? ($userAddress?->lat ?? null)),
                    'lon' => $estimate->pickup_coordinates['longitude'] ?? ($estimate->delivery_longitude ?? ($userAddress?->lon ?? null)),
                    'address_label' => $userAddress?->address_label ?? 'others',
                    'city' => $userAddress?->city ?? null,
                    'street' => $userAddress?->street ?? null,
                    'zip_code' => $userAddress?->zip_code ?? null,
                    'country' => $userAddress?->country ?? null,
                ]);
            }
            $booking->save();

            // Create booking detail
            $detail = new BookingDetail();
            $detail->booking_id = $booking->id;
            $detail->service_id = $estimate->service_id;
            $detail->service_name = $isCarBooking ? ($estimate->car_model . ' (' . ucfirst($estimate->pickup_type ?? 'hire') . ')') : ($estimate->service?->name ?? translate('Custom Service'));
            $detail->service_cost = $estimate->price;
            $detail->quantity = 1;
            $detail->tax_amount = $estimate->tax_amount;
            $detail->total_cost = $estimate->total_amount;
            $detail->save();

            // Create booking details amount (needed for commission and reporting)
            $detailsAmount = new BookingDetailsAmount();
            $detailsAmount->booking_details_id = $detail->id;
            $detailsAmount->booking_id = $booking->id;
            $detailsAmount->service_unit_cost = $estimate->price;
            $detailsAmount->service_quantity = 1;
            $detailsAmount->service_tax = $estimate->tax_amount;
            $detailsAmount->discount_by_admin = 0;
            $detailsAmount->discount_by_provider = 0;
            $detailsAmount->campaign_discount_by_admin = 0;
            $detailsAmount->campaign_discount_by_provider = 0;
            $detailsAmount->coupon_discount_by_admin = 0;
            $detailsAmount->coupon_discount_by_provider = 0;
            $detailsAmount->admin_commission = 0;
            $detailsAmount->save();

            // History
            BookingScheduleHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $customerId,
                'schedule' => $booking->service_schedule,
            ]);
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $customerId,
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
                $carBooking->rent_amount = floatval($estimate->rent_amount > 0 ? $estimate->rent_amount : ($estimate->total_amount - ($estimate->delivery_fee ?? 0) - ($estimate->security_deposit ?? 0)));
                $carBooking->delivery_fee = floatval($estimate->delivery_fee ?? 0);
                $carBooking->security_deposit = floatval($estimate->security_deposit ?? 0);
                $carBooking->total_amount = $estimate->total_amount;
                $carBooking->payment_method = $paymentMethod;
                $carBooking->payment_status = ($paymentMethod == 'cash_after_service') ? 'unpaid' : 'pending';
                $carBooking->booking_status = 'pending';
                $carBooking->is_paid = 0;
                $carBooking->save();

                $estimate->car_booking_id = $carBooking->id;
            }

            // Wallet payment handling
            if ($paymentMethod === 'wallet_payment') {
                $customerUser = User::find($customerId);
                $walletChargeAmount = $isPartial ? $amountToPay : $totalAmount;

                if (!$customerUser || $customerUser->wallet_balance < $walletChargeAmount) {
                    throw new \Exception(translate('Insufficient wallet balance'));
                }

                $customerUser->wallet_balance -= $walletChargeAmount;
                $customerUser->save();

                $tranId = 'wallet_payment_' . time();
                $booking->transaction_id = $tranId;

                if ($isPartial) {
                    BookingPartialPayment::create([
                        'booking_id' => $booking->id,
                        'paid_with' => 'wallet',
                        'paid_amount' => $walletChargeAmount,
                        'due_amount' => $dueAmount,
                    ]);

                    $booking->is_paid = 0;
                    if ($carBooking) {
                        $carBooking->is_paid = 0;
                        $carBooking->payment_status = 'partially_paid';
                        $carBooking->transaction_id = $tranId;
                        $carBooking->save();
                    }
                } else {
                    $booking->is_paid = 1;
                    if ($carBooking) {
                        $carBooking->is_paid = 1;
                        $carBooking->payment_status = 'paid';
                        $carBooking->transaction_id = $tranId;
                        $carBooking->save();
                    }
                }
                $booking->save();
            }

            // Update estimate status
            $estimate->status = 'accepted';
            $estimate->booking_id = $booking->id;
            $estimate->customer_id = $customerId;
            $estimate->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'booking_failed', 'message' => $e->getMessage()]]), 400);
        }

        // Digital Payment Gateway handling (e.g. Stripe, Razorpay, etc.)
        $redirectLink = null;
        if ($paymentMethod !== 'cash_after_service' && $paymentMethod !== 'wallet_payment') {
            try {
                $customerUser = User::find($customerId) ?? auth('api')->user();
                $payer = new Payer(
                    $customerUser ? ($customerUser->first_name . ' ' . $customerUser->last_name) : $estimate->customer_name,
                    $customerUser?->email ?? $estimate->customer_email,
                    $customerUser?->phone ?? $estimate->customer_phone,
                    ''
                );

                $successHook = $isCarBooking ? 'car_booking_payment_success' : 'switch_offline_to_digital_payment_success';
                $failHook = $isCarBooking ? 'car_booking_payment_fail' : 'switch_offline_to_digital_payment_fail';

                $payment_info = new Payment(
                    success_hook: $successHook,
                    failure_hook: $failHook,
                    currency_code: currency_code(),
                    payment_method: $paymentMethod,
                    payment_platform: $request->payment_platform ?? 'app',
                    payer_id: $customerId,
                    receiver_id: null,
                    additional_data: [
                        'car_booking_id' => $carBooking?->id,
                        'booking_id' => $booking->id,
                        'callback' => $request->callback,
                        'is_partial' => $isPartial,
                        'paid_amount' => $amountToPay,
                        'due_amount' => $dueAmount,
                    ],
                    payment_amount: $amountToPay,
                    external_redirect_link: $request->callback ?? null,
                    attribute: $isCarBooking ? 'car_booking_id' : 'booking_id',
                    attribute_id: $isCarBooking ? ($carBooking?->id ?? $booking->id) : $booking->id
                );

                $receiver_info = new Receiver('Admin', 'example.png');
                $redirectLink = $this->generate_link($payer, $payment_info, $receiver_info);
            } catch (\Exception $e) {
                info("Estimate accept digital payment error: " . $e->getMessage());
            }
        }

        // Notify provider
        try {
            $fcmToken = $estimate->provider?->owner?->fcm_token;
            $title = translate("Quotation Accepted!");
            $description = translate("Customer {$estimate->customer_name} accepted quotation #{$estimate->readable_id}");
            if ($fcmToken) {
                device_notification(
                    $fcmToken,
                    $title,
                    $description,
                    null,
                    $booking->id,
                    'booking'
                );
            }
            if ($estimate->provider?->owner?->id) {
                $pushNotification = new \Modules\PromotionManagement\Entities\PushNotification();
                $pushNotification->title = $title;
                $pushNotification->description = $description;
                $pushNotification->zone_ids = [$booking->zone_id ?? config('zone_id')];
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
                    info("Estimate accept email failed for provider: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            info("Notification error on estimate accept: " . $e->getMessage());
        }

        $responseData = [
            'booking_id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'booking' => $booking,
            'is_partial' => $isPartial,
            'paid_amount' => ($paymentMethod === 'wallet_payment' || $paymentMethod === 'cash_after_service') ? ($paymentMethod === 'wallet_payment' ? $amountToPay : 0) : 0,
            'due_amount' => $isPartial ? $dueAmount : 0,
        ];

        if ($redirectLink) {
            $responseData['redirect_link'] = $redirectLink;
            $responseData['amount'] = $amountToPay;
        }

        return response()->json(response_formatter([
            'response_code' => 'booking_placed_200',
            'message' => translate('Quotation accepted and booking created successfully!'),
        ], $responseData), 200);
    }

    /**
     * List all quotations/estimates for the customer
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 1);
        $status = $request->get('status', 'all');

        $user = auth('api')->user();

        $query = BookingEstimate::with([
            'service',
            'category',
            'provider.owner',
            'booking',
            'car',
            'carBooking'
        ]);

        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('customer_id', $user->id)
                    ->orWhere('customer_phone', $user->phone);
                if (!empty($user->email)) {
                    $q->orWhere('customer_email', $user->email);
                }
            });
        } elseif ($request->filled('phone')) {
            $query->where('customer_phone', $request->phone);
        } elseif ($request->filled('email')) {
            $query->where('customer_email', $request->email);
        } else {
            return response()->json(response_formatter(DEFAULT_400, null, [
                ['error_code' => 'auth', 'message' => translate('Please provide customer authentication token or phone/email query.')]
            ]), 400);
        }

        if ($status !== 'all' && in_array($status, ['pending', 'accepted', 'canceled'])) {
            $query->where('status', $status);
        }

        $estimates = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $offset);

        return response()->json(response_formatter(DEFAULT_200, $estimates), 200);
    }
}

