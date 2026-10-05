<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Entities\ProviderQuestion;
use Modules\CategoryManagement\Entities\Category;

class CategoryQuestionController extends Controller
{
    protected ProviderQuestion $question;
    protected Category $category;

    public function __construct(ProviderQuestion $question, Category $category)
    {
        $this->question = $question;
        $this->category = $category;
    }

    /**
     * Display listing of global category questions
     */
    public function index(Request $request): View|Factory|Application
    {
        $search = $request->has('search') ? $request['search'] : '';

        $questions = $this->question
            ->with('category:id,name')
            ->whereNull('provider_id')
            ->when($search, function ($query) use ($search) {
                $query->where('question_text', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('display_order')
            ->paginate(10);

        $categories = $this->category->where('is_active', 1)->select('id', 'name')->get();

        return view('providermanagement::admin.category-questions', compact('questions', 'categories', 'search'));
    }

    /**
     * Store a new global question
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string|max:500',
            'question_type' => 'required|in:yes_no,text,select,file',
            'options' => 'required_if:question_type,select|nullable|string',
            'category_id' => 'required|uuid|exists:categories,id',
            'is_required' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        try {
            // Get max display order for global questions
            $maxOrder = $this->question->whereNull('provider_id')->max('display_order') ?? 0;

            $this->question->create([
                'provider_id' => null,
                'category_id' => $request->category_id,
                'question_text' => $request->question_text,
                'options' => $request->options,
                'question_type' => $request->question_type,
                'is_required' => $request->is_required,
                'is_active' => 1,
                'display_order' => $maxOrder + 1,
            ]);

            return response()->json(response_formatter(DEFAULT_STORE_200), 200);
        } catch (\Exception $e) {
            return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'exception', 'message' => $e->getMessage()]]), 400);
        }
    }

    /**
     * Update an existing global question
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string|max:500',
            'question_type' => 'required|in:yes_no,text,select,file',
            'options' => 'required_if:question_type,select|nullable|string',
            'category_id' => 'required|uuid|exists:categories,id',
            'is_required' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        try {
            $question = $this->question->where('id', $id)->whereNull('provider_id')->first();

            if (!$question) {
                return response()->json(response_formatter(DEFAULT_404), 404);
            }

            $question->update([
                'category_id' => $request->category_id,
                'question_text' => $request->question_text,
                'options' => $request->question_type == 'select' ? $request->options : null,
                'question_type' => $request->question_type,
                'is_required' => $request->is_required,
            ]);

            return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
        } catch (\Exception $e) {
            return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'exception', 'message' => $e->getMessage()]]), 400);
        }
    }

    /**
     * Update question status
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|uuid',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $question = $this->question->where('id', $request->id)->whereNull('provider_id')->first();

        if (!$question) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $question->is_active = $request->status;
        $question->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Delete question
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $question = $this->question->where('id', $id)->whereNull('provider_id')->first();

        if (!$question) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $question->delete();
        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }
}
