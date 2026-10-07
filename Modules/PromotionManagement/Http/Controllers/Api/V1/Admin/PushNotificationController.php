<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PromotionManagement\Entities\Banner;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\ZoneManagement\Entities\Zone;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\Serviceman;
use Modules\ProviderManagement\Entities\Provider;

class PushNotificationController extends Controller
{
    private PushNotification $pushNotification;
    private Zone $zone;

    public function __construct(PushNotification $pushNotification, Zone $zone)
    {
        $this->pushNotification = $pushNotification;
        $this->zone = $zone;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'string' => 'string',
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'required|in:active,inactive,all',
            'to_user_type' => 'required|in:customer,provider,serviceman,all',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $pushNotification = $this->pushNotification
            ->when($request->has('string'), function ($query) use ($request) {
                $keys = explode(' ', base64_decode($request['string']));
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })->when($request->has('to_user_type') && $request['to_user_type'] != 'all', function ($query) use ($request) {
                return $query->whereJsonContains('to_users', $request['to_user_type']);
            })->orderBy('created_at', 'desc')->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $pushNotification->map(function ($query) {
            $query->zone_ids = $this->zone->select('id', 'name')->whereIn('id', $query->zone_ids)->get();
        });

        return response()->json(response_formatter(DEFAULT_200, $pushNotification), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'to_users' => 'required|array',
            'to_users.*' => 'in:customer,provider-admin,provider-serviceman,all',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'cover_image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $imageName = file_uploader('push-notification/', 'png', $request->file('cover_image'));

        $pushNotification = $this->pushNotification;
        $pushNotification->title = $request['title'];
        $pushNotification->description = $request['description'];
        $pushNotification->to_users = $request['to_users'];
        $pushNotification->zone_ids = $request['zone_ids'] ?? [];
        $pushNotification->cover_image = $imageName;
        $pushNotification->is_active = 1;
        $pushNotification->save();

        foreach ($request['to_users'] as $type) {
            foreach ($request['zone_ids'] as $zone_id) {
                topic_notification($type . '-' . $zone_id, $request['title'], $request['description'], $imageName, null, 'general');
            }
        }

        $this->sendDeviceNotificationsToUsers($request['to_users'], $request['zone_ids'] ?? [], $request['title'], $request['description'], $imageName);

        return response()->json(response_formatter(BANNER_CREATE_200), 200);
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return JsonResponse
     */
    public function edit(string $id): JsonResponse
    {
        $pushNotification = $this->pushNotification->where('id', $id)->first();
        if (isset($pushNotification)) {
            $zoneArray = [];
            if ($pushNotification->zone_ids != null) {
                foreach ($pushNotification->zone_ids as $id) {
                    $zone = $this->zone::select('id', 'name')->find($id);
                    if (!is_null($zone)) {
                        $zoneArray[] = $zone;
                    }
                }
                $pushNotification->zone_ids = $zoneArray;
            }
            return response()->json(response_formatter(DEFAULT_200, $pushNotification), 200);
        }
        return response()->json(response_formatter(DEFAULT_204, $pushNotification), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'to_users' => 'required|array',
            'to_users.*' => 'in:customer,provider-admin,provider-serviceman,all',
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'cover_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $pushNotification = $this->pushNotification->where(['id' => $id])->first();
        $pushNotification->title = $request['title'];
        $pushNotification->description = $request['description'];
        $pushNotification->to_users = $request['to_users'];
        $pushNotification->zone_ids = $request['zone_ids'];

        if ($request->has('cover_image')) {
            $pushNotification->cover_image = file_uploader('push-notification/', 'png', $request->file('cover_image'), $pushNotification->cover_image);
        }

        $pushNotification->save();

        return response()->json(response_formatter(BANNER_UPDATE_200), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'push_notification_ids' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $pushNotifications = $this->pushNotification->whereIn('id', $request['push_notification_ids']);

        if ($pushNotifications->count() > 0) {
            foreach ($pushNotifications->get() as $pushNotification) {
                file_remover('push-notification/', $pushNotification['cover_image']);
            }

            $pushNotifications->delete();
            return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function statusUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,0',
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->pushNotification->whereIn('id', $request['notification_ids'])->update(['is_active' => $request['status']]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Send direct device notifications to targeted users with active FCM tokens.
     */
    private function sendDeviceNotificationsToUsers(array $filteredUsers, array $zoneIds, string $title, string $description, ?string $coverImage): void
    {
        $tokens = [];
        $imageUrl = null;
        if (!empty($coverImage)) {
            if (filter_var($coverImage, FILTER_VALIDATE_URL)) {
                $imageUrl = $coverImage;
            } else {
                $imageUrl = function_exists('getSingleImageFullPath')
                    ? getSingleImageFullPath('push-notification/' . $coverImage, null, asset('storage/app/public/push-notification/' . $coverImage))
                    : asset('storage/app/public/push-notification/' . $coverImage);
            }
        }

        // 1. Customers
        if (in_array('customer', $filteredUsers) || in_array('all', $filteredUsers)) {
            $customerTokens = User::where('user_type', 'customer')
                ->where('is_active', 1)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->when(!empty($zoneIds), function ($query) use ($zoneIds) {
                    $query->where(function ($sub) use ($zoneIds) {
                        $sub->whereHas('addresses', function ($aq) use ($zoneIds) {
                            $aq->whereIn('zone_id', $zoneIds);
                        })->orWhereDoesntHave('addresses');
                    });
                })
                ->pluck('fcm_token')
                ->toArray();

            $tokens = array_merge($tokens, $customerTokens);
        }

        // 2. Providers
        if (in_array('provider-admin', $filteredUsers) || in_array('all', $filteredUsers)) {
            $providerUserIds = Provider::where('is_active', 1)
                ->when(!empty($zoneIds), function ($query) use ($zoneIds) {
                    $query->whereIn('zone_id', $zoneIds);
                })
                ->pluck('user_id')
                ->toArray();

            $providerTokens = User::where('user_type', 'provider-admin')
                ->whereIn('id', $providerUserIds)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->pluck('fcm_token')
                ->toArray();

            $tokens = array_merge($tokens, $providerTokens);
        }

        // 3. Servicemen
        if (in_array('provider-serviceman', $filteredUsers) || in_array('all', $filteredUsers)) {
            $providerIds = Provider::where('is_active', 1)
                ->when(!empty($zoneIds), function ($query) use ($zoneIds) {
                    $query->whereIn('zone_id', $zoneIds);
                })
                ->pluck('id')
                ->toArray();

            $servicemanUserIds = Serviceman::whereIn('provider_id', $providerIds)
                ->pluck('user_id')
                ->toArray();

            $servicemanTokens = User::where('user_type', 'provider-serviceman')
                ->whereIn('id', $servicemanUserIds)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->pluck('fcm_token')
                ->toArray();

            $tokens = array_merge($tokens, $servicemanTokens);
        }

        $tokens = array_unique(array_filter($tokens));

        foreach ($tokens as $token) {
            try {
                device_notification($token, $title, $description, $imageUrl, null, 'general');
            } catch (\Exception $e) {
                // Ignore any single failed token and continue
            }
        }
    }
}
