<?php

namespace Modules\CustomerModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BusinessSettingsModule\Entities\ErrorLog;
use Modules\SMSModule\Lib\SMS_gateway;
use Modules\TransactionModule\Entities\LoyaltyPointTransaction;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\Guest;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Illuminate\Support\Facades\Mail;
use Modules\PaymentModule\Traits\SmsGateway;

class CustomerController extends Controller
{

    private $customer;
    private Guest $guest;
    private $transaction;
    private LoyaltyPointTransaction $loyaltyPointTransaction;
    private ErrorLog $errorLog;

    public function __construct(User $user,Guest $guest, Transaction $transaction, LoyaltyPointTransaction $loyaltyPointTransaction, ErrorLog $errorLog)
    {
        $this->customer = $user;
        $this->guest = $guest;
        $this->transaction = $transaction;
        $this->loyaltyPointTransaction = $loyaltyPointTransaction;
        $this->errorLog = $errorLog;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (in_array($request->user()->user_type, CUSTOMER_USER_TYPES)) {
            $customer = $this->customer->with(['bookings', 'car'])->where('id', auth()->user()->id)->first();

            $lastIncompleteOfflineBooking = Booking::where('customer_id', auth()->user()->id)
                ->where('payment_method', 'offline_payment')
                ->whereNotIn('booking_status', ['completed', 'canceled'])
                ->whereDoesntHave('booking_offline_payments')
                ->with(['booking_offline_payments', 'booking_partial_payments'])
                ->first();

            $customer->last_incomplete_offline_booking = $lastIncompleteOfflineBooking;

            return response()->json(response_formatter(DEFAULT_200, $customer), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Modify provider information
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $this->customer::find($request->user()->id);
        if (!isset($customer)) {
            return response()->json(response_formatter(DEFAULT_400), 400);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'nullable',
            'phone' => 'nullable',
            'email' => 'required',
            'password' => '',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',

            'car_brand' => 'nullable|string',
            'car_model' => 'nullable|string',
            'manufacture_year' => 'nullable|integer',
            'registration_number' => 'nullable|string',
            'car_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ], [
            'profile_image.image' => translate('The profile image must be a valid image file.'),
            'profile_image.mimes' => translate('Invalid profile image format! Only JPEG, JPG, PNG, and GIF formats are allowed.'),
            'car_image.image' => translate('Car image must be a valid image file.'),
            'car_image.mimes' => translate('Invalid car image format! Only JPEG, JPG, PNG, and GIF formats are allowed.'),
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        if (User::where('phone', $request['phone'])->where('id', '!=', $customer->id)->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code"=>"phone","message"=>translate('Phone already taken')]]), 400);
        }

        if ($customer->email != $request['email']){
            $customer->is_email_verified = 0;
        }

        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;


        $customer->phone = $request->phone;


        $customer->email = $request->email;

        if ($request->has('profile_image')) {
            $customer->profile_image = file_uploader('user/profile_image/', 'png', $request->file('profile_image'), $customer->profile_image);;
        }

        if (!is_null($request['password'])) {
            $customer->password = bcrypt($request->password);
        }

        $customer->save();

        // Car Details
        $car = \Modules\UserManagement\Entities\UserCar::firstOrNew(['user_id' => $customer->id]);
        if ($request->has('car_brand')) $car->brand = $request->car_brand;
        if ($request->has('car_model')) $car->model = $request->car_model;
        if ($request->has('manufacture_year')) $car->manufacture_year = $request->manufacture_year;
        if ($request->has('registration_number')) $car->registration_number = $request->registration_number;

        if ($request->has('car_image')) {
            $car->car_image = file_uploader('user/car/', 'png', $request->file('car_image'), $car->car_image);
        }
        $car->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }


    /**
     * Modify provider information
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $customer = $this->customer::find($request->user()->id);
        $customer->fcm_token = $request->fcm_token;
        $customer->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAccount(Request $request): JsonResponse
    {
        $customer = $this->customer->whereIn('user_type', CUSTOMER_USER_TYPES)->find($request->user()->id);
        if (!isset($customer)) {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        file_remover('user/profile_image/', $customer->profile_image);
        foreach ($customer->identification_image as $image_name){
            file_remover('user/identity/', $image_name);
        }
        $customer->delete();

        return response()->json(response_formatter(DEFAULT_204), 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transferLoyaltyPointToWallet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'point' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }


        //user point check (if has sufficient amount)
        $user = $this->customer->find($request->user()->id);
        if($request['point'] > $user->loyalty_point) {
            return response()->json(response_formatter(DEFAULT_400, null, null), 400);
        }

        //minimum point check (for transferring)
        $minPoint = business_config('min_loyalty_point_to_transfer', 'customer_config')->live_values;
        if ($request['point'] < $minPoint ) {
            return response()->json(response_formatter(DEFAULT_400, null, null), 400);
        }

        $pointValuePerCurrencyUnit = business_config('loyalty_point_value_per_currency_unit', 'customer_config')->live_values;
        $loyaltyAmount = $request['point']/$pointValuePerCurrencyUnit;

        //point transfer transaction
        loyaltyPointWalletTransferTransaction($user->id, $request['point'], $loyaltyAmount);

        return response()->json(response_formatter(DEFAULT_200), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function walletTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'type' => 'nullable|in:' . implode(',', array_keys(WALLET_TRX_TYPE)),
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $transactions = $this->transaction
            ->with(['booking', 'from_user', 'to_user'])
            ->where('to_user_id', $request->user()->id)
            ->when(!is_null($request['type']), fn($query) => $query->where('trx_type', $request['type']))
            ->when(is_null($request['type']), fn($query) => $query->whereIn('trx_type', WALLET_TRX_TYPE))
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $user = $this->customer->find($request->user()->id);

        return response()->json(response_formatter(DEFAULT_204, [
            'wallet_balance' => with_decimal_point($user->wallet_balance),
            'transactions' => $transactions
        ]), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function loyaltyPointTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $transactions = $this->loyaltyPointTransaction
            ->with(['user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $user = $this->customer->find($request->user()->id);

        return response()->json(response_formatter(DEFAULT_204, [
            'loyalty_point' => $user->loyalty_point,
            'loyalty_point_value_per_currency_unit' => business_config('loyalty_point_value_per_currency_unit', 'customer_config')->live_values,
            'min_loyalty_point_to_transfer' => business_config('min_loyalty_point_to_transfer', 'customer_config')->live_values,
            'transactions' => $transactions
        ]), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeLanguage(Request $request): JsonResponse
    {
        if (auth('api')->user()){
            $customer = $this->customer->find(auth('api')->user()->id);
            $customer->current_language_key = $request->header('X-localization') ?? 'en';
            $customer->save();
            return response()->json(response_formatter(DEFAULT_200), 200);
        }elseif($request->has('guest_id')){
            $guest = $this->guest::find($request->guest_id);
            if (!isset($guest)) {
                $guest = $this->guest;
                $guest->ip_address = $request->ip();
            }
            $guest->current_language_key = $request->header('X-localization') ?? 'en';
            $guest->save();
            return response()->json(response_formatter(DEFAULT_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function errorLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $url = $request->url;
        $errorLog = $this->errorLog->firstOrNew(['url' => $url]);

        if ($errorLog->exists) {
            $errorLog->increment('hit_counts');
        } else {
            $errorLog->hit_counts = 1;
            $errorLog->save();
        }

        return response()->json(response_formatter(DEFAULT_200), 200);
    }


}
