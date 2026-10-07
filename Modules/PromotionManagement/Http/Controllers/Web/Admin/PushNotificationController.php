<?php

namespace Modules\PromotionManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\ZoneManagement\Entities\Zone;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\Serviceman;
use Modules\ProviderManagement\Entities\Provider;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PushNotificationController extends Controller
{
    private PushNotification $pushNotification;
    private Zone $zone;

    use AuthorizesRequests;

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
            })->orderBy('created_at', 'desc')->paginate(pagination_limit(), ['*'], 'offset', $request['offset'])->withPath('');

        $pushNotification->map(function ($query) {
            $query->zone_ids = $this->zone->select('id', 'name')->whereIn('id', $query->zone_ids)->get();
        });

        return response()->json(response_formatter(DEFAULT_200, $pushNotification), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(Request $request): View|Factory|Application
    {
        $this->authorize('push_notification_view');
        $search = $request->has('search') ? $request['search'] : '';
        $toUserType = $request->has('to_user_type') ? $request['to_user_type'] : 'all';
        $queryParam = ['search' => $search, 'to_user_type' => $toUserType];

        $zones = $this->zone->ofStatus(1)->latest()->get();

        $pushNotification = $this->pushNotification
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('to_user_type') && $request['to_user_type'] != 'all', function ($query) use ($request) {
                return $query->whereJsonContains('to_users', $request['to_user_type']);
            })->orderBy('created_at', 'desc')->paginate(pagination_limit())->appends($queryParam);

        $pushNotification->map(function ($query) {
            $query->zone_ids = $this->zone->select('id', 'name')->whereIn('id', $query->zone_ids)->get();
        });

        return view('promotionmanagement::admin.push-notification.create', compact('zones', 'pushNotification', 'toUserType', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('push_notification_add');

        $validatedData = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:200',
            'to_users' => 'required|array',
            'to_users.*' => 'in:customer,provider-admin,provider-serviceman,all',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'cover_image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        $image_name = file_uploader('push-notification/', 'png', $request->file('cover_image'));

        $permissionsMap = [
            'customer' => true,
            'provider-admin' => true,
            'provider-serviceman' => true
        ];

        $filteredUsers = array_filter(array_map(function($user) use ($permissionsMap) {
            return $permissionsMap[$user] ? $user : null;
        }, $validatedData['to_users']));

        if (in_array('all', $validatedData['to_users'], true)) {
            $filteredUsers = array_keys(array_filter($permissionsMap));
        }

        $this->pushNotification->create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'to_users' => $filteredUsers,
            'zone_ids' => $validatedData['zone_ids'] ?? [],
            'cover_image' => $image_name,
            'is_active' => 1,
        ]);


        foreach ($filteredUsers as $user_type) {
            foreach ($validatedData['zone_ids'] as $zone_id) {
                topic_notification("{$user_type}-{$zone_id}", $validatedData['title'], $validatedData['description'], $image_name, null, 'general');
            }
        }

        $this->sendDeviceNotificationsToUsers($filteredUsers, $validatedData['zone_ids'] ?? [], $validatedData['title'], $validatedData['description'], $image_name);

        Toastr::success(translate(BANNER_CREATE_200['message']));
        return back();
    }

    public function resendNotification($id)
    {
        $pushNotification = $this->pushNotification->where(['id' => $id])->first();

        if (!$pushNotification){
            Toastr::error(translate('push_notification_not_found'));
            return back();
        }

        $permissionsMap = [
            'customer' => true,
            'provider-admin' => true,
            'provider-serviceman' => true
        ];

        $filteredUsers = array_filter(array_map(function($user) use ($permissionsMap) {
            return $permissionsMap[$user] ? $user : null;
        }, $pushNotification['to_users']));


        if (in_array('all', $pushNotification['to_users'], true)) {
            $filteredUsers = array_keys(array_filter($permissionsMap));
        }

        foreach ($filteredUsers as $user_type) {
            foreach ($pushNotification['zone_ids'] as $zone_id) {
                try {
                    topic_notification("{$user_type}-{$zone_id}", $pushNotification['title'], $pushNotification['description'], $pushNotification['cover_image'], null, 'general');
                }catch (\Exception $exception){
                    //
                }
            }
        }

        $this->sendDeviceNotificationsToUsers($filteredUsers, $pushNotification['zone_ids'] ?? [], $pushNotification['title'], $pushNotification['description'], $pushNotification['cover_image']);

        Toastr::success(translate('push notification send successfully'));
        return back();
    }


    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit(string $id): View|Factory|Application
    {
        $this->authorize('push_notification_update');
        $pushNotification = $this->pushNotification->where('id', $id)->first();
        $zones = $this->zone->ofStatus(1)->latest()->get();
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
        return view('promotionmanagement::admin.push-notification.edit', compact('pushNotification','zones'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorize('push_notification_update');
        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|max:200',
            'to_users' => 'required|array',
            'to_users.*' => 'in:customer,provider-admin,provider-serviceman,all',
            'zone_ids' => 'required|array',
            'zone_ids.*' => 'uuid',
            'cover_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        $pushNotification = $this->pushNotification->where(['id' => $id])->first();
        $pushNotification->title = $request['title'];
        $pushNotification->description = $request['description'];
        $pushNotification->to_users = $request['to_users'];
        $pushNotification->zone_ids = $request['zone_ids'];

        if ($request->has('cover_image')) {
            $pushNotification->cover_image = file_uploader('push-notification/', 'png', $request->file('cover_image'), $pushNotification->cover_image);
        }

        $pushNotification->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $this->authorize('push_notification_delete');
        $pushNotification = $this->pushNotification->where('id', $id)->withoutGlobalScope('translate')->first();
        if (isset($pushNotification)){
            file_remover('push-notification/', $pushNotification['cover_image']);
            $this->pushNotification->where('id', $id)->withoutGlobalScope('translate')->delete();
        }

        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);

    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $this->authorize('push_notification_manage_status');
        $pushNotification = $this->pushNotification->where('id', $id)->first();
        $this->pushNotification->where('id', $id)->update(['is_active' => !$pushNotification->is_active]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('push_notification_export');

        $items = $this->pushNotification
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('title', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })
            ->when($request->has('to_user_type') && $request['to_user_type'] != 'all', function ($query) use ($request) {
                return $query->whereJsonContains('to_users', $request['to_user_type']);
            })
            ->orderBy('created_at', 'desc')
            ->latest()->get();

        return (new FastExcel($items))->download(time().'-file.xlsx');
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
        if (in_array('customer', $filteredUsers)) {
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
        if (in_array('provider-admin', $filteredUsers)) {
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
        if (in_array('provider-serviceman', $filteredUsers)) {
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
