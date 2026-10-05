@extends('adminmodule::layouts.new-master')

@section('title',translate('zone_setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/select.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/zone-module.css')}}"/>
    <style>
        #pac-container {
            min-width: 260px;
            width: 320px;
            max-width: 90vw;
        }
        #pac-container gmp-place-autocomplete,
        #pac-container input {
            width: 100%;
        }

        /* Zone Table Light & Dark Mode Support */
        .table thead th {
            color: var(--bs-dark, #18181a) !important;
            background-color: var(--bs-light, #f8f9fa) !important;
            font-weight: 600;
        }
        .table tbody td {
            color: var(--bs-body-color, #333333) !important;
        }
        .table tbody tr:hover td {
            color: var(--bs-dark, #18181a) !important;
        }

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] .table,
        body[data-bs-theme="dark"] .table {
            --bs-table-color: rgba(255, 255, 255, 0.9) !important;
            --bs-table-bg: transparent !important;
            --bs-table-border-color: rgba(255, 255, 255, 0.1) !important;
        }
        [data-bs-theme="dark"] .table thead th,
        body[data-bs-theme="dark"] .table thead th {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        [data-bs-theme="dark"] .table tbody td,
        body[data-bs-theme="dark"] .table tbody td {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        [data-bs-theme="dark"] .title-color,
        body[data-bs-theme="dark"] .title-color {
            color: rgba(255, 255, 255, 0.9) !important;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('zone_setup')}}</h2>
                    </div>

                    @can('zone_add')
                        <div class="card zone-setup-instructions mb-30">
                            <div class="card-body p-30">
                                <form id="zone-form" action="{{route('admin.zone.store')}}"
                                      enctype="multipart/form-data"
                                      method="POST">
                                    @csrf
                                    <div class="row justify-content-between">
                                        <div class="col-lg-5 col-xl-4 mb-5 mb-lg-0">
                                            <h4 class="mb-3 c1">{{translate('instructions')}}</h4>
                                            <div class="d-flex flex-column">
                                                <p>{{translate('create_zone_by_click_on_map_and_connect_the_dots_together')}}</p>

                                                <div class="media mb-2 gap-3 align-items-center">
                                                    <img
                                                        src="{{asset('public/assets/admin-module/img/icons/map-drag.png')}}"
                                                        alt="{{ translate('image') }}" class="map-icon-global">
                                                    <div class="media-body ">
                                                        <p>{{translate('use_this_to_drag_map_to_find_proper_area')}}</p>
                                                    </div>
                                                </div>

                                                <div class="media gap-3 align-items-center">
                                                    <img
                                                        src="{{asset('public/assets/admin-module/img/icons/map-draw.png')}}"
                                                        alt="{{ translate('image') }}" class="map-icon-global">
                                                    <div class="media-body ">
                                                        <p>{{translate('click_this_icon_to_start_pin_points_in_the_map_and_connect_them_to_draw_a_
                                                        zone_._Minimum_3_points_required')}}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="map-img mt-4">
                                                    <img class="dark-support"
                                                         src="{{asset('public/assets/admin-module/img/instructions.gif')}}"
                                                         alt="{{ translate('image') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                            @if($language)
                                                <ul class="nav nav--tabs border-color-primary mb-4">
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link active"
                                                           href="#"
                                                           id="default-link">{{translate('default')}}</a>
                                                    </li>
                                                    @foreach ($language?->live_values as $lang)
                                                        <li class="nav-item">
                                                            <a class="nav-link lang_link"
                                                               href="#"
                                                               id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            @if($language)
                                                <div class="form-floating form-floating__icon mb-30 lang-form"
                                                     id="default-form">
                                                    <input type="text" name="name[]" class="form-control"
                                                           placeholder="{{translate('zone_name')}}" required>
                                                    <label>{{translate('zone_name')}} ({{ translate('default') }}
                                                        )</label>
                                                    <span class="material-icons">note_alt</span>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                    <div
                                                        class="form-floating form-floating__icon mb-30 d-none lang-form"
                                                        id="{{$lang['code']}}-form">
                                                        <input type="text" name="name[]" class="form-control"
                                                               placeholder="{{translate('zone_name')}}">
                                                        <label>{{translate('zone_name')}}
                                                            ({{strtoupper($lang['code'])}})</label>
                                                        <span class="material-icons">note_alt</span>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="lang-form">
                                                    <div class="mb-30">
                                                        <div class="form-floating form-floating__icon">
                                                            <input type="text" class="form-control" name="name[]"
                                                                   placeholder="{{translate('zone_name')}} *"
                                                                   required value="{{old('name')}}">
                                                            <label>{{translate('zone_name')}} *</label>
                                                            <span class="material-icons">note_alt</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif

                                            <div class="form-group mb-3 coordinates">
                                                <label class="input-label"
                                                       for="exampleFormControlInput1">{{translate('coordinates')}}
                                                    <span
                                                        class="input-label-secondary">{{translate('draw_your_zone_on_the_map')}}</span>
                                                </label>
                                                <textarea type="text" rows="8" name="coordinates" id="coordinates"
                                                          class="form-control" readonly></textarea>
                                            </div>

                                            <div class="map-warper dark-support rounded overflow-hidden">
                                                <div id="pac-container" class="controls rounded search_area"></div>
                                                <div class="map_canvas" id="map-canvas"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-end gap-20 mt-30">
                                                <button class="btn btn--secondary" type="reset"
                                                        id="reset_btn">{{translate('reset')}}</button>
                                                <button class="btn btn--primary"
                                                        type="submit">{{translate('submit')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <div class="d-flex justify-content-end border-bottom mx-lg-4 mb-10">
                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Zones')}}:</span>
                            <span class="title-color">{{ $zones->total() }}</span>
                        </div>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two"  method="GET">
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input zone-search-input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    @can('zone_export')
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                       href="{{route('admin.zone.download')}}?search={{$search}}">{{translate('excel')}}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endcan
                                </div>
                            </div>

                            <div id="ListTableContainer">
                                @include('zonemanagement::admin.partials._table')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="offset" value="{{ request()->page }}">

@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js')}}"></script>

    @php($api_key=(business_config('google_map', 'third_party'))->live_values)
    <script src="https://maps.googleapis.com/maps/api/js?key={{$api_key['map_api_key_client']}}&libraries=places&v=beta&loading=async&callback=initMap"></script>
    <script>
        "use strict";

        let map;
        let lastPolygon = null;
        let drawingPath = [];
        let tempMarkers = [];
        let drawingPolyline = null;
        let mapClickListener = null;
        let dblClickListener = null;

        function updateCoordinatesField(polygon) {
            // Keep the exact same format the backend already expects from the
            // old DrawingManager flow: an array of LatLng objects, which the
            // browser coerces to "(lat, lng),(lat, lng),..." when assigned to .val()
            $('#coordinates').val(polygon.getPath().getArray());
        }

        function clearDrawing() {
            if (lastPolygon) {
                lastPolygon.setMap(null);
                lastPolygon = null;
            }
            tempMarkers.forEach(m => m.setMap(null));
            tempMarkers = [];
            if (drawingPolyline) {
                drawingPolyline.setMap(null);
                drawingPolyline = null;
            }
            drawingPath = [];
            $('#coordinates').val('');
            if (map) map.setOptions({ disableDoubleClickZoom: false });
            if (mapClickListener) { google.maps.event.removeListener(mapClickListener); mapClickListener = null; }
            if (dblClickListener) { google.maps.event.removeListener(dblClickListener); dblClickListener = null; }
        }

        function finishDrawing() {
            if (mapClickListener) { google.maps.event.removeListener(mapClickListener); mapClickListener = null; }
            if (dblClickListener) { google.maps.event.removeListener(dblClickListener); dblClickListener = null; }
            map.setOptions({ disableDoubleClickZoom: false });

            tempMarkers.forEach(m => m.setMap(null));
            tempMarkers = [];
            if (drawingPolyline) {
                drawingPolyline.setMap(null);
                drawingPolyline = null;
            }

            if (drawingPath.length < 3) {
                toastr.warning('{{ translate("minimum_3_points_required") }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
                drawingPath = [];
                return;
            }

            lastPolygon = new google.maps.Polygon({
                paths: drawingPath,
                editable: true,
                map: map,
                strokeColor: '#FF0000',
                fillColor: '#FF0000',
                fillOpacity: 0.35
            });

            updateCoordinatesField(lastPolygon);

            const path = lastPolygon.getPath();
            google.maps.event.addListener(path, 'insert_at', () => updateCoordinatesField(lastPolygon));
            google.maps.event.addListener(path, 'set_at', () => updateCoordinatesField(lastPolygon));
            google.maps.event.addListener(path, 'remove_at', () => updateCoordinatesField(lastPolygon));

            drawingPath = [];
        }

        function startDrawing() {
            clearDrawing();
            map.setOptions({ disableDoubleClickZoom: true });

            drawingPolyline = new google.maps.Polyline({
                map: map,
                path: [],
                strokeColor: '#FF0000',
                strokeWeight: 2
            });

            mapClickListener = map.addListener('click', function (e) {
                drawingPath.push(e.latLng);
                drawingPolyline.setPath(drawingPath);

                const marker = new google.maps.Marker({
                    position: e.latLng,
                    map: map,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 5,
                        fillColor: '#FF0000',
                        fillOpacity: 1,
                        strokeWeight: 1
                    }
                });
                tempMarkers.push(marker);
            });

            dblClickListener = map.addListener('dblclick', function () {
                finishDrawing();
            });
        }

        function addDrawControl(controlDiv) {
            const controlUI = document.createElement('div');
            controlUI.style.backgroundColor = '#fff';
            controlUI.style.border = '2px solid #fff';
            controlUI.style.borderRadius = '3px';
            controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
            controlUI.style.cursor = 'pointer';
            controlUI.style.margin = '8px';
            controlUI.title = "{{ translate('draw_your_zone_on_the_map') }}";
            controlDiv.appendChild(controlUI);

            const controlText = document.createElement('div');
            controlText.style.color = 'rgb(25,25,25)';
            controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
            controlText.style.fontSize = '12px';
            controlText.style.lineHeight = '30px';
            controlText.style.padding = '0 10px';
            controlText.innerHTML = "{{ translate('draw_zone') }}";
            controlUI.appendChild(controlText);

            controlUI.addEventListener('click', () => startDrawing());
        }

        function addFinishControl(controlDiv) {
            const controlUI = document.createElement('div');
            controlUI.style.backgroundColor = '#fff';
            controlUI.style.border = '2px solid #fff';
            controlUI.style.borderRadius = '3px';
            controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
            controlUI.style.cursor = 'pointer';
            controlUI.style.margin = '8px';
            controlUI.title = "{{ translate('finish_drawing') }}";
            controlDiv.appendChild(controlUI);

            const controlText = document.createElement('div');
            controlText.style.color = 'rgb(25,25,25)';
            controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
            controlText.style.fontSize = '12px';
            controlText.style.lineHeight = '30px';
            controlText.style.padding = '0 10px';
            controlText.innerHTML = "{{ translate('finish') }}";
            controlUI.appendChild(controlText);

            controlUI.addEventListener('click', () => finishDrawing());
        }

        function resetMap(controlDiv) {
            const controlUI = document.createElement("div");
            controlUI.style.backgroundColor = "#fff";
            controlUI.style.border = "2px solid #fff";
            controlUI.style.borderRadius = "3px";
            controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
            controlUI.style.cursor = "pointer";
            controlUI.style.marginTop = "8px";
            controlUI.style.marginBottom = "22px";
            controlUI.style.textAlign = "center";
            controlUI.title = "Reset map";
            controlDiv.appendChild(controlUI);
            const controlText = document.createElement("div");
            controlText.style.color = "rgb(25,25,25)";
            controlText.style.fontFamily = "Roboto,Arial,sans-serif";
            controlText.style.fontSize = "10px";
            controlText.style.lineHeight = "16px";
            controlText.style.paddingLeft = "2px";
            controlText.style.paddingRight = "2px";
            controlText.innerHTML = "X";
            controlUI.appendChild(controlText);
            controlUI.addEventListener("click", () => clearDrawing());
        }

        async function initMap() {
            let myLatLng = { lat: 23.757989, lng: 90.360587 };

            map = new google.maps.Map(document.getElementById("map-canvas"), {
                zoom: 10,
                center: myLatLng,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    map.setCenter({ lat: position.coords.latitude, lng: position.coords.longitude });
                });
            }

            const drawDiv = document.createElement("div");
            addDrawControl(drawDiv);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(drawDiv);

            const finishDiv = document.createElement("div");
            addFinishControl(finishDiv);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(finishDiv);

            const resetDiv = document.createElement("div");
            resetMap(resetDiv);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(resetDiv);

            // ---- Modern Place Autocomplete widget (replaces deprecated SearchBox) ----
            try {
                const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");
                const placeAutocomplete = new PlaceAutocompleteElement({
                    locationBias: map.getBounds()
                });
                document.getElementById('pac-container').appendChild(placeAutocomplete);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('pac-container'));

                map.addListener('bounds_changed', () => {
                    placeAutocomplete.locationBias = map.getBounds();
                });

                let searchMarker = null;
                placeAutocomplete.addEventListener('gmp-select', async ({ placePrediction }) => {
                    const place = placePrediction.toPlace();
                    await place.fetchFields({ fields: ['displayName', 'location', 'viewport'] });

                    if (!place.location) return;

                    if (searchMarker) searchMarker.setMap(null);
                    searchMarker = new google.maps.Marker({
                        map,
                        position: place.location,
                        title: place.displayName
                    });

                    if (place.viewport) {
                        map.fitBounds(place.viewport);
                    } else {
                        map.setCenter(place.location);
                        map.setZoom(15);
                    }
                });
            } catch (err) {
                console.error('Place Autocomplete could not be loaded:', err);
            }
        }

        // Note: initMap is already invoked once via the script tag's callback=initMap
    // parameter above. Do NOT also call it on window 'load' — that caused
    // initMap to run twice, duplicating the search box.

        $('#reset_btn').click(function () {
            clearDrawing();
        });

        function performValidation(event) {
            if (!lastPolygon) {
                event.preventDefault();
                toastr.warning('{{ translate('Please draw your zone on the map') }}', {
                    CloseButton: true,
                    ProgressBar: true,
                });
            }
        }

        $('#zone-form').submit(function (event) {
            performValidation(event);
        });

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form").removeClass('d-none');
        });

        let statusSelectedItem;
        let statusSelectedRoute;
        let statusInitialState;

        $('.nav-link').on('click', function () {
            const urlParams = new URLSearchParams($(this).attr('href').split('?')[1]);
        });

        $(document).on('change', '.status-update', function (e) {
            // Prevent default toggle behavior to avoid checkbox jumping
            e.preventDefault();
            e.stopImmediatePropagation();

            statusSelectedItem = $(this);
            statusInitialState = statusSelectedItem.prop('checked'); // Get current state (true if ON)

            // Immediately revert the checkbox visually until confirmation
            statusSelectedItem.prop('checked', !statusInitialState);

            let itemId = statusSelectedItem.data('id');
            statusSelectedRoute = '{{ route('admin.zone.status-update', ['id' => ':itemId']) }}'.replace(':itemId', itemId);

            let confirmationTitleText = statusInitialState
                ? '{{ translate('Are you sure to Turn On the Zone Status') }}?'
                : '{{ translate('Are you sure to Turn Off the Zone Status') }}?';

            $('.confirmation-title-text').text(confirmationTitleText);

            let confirmationDescriptionText = statusInitialState
                ? '{{ translate('Once you turn on the Zone Status, the user can find the category, services, and location in that zone') }}.'
                : '{{ translate('Once you turn off the Zone Status it will impact the category, services, and location finding for customers') }}.';

            $('.confirmation-description-text').text(confirmationDescriptionText);

            let imgSrc = statusInitialState
                ? "{{ asset('public/assets/admin-module/img/icons/status-on.png') }}"
                : "{{ asset('public/assets/admin-module/img/icons/status-off.png') }}";

            $('#confirmChangeModal img').attr('src', imgSrc);

            showModal();
        });

        $('#confirmChange').on('click', function () {
            updateStatus(statusSelectedRoute);
        });

        $('.cancel-change').on('click', function () {
            resetCheckboxState();
            hideModal();
        });

        $('#confirmChangeModal').on('hidden.bs.modal', function () {
            resetCheckboxState();
        });

        function showModal() {
            $('#confirmChangeModal').modal('show');
        }

        function hideModal() {
            $('#confirmChangeModal').modal('hide');
        }

        //  Reverts checkbox if user cancels
        function resetCheckboxState() {
            if (statusSelectedItem) {
                statusSelectedItem.prop('checked', !statusInitialState);
            }
        }

        //  AJAX update - triggers only if user confirms
        function updateStatus(route) {
            let page = $('#offset').val();
            $.ajax({
                url: route,
                type: 'POST',
                data: {_token: '{{ csrf_token() }}'},
                dataType: 'json',
                success: function (data) {
                    toastr.success(data.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });

                    // Update UI manually or reload table as needed
                    reloadTable(page); // Optional - if backend changes are needed
                    hideModal();
                },
                error: function () {
                    resetCheckboxState();
                    toastr.error('Something went wrong! Please try again.');
                }
            });
        }

        function reloadTable(page) {
            let search = $('.zone-search-input').val();
            $.ajax({
                url: "{{ route('admin.zone.table') }}",
                type: "GET",
                data: {
                    search: search,
                    page: page
                },
                success: function (response) {
                    if (response.page != page) {
                        updateBrowserUrl(search, response.page);
                        $('#offset').val((response.page - 1) * {{ pagination_limit() }});
                    } else {
                        $('#offset').val(response.offset);
                        updateBrowserUrl(search, page);
                    }

                    $('#totalListCount').html(response.totalCount)
                    $('#ListTableContainer').empty().html(response.view);
                },
                error: function () {
                    toastr.error('Failed to update table. Please reload the page.', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function updateBrowserUrl(search, page) {
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (page > 1) params.set('page', page);

            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);
        }
    </script>
@endpush