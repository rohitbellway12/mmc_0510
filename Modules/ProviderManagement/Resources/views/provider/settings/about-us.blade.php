@extends('providermanagement::layouts.master')

@section('title', translate('About_Us'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{ translate('About_Us') }}</h2>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <form action="{{ route('provider.settings.about-us') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="about_us" class="mb-2">{{ translate('About_Us') }}</label>
                            <textarea id="about_us" name="about_us" class="form-control ckeditor" rows="10"
                                placeholder="{{ translate('Enter_about_us_description') }}">{!! $aboutUs !!}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
    <script>
        $(document).ready(function() {
            $('.ckeditor').each(function() {
                CKEDITOR.replace($(this).attr('id'));
            });
        });
    </script>
@endpush
