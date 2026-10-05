<?php

namespace Modules\BookingModule\Entities;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Mail;
use Modules\BookingModule\Emails\BookingStatusUpdateMail;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\PromotionManagement\Entities\PushNotificationUser;
use Modules\BidModule\Entities\Post;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\BookingModule\Http\Traits\BookingScopes;
use Modules\BusinessSettingsModule\Emails\CashInHandOverflowMail;
use Modules\BusinessSettingsModule\Emails\SubscriptionToCommissionMail;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ReviewModule\Entities\Review;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;

class Booking extends Model
{
    use HasFactory, HasUuid, BookingTrait, BookingScopes;

    protected $casts = [
        'readable_id' => 'integer',
        'is_paid' => 'integer',
        'is_verified' => 'integer',
        'total_booking_amount' => 'float',
        'total_tax_amount' => 'float',
        'total_discount_amount' => 'float',
        'total_campaign_discount_amount' => 'float',
        'total_coupon_discount_amount' => 'float',
        'is_checked' => 'integer',
        'additional_charge' => 'float',
        'additional_tax_amount' => 'float',
        'additional_discount_amount' => 'float',
        'additional_campaign_discount_amount' => 'float',
        'evidence_photos' => 'array',
        'extra_fee' => 'float',
        'total_referral_discount_amount' => 'float',
    ];

    protected $fillable = [
        'id',
        'readable_id',
        'customer_id',
        'provider_id',
        'zone_id',
        'booking_status',
        'is_paid',
        'payment_method',
        'transaction_id',
        'total_booking_amount',
        'total_tax_amount',
        'total_discount_amount',
        'service_schedule',
        'service_address_id',
        'created_at',
        'updated_at',
        'category_id',
        'sub_category_id',
        'serviceman_id',
        'total_campaign_discount_amount',
        'total_coupon_discount_amount',
        'coupon_code',
        'is_checked',
        'additional_charge',
        'additional_tax_amount',
        'additional_discount_amount',
        'additional_campaign_discount_amount',
        'evidence_photos',
        'booking_otp',
        'is_verified',
        'service_address_location',
        'service_location',
        'booking_type',
        'emergency_charge',
        'selected_slot_id',
        'damage_description',
        'car_registration_number',
        'car_model',
        'car_manufacture_year',
        'car_color',
        'special_conditions',
        'notes',
        'postcode'
    ];

    protected $appends = ['evidence_photos_full_path'];


    public function service_address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'service_address_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class, 'serviceman_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function repeatDetail(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
    public function repeat(): HasMany
    {
        return $this->hasMany(BookingRepeat::class);
    }

    public function booking_partial_payments(): HasMany
    {
        return $this->hasMany(BookingPartialPayment::class)->latest();
    }

    public function booking_details_amounts(): hasOne
    {
        return $this->hasOne(BookingDetailsAmount::class);
    }

    public function bookingDeniedNote(): hasOne
    {
        return $this->hasOne(BookingAdditionalInformation::class, 'booking_id')->where('key', 'booking_deny_note');
    }

    public function details_amounts(): hasMany
    {
        return $this->hasMany(BookingDetailsAmount::class);
    }

    public function schedule_histories(): HasMany
    {
        return $this->hasMany(BookingScheduleHistory::class);
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function status_histories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function booking_offline_payments(): HasMany
    {
        return $this->hasMany(BookingOfflinePayment::class, 'booking_id');
    }

    public function ignores(): HasMany
    {
        return $this->hasMany(BookingIgnore::class, 'booking_id');
    }

    public function customizeBooking(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'id', 'booking_id');
    }

    public function slotBooking(): HasOne
    {
        return $this->hasOne(SlotBooking::class, 'booking_id');
    }

    public function questionAnswers(): HasMany
    {
        return $this->hasMany(BookingQuestionAnswer::class, 'booking_id');
    }

    public function carHireBooking(): HasOne
    {
        return $this->hasOne(\Modules\CarHire\Entities\CarBooking::class, 'booking_id');
    }

    public function getEvidencePhotosFullPathAttribute()
    {
        $evidenceImages = $this->evidence_photos ?? [];
        $defaultImagePath = asset('public/assets/admin-module/img/media/user.png');
        if (empty($evidenceImages)) {
            if (request()->is('api/*')) {
                $defaultImagePath = null;
            }
            return $defaultImagePath ? [$defaultImagePath] : [];
        }

        $path = 'booking/evidence/';

        return getIdentityImageFullPath(identityImages: $evidenceImages, path: $path, defaultPath: $defaultImagePath);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->readable_id = $model->count() + 100000;
        });

