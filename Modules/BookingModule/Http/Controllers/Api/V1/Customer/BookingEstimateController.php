<?php

namespace Modules\BookingModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingEstimate;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\UserManagement\Entities\User;

class BookingEstimateController extends Controller
{
    /**
     * Get estimate details by token for Customer App or Web
     */
    public function details(Request $request, string $token): JsonResponse
    {
        $estimate = BookingEstimate::where('link_token', $token)
            ->orWhere('id', $token)
            ->with([
                'service',
                'category',
                'provider.owner',
                'provider.zones',
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
            'payment_method' => 'nullable|string|in:cash_after_service,wallet_payment,offline_payment,digital_payment',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $token = $request->link_token ?? $request->estimate_id;

        $estimate = BookingEstimate::where('link_token', $token)
            ->orWhere('id', $token)
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

        // Convert to Booking in a transaction
        $booking = DB::transaction(function () use ($estimate, $customerId, $request) {
            $isCarBooking = ($estimate->module_type === 'car_hire' || $estimate->module_type === 'chauffeur');

            $booking = new Booking();
            $booking->customer_id = $customerId;
            $booking->provider_id = $estimate->provider_id;
            $booking->category_id = $estimate->category_id;
            $booking->sub_category_id = $estimate->sub_category_id;
            $booking->zone_id = $estimate->zone_id;
            $booking->booking_status = 'accepted';
            $booking->is_paid = 0;
            $booking->payment_method = $request->get('payment_method', 'cash_after_service');
            $booking->transaction_id = $request->get('transaction_id', 'cash-payment');
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

            // History
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
                $carBooking->payment_method = $request->get('payment_method', 'cash_after_service');
                $carBooking->payment_status = ($request->get('payment_method') == 'cash_after_service') ? 'unpaid' : 'pending';
                $carBooking->booking_status = 'pending';
                $carBooking->is_paid = 0;
                $carBooking->save();

                $estimate->car_booking_id = $carBooking->id;
            }

            // Update estimate status
            $estimate->status = 'accepted';
            $estimate->booking_id = $booking->id;
            $estimate->customer_id = $customerId;
            $estimate->save();

            return $booking;
        });

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

        return response()->json(response_formatter([
            'response_code' => 'booking_placed_200',
            'message' => translate('Quotation accepted and booking created successfully!'),
        ], [
            'booking_id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'booking' => $booking,
        ]), 200);
    }
}
