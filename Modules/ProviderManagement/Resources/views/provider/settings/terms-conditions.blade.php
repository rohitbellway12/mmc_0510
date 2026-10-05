@extends('providermanagement::layouts.master')

@section('title', translate('Terms_&_Conditions'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{ translate('Terms_&_Conditions') }}</h2>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <form action="{{ route('provider.settings.terms-conditions') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="terms_and_conditions" class="mb-2">{{ translate('Terms_&_Conditions') }}</label>
                            <textarea id="terms_and_conditions" name="terms_and_conditions" class="form-control ckeditor" rows="10"
                                placeholder="{{ translate('Enter_terms_and_conditions') }}">{!! $terms !!}</textarea>
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
