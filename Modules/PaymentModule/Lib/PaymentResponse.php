<?php

namespace Modules\PaymentModule\Lib;

use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Modules\BidModule\Entities\PostBid;
use Modules\BidModule\Http\Controllers\APi\V1\Customer\PostBidController;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingPartialPayment;
use Modules\BookingModule\Entities\BookingRepeat;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\User;
use Modules\CarHire\Entities\CarBooking;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;

class PaymentResponse
{
    use BookingTrait;
    use SubscriptionTrait;

    /**
     * @param $data
     * @return array
     */
    public static function success($data): array
    {
        $customer_user_id = $data['payer_id'];
        $tran_id = $data['transaction_id'];
        $payment_request_id = $data->id;

        $additional_data = json_decode($data['additional_data'], true);
        $request = collect([
            'access_token' => $additional_data['access_token'] ?? null,
            'zone_id' => $additional_data['zone_id'] ?? null,
            'service_schedule' => $additional_data['service_schedule'] ?? null,
            'service_address_id' => $additional_data['service_address_id'] ?? null,
            'service_address' => $additional_data['service_address'] ?? null,
            'payment_method' => $additional_data['payment_method'] ?? null,
            'callback' => $additional_data['callback'] ?? null,
            'is_partial' => $additional_data['is_partial'] ?? null,
            'post_id' => $additional_data['post_id'] ?? null,
            'provider_id' => $additional_data['provider_id'] ?? null,
            'register_new_customer' => $additional_data['register_new_customer'] ?? 0,
            'first_name' => $additional_data['first_name'] ?? null,
            'phone' => $additional_data['phone'] ?? null,
            'password' => $additional_data['password'] ?? null,
            'service_location' => $additional_data['service_location'] ?? 'customer',
            'booking_type' => $additional_data['booking_type'] ?? 'normal',
            'selected_slot_id' => $additional_data['selected_slot_id'] ?? null,
            'answers' => $additional_data['answers'] ?? null,
            'damage_description' => $additional_data['damage_description'] ?? null,
            'car_registration_number' => $additional_data['car_registration_number'] ?? null,
            'car_model' => $additional_data['car_model'] ?? null,
            'car_manufacture_year' => $additional_data['car_manufacture_year'] ?? null,
            'car_color' => $additional_data['car_color'] ?? null,
            'special_conditions' => $additional_data['special_conditions'] ?? null,
            'notes' => $additional_data['notes'] ?? null,
            'postcode' => $additional_data['postcode'] ?? null,
            'evidence_photos' => $additional_data['evidence_photos'] ?? null,
        ]);

        if (!$request->has('post_id') || is_null($request['post_id'])) {
            $is_guest = !User::where('id', $customer_user_id)->exists();
            $response = (new PaymentResponse)->placeBookingRequest(userId: $customer_user_id, request: $request, transactionId: $tran_id, isGuest: $is_guest);

        } else {
            //for bidding
            $post_bid = PostBid::with(['post'])
                ->where('post_id', $request['post_id'])
                ->where('provider_id', $request['provider_id'])
                ->first();

            if (!$post_bid) {
                $post_bid = PostBid::with(['post'])
                    ->where('post_id', $request['post_id'])
                    ->first();
            }

            if ($post_bid) {
                $data = [
                    'post_id' => $request['post_id'],
                    'payment_method' => $request['payment_method'],
                    'zone_id' => $request['zone_id'],
                    'service_tax' => $post_bid?->post?->service?->tax ?? 0,
                    'provider_id' => $request['provider_id'] ?? $post_bid?->provider_id,
                    'price' => $post_bid?->offered_price,
                    'service_schedule' => !is_null($request['service_schedule']) ? $request['service_schedule'] : $post_bid?->post?->booking_schedule,
                    'service_id' => $post_bid?->post?->service_id,
                    'category_id' => $post_bid?->post?->category_id,
                    'sub_category_id' => $post_bid?->post?->sub_category_id ?? $post_bid?->post?->category_id,
                    'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $post_bid?->post?->service_address_id,
                    'is_partial' => $request['is_partial'] ?? 0
                ];

                $userIdToUse = !empty($customer_user_id) ? $customer_user_id : (base64_decode($request['access_token']) ?: $request['access_token']);
                $response = (new PaymentResponse)->placeBookingRequestForBidding($userIdToUse, $request, $tran_id, $data);
                if (isset($response['flag']) && $response['flag'] == 'success') {
                    PostBidController::acceptPostBidOffer($post_bid->id, $response['booking_id']);
                }
            } else {
                $post = \Modules\BidModule\Entities\Post::with(['service'])->find($request['post_id']);
                if ($post) {
                    $data = [
                        'post_id' => $post->id,
                        'payment_method' => $request['payment_method'],
                        'zone_id' => $request['zone_id'],
                        'service_tax' => $post?->service?->tax ?? 0,
                        'provider_id' => $request['provider_id'],
                        'price' => 0,
                        'service_schedule' => $request['service_schedule'] ?? $post->booking_schedule,
                        'service_id' => $post->service_id,
                        'category_id' => $post->category_id,
                        'sub_category_id' => $post->sub_category_id ?? $post->category_id,
                        'service_address_id' => $request['service_address_id'] ?? $post->service_address_id,
                        'is_partial' => $request['is_partial'] ?? 0
                    ];
                    $userIdToUse = !empty($customer_user_id) ? $customer_user_id : (base64_decode($request['access_token']) ?: $request['access_token']);
                    $response = (new PaymentResponse)->placeBookingRequestForBidding($userIdToUse, $request, $tran_id, $data);
                } else {
                    $response = ['flag' => 'failed', 'message' => 'Post not found'];
                }
            }
        }

        //        if ($request['register_new_customer'] == 1){
//            $user = new User();
//            $user->first_name = $request['first_name'];
//            $user->last_name = '';
//            $user->phone = $request['phone'];
//            $user->password = bcrypt($request['password']);
//            $user->user_type = 'customer';
//            $user->is_active = 1;
//            $user->save();
//
//            $loginToken = $user->createToken('CUSTOMER_PANEL_ACCESS')->accessToken;
//
//            $response['loginToken'] = $loginToken;
//        }

        //update payment request
        if (isset($response['flag']) && in_array($response['flag'], ['success', 'booking_placed']) && !empty($response['readable_id'])) {
            $payment_request = PaymentRequest::find($payment_request_id);
            if ($payment_request) {
                $payment_request->attribute = 'booking';
                $payment_request->attribute_id = $response['readable_id'];
                $payment_request->save();
            }
        }

        $response['callback'] = $request['callback'];
        return $response;
    }

