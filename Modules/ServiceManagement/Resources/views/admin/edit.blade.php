@extends('adminmodule::layouts.master')

@section('title', translate('service_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module') }}/plugins/select2/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module') }}/plugins/dataTables/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module') }}/plugins/dataTables/select.dataTables.min.css" />
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module') }}/plugins/wysiwyg-editor/froala_editor.min.css" />
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module') }}/css/tags-input.min.css" />

    {{-- AI --}}
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/css/ai-sidebar.css') }}" />
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('update_service') }}</h2>
                    </div>

                    <div class="card-wrap">
                        <div class="card-body-inner">
                            <form action="{{ route('admin.service.update', [$service->id]) }}" method="post"
                                enctype="multipart/form-data" id="service-add-form">
                                @csrf
                                @method('PUT')

                                <div id="form-wizard">
                                    <h3>{{ translate('service_information') }}</h3>
                                    <section class="">
                                        <div class="row service-description-wrapper">
                                            <div class="col-xxl-9 col-lg-8 mb-5 mb-lg-0">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <div class="mb-20">
                                                            <h3 class="mb-1 text-dark">{{ translate('Basic Setup') }}</h3>
                                                            <p class="fs-12 text-color">
                                                                {{ translate('Provide essential service details') }}</p>
                                                        </div>
                                                        <div class="bg-light p-xxl-20 p-12px rounded">
                                                            @php($language = Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name', 'system_language')->first())
                                                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                                            @if ($language)
                                                                <ul
                                                                    class="nav nav--tabs border-color-primary mb-5 flex-nowrap text-nowrap overflow-auto">
                                                                    <li class="nav-item">
                                                                        <a class="nav-link lang_link active" href="#"
                                                                            id="default-link">{{ translate('default') }}</a>
                                                                    </li>
                                                                    @foreach ($language?->live_values as $lang)
                                                                        <li class="nav-item">
                                                                            <a class="nav-link lang_link" href="#"
                                                                                id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                            <!-- Language End -->
                                                            @if ($language)
                                                                <div class="mb-30 lang-form" id="default-form">
                                                                    
                                                                    <div
                                                                        class="form-floating form-floating__icon outline-wrapper title-container-default">
                                                                        <input type="text" name="name[]"
                                                                            id="default_name"
                                                                            class="form-control default-name"
                                                                            placeholder="{{ translate('service_name') }}"
                                                                            value="{{ $service?->getRawOriginal('name') }}"
                                                                            required>
                                                                        <label>{{ translate('service_name') }}
                                                                            ({{ translate('default') }})</label>
                                                                        <span class="material-icons">subtitles</span>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="lang[]" value="default">
                                                                @foreach ($language?->live_values as $lang)
                                                                    <?php
                                                                    if (count($service['translations'])) {
                                                                        $translate = [];
                                                                        foreach ($service['translations'] as $t) {
                                                                            if ($t->locale == $lang['code'] && $t->key == 'name') {
                                                                                $translate[$lang['code']]['name'] = $t->value;
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>

                                                                    <div class="mb-30 d-none lang-form"
                                                                        id="{{ $lang['code'] }}-form">
                                                                        
                                                                        <div
                                                                            class="form-floating form-floating__icon outline-wrapper title-container-{{ $lang['code'] }}">
                                                                            <input type="text" name="name[]"
                                                                                id="{{ $lang['code'] }}_name"
                                                                                class="form-control"
                                                                                placeholder="{{ translate('service_name') }}"
                                                                                value="{{ $translate[$lang['code']]['name'] ?? '' }}">
                                                                            <label>{{ translate('service_name') }}({{ strtoupper($lang['code']) }})</label>
                                                                            <span class="material-icons">subtitles</span>
                                                                        </div>
                                                                    </div>
                                                                    <input type="hidden" name="lang[]"
                                                                        value="{{ $lang['code'] }}">
                                                                @endforeach
                                                            @else
                                                                <div class="lang-form">
                                                                    <div class="mb-30">
                                                                        <div class="form-floating form-floating__icon">
                                                                            <input type="text" class="form-control"
                                                                                name="name[]"
                                                                                placeholder="{{ translate('service_name') }} *"
                                                                                required value="{{ $service->name }}">
                                                                            <label>{{ translate('service_name') }}
                                                                                *</label>
                                                                            <span class="material-icons">subtitles</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="lang[]" value="default">
                                                                
                                                            @endif
                                                            <!-- Service Name End -->

                                                            @if ($language)
                                                                <div class="lang-form2" id="default-form2">
                                                                    <div class="mb-30">
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-between gap-1 flex-wrap mb-3">
                                                                            <label
                                                                                class="m-0 lh-1">{{ translate('short_description') }}({{ translate('default') }})
                                                                                *</label>
                                                                            
                                                                        </div>
                                                                        <div class="outline-wrapper">
                                                                            <textarea type="text" class="form-control default_short_description"  name="short_description[]">{{ $service?->getRawOriginal('short_description') }}</textarea>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Removed Long Description (Default) -->
                                                                    <input type="hidden" name="description[]"
                                                                        value="{{ $service?->getRawOriginal('description') }}">
                                                                </div>
                                                                @foreach ($language?->live_values as $lang)
                                                                    <?php
                                                                    if (count($service['translations'])) {
                                                                        $translate = [];
                                                                        foreach ($service['translations'] as $t) {
                                                                            if ($t->locale == $lang['code'] && $t->key == 'short_description') {
                                                                                $translate[$lang['code']]['short_description'] = $t->value;
                                                                            }
                                                                    
                                                                            if ($t->locale == $lang['code'] && $t->key == 'description') {
                                                                                $translate[$lang['code']]['description'] = $t->value;
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <div class="d-none lang-form2"
                                                                        id="{{ $lang['code'] }}-form2">
                                                                        <div class="col-lg-12 mt-5">
                                                                            <div class="mb-30">
                                                                                <div
                                                                                    class="d-flex align-items-center justify-content-between gap-1 flex-wrap mb-3">
                                                                                    <label
                                                                                        class="m-0">{{ translate('short_description') }}({{ strtoupper($lang['code']) }})
                                                                                        *</label>
                                                                                    
                                                                                </div>

                                                                                <div class="form-floating outline-wrapper">
                                                                                    <textarea type="text" class="form-control {{ $lang['code'] }}_short_description" name="short_description[]">{{ $translate[$lang['code']]['short_description'] ?? '' }}</textarea>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- Removed Long Description (Translated) -->
                                                                        <input type="hidden" name="description[]"
                                                                            value="{{ $translate[$lang['code']]['description'] ?? '' }}">
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <div class="normal-form">
                                                                    <div class="col-lg-12 mt-5">
                                                                        <div class="mb-30">
                                                                            <div class="form-floating">
                                                                                <textarea type="text" class="form-control"  name="short_description[]">{{ $service->short_description }}</textarea>
                                                                                <label>{{ translate('short_description') }}
                                                                                    *</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Removed Long Description (English) -->
                                                                    <input type="hidden" name="description[]"
                                                                        value="{{ $service->description }}">
                                                                </div>
                                                            @endif
                                                            <!-- ShotDescription End -->
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                            <div class="col-xxl-3 col-lg-4 mb-5 mb-sm-0">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-column align-items-center gap-3">
                                                            <div class="text-center">
                                                                <h3 class="mb-1 text-dark">{{ translate('Service Image') }}</h3>
                                                                <p class="fs-12 text-muted mb-0">{{ translate('Optional (1:1 Ratio)') }}</p>
                                                            </div>
                                                            <div class="upload-file">
                                                                <input type="file" class="upload-file__input" name="thumbnail"
                                                                    accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                                <div class="upload-file__img">
                                                                    <img src="{{ onErrorImage(
                                                                        $service->thumbnail,
                                                                        asset('storage/app/public/service') . '/' . $service->thumbnail,
                                                                        asset('public/assets/admin-module/img/media/upload-file.png'),
                                                                        'service/',
                                                                    ) }}"
                                                                        alt="{{ translate('image') }}">
                                                                </div>
                                                                <span class="upload-file__edit">
                                                                    <span class="material-icons">edit</span>
                                                                </span>
                                                            </div>
                                                            <p class="opacity-75 fs-12 text-center">{{ translate('image_format_-_jpg,_png,_jpeg,_gif_image_size_-_maximum_size_2_MB') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="general_wrapper mt-4">
                                            <div class="outline-wrapper">
                                                <div class="card bg-animate">
                                                    <div class="card-body">
                                                        
                                                        <div class="mb-20 max-w-500">
                                                            <h3 class="mb-1 text-dark">{{ translate('General Setup') }}
                                                            </h3>
                                                            <p class="fs-12 text-color m-0">
                                                                {{ translate('Here you can set up the foundational details required for service creation.') }}
                                                            </p>
                                                        </div>
                                                        <div class="bg-light rounded p-xxl-20 p-12px">
                                                            <div class="row g-lg-4 g-3">
                                                                <div class="col-lg-4 col-md-6">
                                                                    <select class="js-select theme-input-style w-100"
                                                                        name="category_id" id="category-id">
                                                                        <option value="0" selected disabled>
                                                                            {{ translate('choose_category') }}</option>
                                                                        @foreach ($categories as $category)
                                                                            <option value="{{ $category->id }}"
                                                                                {{ $category->id == $service->category_id ? 'selected' : '' }}>
                                                                                {{ $category->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-lg-4 col-md-6 d-none">
                                                                    <div class="m-0" id="sub-category-selector">
                                                                        <select class="js-select theme-input-style w-100"
                                                                            name="sub_category_id"></select>
                                                                    </div>
                                                                </div>
                                                                {{-- 
                                                                <!-- Tax Percentage Field (Hidden - Default 0) -->
                                                                <div class="col-lg-4 col-md-6">
                                                                    <div class="form-floating form-floating__icon">
                                                                        <input type="text" class="form-control"
                                                                            name="tax_input" min="0" max="100"
                                                                            step="0.01"
                                                                            placeholder="{{ translate('add_tax_percentage') }}"
                                                                            value="{{ $service->tax }}">
                                                                        <label>{{ translate('add_tax_percentage') }}</label>
                                                                        <span class="material-icons">percent</span>
                                                                    </div>
                                                                </div>
                                                                --}}
                                                                <input type="hidden" name="tax" value="0">
                                                                <div class="col-lg-4 col-md-6">
                                                                    <div class="form-floating form-floating__icon">
                                                                        <input type="number" class="form-control"
                                                                            name="price" min="0" step="any"
                                                                            placeholder="{{ translate('base_price') }} *"
                                                                            required="" value="{{ $service->price }}">
                                                                        <label>{{ translate('base_price') }}
                                                                            *</label>
                                                                        <span class="material-icons">monetization_on</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-md-5 d-none">
                                                                    <div class="form-floating form-floating__icon">
                                                                        <input type="number" class="form-control"
                                                                            name="min_bidding_price" min="0"
                                                                            max="100" step="any"
                                                                            placeholder="{{ translate('min_bidding_price') }} *"
                                                                            required=""
                                                                            value="{{ $service->min_bidding_price }}">
                                                                        <label>{{ translate('min_bidding_price') }}
                                                                            *</label>
                                                                        <span class="material-icons">price_change</span>
                                                                    </div>
                                                                </div>
                                                                <div class="ol-lg-8 col-md-7 d-none">
                                                                    <div class="form-floating taginput-dark-support">
                                                                        <input type="text" class="form-control"
                                                                            name="tags"
                                                                            placeholder="{{ translate('Enter tags') }}"
                                                                            value="{{ implode(',', $tagNames) }}"
                                                                            data-role="tagsinput">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </section>

                                    <div class="card mt-4">
                                        <div class="card-body">
                                            <div
                                                class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <h3 class="mb-1 text-dark">{{ translate('Price Variation') }}</h3>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="switcher">
                                                        <input type="checkbox" class="switcher_input"
                                                            id="variation-toggle"
                                                            {{ $service->variations->count() > 0 ? 'checked' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                    <span class="fw-medium">{{ translate('Enable Variations') }}</span>
                                                </div>
                                            </div>

                                            <div id="variation-section"
                                                style="{{ $service->variations->count() > 0 ? '' : 'display: none;' }}">
                                                <div class="general_wrapper mb-20">
                                                    <div class="outline-wrapper">
                                                        <div class="card bg-animate border-0 shadow-none bg-light">
                                                            <div class="card-body">
                                                                
                                                                <div class="p-xxl-20 p-12px rounded">
                                                                    <div class="d-flex flex-wrap gap-20 mb-01">
                                                                        <div class="form-floating flex-grow-1">
                                                                            <input type="text" class="form-control"
                                                                                name="variant_name" id="variant-name"
                                                                                placeholder="{{ translate('add_variant') }}">
                                                                            <label>{{ translate('add_variant') }}</label>
                                                                        </div>
                                                                        <button type="button"
                                                                            class="btn rounded btn--primary"
                                                                            id="service-ajax-variation">
                                                                            <span class="material-icons">add</span>
                                                                            {{ translate('add') }}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive p-01 mt-3">
                                                        <table class="table align-middle table-variation">
                                                            <thead id="category-wise-zone" class="text-nowrap">
                                                                <tr>
                                                                    <th scope="col">{{ translate('variations') }}</th>
                                                                    <th scope="col" class="d-none">
                                                                        {{ translate('default_price') }}</th>
                                                                    @foreach ($zones as $zone)
                                                                        <th scope="col" class="d-none">
                                                                            {{ $zone->name }}
                                                                        </th>
                                                                    @endforeach
                                                                    <th scope="col">{{ translate('action') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="variation-update-table">
                                                                @include(
                                                                    'servicemanagement::admin.partials._update-variant-data',
                                                                    [
                                                                        'variants' => $service->variations,
                                                                        'zones' => $zones,
                                                                    ]
                                                                )
                                                            </tbody>
                                                        </table>

                                                        <div id="new-variations-table"
                                                            class="{{ session()->has('variations') && count(session('variations')) > 0 ? '' : 'hide-div' }}">
                                                            <label
                                                                class="badge badge-primary mb-10">{{ translate('new_variations') }}</label>
                                                            <table class="table align-middle table-variation">
                                                                <tbody id="variation-table">
                                                                    @include(
                                                                        'servicemanagement::admin.partials._variant-data',
                                                                        ['zones' => $zones]
                                                                    )
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        

        {{-- AI assistant --}}
        
    </div>
@endsection

@push('script')
    <script src="{{ asset('public/assets/admin-module') }}/js//tags-input.min.js"></script>
    <script src="{{ asset('public/assets/admin-module') }}/plugins/select2/select2.min.js"></script>
    <script src="{{ asset('public/assets/admin-module') }}/plugins/jquery-steps/jquery.steps.min.js"></script>
    <script src="{{ asset('public/assets/admin-module/plugins/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('public/assets/ckeditor/jquery.js') }}"></script>

    {{-- AI --}}
    
    
    
    
    
    
    <script src="{{ asset('public/assets/admin-module/js/AI/image-compressor/image-compressor.js') }}"></script>
    <script src="{{ asset('public/assets/admin-module/js/AI/image-compressor/compressor.min.js') }}"></script>

    <script>
        "use strict";

        $(document).ready(function() {
            $('.js-select').select2();

            $('#variation-toggle').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#variation-section').slideDown();
                } else {
                    $('#variation-section').slideUp();
                }
            });
        });

        $("#form-wizard").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            autoFocus: true,
            onStepChanged: function(event, currentIndex, priorIndex) {
                let nextBtn = $(".actions a[href='#next']");
                if (nextBtn.hasClass("proceed-to-next")) {
                    setTimeout(function() {
                        $(".variation_setup_auto_fill").trigger("click");
                    }, 1000);
                }
            },
            onFinished: function(event, currentIndex) {
                event.preventDefault();

                let isValid = true;
                $(".desc-err").remove(); // Remove previous error messages

                let variationSections = $("#variation-update-table, #variation-table");

                // Loop through all number inputs
                variationSections.find('input[type="number"]').each(function() {
                    let value = parseFloat($(this).val());
                    let minValue = parseFloat($(this).attr('min'));

                    if (isNaN(value) || value === "") {
                        toastr.error('Please enter a valid number');
                        isValid = false;
                    } else if (value < 0) { // Changed from <= 0 to < 0 to allow 0
                        toastr.error('Value must be non-negative');
                        isValid = false;
                    } else if (!isNaN(minValue) && value < minValue) {
                        toastr.error(`Minimum allowed value is ${minValue}`);
                        isValid = false;
                    }
                });

                if (!isValid) {
                    return false; // Stop form submission if validation fails
                }

                $("#service-add-form")[0].submit();

            }
        });

        ajax_get(
            '{{ url('/') }}/admin/category/ajax-childes-only/{{ $service->category_id }}?sub_category_id={{ $service->sub_category_id }}',
            'sub-category-selector')

        $("#service-ajax-variation").on('click', function() {
            let route = "{{ route('admin.service.ajax-add-variant') }}";
            let id = "variation-table";
            ajax_variation(route, id);
        })

        function ajax_variation(route, id) {

            let name = $('#variant-name').val();
            let price = 0; // Default price to 0

            if (name.length > 0) {
                $.get({
                    url: route,
                    dataType: 'json',
                    data: {
                        name: $('#variant-name').val(),
                        price: 0,
                    },
                    success: function(response) {
                        if (response.flag == 0) {
                            toastr.info('Already added');
                        } else {
                            $('#new-variations-table').show();
                            $('#' + id).html(response.template);
                            $('#variant-name').val("");
                            // $('#variant-price').val(0);
                        }
                    },
                });
            } else {
                toastr.warning('{{ translate('fields_are_required') }}');
            }
        }

        document.addEventListener('click', function(event) {
            if (event.target.closest('.service-ajax-remove-variant')) {
                const btn = event.target.closest('.service-ajax-remove-variant');
                if (!btn) return;
                var route = event.target.closest('.service-ajax-remove-variant').getAttribute('data-route');
                var id = event.target.closest('.service-ajax-remove-variant').getAttribute('data-id');
                const count = parseInt(btn.getAttribute('data-item'));
                if (count <= 1) {
                    Swal.fire({
                        title: "{{ translate('Warning') }}",
                        text: "{{ translate('Minimum variant cannot be less than one') }}",
                        icon: 'warning',
                        confirmButtonColor: 'var(--bs-primary)',
                    });
                    return;
                }
                ajax_remove_variant(route, id);
            }
        });


        function ajax_remove_variant(route, id) {
            Swal.fire({
                title: "{{ translate('are_you_sure') }}?",
                text: "{{ translate('want_to_remove_this_variation') }}",
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        dataType: 'json',
                        data: {},
                        beforeSend: function() {},
                        success: function(response) {
                            console.log(response.template)
                            $('#' + id).html(response.template);
                        },
                        complete: function() {},
                    });
                }
            })
        }


        $("#category-id").change(function() {
            let id = this.value;
            let route = "{{ url('/admin/category/ajax-childes/') }}/" + id;
            ajax_switch_category(route)
        });

        function ajax_switch_category(route) {
            $.get({
                url: route + '?service_id={{ $service->id }}',
                dataType: 'json',
                data: {},
                beforeSend: function() {},
                success: function(response) {
                    console.log(response);
                    $('#sub-category-selector').html(response.template);
                    $('#category-wise-zone').html(response.template_for_zone);
                    $('#variation-table').html(response.template_for_variant);
                    $('#variation-update-table').html(response.template_for_update_variant);
                },
                complete: function() {},
            });
        }

        $(document).ready(function() {
            tinymce.init({
                selector: 'textarea.ckeditor'
            });
        });

        $(".lang_link").on('click', function(e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(".lang-form2").addClass('d-none')

            $(".title-btn-wrapper").addClass('d-none');
            $(".short-description-btn-wrapper").addClass('d-none');
            $(".description-btn-wrapper").addClass('d-none');

            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);

            $("#" + lang + "-form").removeClass('d-none');
            $("#" + lang + "-form2").removeClass('d-none');

            // show the right button
            $("#title-" + lang + "-action-btn").removeClass('d-none');
            $("#short-description-" + lang + "-action-btn").removeClass('d-none');
            $("#description-" + lang + "-action-btn").removeClass('d-none');

            if (lang == '{{ $default_lang }}') {
                $(".from_part_2").removeClass('d-none');
            } else {
                $(".from_part_2").addClass('d-none');
            }
        });
    </script>
@endpush