        self::created(function ($model) {
            $providerId = $model->provider_id;

            if ($providerId) {
                $provider = PackageSubscriber::where('provider_id', $providerId)->first();

                if ($provider) {
                    $firstLog = $provider->package_subscriber_log_id;

                    $bookingType = new SubscriptionBookingType();
                    $bookingType->booking_id = $model->id;
                    $bookingType->type = 'subscription';
                    $bookingType->save();

                    $subscriptionSubscriberBooking = new SubscriptionSubscriberBooking();
                    $subscriptionSubscriberBooking->provider_id = $providerId;
                    $subscriptionSubscriberBooking->booking_id = $model->id;

                    if ($firstLog) {
                        $subscriptionSubscriberBooking->package_subscriber_log_id = $firstLog;
                    }

                    $subscriptionSubscriberBooking->save();
                }
            }

            if ($model->provider_id) {
                $providerEmail = $model->provider?->owner?->email;
                if (!empty($providerEmail)) {
                    try {
                        Mail::to($providerEmail)->send(new \Modules\BookingModule\Emails\ProviderNewBookingMail($model));
                    } catch (\Exception $e) {
                        info("Provider new booking email failed: " . $e->getMessage());
                    }
                }
            }
        });


        self::updating(function ($model) {
            $booking_notification_status = business_config('booking', 'notification_settings')->live_values;
            $permission = isNotificationActive(null, 'booking', 'notification', 'user');
            $providerPermission = isNotificationActive(null, 'booking', 'notification', 'provider');
            $servicemanPermission = isNotificationActive(null, 'booking', 'notification', 'serviceman');

            if ($model->isDirty('booking_status')) {
                $key = null;
                if ($model->booking_status == 'pending') {
                    if ($permission) {
                        $notifications[] = [
                            'key' => 'booking_place',
                            'settings_type' => 'customer_notification'
                        ];
                    }
                } elseif ($model->booking_status == 'ongoing') {
                    if ($permission) {
                        $notifications[] = [
                            'key' => 'booking_ongoing',
                            'settings_type' => 'customer_notification'
                        ];
                    }
                    if ($providerPermission) {
                        $notifications[] = [
                            'key' => 'ongoing_booking',
                            'settings_type' => 'provider_notification'
                        ];
                    }
                    if ($servicemanPermission) {
                        $notifications[] = [
                            'key' => 'ongoing_booking',
                            'settings_type' => 'serviceman_notification'
                        ];
                    }
                } elseif ($model->booking_status == 'accepted') {
                    if ($permission) {
                        $notifications[] = [
                            'key' => 'booking_accepted',
                            'settings_type' => 'customer_notification'
                        ];
                    }
                    if ($providerPermission && $model->is_repeated == 0) {
                        $notifications[] = [
                            'key' => 'booking_accepted',
                            'settings_type' => 'provider_notification'
                        ];
                    }
                } elseif ($model->booking_status == 'completed' && $model->is_repeated == 0) {
                    if ($permission) {
                        $notifications[] = [
                            'key' => 'booking_complete',
                            'settings_type' => 'customer_notification'
                        ];
                    }
                    if ($providerPermission) {
                        $notifications[] = [
                            'key' => 'booking_complete',
                            'settings_type' => 'provider_notification'
                        ];
                    }
                    if ($servicemanPermission) {
                        $notifications[] = [
                            'key' => 'booking_complete',
                            'settings_type' => 'serviceman_notification'
                        ];
                    }

                    $model->is_paid = 1;

                    $provider = $model->provider;

                    if ($provider) {
                        $model->update_admin_commission($model, $model->total_booking_amount, $model->provider_id);
                    }


                    if (!$model->is_guest && $model?->customer) {
                        $model->referral_earning_calculation($model->customer_id, $model->zone_id);

                        $model->loyaltyPointCalculation($model->customer_id, $model->total_booking_amount);

                        if ($model->total_referral_discount_amount > 0) {
                            referralEarningTransactionAfterBookingCompleteFirst($model->customer, $model->total_referral_discount_amount, $model->id);
                        }
                    }

                    //================ Transactions for Booking ================

                    if ($model?->provider) {
                        if ($model->booking_partial_payments->isNotEmpty()) {
                            if ($model['payment_method'] == 'cash_after_service') {
                                $booking_partial_payment = new BookingPartialPayment;
                                $booking_partial_payment->booking_id = $model->id;
                                $booking_partial_payment->paid_with = 'cash_after_service';
                                $booking_partial_payment->paid_amount = $model->booking_partial_payments->first()?->due_amount;
                                $booking_partial_payment->due_amount = 0;
                                $booking_partial_payment->save();

                                completeBookingTransactionForPartialCas($model);
                            } elseif ($model['payment_method'] != 'wallet_payment') {
                                completeBookingTransactionForPartialDigital($model);
                            }

                        } elseif ($model->payment_method == 'cash_after_service') {
                            completeBookingTransactionForCashAfterService($model);
                        } else {
                            if ($model->additional_charge == 0) {
                                completeBookingTransactionForDigitalPayment($model);
                            }

                            if ($model->additional_charge > 0) {
                                completeBookingTransactionForDigitalPaymentAndExtraService($model);
                            }
                        }

                        $limit_status = provider_warning_amount_calculate($provider->owner->account->account_payable, $provider->owner->account->account_receivable);

                        if ($limit_status == '100_percent' && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
                            $provider->is_suspended = 1;
                            $provider->save();

                            $notification = isNotificationActive($provider?->id, 'transaction', 'notification', 'provider');
                            $title = get_push_notification_message('provider_suspend', 'provider_notification', $provider?->owner?->current_language_key);
                            if ($provider?->owner?->fcm_token && $title && $notification) {
                                device_notification($provider?->owner?->fcm_token, $title, null, null, $model->id, 'suspend', null, $provider->id);
                            }

                            $emailStatus = business_config('email_config_status', 'email_config')->live_values;

                            if ($emailStatus) {
                                try {
                                    Mail::to($provider?->owner?->email)->send(new CashInHandOverflowMail($provider));
                                } catch (\Exception $exception) {
                                    info($exception);
                                }
                            }

                        }
                    }

                } elseif ($model->booking_status == 'canceled') {
                    if ($permission) {
                        $notifications[] = [
                            'key' => 'booking_cancel',
                            'settings_type' => 'customer_notification'
                        ];
                    }
                    if ($providerPermission) {
                        $notifications[] = [
                            'key' => 'booking_cancel',
                            'settings_type' => 'provider_notification'
                        ];
                    }
                    if ($servicemanPermission) {
                        $notifications[] = [
                            'key' => 'booking_cancel',
                            'settings_type' => 'serviceman_notification'
                        ];
                    }

                    if ($model?->customer) {
                        refundTransactionForCanceledBooking($model);
                    }

                } elseif ($model->booking_status == 'refund_request') {
                    if ($permission) {
                        $notifications[] = [
                            [
                                'key' => 'refund',
                                'settings_type' => 'customer_notification'
                            ]
                        ];
                    }
                }


                // 1. Send Customer Email on Booking Status Change
                try {
                    $emailStatus = business_config('email_config_status', 'email_config')?->live_values;
                    $emailPermission = isNotificationActive(null, 'booking', 'email', 'user');
                    $customerEmail = $model->customer?->email;

                    if ($emailStatus && $emailPermission && !empty($customerEmail)) {
                        Mail::to($customerEmail)->send(new BookingStatusUpdateMail($model, $model->booking_status));
                    }
                } catch (\Exception $e) {
                    info("Booking status update email failed: " . $e->getMessage());
                }

                // 2. Send Push Notifications & Record In-App Notifications
                if (isset($booking_notification_status) && $booking_notification_status['push_notification_booking']) {
                    foreach ($notifications ?? [] as $notification) {
                        $key = $notification['key'];
                        $settingsType = $notification['settings_type'];

                        $readableStatus = ucfirst(str_replace('_', ' ', $model->booking_status));
                        $bookingCode = $model->readable_id ?? $model->id;

                        if ($settingsType == 'customer_notification') {
                            $user = $model?->customer;
                            $repeatOrRegular = $model?->is_repeated ? 'repeat' : 'regular';
                            $title = get_push_notification_message($key, $settingsType, $user?->current_language_key);
                            if (empty($title)) {
                                $title = "Booking #{$bookingCode} {$readableStatus}";
                            }
                            $description = "Your booking #{$bookingCode} status has been updated to {$readableStatus}.";

                            $permission = isNotificationActive(null, 'booking', 'notification', 'user');
                            if ($user?->fcm_token && $user?->is_active && $permission) {
                                device_notification($user?->fcm_token, $title, $description, null, $model->id, 'booking', null, null, null, null, $repeatOrRegular);
                            }

                            // Record in-app notification for Customer
                            if ($user && $permission) {
                                try {
                                    $pushNotification = new PushNotification();
                                    $pushNotification->title = $title;
                                    $pushNotification->description = $description;
                                    $pushNotification->zone_ids = [$model->zone_id];
                                    $pushNotification->to_users = ['customer'];
                                    $pushNotification->notification_type = 'booking';
                                    $pushNotification->booking_id = $model->id;
                                    $pushNotification->booking_status = $model->booking_status;
                                    $pushNotification->reference_id = $model->id;
                                    $pushNotification->is_active = 1;
                                    $pushNotification->save();

                                    $pushNotificationUser = new PushNotificationUser();
                                    $pushNotificationUser->push_notification_id = $pushNotification->id;
                                    $pushNotificationUser->user_id = $user->id;
                                    $pushNotificationUser->save();
                                } catch (\Exception $e) {
                                    info("Customer in-app notification log failed: " . $e->getMessage());
                                }
                            }
                        }

                        if ($settingsType == 'provider_notification') {
                            $provider = $model?->provider?->owner;
                            $repeatOrRegular = $model?->is_repeated ? 'repeat' : 'regular';
                            $title = get_push_notification_message($key, $settingsType, $provider?->current_language_key);
                            if (empty($title)) {
                                $title = "Booking #{$bookingCode} {$readableStatus}";
                            }
                            $description = "Booking #{$bookingCode} status is now {$readableStatus}.";

                            if ($provider?->fcm_token && sendDeviceNotificationPermission($model?->provider_id)) {
                                device_notification($provider?->fcm_token, $title, $description, null, $model->id, 'booking', null, null, null, null, $repeatOrRegular);
                            }

                            // Record in-app notification for Provider
                            if ($provider) {
                                try {
                                    $pushNotification = new PushNotification();
                                    $pushNotification->title = $title;
                                    $pushNotification->description = $description;
                                    $pushNotification->zone_ids = [$model->zone_id];
                                    $pushNotification->to_users = ['provider-admin'];
                                    $pushNotification->notification_type = 'booking';
                                    $pushNotification->booking_id = $model->id;
                                    $pushNotification->booking_status = $model->booking_status;
                                    $pushNotification->reference_id = $model->id;
                                    $pushNotification->is_active = 1;
                                    $pushNotification->save();
                                } catch (\Exception $e) {
                                    info("Provider in-app notification log failed: " . $e->getMessage());
                                }
                            }
                        }

                        if ($settingsType == 'serviceman_notification') {
                            $serviceman = $model?->serviceman?->user;
                            $title = get_push_notification_message($key, $settingsType, $serviceman?->current_language_key);
                            if (empty($title)) {
                                $title = "Booking #{$bookingCode} {$readableStatus}";
                            }
                            $description = "Assigned Booking #{$bookingCode} is now {$readableStatus}.";

                            if ($serviceman?->fcm_token) {
                                device_notification($serviceman?->fcm_token, $title, $description, null, $model->id, 'booking');
                            }
                        }
                    }
                }
            }
        });

