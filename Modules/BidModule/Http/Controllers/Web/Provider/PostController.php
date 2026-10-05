<?php

namespace Modules\BidModule\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BidModule\Entities\IgnoredPost;
use Modules\BidModule\Entities\Post;
use Modules\BidModule\Entities\PostBid;
use Modules\ProviderManagement\Entities\SubscribedService;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostController extends Controller
{
    public function __construct(
        private Post              $post,
        private SubscribedService $subscribed_service,
        private IgnoredPost       $ignored_post,
        private PostBid           $post_bid,
    )
    {
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse|Renderable
     * @throws ValidationException
     */
    public function index(Request $request): Renderable|RedirectResponse
    {
        Validator::make($request->all(), [
            'type' => 'required|in:all,new_booking_request,placed_offer'
        ])->validate();

        $query_param = ['type' => $request['type'], 'search' => $request['search']];

        $subscribed_services = $this->subscribed_service
            ->where(['provider_id' => $request->user()->provider->id])
            ->where(['is_subscribed' => 1])->get();

        $subCategoryIds = $subscribed_services->pluck('sub_category_id')->filter()->toArray();
        $categoryIds = $subscribed_services->pluck('category_id')->filter()->toArray();
        $serviceIds = $subscribed_services->pluck('service_id')->filter()->toArray();

        $ignored_posts = $this->ignored_post->where('provider_id', $request->user()->provider->id)->pluck('post_id')->toArray();
        $bidding_post_validity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;
        $bidding_post_validity = $bidding_post_validity > 0 ? $bidding_post_validity : 30;
        $providerId = $request->user()->provider->id;
        $posts = $this->post
            ->with(['bids.provider', 'addition_instructions', 'service', 'services', 'category', 'sub_category', 'booking', 'customer', 'targeted_providers'])
            ->where('is_booked', 0)
            ->whereNotIn('id', $ignored_posts)
            ->where('zone_id', $request->user()->provider->zone_id)
            ->where(function ($query) use ($providerId, $subCategoryIds, $categoryIds, $serviceIds) {
                $query->whereHas('targeted_providers', function ($sub) use ($providerId) {
                    $sub->where('provider_id', $providerId);
                })
                ->orWhere(function ($openQuery) use ($subCategoryIds, $categoryIds, $serviceIds) {
                    $openQuery->whereDoesntHave('targeted_providers')
                        ->where(function ($matchQuery) use ($subCategoryIds, $categoryIds, $serviceIds) {
                            $matchQuery->whereIn('sub_category_id', $subCategoryIds)
                                ->orWhereIn('category_id', $categoryIds)
                                ->orWhereIn('service_id', $serviceIds);
                        });
                });
            })
            ->whereBetween('created_at', [Carbon::now()->subDays($bidding_post_validity), Carbon::now()])
            ->when($request['type'] != 'all' && $request['type'] != 'new_booking_request', function ($query) use ($request) {
                $query->whereHas('bids', function ($query) use ($request) {
                    if ($request['type'] == 'placed_offer') {
                        $query->where('status', 'pending')->where('provider_id', $request->user()->provider->id);
                    } else if ($request['type'] == 'booking_placed') {
                        $query->where('status', 'accepted');
                    }
                });
            })
            ->when($request['type'] != 'all' && $request['type'] == 'new_booking_request', function ($query) use ($request) {
                if ($request->user()?->provider?->service_availability && (!$request->user()->provider->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                    $query->whereDoesntHave('bids', function ($query) use ($request) {
                        $query->where('provider_id', $request->user()->provider->id);
                    });
                } else {
                    $query->whereNull('id');
                }
            })
            ->when($request['type'] == 'all', function ($query) use ($request) {
                if (!$request->user()?->provider?->service_availability || ($request->user()->provider->is_suspended && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                    $query->whereHas('bids', function ($query) use ($request) {
                        if ($request['type'] == 'placed_offer') {
                            $query->where('status', 'pending')->where('provider_id', $request->user()->provider->id);
                        } else if ($request['type'] == 'booking_placed') {
                            $query->where('status', 'accepted');
                        }
                    });
                }
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->whereHas('customer', function ($query) use ($request, $keys) {
                    foreach ($keys as $key) {
                        $query->where('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit())
            ->appends($query_param);

        if ($request['type'] == 'all') {
            foreach ($posts as $key => $post) {
                if ($post->bids) {
                    foreach ($post->bids as $bid) {
                        if ($bid->status == 'denied') unset($posts[$key]);
                    }
                }
            }
        }

        $coordinates = auth()->user()->provider->coordinates ?? null;
        foreach ($posts as $post) {
            $distance = null;
            if (!is_null($coordinates) && $post->service_address) {
                $distance = get_distance(
                    [$coordinates['latitude'] ?? null, $coordinates['longitude'] ?? null],
                    [$post->service_address?->lat, $post->service_address?->lon]
                );
                $distance = ($distance) ? number_format($distance, 2) . ' km' : null;
            }
            $post->distance = $distance;
        }

        $bid_offers_visibility_for_providers = business_config('bid_offers_visibility_for_providers', 'bidding_system')?->live_values;
        $type = $request['type'];
        $search = $request['search'];

        $this->post->where('is_checked', 0)->update(['is_checked' => 1]);
        return view('bidmodule::provider.customize-list', compact('posts', 'bid_offers_visibility_for_providers', 'type', 'search'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     * @throws ValidationException
     */
    public function export(Request $request): string|StreamedResponse
    {
        Validator::make($request->all(), [
            'type' => 'in:all,new_booking_request,placed_offer',
            'search' => 'max:255'
        ])->validate();

        $subscribed_sub_categories = $this->subscribed_service
            ->where(['provider_id' => $request->user()->provider->id])
            ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

        $ignored_posts = $this->ignored_post->where('provider_id', $request->user()->provider->id)->pluck('post_id')->toArray();
        $bidding_post_validity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;
        $posts = $this->post
            ->with(['bids', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->whereNotIn('id', $ignored_posts)
            ->whereIn('sub_category_id', $subscribed_sub_categories)
            ->whereBetween('created_at', [Carbon::now()->subDays($bidding_post_validity), Carbon::now()])
            ->when($request['type'] != 'all', function ($query) use ($request) {
                $query->where('is_booked', ($request['type'] == 'placed_offer' ? 1 : 0));
            })
            ->get();

        return (new FastExcel($posts))->download(time() . '-file.xlsx');
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param $post_id
     * @return RedirectResponse|Renderable
     */
    public function details(Request $request, $post_id): Renderable|RedirectResponse
    {
        $post = $this->post
            ->with(['bids', 'addition_instructions', 'question_answers.question', 'service', 'services.category', 'services.subCategory', 'category', 'sub_category', 'booking', 'customer', 'service_address'])
            ->where('id', $post_id)
            ->first();

        $coordinates = auth()->user()->provider->coordinates ?? null;
        $distance = null;
        if (!is_null($coordinates) && $post->service_address) {
            $distance = get_distance(
                [$coordinates['latitude'] ?? null, $coordinates['longitude'] ?? null],
                [$post->service_address?->lat, $post->service_address?->lon]
            );
            $distance = ($distance) ? number_format($distance, 2) . ' km' : null;
        }

        if (!isset($post)) {
            Toastr::error(translate(DEFAULT_404['message']));
            return back();
        }

        $bid_offers_visibility_for_providers = business_config('bid_offers_visibility_for_providers', 'bidding_system')?->live_values;
        return view('bidmodule::provider.details', compact('post', 'bid_offers_visibility_for_providers', 'distance'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Application|Redirector|RedirectResponse
     * @throws ValidationException
     */
    public function updateStatus(Request $request, $id): Redirector|RedirectResponse|Application
    {
        Validator::make($request->all(), [
            'status' => 'in:accept,ignore',

            'offered_price' => $request['status'] == 'accept' ? 'required' : '',
            'provider_note' => '',
        ])->validate();

        if ($request['status'] == 'accept') {
            $post_bid = $this->post_bid->firstOrNew([
                'post_id' => $id,
                'provider_id' => $request->user()->provider->id
            ]);
            $post_bid->offered_price = $request['offered_price'];
            $post_bid->provider_note = $request['provider_note'];
            $post_bid->status = 'pending';
            $post_bid->post_id = $id;
            $post_bid->provider_id = $request->user()->provider->id;
            $post_bid->save();

            $postModel = Post::with(['customer', 'service_address'])->find($id);
            $customer = $postModel?->customer;
            $zoneId = $postModel?->zone_id ?? $postModel?->service_address?->zone_id ?? $customer?->zone_id ?? config('zone_id');

            $title = get_push_notification_message('customer_notification_for_provider_bid_offer', 'customer_notification', $customer?->current_language_key);
            $data_info = [
                'provider_name' => $request->user()?->provider?->company_name,
            ];
            $userNotify = isNotificationActive(null, 'booking', 'notification', 'user');
            if ($customer && $title && $userNotify) {
                if (!empty($customer->fcm_token)) {
                    device_notification_for_bidding($customer->fcm_token, $title, null, null, 'bidding', null, $post_bid->post_id, $request->user()->provider->id, data: $data_info);
                }

                try {
                    $formattedTitle = text_variable_data_format($title, null, 'bidding', $data_info);
                    $pushNotification = new \Modules\PromotionManagement\Entities\PushNotification();
                    $pushNotification->title = $formattedTitle;
                    $pushNotification->description = translate('A service provider has submitted a price quotation of ') . with_currency_symbol($post_bid->offered_price) . translate(' for your request.');
                    $pushNotification->zone_ids = [$zoneId];
                    $pushNotification->to_users = ['customer'];
                    $pushNotification->notification_type = 'post_bid';
                    $pushNotification->reference_id = $post_bid->post_id;
                    $pushNotification->is_active = 1;
                    $pushNotification->save();

                    $pushNotificationUser = new \Modules\PromotionManagement\Entities\PushNotificationUser();
                    $pushNotificationUser->push_notification_id = $pushNotification->id;
                    $pushNotificationUser->user_id = $customer->id;
                    $pushNotificationUser->save();
                } catch (\Exception $e) {
                    info("Bid in-app notification log error: " . $e->getMessage());
                }

                if (!empty($customer?->email)) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($customer->email)->send(new \Modules\BidModule\Emails\BidOfferMail($post_bid));
                    } catch (\Exception $e) {
                        info("Bid offer email error: " . $e->getMessage());
                    }
                }
            }

            Toastr::success(translate('Quotation sent to customer successfully!'));
            return back();

        } else if ($request['status'] == 'ignore') {
            $this->ignored_post->updateOrCreate(
                ['post_id' => $id, 'provider_id' => $request->user()->provider->id], [
                'post_id' => $id
            ]);
        }

        return redirect(route('provider.booking.post.list', ['type' => 'all']));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function multiIgnore(Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'uuid',
        ])->validate();

        foreach ($request->post_ids as $id) {
            IgnoredPost::updateOrCreate(
                ['post_id' => $id, 'provider_id' => auth()->user()->provider->id], [
                'post_id' => $id
            ]);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    public function withdraw($id, Request $request): RedirectResponse
    {
        $post_bids = $this->post_bid
            ->where('status', 'pending')
            ->where('post_id', $id)
            ->where('provider_id', auth()->user()->provider->id);

        if ($post_bids->count() < 1) {
            Toastr::success(translate(DEFAULT_404['message']));
            return back();
        }

        $post_bids->delete();

        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
    }

    /**
     * @return void
     */
    public function check_all(): void
    {
        $this->post->where('is_checked', 0)->update(['is_checked' => 1]);
    }
}
