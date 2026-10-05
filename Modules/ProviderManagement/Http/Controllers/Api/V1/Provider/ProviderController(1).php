<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\IgnoredPost;
use Modules\BidModule\Entities\Post;
use Modules\BookingModule\Entities\Booking;
use Modules\BusinessSettingsModule\Entities\SettingsTutorials;
use Modules\PromotionManagement\Entities\Advertisement;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\ProviderManagement\Entities\BankDetail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ReviewModule\Entities\Review;
use Modules\SMSModule\Lib\SMS_gateway;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\PaymentModule\Traits\SmsGateway;

class ProviderController extends Controller
{
    private $bankDetail, $provider, $account, $user, $pushNotification, $serviceman, $ignoredPost;

    protected $post;
    private $google_map;
    private $subscribedService;
    private Booking $booking;
    private Review $review;
    private Advertisement $advertisement;


    protected Transaction $transaction;


    public function __construct(Transaction $transaction, SubscribedService $subscribedService, BankDetail $bankDetail, Provider $provider, Account $account, User $user, PushNotification $pushNotification, Serviceman $serviceman, Booking $booking, Review $review, Post $post, IgnoredPost $ignoredPost, Advertisement $advertisement)
    {
        $this->bankDetail = $bankDetail;
        $this->provider = $provider;
        $this->user = $user;
        $this->account = $account;
        $this->pushNotification = $pushNotification;
        $this->serviceman = $serviceman;
        $this->subscribedService = $subscribedService;
        $this->google_map = business_config('google_map', 'third_party');
        $this->booking = $booking;
        $this->review = $review;
        $this->transaction = $transaction;
        $this->post = $post;
        $this->ignoredPost = $ignoredPost;
        $this->advertisement = $advertisement;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param Transaction $transaction
     * @param SubscribedService $subscribedService
     * @param Serviceman $serviceman
     * @return JsonResponse
     */
    public function dashboard(Request $request, Transaction $transaction, SubscribedService $subscribedService, Serviceman $serviceman): JsonResponse
    {
        // ... (truncated dashboard content)
    }

    /**
     * Toggle emergency availability for provider
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleEmergencyAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_emergency_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        // If value is passed explicitly use it, otherwise toggle current value
        if ($request->has('is_emergency_active')) {
            $provider->is_emergency_active = (bool) $request->input('is_emergency_active');
        } else {
            $provider->is_emergency_active = !$provider->is_emergency_active;
        }
        $provider->save();

        return response()->json(response_formatter(DEFAULT_200, [
            'is_emergency_active' => (bool) $provider->is_emergency_active,
        ]), 200);
    }

    /**
     * Update emergency settings for provider
     * @param Request $request
     * @return JsonResponse
     */
    public function updateEmergencySettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'after_hours_available'       => 'nullable|boolean',
            'weekend_emergency_available' => 'nullable|boolean',
            'emergency_response_time'     => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        if ($request->has('after_hours_available')) {
            $provider->after_hours_available = (bool) $request->input('after_hours_available');
        }
        if ($request->has('weekend_emergency_available')) {
            $provider->weekend_emergency_available = (bool) $request->input('weekend_emergency_available');
        }
        if ($request->has('emergency_response_time')) {
            $provider->emergency_response_time = $request->input('emergency_response_time');
        }
        $provider->save();

        return response()->json(response_formatter(DEFAULT_200, [
            'is_emergency_active'         => (bool) $provider->is_emergency_active,
            'after_hours_available'       => (bool) $provider->after_hours_available,
            'weekend_emergency_available' => (bool) $provider->weekend_emergency_available,
            'emergency_response_time'     => $provider->emergency_response_time,
        ]), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $this->provider->where(['user_id' => auth('api')->user()->id])
            ->with(['owner', 'zone'])
            ->first();

        $tutorialOptions = [
            'business_information' => 0,
            'service_subscription' => 0,
            'service_availability' => 0,
            'payment_information' => 0,
        ];

        if ($provider && $provider->owner) {
            $tutorial = $provider->owner->getTutorialByPlatform('app');

            if ($tutorial && is_array($tutorial->options ?? null)) {
                $tutorialOptions = array_merge($tutorialOptions, $tutorial->options);
            }
            $provider->tutorial_options = $tutorialOptions;
        }

        if (in_array($request->user()->user_type, PROVIDER_USER_TYPES)) {
            return response()->json(response_formatter(DEFAULT_200, $provider), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getBankDetails(Request $request): JsonResponse
    {
        $bankDetails = $this->bankDetail->where('provider_id', $request->user()->provider->id)->first();

        return response()->json(response_formatter(DEFAULT_200, $bankDetails), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteProvider(Request $request): JsonResponse
    {
        $provider = $this->provider::where('user_id', $request->user()->id)->first();
        if ($provider) {

            // Disable is_active for associated servicemen users
            $provider->servicemen->each(function ($serviceman) {
                $servicemanUser = $serviceman->user;
                $servicemanUser->is_active = 0;
                $servicemanUser->save();
            });

            $provider->delete();
            $provider->owner->delete();
            return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateBankDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'branch_name' => 'required',
            'acc_no' => 'required',
            'acc_holder_name' => 'required',
            'routing_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->bankDetail->updateOrCreate(
            [
                'provider_id' => $request->user()->provider->id,
            ],
            [
                'provider_id' => $request->user()->provider->id,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'acc_no' => $request->acc_no,
                'acc_holder_name' => $request->acc_holder_name,
                'routing_number' => $request->routing_number
            ],
        );

        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
    }

    /**
     * Modify provider information
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'contact_person_email' => 'required',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',

            'password' => '',
            'confirm_password' => isset($request->password) ? 'required|same:password' : '',

            'company_name' => 'required',
            'company_phone' => 'required|unique:providers,id,' . auth()->user()->provider->id,
            'company_address' => 'required',
            'logo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'cover_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',

            'latitude' => 'required',
            'longitude' => 'required',

            'identity_type' => 'required|in:passport,driving_license,nid,trade_license,company_id',
            'identity_number' => 'required',
            // 'identity_images' => 'required|array',
            //  'identity_images.*' => 'image|mimes:jpeg,jpg,png,gif',

            'uploaded_identity_images' => 'nullable',
            'uploaded_identity_images.*' => 'image|mimes:jpeg,jpg,png,gif|max:5120', // max 5MB per image

            'deleted_identity_images' => 'nullable',
            'deleted_identity_images.*' => 'string', // filenames to delete
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $this->provider::where('user_id', $request->user()->id)->first();
        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;

        if ($request->has('logo')) {
            $provider->logo = file_uploader('provider/logo/', 'png', $request->file('logo'), $provider->logo);
        }

        if ($request->has('cover_image')) {
            $provider->cover_image = file_uploader('provider/logo/', 'png', $request->file('cover_image'), $provider->cover_image);
        }

        $provider->company_address = $request->company_address;
        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;
        $provider->zone_id = $request['zone_ids'][0];
        $provider->coordinates = ['latitude' => $request['latitude'], 'longitude' => $request['longitude']];

        $owner = $this->user->where('id', $request->user()->id)->first();
        if ($request->has('password')) {
            $owner->password = bcrypt($request->password);
        }


        $existingImages = is_string($owner->identification_image) ? json_decode($owner->identification_image, true) : ($owner->identification_image ?? []);
        $deletedImages = is_string($request->deleted_identity_images) ? json_decode($request->deleted_identity_images, true) : ($request->deleted_identity_images ?? []);
        $newImages = $request->uploaded_identity_images ?? [];

        $filteredImages = [];

        foreach ($existingImages as $item) {
            if (is_string($item)) {
                if (in_array($item, $deletedImages)) {
                    file_remover('provider/identity', $item);
                    continue;
                }

                $filteredImages[] = [
                    'image' => $item,
                    'storage' => getDisk()
                ];
            } elseif (is_array($item) && isset($item['image'])) {
                if (in_array($item['image'], $deletedImages)) {
                    file_remover('provider/identity', $item);
                    continue;
                }

                $filteredImages[] = $item;
            }
        }

        foreach ($newImages as $image) {
            $imageName = file_uploader('provider/identity/', 'png', $image);
            $filteredImages[] = ['image' => $imageName, 'storage' => getDisk()];
        }

        $owner->identification_image = array_values($filteredImages);
        $owner->identification_number = $request->identity_number;
        $owner->identification_type = $request->identity_type;

        DB::transaction(function () use ($provider, $owner) {
            $owner->save();
            $provider->save();
        });

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->user->whereIn('user_type', PROVIDER_USER_TYPES)
            ->where('id', $request->user()->id)
            ->update([
                'password' => bcrypt(str_replace(' ', '', $request['password']))
            ]);

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_or_email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        DB::table('password_resets')->where('phone', $request['phone_or_email'])->delete();
        $customer = $this->user->whereIn('user_type', PROVIDER_USER_TYPES)
            ->where(['phone' => $request['phone_or_email']])
            ->first();

        if (isset($customer)) {
            $token = env('APP_ENV') != 'live' ? '1234' : rand(1000, 9999);

            DB::table('password_resets')->insert([
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'token' => $token,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(3),
            ]);

            $method = business_config('forget_password_verification_method', 'business_information')?->live_values;
            if ($method == 'phone') {
                $publishedStatus = 0;
                $paymentPublishedStatus = config('get_payment_publish_status');
                if (isset($paymentPublishedStatus[0]['is_published'])) {
                    $publishedStatus = $paymentPublishedStatus[0]['is_published'];
                }

                if ($publishedStatus == 1) {
                    $response = SmsGateway::send($customer->phone, $token);
                } else {
                    SMS_gateway::send($customer->phone, $token);
                }

            } elseif ($method == 'email') {
                //mail will be sent
                $emailStatus = business_config('email_config_status', 'email_config')->live_values;

                if ($emailStatus) {
                    try {
                        Mail::to($customer['email'])->send(new \App\Mail\PasswordResetMail($token));
                    } catch (\Exception $exception) {
                    }
                }
            }

        } else {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        return response()->json(response_formatter(DEFAULT_SENT_OTP_200), 200);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function otpVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_or_email' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $data = DB::table('password_resets')
            ->where('phone', $request['phone_or_email'])
            ->where(['token' => $request['otp']])->first();

        if (isset($data)) {
            return response()->json(response_formatter(DEFAULT_VERIFIED_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_or_email' => 'required',
            'otp' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $data = DB::table('password_resets')
            ->where('phone', $request['phone_or_email'])
            ->where(['token' => $request['otp']])
            ->where('expires_at', '>', now())
            ->first();

        if (isset($data)) {
            $this->user->whereIn('user_type', PROVIDER_USER_TYPES)
                ->where('phone', $request['phone_or_email'])
                ->update([
                    'password' => bcrypt(str_replace(' ', '', $request['password']))
                ]);
            DB::table('password_resets')
                ->where('phone', $request['phone_or_email'])
                ->where(['token' => $request['otp']])->delete();

        } else {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }

        return response()->json(response_formatter(DEFAULT_PASSWORD_RESET_200), 200);
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

        $customer = $this->user::find($request->user()->id);
        $customer->fcm_token = $request->fcm_token;
        $customer->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function notifications(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $createdAt = $request->user()->created_at ?? null;

        $pushNotification = $this->pushNotification->ofStatus(1)
            ->whereJsonContains('to_users', 'provider-admin')
            ->whereJsonContains('zone_ids', $request->user()->provider->zone_id)
            ->when($createdAt, function ($query) use ($createdAt) {
                $query->where('created_at', '>=', $createdAt);
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $pushNotification), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function subscribedSubCategories(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $subscribed = $this->subscribedService->where('provider_id', $request->user()->provider->id)
            ->with([
                'sub_category' => function ($query) {
                    return $query->withCount('services')->with(['services']);
                }
            ])
            ->whereHas('category', function ($query) {
                $query->where('is_active', 1);
            })
            ->whereHas('sub_category', function ($query) {
                $query->where('is_active', 1);
            })
            ->ofStatus(1)
            ->withCount(['ongoing_booking', 'completed_booking', 'canceled_booking'])
            ->when(isset($request['category_id']) && ($request['category_id'] != null), function ($query) use ($request) {
                $query->where('category_id', $request['category_id']);
            })
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $subscribed), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param string $service_id
     * @return JsonResponse
     */
    public function review(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $reviews = $this->review->with(['booking.detail', 'provider', 'customer', 'reviewReply', 'service'])
            ->where('provider_id', $request->user()->provider->id)
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $ratingGroupCount = DB::table('reviews')
            ->where('provider_id', $request->user()->provider->id)
            ->select('review_rating', DB::raw('count(review_comment) as total_comment'), DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        $activeReviews = DB::table('reviews')
            ->where('provider_id', $request->user()->provider->id)
            ->where('is_active', 1)
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        $totalRating = 0;
        $ratingCount = 0;
        $reviewCount = 0;

        foreach ($ratingGroupCount as $count) {
            $ratingCount += $count->total;
            $reviewCount += $count->total_comment;
        }

        $totalActiveRating = 0;
        $activeRatingCount = 0;

        foreach ($activeReviews as $activeReview) {
            $totalActiveRating += round($activeReview->review_rating * $activeReview->total, 2);
            $activeRatingCount += $activeReview->total;
        }

        $ratingInfo = [
            'rating_count' => $ratingCount,
            'review_count' => $reviewCount,
            'average_rating' => $activeRatingCount > 0 ? round($totalActiveRating / $activeRatingCount, 2) : 0,
            'rating_group_count' => $ratingGroupCount,
        ];

        if ($reviews->count() > 0) {
            return response()->json(response_formatter(DEFAULT_200, ['reviews' => $reviews, 'rating' => $ratingInfo]), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeLanguage(Request $request): JsonResponse
    {
        if (auth('api')->user()) {
            $customer = $this->user::find(auth('api')->user()->id);
            $customer->current_language_key = $request->header('X-localization') ?? 'en';
            $customer->save();
            return response()->json(response_formatter(DEFAULT_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    public function adjust(Request $request): JsonResponse
    {
        $provider = Provider::where('user_id', $request->user()->id)->first();
        $account = $this->account->where('user_id', $request->user()->id)->first();
        $receivable = $account->account_receivable;
        $payable = $account->account_payable;

        if ($receivable == $payable) {

            withdrawRequestAcceptForAdjustTransaction($request->user()->id, $receivable);
            collectCashTransaction($provider->id, $payable);

            return response()->json(response_formatter(ADJUST_AMOUNT_SUCCESS_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);

    }
    public function transaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'nullable|in:paid_commission,paid_amount,all',
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }
        $filteredTransactions = $this->transaction
            ->with(['booking', 'from_user.provider', 'to_user.provider'])
            ->when($request->transaction_type !== 'all', function ($query) use ($request) {
                return $query->where('trx_type', $request->transaction_type);
            })
            ->when($request->transaction_type === 'all', function ($query) {
                return $query->whereIn('trx_type', ['paid_commission', 'paid_amount']);
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $filteredTransactions, 200));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTutorial(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_information' => 'required|in:0,1',
            'service_subscription' => 'required|in:0,1',
            'service_availability' => 'required|in:0,1',
            'payment_information' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $tutorial = SettingsTutorials::updateOrCreate(
            [
                'user_id' => auth('api')->user()->id,
                'platform' => 'app',
            ],
            [
                'options' => [
                    'business_information' => (int) $request->business_information,
                    'service_subscription' => (int) $request->service_subscription,
                    'service_availability' => (int) $request->service_availability,
                    'payment_information' => (int) $request->payment_information,
                ],
            ]
        );

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTermsAndConditions(Request $request): JsonResponse
    {
        $providerId = $request->user()->provider->id;
        $settings = getProviderSettings($providerId, 'terms_and_conditions', 'terms_conditions');
        $terms = $settings['terms'] ?? '';

        $defaultTerms = "By booking this service, you agree that if any additional faults or requirements are identified during the inspection or service that were not included in the initial booking, additional charges will apply. These charges will be discussed and agreed upon before proceeding with the extra work.";

        return response()->json(response_formatter(DEFAULT_200, [
            'terms_and_conditions' => $terms ?: $defaultTerms
        ]), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTermsAndConditions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'terms_and_conditions' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->user()->provider->id;

        \Modules\ProviderManagement\Entities\ProviderSetting::updateOrCreate(
            [
                'provider_id' => $providerId,
                'key_name' => 'terms_and_conditions',
                'settings_type' => 'terms_conditions',
            ],
            [
                'live_values' => ['terms' => $request->terms_and_conditions],
                'test_values' => ['terms' => $request->terms_and_conditions],
                'mode' => 'live',
                'is_active' => 1
            ]
        );

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAboutUs(Request $request): JsonResponse
    {
        $providerId = $request->user()->provider->id;
        $settings = getProviderSettings($providerId, 'about_us', 'about_us');
        $aboutUs = $settings['about_us'] ?? "";

        return response()->json(response_formatter(DEFAULT_200, [
            'about_us' => $aboutUs
        ]), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAboutUs(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'about_us' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->user()->provider->id;

        \Modules\ProviderManagement\Entities\ProviderSetting::updateOrCreate(
            [
                'provider_id' => $providerId,
                'key_name' => 'about_us',
                'settings_type' => 'about_us',
            ],
            [
                'live_values' => ['about_us' => $request->about_us],
                'test_values' => ['about_us' => $request->about_us],
                'mode' => 'live',
                'is_active' => 1
            ]
        );

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }
}