    /**
     * @param $data
     * @return array
     */
    public static function repeatBookingPaymentSuccess($data): array
    {
        $customer_user_id = $data['payer_id'];
        $tran_id = $data['transaction_id'];
        $payment_request_id = $data->id;

        $additional_data = json_decode($data['additional_data'], true);
        $request = collect([
            'access_token' => $additional_data['access_token'] ?? null,
            'booking_repeat_id' => $additional_data['booking_repeat_id'] ?? null,
            'payment_method' => $additional_data['payment_method'] ?? null,
            'callback' => $additional_data['callback'] ?? null,
        ]);

        if (!is_null($request['booking_repeat_id'])) {
            $repeatBooking = BookingRepeat::find($request['booking_repeat_id']);
            $repeatBooking->is_paid = 1;
            $repeatBooking->payment_method = $request['payment_method'];
            $repeatBooking->transaction_id = $tran_id;
            $repeatBooking->save();

            placeBookingRepeatTransactionForDigitalPayment($repeatBooking);

            $response = [
                'flag' => 'success',
                'booking_id' => $repeatBooking->id,
                'readable_id' => $repeatBooking->readable_id
            ];

        }

        //update payment request
        if ($response['flag'] == 'success' && $response['readable_id']) {
            $payment_request = PaymentRequest::find($payment_request_id);
            $payment_request->attribute = 'booking';
            $payment_request->attribute_id = $response['readable_id'];
            $payment_request->save();
        }

        $response['callback'] = $request['callback'];
        return $response;
    }

