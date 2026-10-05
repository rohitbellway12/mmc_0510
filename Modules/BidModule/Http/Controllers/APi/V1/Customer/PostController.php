<?php

namespace Modules\BidModule\Http\Controllers\APi\V1\Customer;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\Post;
use Modules\BidModule\Entities\PostAdditionalInstruction;
use Modules\BidModule\Entities\PostBid;
use Modules\CartModule\Traits\CartTrait;
use Modules\CustomerModule\Traits\CustomerAddressTrait;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\BidModule\Entities\PostProvider;
use Modules\BidModule\Entities\PostService;
use Modules\ServiceManagement\Entities\Service;
use Ramsey\Uuid\Uuid;
use function response;
use function response_formatter;

class PostController extends Controller
{
    use CustomerAddressTrait, CartTrait;


    public function __construct(
        private Post                      $post,
        private PostBid                   $postBid,
        private PostAdditionalInstruction $post_additional_instruction,
    )
    {
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'is_booked' => 'in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $biddingPostValidity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;
        $posts = $this->post
            ->with(['addition_instructions', 'question_answers.question', 'service', 'services', 'category', 'sub_category', 'booking', 'targeted_providers'])
            ->withCount(['bids' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->where('customer_user_id', $request->user()->id)
            ->whereBetween('created_at', [Carbon::now()->subDays($biddingPostValidity), Carbon::now()])
            ->when(!is_null($request['is_booked']), function ($query) use ($request) {
                $query->where('is_booked', $request['is_booked']);
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        if ($posts->count() < 1) {
            return response()->json(response_formatter(DEFAULT_404, null), 404);
        }

        return response()->json(response_formatter(DEFAULT_200, $posts), 200);
    }


    /**
     * Display a listing of the resource.
     * @param $postId
     * @param Request $request
     * @return JsonResponse
     */
    public function show($postId, Request $request): JsonResponse
    {
        $post = $this->post
            ->with(['addition_instructions', 'question_answers.question', 'service', 'services', 'category', 'sub_category', 'booking', 'service_address', 'targeted_providers'])
            ->withCount(['bids'])
            ->where('id', $postId)
            ->where('customer_user_id', $request->user()->id)
            ->first();

        if (!isset($post)) {
            return response()->json(response_formatter(DEFAULT_404, null), 404);
        }

        $postBid = $this->postBid->where('id', $request->post_bid_id)->first();
        $referralAmount = 0;
        if ($postBid){

            $price = $postBid?->offered_price;

            $referralAmount = $this->referralEarningEligiblityCheck($post?->customer?->id, $price);
        }

        return response()->json(response_formatter(DEFAULT_200, ['post_details' => $post, 'referral_amount' => $referralAmount]), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_description' => 'required',
            'booking_schedule' => 'required|date',
            'service_id' => 'required_without:service_ids|uuid',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'uuid',
            'category_id' => 'required|uuid',
            'sub_category_id' => 'nullable|uuid',
            'provider_ids' => 'nullable|array',
            'provider_ids.*' => 'uuid',
            'car_model' => 'nullable|string',
            'car_registration_number' => 'nullable|string',
            'car_image' => 'nullable',
            'car_images' => 'nullable|array',
            'car_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'damage_description' => 'nullable|string',
            'additional_instructions' => 'array',
            'service_address_id' => is_null($request['service_address']) ? 'required' : 'nullable',
            'service_address' => is_null($request['service_address_id']) ? [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail($attribute . ' must be a valid JSON string.');
                        return;
                    }

                    if (is_null($decoded['lat']) || $decoded['lat'] == '') $fail($attribute . ' must contain "lat" properties.');
                    if (is_null($decoded['lon']) || $decoded['lon'] == '') $fail($attribute . ' must contain "lon" properties.');
                    if (is_null($decoded['address']) || $decoded['address'] == '') $fail($attribute . ' must contain "address" properties.');
                    if (is_null($decoded['contact_person_name']) || $decoded['contact_person_name'] == '') $fail($attribute . ' must contain "contact_person_name" properties.');
                    if (is_null($decoded['contact_person_number']) || $decoded['contact_person_number'] == '') $fail($attribute . ' must contain "contact_person_number" properties.');
                    if (is_null($decoded['address_label']) || $decoded['address_label'] == '') $fail($attribute . ' must contain "address_label" properties.');
                },
            ] : '',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        if (is_null($request['service_address_id'])) {
            $request['service_address_id'] = $this->add_address(json_decode($request['service_address']), $request->user()->id);
        }

        // Resolve service IDs (supports single service_id or multiple service_ids[])
        $serviceIds = [];
        if ($request->has('service_ids') && is_array($request['service_ids'])) {
            $serviceIds = array_values(array_unique(array_filter($request['service_ids'])));
        }
        if (!empty($request['service_id']) && !in_array($request['service_id'], $serviceIds)) {
            array_unshift($serviceIds, $request['service_id']);
        }
        $primaryServiceId = $request['service_id'] ?? ($serviceIds[0] ?? null);

        $subCategoryId = $request['sub_category_id'];
        if (empty($subCategoryId)) {
            $service = Service::withoutGlobalScopes()->find($primaryServiceId);
            $subCategoryId = $service?->sub_category_id ?? $request['category_id'];
        }

        $post = $this->post;
        $post->service_description = $request['service_description'];
        $post->booking_schedule = $request['booking_schedule'];
        $post->customer_user_id = $request->user()->id;
        $post->service_id = $primaryServiceId;
        $post->category_id = $request['category_id'];
        $post->sub_category_id = $subCategoryId;
        $post->service_address_id = $request['service_address_id'];
        $post->zone_id = $request['zone_id'] ?? config('zone_id');

        // Car and damage details
        $rawDamageDesc = $request['damage_description'] ?? null;
        $extractedAssessment = null;
        if (!empty($rawDamageDesc) && preg_match('/\[Assessment:\s*(.*?)\]/is', $rawDamageDesc, $matches)) {
            $extractedAssessment = $matches[1];
            $cleanDamageDesc = trim(preg_replace('/\[Assessment:\s*.*?\]/is', '', $rawDamageDesc));
        } else {
            $cleanDamageDesc = $rawDamageDesc;
        }

        $post->car_model = $request['car_model'] ?? null;
        $post->car_registration_number = $request['car_registration_number'] ?? null;
        $post->damage_description = $cleanDamageDesc;

        // Multiple car images handling (supports car_images[] or car_image[] or single car_image)
        $uploadedCarImages = [];
        if ($request->hasFile('car_images')) {
            $files = $request->file('car_images');
            if (!is_array($files)) $files = [$files];
            foreach ($files as $imgFile) {
                if ($imgFile->isValid()) {
                    $uploadedCarImages[] = file_uploader('post/car/', 'png', $imgFile);
                }
            }
        } elseif ($request->hasFile('car_image')) {
            $files = $request->file('car_image');
            if (!is_array($files)) $files = [$files];
            foreach ($files as $imgFile) {
                if ($imgFile->isValid()) {
                    $uploadedCarImages[] = file_uploader('post/car/', 'png', $imgFile);
                }
            }
        }

        if (!empty($uploadedCarImages)) {
            $post->car_image = count($uploadedCarImages) > 1 ? json_encode($uploadedCarImages) : $uploadedCarImages[0];
        }

        $post->save();

        // Save multiple services in post_services pivot table
        if (!empty($serviceIds)) {
            $postServices = [];
            foreach ($serviceIds as $sId) {
                $postServices[] = [
                    'post_id' => $post->id,
                    'service_id' => $sId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            PostService::insert($postServices);
        }

        // Handle targeted providers
        $hasTargetedProviders = false;
        if ($request->has('provider_ids') && is_array($request['provider_ids']) && count($request['provider_ids']) > 0) {
            $providerRecords = [];
            foreach ($request['provider_ids'] as $pId) {
                if (!empty($pId)) {
                    $providerRecords[] = [
                        'post_id' => $post->id,
                        'provider_id' => $pId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($providerRecords)) {
                PostProvider::insert($providerRecords);
                $hasTargetedProviders = true;
            }
        }

        $title = get_push_notification_message('customized_booking_request', 'customer_notification', $request->user()?->current_language_key);
        $permission = isNotificationActive(null, 'booking', 'notification', 'user');
        if ($title && $request->user()?->fcm_token && $permission) {
            device_notification_for_bidding($request->user()->fcm_token, $title, null, null, 'bidding', null, $post->id, null);
        }

        if (count($request['additional_instructions'] ?? []) > 0) {
            $data = [];
            foreach ($request['additional_instructions'] as $key => $item) {
                $data[$key]['id'] = Uuid::uuid4();
                $data[$key]['details'] = $item;
                $data[$key]['post_id'] = $post->id;
                $data[$key]['created_at'] = now();
                $data[$key]['updated_at'] = now();
            }
            $this->post_additional_instruction->insert($data);
        }

        // Save Category Question Answers
        $answers = $request->answers ?? $request->question_answers ?? [];
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        $savedQuestionIds = [];
        if (is_array($answers) && count($answers) > 0) {
            foreach ($answers as $questionId => $answer) {
                $qId = null;
                $ansVal = '';
                if (is_array($answer)) {
                    $qId = $answer['provider_question_id'] ?? ($answer['question_id'] ?? ($answer['id'] ?? null));
                    if (!$qId && !empty($answer['question'])) {
                        $matchedQ = \Modules\ProviderManagement\Entities\ProviderQuestion::where('question_text', 'LIKE', '%' . trim($answer['question']) . '%')->first();
                        $qId = $matchedQ?->id;
                    }
                    $ansVal = is_array($answer['answer_value'] ?? null)
                        ? implode(', ', $answer['answer_value'])
                        : (is_array($answer['answer'] ?? null) ? implode(', ', $answer['answer']) : ($answer['answer_value'] ?? ($answer['answer'] ?? '')));
                } elseif (preg_match('/^[0-9a-fA-F-]{36}$/', (string)$questionId)) {
                    $qId = $questionId;
                    $ansVal = is_array($answer) ? implode(', ', $answer) : (string)$answer;
                }

                if (!empty($qId)) {
                    \Modules\BookingModule\Entities\BookingQuestionAnswer::create([
                        'post_id' => $post->id,
                        'provider_question_id' => $qId,
                        'answer_value' => $ansVal
                    ]);
                    $savedQuestionIds[] = $qId;
                }
            }
        }

        // Fallback: If no answers were provided, but assessment text was embedded in damage_description
        if (empty($savedQuestionIds) && !empty($extractedAssessment)) {
            $assessmentParts = explode('|', $extractedAssessment);
            foreach ($assessmentParts as $part) {
                $kv = explode(':', trim($part), 2);
                if (count($kv) === 2) {
                    $qText = trim($kv[0]);
                    $ansVal = trim($kv[1]);
                    $matchedQ = \Modules\ProviderManagement\Entities\ProviderQuestion::where('question_text', 'LIKE', '%' . $qText . '%')->first();
                    if ($matchedQ && !in_array($matchedQ->id, $savedQuestionIds)) {
                        \Modules\BookingModule\Entities\BookingQuestionAnswer::create([
                            'post_id' => $post->id,
                            'provider_question_id' => $matchedQ->id,
                            'answer_value' => $ansVal
                        ]);
                        $savedQuestionIds[] = $matchedQ->id;
                    }
                }
            }
        }

        // Notify Providers: Only selected providers if targeted, otherwise all subscribed providers
        $providerQuery = Provider::with('owner')->where('zone_id', $post->zone_id);
        if ($hasTargetedProviders) {
            $providerQuery->whereIn('id', $request['provider_ids']);
        } else {
            $provider_ids = SubscribedService::where('is_subscribed', 1)
                ->where(function ($q) use ($subCategoryId, $post, $serviceIds) {
                    if (!empty($subCategoryId)) {
                        $q->where('sub_category_id', $subCategoryId);
                    }
                    if (!empty($post->category_id)) {
                        $q->orWhere('category_id', $post->category_id);
                    }
                    if (!empty($serviceIds)) {
                        $q->orWhereIn('service_id', $serviceIds);
                    }
                })
                ->pluck('provider_id')
                ->unique()
                ->toArray();
            $providerQuery->whereIn('id', $provider_ids);
        }

        if (business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
            $providerQuery->where('is_suspended', 0);
        }

        $providers = $providerQuery->get();

        $bookingNotificationStatus = business_config('booking', 'notification_settings')->live_values;

        foreach ($providers as $provider) {
            $fcm_token = $provider->owner->fcm_token ?? null;
            $title = get_push_notification_message('new_service_request_arrived', 'provider_notification', $provider?->owner?->current_language_key);
            $data_info = [
                'user_name' => $request->user()?->first_name . ' ' . $request->user()?->last_name,
            ];
            if (!is_null($fcm_token) && $provider?->service_availability && $title && isset($bookingNotificationStatus) && $bookingNotificationStatus['push_notification_booking']) {
                device_notification_for_bidding($fcm_token, $title, null, null, 'bidding', null, $post->id, null, $data_info);
            }
        }

        return response()->json(response_formatter(DEFAULT_STORE_200, ['post_id' => $post->id]), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateInfo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'booking_schedule' => 'date',
            'service_address_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $post = $this->post->find($request['post_id']);
        if (!isset($post)) return response()->json(response_formatter(DEFAULT_404, null), 404);

        $post->service_address_id = !is_null($request['service_address_id']) ? $request['service_address_id'] : $post->service_address_id;
        $post->booking_schedule = $request['booking_schedule'] ? $request['booking_schedule'] : $post->booking_schedule;
        $post->save();

        return response()->json(response_formatter(DEFAULT_UPDATE_200, null), 200);
    }

}
