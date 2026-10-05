@extends('providermanagement::layouts.master')

@section('title', translate('Edit Tyre'))

@push('css_or_js')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-preview-box {
            position: relative;
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .image-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <h2 class="page-title">{{ translate('Edit Tyre') }}</h2>
            </div>

            <form action="{{ route('provider.tyre.update', [$tyre->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-info-circle me-2"></i> {{ translate('Tyre Information') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="brand" class="form-label">{{ translate('Brand') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="brand" name="brand" required
                                            value="{{ old('brand', $tyre->brand) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="model" class="form-label">{{ translate('Pattern / Model') }}</label>
                                        <input type="text" class="form-control" id="model" name="model"
                                            value="{{ old('model', $tyre->model) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tyre_type" class="form-label">{{ translate('Tyre Type') }}</label>
                                        <select class="form-select" id="tyre_type" name="tyre_type">
                                            <option value="tubeless" {{ old('tyre_type', $tyre->tyre_type) == 'tubeless' ? 'selected' : '' }}>{{ translate('Tubeless (TL)') }}</option>
                                            <option value="tube_type" {{ old('tyre_type', $tyre->tyre_type) == 'tube_type' ? 'selected' : '' }}>{{ translate('Tube-Type (TT)') }}</option>
                                            <option value="run_flat" {{ old('tyre_type', $tyre->tyre_type) == 'run_flat' ? 'selected' : '' }}>{{ translate('Run-Flat (RFT)') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="season" class="form-label">{{ translate('Season') }}</label>
                                        <select class="form-select" id="season" name="season">
                                            <option value="all_season" {{ old('season', $tyre->season) == 'all_season' ? 'selected' : '' }}>{{ translate('All-Season') }}</option>
                                            <option value="summer" {{ old('season', $tyre->season) == 'summer' ? 'selected' : '' }}>{{ translate('Summer') }}</option>
                                            <option value="winter" {{ old('season', $tyre->season) == 'winter' ? 'selected' : '' }}>{{ translate('Winter') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="vehicle_type" class="form-label">{{ translate('Vehicle Type') }}</label>
                                        <select class="form-select" id="vehicle_type" name="vehicle_type">
                                            <option value="passenger_car" {{ old('vehicle_type', $tyre->vehicle_type) == 'passenger_car' ? 'selected' : '' }}>{{ translate('Passenger Car') }}</option>
                                            <option value="suv_4x4" {{ old('vehicle_type', $tyre->vehicle_type) == 'suv_4x4' ? 'selected' : '' }}>{{ translate('SUV / 4x4') }}</option>
                                            <option value="commercial" {{ old('vehicle_type', $tyre->vehicle_type) == 'commercial' ? 'selected' : '' }}>{{ translate('Commercial / Van') }}</option>
                                            <option value="motorcycle" {{ old('vehicle_type', $tyre->vehicle_type) == 'motorcycle' ? 'selected' : '' }}>{{ translate('Motorcycle / Bike') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="width" class="form-label">{{ translate('Width (mm)') }}</label>
                                        <input type="text" class="form-control" id="width" name="width"
                                            value="{{ old('width', $tyre->width) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="profile" class="form-label">{{ translate('Profile / Aspect Ratio (%)') }}</label>
                                        <input type="text" class="form-control" id="profile" name="profile"
                                            value="{{ old('profile', $tyre->profile) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="rim_size" class="form-label">{{ translate('Rim Size') }}</label>
                                        <input type="text" class="form-control" id="rim_size" name="rim_size"
                                            value="{{ old('rim_size', $tyre->rim_size) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="speed_rating" class="form-label">{{ translate('Speed Rating') }}</label>
                                        <input type="text" class="form-control" id="speed_rating" name="speed_rating"
                                            value="{{ old('speed_rating', $tyre->speed_rating) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="load_index" class="form-label">{{ translate('Load Index') }}</label>
                                        <input type="text" class="form-control" id="load_index" name="load_index"
                                            value="{{ old('load_index', $tyre->load_index) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="size" class="form-label">{{ translate('Full Size Label') }}</label>
                                        <input type="text" class="form-control" id="size" name="size"
                                            value="{{ old('size', $tyre->size) }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="category_id" class="form-label">{{ translate('Category') }}</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">{{ translate('Select Category') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('category_id', $tyre->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-tags me-2"></i> {{ translate('Pricing & Stock') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">{{ translate('Price per Tyre') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ currency_symbol() }}</span>
                                            <input type="number" class="form-control" id="price" name="price"
                                                required step="0.01" value="{{ old('price', $tyre->price) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stock" class="form-label">{{ translate('Stock Quantity') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="stock" name="stock" required
                                            min="0" value="{{ old('stock', $tyre->stock) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-images me-2"></i> {{ translate('Tyre Images') }}
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Images') }}
                                        <small>({{ translate('Add more') }})</small></label>
                                    <input type="file" name="images[]" id="tyre_images" class="form-control" multiple
                                        accept="image/*">
                                    <div class="image-preview-container mt-3" id="image_preview_container">
                                        @if ($tyre->images)
                                            @foreach ($tyre->images as $image)
                                                <div class="image-preview-box">
                                                    <img src="{{ asset('storage/app/public/tyre/' . $image) }}">
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <button type="submit" class="btn btn--primary w-100">
                                    <i class="fas fa-save me-2"></i> {{ translate('Update Tyre') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.getElementById('tyre_images').addEventListener('change', function(e) {
            const container = document.getElementById('image_preview_container');
            // Keep existing previews, just add new ones or clear and re-render if preferred.
            // For simplicity, let's clear and re-render only the new ones if we want to show what's being uploaded.
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'image-preview-box';
                        div.innerHTML = `<img src="${e.target.result}">`;
                        container.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
@endpush