        self::updated(function ($model) {
            $status = $model->booking_status;
            $bookingScheduleTimeChange = isNotificationActive(null, 'booking', 'notification', 'user');
            $bookingScheduleTimeChangeProvider = isNotificationActive(null, 'booking', 'notification', 'provider');
            $bookingScheduleTimeChangeServiceman = isNotificationActive(null, 'booking', 'notification', 'serviceman');

            $providerId = $model->provider_id;

            if ($status == 'accepted') {
                $providerId = $model->provider_id;

                $subscriptionSubscriberBooking = new SubscriptionSubscriberBooking();
                $subscriptionSubscriberBooking->provider_id = $providerId;
                $subscriptionSubscriberBooking->booking_id = $model->id;

                if ($providerId) {
                    $provider = PackageSubscriber::where('provider_id', $providerId)->first();

                    if ($provider && !$model->isDirty('provider_id')) {
                        $firstLog = $provider->package_subscriber_log_id;

                        $bookingType = new SubscriptionBookingType();
                        $bookingType->booking_id = $model->id;
                        $bookingType->type = 'subscription';
                        $bookingType->save();

                        $subscriptionSubscriberBooking = new SubscriptionSubscriberBooking();
                        $subscriptionSubscriberBooking->provider_id = $providerId;
                        $subscriptionSubscriberBooking->booking_id = $model->id;

                        if ($firstLog) {
                            $subscriptionSubscriberBooking->package_subscriber_log_id = $firstLog;
                        }

                        $subscriptionSubscriberBooking->save();
                    }
                }
            }

            if ($model->isDirty('provider_id')) {
                $provider = PackageSubscriber::where('provider_id', $providerId)->first();
                $firstLog = $provider?->package_subscriber_log_id;
                if ($provider) {
                    $firstLog = $provider->package_subscriber_log_id;

                    SubscriptionBookingType::updateOrCreate(
                        [
                            'booking_id' => $model->id,
                        ],
                        [
                            'booking_id' => $model->id,
                            'type' => 'subscription'
                        ]
                    );
                }
                SubscriptionSubscriberBooking::updateOrCreate(
                    [
                        'booking_id' => $model->id,
                    ],
                    [
                        'provider_id' => $providerId,
                        'booking_id' => $model->id,
                        'package_subscriber_log_id' => $firstLog,
                    ]
                );
            }
            $notifications = [];
            $booking_notification_status = business_config('booking', 'notification_settings')->live_values;

            if ($model->isDirty('serviceman_id') && !$model->is_repeted) {
                if ($bookingScheduleTimeChange) {
                    $notifications[] = [
                        'key' => 'serviceman_assign',
                        'settings_type' => 'customer_notification'
                    ];
                }
                if ($bookingScheduleTimeChangeProvider && !$model->is_repeted) {
                    $notifications[] = [
                        'key' => 'serviceman_assign',
                        'settings_type' => 'provider_notification'
                    ];
                }
                if ($bookingScheduleTimeChangeServiceman && !$model->is_repeted) {
                    $notifications[] = [
                        'key' => 'serviceman_assign',
                        'settings_type' => 'serviceman_notification'
                    ];
                }
            }

            if ($model->isDirty('service_schedule')) {
                if ($bookingScheduleTimeChange) {
                    $notifications[] = [
                        'key' => 'booking_schedule_time_change',
                        'settings_type' => 'customer_notification'
                    ];
                }
                if ($bookingScheduleTimeChangeProvider) {
                    $notifications[] = [
                        'key' => 'booking_schedule_time_change',
                        'settings_type' => 'provider_notification'
                    ];
                }
                if ($bookingScheduleTimeChangeServiceman) {
                    $notifications[] = [
                        'key' => 'booking_schedule_time_change',
                        'settings_type' => 'serviceman_notification'
                    ];
                }
            }

            if (isset($booking_notification_status) && $booking_notification_status['push_notification_booking']) {
                foreach ($notifications ?? [] as $notification) {
                    $key = $notification['key'];
                    $settingsType = $notification['settings_type'];

                    $bookingCode = $model->readable_id ?? $model->id;

                    if ($settingsType == 'customer_notification') {
                        $user = $model?->customer;
                        $repeatOrRegular = $model?->is_repeated ? 'repeat' : 'regular';
                        $title = get_push_notification_message($key, $settingsType, $user?->current_language_key);
                        if (empty($title)) {
                            $title = ($key == 'serviceman_assign') ? translate('Serviceman Assigned') : translate('Schedule Updated');
                        }
                        $description = ($key == 'serviceman_assign')
                            ? translate("A serviceman has been assigned to your booking #{$bookingCode}")
                            : translate("Schedule for your booking #{$bookingCode} has been updated to ") . $model->service_schedule;

                        if ($user?->fcm_token && $title) {
                            device_notification($user?->fcm_token, $title, $description, null, $model->id, 'booking', null, null, null, null, $repeatOrRegular);
                        }

                        if ($user) {
                            try {
                                $pushNotification = new PushNotification();
                                $pushNotification->title = $title;
                                $pushNotification->description = $description;
                                $pushNotification->zone_ids = [$model->zone_id];
                                $pushNotification->to_users = ['customer'];
                                $pushNotification->notification_type = 'booking';
                                $pushNotification->booking_id = $model->id;
                                $pushNotification->booking_status = $model->booking_status;
                                $pushNotification->reference_id = $model->id;
                                $pushNotification->is_active = 1;
                                $pushNotification->save();

                                $pushNotificationUser = new PushNotificationUser();
                                $pushNotificationUser->push_notification_id = $pushNotification->id;
                                $pushNotificationUser->user_id = $user->id;
                                $pushNotificationUser->save();
                            } catch (\Exception $e) {
                                info("Customer in-app notification log failed for {$key}: " . $e->getMessage());
                            }
                        }
                    }

                    if ($settingsType == 'provider_notification') {
                        $provider = $model?->provider?->owner;
                        $repeatOrRegular = $model?->is_repeated ? 'repeat' : 'regular';
                        $title = get_push_notification_message($key, $settingsType, $provider?->current_language_key);
                        if (empty($title)) {
                            $title = ($key == 'serviceman_assign') ? translate('Serviceman Assigned') : translate('Schedule Updated');
                        }
                        $description = ($key == 'serviceman_assign')
                            ? translate("Serviceman assigned to booking #{$bookingCode}")
                            : translate("Schedule for booking #{$bookingCode} updated to ") . $model->service_schedule;

                        if ($provider?->fcm_token && $title && sendDeviceNotificationPermission($model?->provider_id)) {
                            device_notification($provider?->fcm_token, $title, $description, null, $model->id, 'booking', null, null, null, null, $repeatOrRegular);
                        }

                        if ($provider) {
                            try {
                                $pushNotification = new PushNotification();
                                $pushNotification->title = $title;
                                $pushNotification->description = $description;
                                $pushNotification->zone_ids = [$model->zone_id];
                                $pushNotification->to_users = ['provider-admin'];
                                $pushNotification->notification_type = 'booking';
                                $pushNotification->booking_id = $model->id;
                                $pushNotification->booking_status = $model->booking_status;
                                $pushNotification->reference_id = $model->id;
                                $pushNotification->is_active = 1;
                                $pushNotification->save();

                                $pushNotificationUser = new PushNotificationUser();
                                $pushNotificationUser->push_notification_id = $pushNotification->id;
                                $pushNotificationUser->user_id = $provider->id;
                                $pushNotificationUser->save();
                            } catch (\Exception $e) {
                                info("Provider in-app notification log failed for {$key}: " . $e->getMessage());
                            }
                        }
                    }

                    if ($settingsType == 'serviceman_notification') {
                        $serviceman = $model?->serviceman?->user;
                        $repeatOrRegular = $model?->is_repeated ? 'repeat' : 'regular';
                        $title = get_push_notification_message($key, $settingsType, $serviceman?->current_language_key);
                        if (empty($title)) {
                            $title = ($key == 'serviceman_assign') ? translate('Serviceman Assigned') : translate('Schedule Updated');
                        }
                        $description = ($key == 'serviceman_assign')
                            ? translate("You have been assigned to booking #{$bookingCode}")
                            : translate("Schedule for assigned booking #{$bookingCode} updated to ") . $model->service_schedule;

                        if ($serviceman?->fcm_token && $title) {
                            device_notification($serviceman?->fcm_token, $title, $description, null, $model->id, 'booking', null, null, null, null, $repeatOrRegular);
                        }
                    }
                }
            }
        });


        self::deleting(function ($model) {

        });

        self::deleted(function ($model) {

        });
    }
}
