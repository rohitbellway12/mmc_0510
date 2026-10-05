<?php

namespace Modules\BidModule\Http\Controllers\APi\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\Post;
use Modules\BidModule\Entities\PostBid;
use Modules\BookingModule\Http\Traits\BookingTrait;
use function response;
use function response_formatter;

class PostBidController extends Controller
{
    use BookingTrait;

    public function __construct(
        private PostBid $post_bid,
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'post_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $bid_offers_visibility_for_providers = (business_config('bid_offers_visibility_for_providers', 'bidding_system'))->live_values ?? 0;

        if (!$bid_offers_visibility_for_providers) {
            return response()->json(response_formatter(DEFAULT_403, null), 403);
        }

        $post_bids = $this->post_bid
            ->with(['provider.owner.reviews'])
            ->where('post_id', $request['post_id'])
            ->where('provider_id', '!=', $request->user()->id)
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        if ($post_bids->count() < 1) {
            return response()->json(response_formatter(DEFAULT_404, null), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $post_bids), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|uuid',
            'offered_price' => 'required',
            'provider_note' => '',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $post_bid = $this->post_bid->firstOrNew([
            'post_id' => $request['post_id'],
            'provider_id' => $request->user()->provider->id
        ]);
        $post_bid->offered_price = $request['offered_price'];
        $post_bid->provider_note = $request['provider_note'];
        $post_bid->status = 'pending';
        $post_bid->post_id = $request['post_id'];
        $post_bid->provider_id = $request->user()->provider->id;
        $post_bid->save();

        $postModel = Post::with(['customer', 'service_address'])->find($request['post_id']);
        $customer = $postModel?->customer;
        $zoneId = $postModel?->zone_id ?? $postModel?->service_address?->zone_id ?? $customer?->zone_id ?? config('zone_id');

        $title = get_push_notification_message('customer_notification_for_provider_bid_offer', 'customer_notification', $customer?->current_language_key);
        $data_info = [
            'provider_name' => $request->user()?->provider?->company_name,
        ];
        info($request->user()->provider?->company_name);
        $userNotify  = isNotificationActive(null, 'booking', 'notification', 'user');
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

        return response()->json(response_formatter(DEFAULT_STORE_200, null), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $post_bids = $this->post_bid
            ->where('status', 'pending')
            ->where('post_id', $request['post_id'])
            ->where('provider_id', $request->user()->provider->id);

        if ($post_bids->count() < 1) {
            return response()->json(response_formatter(DEFAULT_404, null), 404);
        }

        $fcmToken = $post_bids->first()?->post?->customer?->fcm_token ?? null;

        if (!is_null($fcmToken)) {
            $languageKey = $post_bids->first()?->post?->customer?->current_language_key;
            $title = get_push_notification_message('customer_notification_for_provider_bid_withdraw', 'customer_notification', $languageKey);
            $userNotify  = isNotificationActive(null, 'booking', 'notification', 'user');
            if ($userNotify) {
                device_notification($fcmToken, $title, null, null, null, 'bid-withdraw');
            }
        }

        $post_bids->delete();

        return response()->json(response_formatter(DEFAULT_DELETE_200, null), 200);
    }
}
