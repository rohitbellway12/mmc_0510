@extends('adminmodule::layouts.master')

@section('title', translate('Manage Global Category Questions'))

@push('css_or_js')
    <style>
        /* Form & Card Base Styles */
        .card {
            border-radius: 10px;
            border: 1px solid var(--border-color, #EFF1F4);
        }
        .form-label, label {
            color: var(--bs-dark, #18181a);
            font-weight: 500;
        }

        /* Table Light Mode */
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

        /* Badge Soft Overrides */
        .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.12) !important;
            color: #0dcaf0 !important;
            font-weight: 500;
        }
        .badge-soft-primary {
            background-color: rgba(65, 83, 179, 0.12) !important;
            color: #4153B3 !important;
            font-weight: 500;
        }
        .badge-soft-danger {
            background-color: rgba(220, 53, 69, 0.12) !important;
            color: #dc3545 !important;
            font-weight: 500;
        }
        .badge-soft-secondary {
            background-color: rgba(108, 117, 125, 0.12) !important;
            color: #6c757d !important;
            font-weight: 500;
        }

        /* Complete Dark Mode Overrides */
        [data-bs-theme="dark"],
        body[data-bs-theme="dark"] {
            --card-bg-dark: #232325;
            --input-bg-dark: #2c2d32;
            --border-dark: #3a3b40;
        }
        [data-bs-theme="dark"] .page-title,
        body[data-bs-theme="dark"] .page-title {
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .card,
        body[data-bs-theme="dark"] .card {
            background-color: #232325 !important;
            border-color: #3a3b40 !important;
        }
        [data-bs-theme="dark"] .card-header,
        body[data-bs-theme="dark"] .card-header {
            background-color: rgba(255, 255, 255, 0.03) !important;
            border-bottom-color: #3a3b40 !important;
        }
        [data-bs-theme="dark"] .card-title,
        body[data-bs-theme="dark"] .card-title {
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] label,
        body[data-bs-theme="dark"] label {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        body[data-bs-theme="dark"] .form-control,
        body[data-bs-theme="dark"] .form-select {
            background-color: #2c2d32 !important;
            border-color: #4a4b50 !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .form-control::placeholder,
        body[data-bs-theme="dark"] .form-control::placeholder {
            color: #888888 !important;
        }
        [data-bs-theme="dark"] .form-select option,
        body[data-bs-theme="dark"] .form-select option {
            background-color: #232325 !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .table,
        body[data-bs-theme="dark"] .table {
            --bs-table-color: rgba(255, 255, 255, 0.9) !important;
            --bs-table-bg: transparent !important;
            --bs-table-border-color: rgba(255, 255, 255, 0.08) !important;
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
        [data-bs-theme="dark"] .text-muted,
        body[data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .badge-soft-primary {
            background-color: rgba(65, 83, 179, 0.25) !important;
            color: #98a5f3 !important;
        }
        [data-bs-theme="dark"] .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.25) !important;
            color: #6edff6 !important;
        }
        [data-bs-theme="dark"] .badge-soft-secondary {
            background-color: rgba(148, 163, 184, 0.2) !important;
            color: #cbd5e1 !important;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('Global_Category_Questions') }}</h2>
                        <p class="text-muted">
                            {{ translate('These questions will be shown to all users booking services in the selected categories, regardless of the provider.') }}
                        </p>
                    </div>

                    <!-- Add New Question Card -->
                    <div class="card mb-30">
                        <div class="card-header py-3">
                            <h5 class="card-title d-flex align-items-center gap-2 mb-0">
                                <span class="material-icons text-primary">add_circle</span>
                                {{ translate('Add_New_Global_Question') }}
                            </h5>
                        </div>
                        <div class="card-body p-30">
                            <form id="question-form">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            <label class="form-label mb-2">{{ translate('Question_Text') }} <span class="text-danger">*</span></label>
                                            <textarea name="question_text" id="question_text" class="form-control" rows="2"
                                                placeholder="{{ translate('e.g., Please enter your tyre size') }}" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="form-label mb-2">{{ translate('Category') }} <span class="text-danger">*</span></label>
                                            <select name="category_id" id="category_id" class="form-control form-select" required>
                                                <option value="" disabled selected>{{ translate('Select_Category') }}</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="form-label mb-2">{{ translate('Question_Type') }} <span class="text-danger">*</span></label>
                                            <select name="question_type" id="question_type" class="form-control form-select" required>
                                                <option value="text">{{ translate('Text_Answer') }}</option>
                                                <option value="yes_no">{{ translate('Yes/No') }}</option>
                                                <option value="select">{{ translate('Select/Dropdown') }}</option>
                                                <option value="file">{{ translate('File/Image Upload') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="form-label mb-2 d-block">{{ translate('Required') }}?</label>
                                            <div class="pt-2">
                                                <label class="switcher">
                                                    <input type="checkbox" class="switcher_input" id="is_required" name="is_required" value="1" checked>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 d-none" id="options_section">
                                        <div class="form-group mb-0">
                                            <label class="form-label mb-2">{{ translate('Options') }} ({{ translate('Comma separated') }}) <span class="text-danger">*</span></label>
                                            <input type="text" name="options" id="options" class="form-control"
                                                placeholder="{{ translate('e.g., Option 1, Option 2, Option 3') }}">
                                        </div>
                                    </div>

                                    <div class="col-12 text-end mt-4">
                                        <button type="submit" class="btn btn--primary px-4">
                                            {{ translate('Save_Question') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Questions List Table -->
                    <div class="card mb-30">
                        <div class="card-header py-3">
                            <h5 class="card-title mb-0">{{ translate('Standardized_Category_Questions') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="align-middle title-color">
                                        <tr>
                                            <th class="title-color">{{ translate('SL') }}</th>
                                            <th class="title-color">{{ translate('Question') }}</th>
                                            <th class="title-color">{{ translate('Category') }}</th>
                                            <th class="title-color">{{ translate('Question_Type') }}</th>
                                            <th class="title-color">{{ translate('Required') }}</th>
                                            <th class="title-color">{{ translate('Status') }}</th>
                                            <th class="title-color text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($questions as $key => $question)
                                            <tr>
                                                <td class="title-color">{{ $questions->firstItem() + $key }}</td>
                                                <td>
                                                    <div class="fw-semibold title-color mb-1">{{ $question->question_text }}</div>
                                                    @if ($question->question_type == 'select' && $question->options)
                                                        <small class="text-muted d-block">
                                                            <strong>{{ translate('Options') }}:</strong> {{ $question->options }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-soft-info fs-12 px-2 py-1">
                                                        {{ $question->category->name ?? translate('General') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-soft-primary fs-12 px-2 py-1 d-inline-flex align-items-center gap-1">
                                                        <span class="material-icons fs-14">
                                                            @if ($question->question_type == 'yes_no')
                                                                check_circle
                                                            @elseif($question->question_type == 'select')
                                                                list
                                                            @elseif($question->question_type == 'file')
                                                                image
                                                            @else
                                                                text_fields
                                                            @endif
                                                        </span>
                                                        {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($question->is_required)
                                                        <span class="badge badge-soft-danger px-2 py-1">{{ translate('Required') }}</span>
                                                    @else
                                                        <span class="badge badge-soft-secondary px-2 py-1">{{ translate('Optional') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <label class="switcher">
                                                        <input type="checkbox" class="switcher_input status-toggle"
                                                            data-id="{{ $question->id }}"
                                                            {{ $question->is_active ? 'checked' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" class="action-btn btn--primary edit-question"
                                                            data-id="{{ $question->id }}"
                                                            data-text="{{ htmlspecialchars($question->question_text, ENT_QUOTES) }}"
                                                            data-category="{{ $question->category_id }}"
                                                            data-type="{{ $question->question_type }}"
                                                            data-options="{{ htmlspecialchars($question->options ?? '', ENT_QUOTES) }}"
                                                            data-required="{{ $question->is_required ? 1 : 0 }}"
                                                            style="--size: 30px" title="{{ translate('Edit') }}">
                                                            <span class="material-symbols-outlined">edit</span>
                                                        </button>
                                                        <button type="button" class="action-btn btn--danger delete-question"
                                                            data-id="{{ $question->id }}" style="--size: 30px" title="{{ translate('Delete') }}">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    {{ translate('No global questions added yet') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($questions->count() > 0)
                        <div class="d-flex justify-content-end mt-3">
                            {{ $questions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="editQuestionModalLabel">
                        <span class="material-icons text-primary">edit</span>
                        {{ translate('Edit_Global_Question') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-question-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_question_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label mb-2">{{ translate('Question_Text') }} <span class="text-danger">*</span></label>
                                    <textarea name="question_text" id="edit_question_text" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label mb-2">{{ translate('Category') }} <span class="text-danger">*</span></label>
                                    <select name="category_id" id="edit_category_id" class="form-control form-select" required>
                                        <option value="" disabled>{{ translate('Select_Category') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label mb-2">{{ translate('Question_Type') }} <span class="text-danger">*</span></label>
                                    <select name="question_type" id="edit_question_type" class="form-control form-select" required>
                                        <option value="select">{{ translate('Dropdown_/_Multiple_Choice') }}</option>
                                        <option value="text">{{ translate('Short_Text_Answer') }}</option>
                                        <option value="yes_no">{{ translate('Yes_/_No') }}</option>
                                        <option value="file">{{ translate('File_/_Image_Upload') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12" id="edit_options_section">
                                <div class="form-group mb-0">
                                    <label class="form-label mb-2">{{ translate('Options_(Comma_Separated)') }} <span class="text-danger">*</span></label>
                                    <textarea name="options" id="edit_options" class="form-control" rows="4"
                                        placeholder="{{ translate('e.g., 1 Wheel, 2 Wheels, 3 Wheels, 4 Wheels') }}"></textarea>
                                    <small class="text-muted">{{ translate('Enter options separated by comma') }}</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_required" id="edit_is_required" value="1">
                                    <label class="form-check-label" for="edit_is_required">
                                        <strong>{{ translate('Is_Required') }}</strong>
                                        <span class="text-muted d-block fs-12">{{ translate('Customer must answer this question to proceed with booking') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('Update_Question') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        // Show/hide options section based on type
        $('#question_type').on('change', function() {
            if ($(this).val() === 'select') {
                $('#options_section').removeClass('d-none');
                $('#options').attr('required', true);
            } else {
                $('#options_section').addClass('d-none');
                $('#options').attr('required', false);
            }
        });

        // Add new question
        $('#question-form').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            if (!$('#is_required').is(':checked')) {
                formData.set('is_required', '0');
            }

            $.ajax({
                url: '{{ route('admin.provider.category_questions.store') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    toastr.success('{{ translate('Global question added successfully') }}');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(key => {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error('{{ translate('Something went wrong') }}');
                    }
                }
            });
        });

        // Toggle status
        $('.status-toggle').on('change', function() {
            let questionId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{ route('admin.provider.category_questions.update_status') }}',
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: questionId,
                    status: status
                },
                success: function(response) {
                    toastr.success('{{ translate('Status updated successfully') }}');
                },
                error: function() {
                    toastr.error('{{ translate('Failed to update status') }}');
                    location.reload();
                }
            });
        });

        // Delete question
        $('.delete-question').on('click', function() {
            let questionId = $(this).data('id');

            Swal.fire({
                title: '{{ translate('Are you sure') }}?',
                text: "{{ translate('Standardized questions for this category will be removed') }}!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ translate('Yes, delete it') }}!',
                cancelButtonText: '{{ translate('Cancel') }}'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('admin.provider.category_questions.destroy', ':id') }}'
                            .replace(':id', questionId),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success('{{ translate('Question deleted successfully') }}');
                            location.reload();
                        },
                        error: function() {
                            toastr.error('{{ translate('Failed to delete question') }}');
                        }
                    });
                }
            });
        });

        // Open Edit Modal
        $(document).on('click', '.edit-question', function() {
            let id = $(this).data('id');
            let text = $(this).data('text');
            let category = $(this).data('category');
            let type = $(this).data('type');
            let options = $(this).data('options');
            let required = $(this).data('required');

            $('#edit_question_id').val(id);
            $('#edit_question_text').val(text);
            $('#edit_category_id').val(category);
            $('#edit_question_type').val(type);
            $('#edit_options').val(options || '');
            $('#edit_is_required').prop('checked', required == 1);

            if (type === 'select') {
                $('#edit_options_section').removeClass('d-none');
                $('#edit_options').attr('required', true);
            } else {
                $('#edit_options_section').addClass('d-none');
                $('#edit_options').attr('required', false);
            }

            $('#editQuestionModal').modal('show');
        });

        // Toggle edit options section on type change
        $('#edit_question_type').on('change', function() {
            if ($(this).val() === 'select') {
                $('#edit_options_section').removeClass('d-none');
                $('#edit_options').attr('required', true);
            } else {
                $('#edit_options_section').addClass('d-none');
                $('#edit_options').attr('required', false);
            }
        });

        // Submit Edit Form
        $('#edit-question-form').on('submit', function(e) {
            e.preventDefault();
            let questionId = $('#edit_question_id').val();
            let url = '{{ route('admin.provider.category_questions.update', ':id') }}'.replace(':id', questionId);

            let formData = {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                question_text: $('#edit_question_text').val(),
                category_id: $('#edit_category_id').val(),
                question_type: $('#edit_question_type').val(),
                options: $('#edit_options').val(),
                is_required: $('#edit_is_required').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    toastr.success('{{ translate('Global question updated successfully') }}');
                    $('#editQuestionModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(key => {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error('{{ translate('Something went wrong') }}');
                    }
                }
            });
        });
    </script>
@endpush
