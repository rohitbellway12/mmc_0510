<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Provider;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;
use Modules\ServiceManagement\Entities\Tyre;
use Modules\CategoryManagement\Entities\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProviderTyreController extends Controller
{
    private $tyre;

    public function __construct(Tyre $tyre)
    {
        $this->tyre = $tyre;
    }

    public function index(Request $request)
    {
        $search = $request->has('search') ? $request['search'] : '';
        $provider = $request->user()->provider;
        if (!$provider) {
            \Brian2694\Toastr\Facades\Toastr::error(translate('provider_not_found'), translate('Error'));
            return redirect()->back();
        }
        $providerId = $provider->id;


        $tyres = $this->tyre
            ->where('provider_id', $providerId)
            ->when($request->has('search'), function ($query) use ($search) {
                $query->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(pagination_limit());

        return view('servicemanagement::provider.tyre.index', compact('tyres', 'search'));
    }

    public function create()
    {
        $categories = Category::ofStatus(1)->ofType('main')->get();
        return view('servicemanagement::provider.tyre.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'required|string',
            'model' => 'nullable|string',
            'tyre_type' => 'nullable|string',
            'season' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'width' => 'nullable|string',
            'profile' => 'nullable|string',
            'rim_size' => 'nullable|string',
            'speed_rating' => 'nullable|string',
            'load_index' => 'nullable|string',
            'size' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images' => 'required|array',
            'images.*' => 'image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $provider = $request->user()->provider;
        if (!$provider) {
            \Brian2694\Toastr\Facades\Toastr::error(translate('provider_not_found'), translate('Error'));
            return redirect()->back();
        }

        // Construct standard size string if width, profile, rim_size are provided
        $size = $request->size;
        if (empty($size) && $request->filled(['width', 'profile', 'rim_size'])) {
            $size = $request->width . '/' . $request->profile . ' ' . $request->rim_size;
            if ($request->filled('load_index') || $request->filled('speed_rating')) {
                $size .= ' ' . $request->load_index . $request->speed_rating;
            }
        }

        $tyreInstance = new Tyre();
        $tyreInstance->fill([
            'category_id' => $request->category_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'tyre_type' => $request->tyre_type ?? 'tubeless',
            'season' => $request->season ?? 'all_season',
            'vehicle_type' => $request->vehicle_type ?? 'passenger_car',
            'width' => $request->width,
            'profile' => $request->profile,
            'rim_size' => $request->rim_size,
            'speed_rating' => $request->speed_rating,
            'load_index' => $request->load_index,
            'size' => $size ?? 'Standard',
            'price' => $request->price,
            'stock' => $request->stock,
        ]);
        $tyreInstance->provider_id = $provider->id;
        $tyreInstance->status = 1;

        if ($request->has('images')) {
            $images = [];
            foreach ($request->images as $image) {
                $images[] = file_uploader('tyre/', 'png', $image);
            }
            $tyreInstance->images = $images;
        }

        $tyreInstance->save();
        \Brian2694\Toastr\Facades\Toastr::success(translate('Tyre added successfully'), translate('Success'));
        return redirect()->route('provider.tyre.index');
    }

    public function edit($id)
    {
        $provider = auth()->user()->provider;
        if (!$provider) {
            \Brian2694\Toastr\Facades\Toastr::error(translate('provider_not_found'), translate('Error'));
            return redirect()->back();
        }
        $providerId = $provider->id;
        $tyre = $this->tyre->where('id', $id)->where('provider_id', $providerId)->firstOrFail();
        $categories = Category::ofStatus(1)->ofType('main')->get();

        return view('servicemanagement::provider.tyre.edit', compact('tyre', 'categories'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $providerId = $request->user()->provider->id;
        $tyre = $this->tyre->where('id', $id)->where('provider_id', $providerId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'brand' => 'required|string',
            'model' => 'nullable|string',
            'tyre_type' => 'nullable|string',
            'season' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'width' => 'nullable|string',
            'profile' => 'nullable|string',
            'rim_size' => 'nullable|string',
            'speed_rating' => 'nullable|string',
            'load_index' => 'nullable|string',
            'size' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $size = $request->size;
        if (empty($size) && $request->filled(['width', 'profile', 'rim_size'])) {
            $size = $request->width . '/' . $request->profile . ' ' . $request->rim_size;
            if ($request->filled('load_index') || $request->filled('speed_rating')) {
                $size .= ' ' . $request->load_index . $request->speed_rating;
            }
        }

        $tyre->category_id = $request->category_id;
        $tyre->brand = $request->brand;
        $tyre->model = $request->input('model');
        $tyre->tyre_type = $request->tyre_type ?? $tyre->tyre_type;
        $tyre->season = $request->season ?? $tyre->season;
        $tyre->vehicle_type = $request->vehicle_type ?? $tyre->vehicle_type;
        $tyre->width = $request->width;
        $tyre->profile = $request->profile;
        $tyre->rim_size = $request->rim_size;
        $tyre->speed_rating = $request->speed_rating;
        $tyre->load_index = $request->load_index;
        $tyre->size = $size ?? $tyre->size;
        $tyre->price = $request->price;
        $tyre->stock = $request->stock;

        if ($request->has('images')) {
            $images = $tyre->images ?? [];
            foreach ($request->images as $image) {
                $images[] = file_uploader('tyre/', 'png', $image);
            }
            $tyre->images = $images;
        }

        $tyre->save();

        Toastr::success(translate('Tyre updated successfully'), translate('Success'));
        return redirect()->route('provider.tyre.index');
    }

    public function destroy(Request $request, $id): RedirectResponse
    {
        $providerId = $request->user()->provider->id;
        $tyre = $this->tyre->where('id', $id)->where('provider_id', $providerId)->firstOrFail();
        $tyre->delete();

        Toastr::success(translate('Tyre deleted successfully'), translate('Success'));
        return back();
    }

    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $providerId = $request->user()->provider->id;
        $tyre = $this->tyre->where('id', $id)->where('provider_id', $providerId)->firstOrFail();
        $tyre->status = !$tyre->status;
        $tyre->save();

        return response()->json(response_formatter(DEFAULT_200), 200);
    }
}