    /**
     * @param $data
     * @return array
     */
    public static function switchOfflineToDigitalPaymentSuccess($data): array
    {
        $customer_user_id = $data['payer_id'];
        $tran_id = $data['transaction_id'];
        $payment_request_id = $data->id;

        $additional_data = json_decode($data['additional_data'], true);
        $request = collect([
            'access_token' => $additional_data['access_token'] ?? null,
            'booking_id' => $additional_data['booking_id'] ?? null,
            'car_booking_id' => $additional_data['car_booking_id'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'callback' => $additional_data['callback'] ?? null,
            'is_partial' => $additional_data['is_partial'] ?? 0,
            'is_due_payment' => $additional_data['is_due_payment'] ?? 0,
            'paid_amount' => $additional_data['paid_amount'] ?? 0,
            'due_amount' => $additional_data['due_amount'] ?? 0,
            'wallet_paid_amount' => $additional_data['wallet_paid_amount'] ?? 0,
            'digitally_paid_amount' => $additional_data['digitally_paid_amount'] ?? 0,
        ]);

        if (!is_null($request['booking_id'])) {
            $booking = Booking::with('booking_partial_payments')->find($request['booking_id']);
            if ($booking) {
                $paymentMethod = $request['payment_method'] ?? $data['payment_method'] ?? 'digital';
                $paidAmount = (float) ($request['paid_amount'] > 0 ? $request['paid_amount'] : ($data['payment_amount'] ?? 0));
                $hasExistingPartials = $booking->booking_partial_payments && $booking->booking_partial_payments->isNotEmpty();

                if ($request['is_due_payment'] == 1 || $hasExistingPartials) {
                    // Remaining due payment (e.g. 75%)
                    $booking->booking_partial_payments()->update(['due_amount' => 0]);

                    BookingPartialPayment::create([
                        'booking_id' => $booking->id,
                        'paid_with' => $paymentMethod,
                        'paid_amount' => $paidAmount,
                        'due_amount' => 0,
                    ]);

                    $booking->is_paid = 1;
                    $booking->payment_method = $paymentMethod;
                    $booking->transaction_id = $tran_id;
                    $booking->save();

                    // Credit admin balance for remaining payment
                    $admin_user = User::where('user_type', ADMIN_USER_TYPES[0])->first();
                    if ($admin_user) {
                        $account = Account::where('user_id', $admin_user->id)->first();
                        if ($account) {
                            $account->balance_pending += $paidAmount;
                            $account->save();
                        }
                        Transaction::create([
                            'ref_trx_id' => null,
                            'booking_id' => $booking->id,
                            'trx_type' => TRX_TYPE['booking_amount'],
                            'debit' => 0,
                            'credit' => $paidAmount,
                            'balance' => $account ? $account->balance_pending : 0,
                            'from_user_id' => $booking->customer_id,
                            'to_user_id' => $admin_user->id,
                            'from_user_account' => null,
                            'to_user_account' => ACCOUNT_STATES[0]['value'],
                            'is_guest' => $booking->is_guest
                        ]);
                    }
                } elseif ($request['is_partial'] == 1) {
                    // Initial partial payment (25%) via switch
                    $dueAmount = round((float)$booking->total_booking_amount - $paidAmount, 2);
                    BookingPartialPayment::create([
                        'booking_id' => $booking->id,
                        'paid_with' => $paymentMethod,
                        'paid_amount' => $paidAmount,
                        'due_amount' => $dueAmount,
                    ]);

                    $booking->is_paid = 0; // Not fully paid yet
                    $booking->payment_method = $paymentMethod;
                    $booking->transaction_id = $tran_id;
                    $booking->save();

                    // Credit admin balance for 25% payment
                    $admin_user = User::where('user_type', ADMIN_USER_TYPES[0])->first();
                    if ($admin_user) {
                        $account = Account::where('user_id', $admin_user->id)->first();
                        if ($account) {
                            $account->balance_pending += $paidAmount;
                            $account->save();
                        }
                        Transaction::create([
                            'ref_trx_id' => null,
                            'booking_id' => $booking->id,
                            'trx_type' => TRX_TYPE['booking_amount'],
                            'debit' => 0,
                            'credit' => $paidAmount,
                            'balance' => $account ? $account->balance_pending : 0,
                            'from_user_id' => $booking->customer_id,
                            'to_user_id' => $admin_user->id,
                            'from_user_account' => null,
                            'to_user_account' => ACCOUNT_STATES[0]['value'],
                            'is_guest' => $booking->is_guest
                        ]);
                    }
                } else {
                    // Full 100% payment
                    $booking->is_paid = 1;
                    $booking->payment_method = $paymentMethod;
                    $booking->transaction_id = $tran_id;
                    $booking->save();

                    placeBookingTransactionForDigitalPayment($booking);
                }

                // Also update CarBooking if ID exists
                if (!is_null($request['car_booking_id'])) {
                    $carBooking = CarBooking::find($request['car_booking_id']);
                    if ($carBooking) {
                        if ($request['is_partial'] == 1 && $request['is_due_payment'] != 1) {
                            $carBooking->is_paid = 0;
                            $carBooking->payment_status = 'partially_paid';
                        } else {
                            $carBooking->is_paid = 1;
                            $carBooking->payment_status = 'paid';
                        }
                        $carBooking->payment_method = $paymentMethod;
                        $carBooking->transaction_id = $tran_id;
                        $carBooking->save();
                    }
                }

                $response = [
                    'flag' => 'success',
                    'booking_id' => $booking->id,
                    'readable_id' => $booking->readable_id
                ];
            } else {
                $response = ['flag' => 'failed', 'message' => 'Booking not found'];
            }
        }

        //update payment request
        if ($response['flag'] == 'success' && $response['readable_id']) {
            $payment_request = PaymentRequest::find($payment_request_id);
            $payment_request->attribute = 'booking';
            $payment_request->attribute_id = $response['readable_id'];
            $payment_request->save();
        }

        $response['callback'] = $request['callback'];
        return $response;
    }


    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function purchaseSubscriptionSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handlePurchasePackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            } else {
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web') {
                Toastr::success(translate(PROVIDER_REGISTERED_200['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse|string
     */
    public static function purchaseSubscriptionFailed($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $freeTrialStatus = (int) ((business_config('free_trial_period', 'subscription_Setting'))->is_active);

            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handlePurchaseSubscriptionFailed(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            } else {
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if (!$freeTrialStatus) {
                Toastr::success(translate(PAYMENT_FAILED['message']));
                return back();
            }

            if ($request['payment_platform'] == 'web') {
                Toastr::success(translate(PAYMENT_FAILED_SHIFT_FREE_TRIAL['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function renewSubscriptionSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handleRenewPackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            } else {
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web') {
                Toastr::success(translate(RENEW_SUBSCRIPTION_PACKAGE['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function shiftSubscriptionSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handleShiftPackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            } else {
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web') {
                Toastr::success(translate(SHIFT_SUBSCRIPTION_PACKAGE['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function businessPlanChangeSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handlePurchasePackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            } else {
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web') {
                Toastr::success(translate(PURCHASE_SUBSCRIPTION_PACKAGE['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array
     */
    public static function carBookingPaymentSuccess($data): array
    {
        $tran_id = $data['transaction_id'];
        $payment_request_id = $data->id;

        $additional_data = json_decode($data['additional_data'], true);
        $car_booking_id = $additional_data['car_booking_id'] ?? null;
        $booking_id = $additional_data['booking_id'] ?? null;
        $payment_method = $data['payment_method'] ?? 'digital';
        $callback = $additional_data['callback'] ?? null;
        $is_partial = (int) ($additional_data['is_partial'] ?? 0);
        $is_due_payment = (int) ($additional_data['is_due_payment'] ?? 0);

        $carBooking = null;
        if (!is_null($car_booking_id)) {
            $carBooking = CarBooking::find($car_booking_id);
        }
        if (!$carBooking && !is_null($booking_id)) {
            $carBooking = CarBooking::where('booking_id', $booking_id)->first();
        }

        if ($carBooking) {
            $linkedBooking = !empty($carBooking->booking_id)
                ? Booking::with('booking_partial_payments')->find($carBooking->booking_id)
                : null;

            $paid_amount = (float) (isset($additional_data['paid_amount']) && (float)$additional_data['paid_amount'] > 0
                ? $additional_data['paid_amount']
                : ($data['payment_amount'] ?? $carBooking->total_amount));

            $hasExistingPartials = $linkedBooking && $linkedBooking->booking_partial_payments && $linkedBooking->booking_partial_payments->isNotEmpty();

            if ($is_due_payment == 1 || ($hasExistingPartials && $is_partial != 1)) {
                // Remaining due payment (e.g. 75%)
                if ($linkedBooking) {
                    $linkedBooking->booking_partial_payments()->update(['due_amount' => 0]);

                    BookingPartialPayment::create([
                        'booking_id' => $linkedBooking->id,
                        'paid_with' => $payment_method,
                        'paid_amount' => $paid_amount,
                        'due_amount' => 0,
                    ]);

                    $linkedBooking->is_paid = 1;
                    $linkedBooking->payment_method = $payment_method;
                    $linkedBooking->transaction_id = $tran_id;
                    $linkedBooking->save();
                }

                $carBooking->is_paid = 1;
                $carBooking->payment_status = 'paid';
                $carBooking->payment_method = $payment_method;
                $carBooking->transaction_id = $tran_id;
                $carBooking->save();

            } elseif ($is_partial == 1) {
                // Initial 25% partial payment
                $due_amount = isset($additional_data['due_amount']) && (float)$additional_data['due_amount'] > 0
                    ? (float)$additional_data['due_amount']
                    : round((float)$carBooking->total_amount - $paid_amount, 2);

                if ($linkedBooking) {
                    BookingPartialPayment::create([
                        'booking_id' => $linkedBooking->id,
                        'paid_with' => $payment_method,
                        'paid_amount' => $paid_amount,
                        'due_amount' => $due_amount,
                    ]);

                    $linkedBooking->is_paid = 0; // Not fully paid yet
                    $linkedBooking->payment_method = $payment_method;
                    $linkedBooking->transaction_id = $tran_id;
                    $linkedBooking->save();
                }

                $carBooking->is_paid = 0; // Not fully paid yet
                $carBooking->payment_status = 'partially_paid';
                $carBooking->payment_method = $payment_method;
                $carBooking->transaction_id = $tran_id;
                $carBooking->save();

            } else {
                // Full payment
                $carBooking->is_paid = 1;
                $carBooking->payment_status = 'paid';
                $carBooking->payment_method = $payment_method;
                $carBooking->transaction_id = $tran_id;
                $carBooking->save();

                if ($linkedBooking) {
                    $linkedBooking->is_paid = 1;
                    $linkedBooking->payment_method = $payment_method;
                    $linkedBooking->transaction_id = $tran_id;
                    $linkedBooking->save();
                }
            }

            // Transactions for admin account
            $admin_user = User::where('user_type', 'admin')->first();
            $admin_user_id = $admin_user ? $admin_user->id : null;

            if ($admin_user_id) {
                // Update admin account balance_pending
                $admin_account = Account::where('user_id', $admin_user_id)->first();
                if ($admin_account) {
                    $admin_account->balance_pending += $paid_amount;
                    $admin_account->save();
                }

                // Admin transaction
                Transaction::create([
                    'booking_id' => $linkedBooking?->id ?? 0,
                    'car_booking_id' => $carBooking->id,
                    'trx_type' => 'car_booking_amount',
                    'debit' => 0,
                    'credit' => $paid_amount,
                    'balance' => $admin_account ? $admin_account->balance_pending : 0,
                    'from_user_id' => $carBooking->user_id,
                    'to_user_id' => $admin_user_id,
                    'to_user_account' => 'admin_balance'
                ]);
            }

            $response = [
                'flag' => 'success',
                'car_booking_id' => $carBooking->id,
            ];
        }

        //update payment request
        if (isset($response) && $response['flag'] == 'success') {
            $payment_request = PaymentRequest::find($payment_request_id);
            if ($payment_request) {
                $payment_request->attribute = 'car_booking';
                $payment_request->attribute_id = $carBooking->id;
                $payment_request->save();
            }
        }

        $response['callback'] = $callback;
        return $response;
    }
}

