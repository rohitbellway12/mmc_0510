@extends('providermanagement::layouts.master')

@section('title',translate('Manage Custom Questions'))

@push('css_or_js')
    <style>
        .question-card {
            transition: all 0.3s ease;
        }
        .question-card:hover {
            box-shadow: 0 0.125rem 0.5rem rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Custom_Questions_Management')}}</h2>
                    </div>

                    <!-- Add New Question Card -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="material-icons">add_circle</span>
                                {{translate('Add_New_Question')}}
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="question-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{translate('Question_Text')}} <span class="text-danger">*</span></label>
                                            <textarea name="question_text" id="question_text" class="form-control" rows="2" 
                                                      placeholder="e.g., Will you need the car shortly after repair?" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{translate('Question_Type')}} <span class="text-danger">*</span></label>
                                            <select name="question_type" id="question_type" class="form-control" required>
                                                <option value="yes_no">{{translate('Yes/No')}}</option>
                                                <option value="text">{{translate('Text_Answer')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{translate('Category')}} </label>
                                            <select name="category_id" id="category_id" class="form-control">
                                                <option value="">{{translate('All_Categories')}}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">{{translate('Leave empty for all categories')}}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>{{translate('Required')}}?</label>
                                            <div class="custom-control custom-checkbox mt-2">
                                                <input type="checkbox" class="custom-control-input" id="is_required" name="is_required" value="1" checked>
                                                <label class="custom-control-label" for="is_required"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn--primary btn-block">
                                                <span class="material-icons">add</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{translate('Your_Custom_Questions')}}</h5>
                        </div>
                        <div class="card-body">
                            @forelse($questions as $question)
                                <div class="question-card card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-5">
                                                <h6 class="mb-1">{{$question->question_text}}</h6>
                                                <div class="mt-2">
                                                    <span class="badge badge-soft-info">
                                                        @if($question->question_type == 'yes_no')
                                                            <span class="material-icons" style="font-size: 14px;">check_circle</span>
                                                            {{translate('Yes/No')}}
                                                        @else
                                                            <span class="material-icons" style="font-size: 14px;">text_fields</span>
                                                            {{translate('Text Answer')}}
                                                        @endif
                                                    </span>
                                                    @if($question->is_required)
                                                        <span class="badge badge-soft-danger">{{translate('Required')}}</span>
                                                    @else
                                                        <span class="badge badge-soft-secondary">{{translate('Optional')}}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <small class="text-muted d-block">{{translate('Category')}}</small>
                                                @if($question->category_id)
                                                    <span class="badge badge-light border" style="color: black !important;">{{$question->category->name ?? 'Category'}}</span>
                                                @else
                                                    <span class="badge badge-soft-success">{{translate('All Categories')}}</span>
                                                @endif
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <small class="text-muted d-block">{{translate('Display Order')}}</small>
                                                <span class="badge badge-primary">#{{$question->display_order}}</span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <small class="text-muted d-block">{{translate('Status')}}</small>
                                                <label class="switcher mx-auto">
                                                    <input type="checkbox" class="switcher_input status-toggle" 
                                                           data-id="{{$question->id}}" 
                                                           {{$question->is_active ? 'checked' : ''}}>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <button type="button" class="btn btn-sm btn-danger delete-question" data-id="{{$question->id}}">
                                                    <span class="material-icons">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <img src="{{asset('public/assets/admin-module/img/media/question.png')}}" alt="" class="mb-3" width="100">
                                    <p class="text-muted">{{translate('No custom questions added yet')}}</p>
                                    <p class="text-muted small">{{translate('Add questions that customers will answer during booking')}}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if($questions->count() > 0)
                        <div class="d-flex justify-content-end mt-3">
                            {{$questions->links()}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        // Add new question
        $('#question-form').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            if (!$('#is_required').is(':checked')) {
                formData.set('is_required', '0');
            }
            
            $.ajax({
                url: '{{route('provider.custom_questions.store')}}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    toastr.success('{{translate('Question added successfully')}}');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(key => {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error('{{translate('Something went wrong')}}');
                    }
                }
            });
        });

        // Toggle status
        $('.status-toggle').on('change', function() {
            let questionId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            
            $.ajax({
                url: '{{route('provider.custom_questions.update_status')}}',
                method: 'PUT',
                data: {
                    _token: '{{csrf_token()}}',
                    id: questionId,
                    status: status
                },
                success: function(response) {
                    toastr.success('{{translate('Status updated successfully')}}');
                },
                error: function() {
                    toastr.error('{{translate('Failed to update status')}}');
                    location.reload();
                }
            });
        });

        // Delete question
        $('.delete-question').on('click', function() {
            let questionId = $(this).data('id');
            
            Swal.fire({
                title: '{{translate('Are you sure')}}?',
                text: "{{translate('You will not be able to recover this question')}}!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{translate('Yes, delete it')}}!',
                cancelButtonText: '{{translate('Cancel')}}'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{route('provider.custom_questions.destroy', ':id')}}'.replace(':id', questionId),
                        method: 'DELETE',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function(response) {
                            toastr.success('{{translate('Question deleted successfully')}}');
                            location.reload();
                        },
                        error: function() {
                            toastr.error('{{translate('Failed to delete question')}}');
                        }
                    });
                }
            });
        });
    </script>
@endpush
