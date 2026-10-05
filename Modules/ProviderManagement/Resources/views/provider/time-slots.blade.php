@extends('providermanagement::layouts.master')

@section('title',translate('Manage Time Slots'))

@push('css_or_js')
    <style>
        .badge-day {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Time_Slots_Management')}}</h2>
                    </div>

                    <!-- Add New Slot Card -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="material-icons">add_circle</span>
                                {{translate('Add_New_Time_Slot')}}
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="slot-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{translate('Day_of_Week')}} <span class="text-danger">*</span></label>
                                            <select name="day_of_week" id="day_of_week" class="form-control" required>
                                                <option value="">{{translate('Select_Day')}}</option>
                                                <option value="monday">{{translate('Monday')}}</option>
                                                <option value="tuesday">{{translate('Tuesday')}}</option>
                                                <option value="wednesday">{{translate('Wednesday')}}</option>
                                                <option value="thursday">{{translate('Thursday')}}</option>
                                                <option value="friday">{{translate('Friday')}}</option>
                                                <option value="saturday">{{translate('Saturday')}}</option>
                                                <option value="sunday">{{translate('Sunday')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{translate('Start_Time')}} <span class="text-danger">*</span></label>
                                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{translate('End_Time')}} <span class="text-danger">*</span></label>
                                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{translate('Max_Bookings')}} <span class="text-danger">*</span></label>
                                            <input type="number" name="max_bookings" id="max_bookings" class="form-control" min="1" value="1" required>
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

                    <!-- Time Slots List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{translate('Your_Time_Slots')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless align-middle">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{translate('Day')}}</th>
                                            <th>{{translate('Start_Time')}}</th>
                                            <th>{{translate('End_Time')}}</th>
                                            <th>{{translate('Max_Bookings')}}</th>
                                            <th>{{translate('Status')}}</th>
                                            <th class="text-center">{{translate('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($timeSlots as $slot)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-info">{{ucfirst($slot->day_of_week ?? 'N/A')}}</span>
                                                </td>
                                                <td>{{date('h:i A', strtotime($slot->start_time))}}</td>
                                                <td>{{date('h:i A', strtotime($slot->end_time))}}</td>
                                                <td>
                                                    <span class="badge badge-info">{{$slot->max_bookings}} {{translate('bookings')}}</span>
                                                </td>
                                                <td>
                                                    <label class="switcher">
                                                        <input type="checkbox" class="switcher_input status-toggle" 
                                                               data-id="{{$slot->id}}" 
                                                               {{$slot->is_active ? 'checked' : ''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-slot" data-id="{{$slot->id}}">
                                                        <span class="material-icons">delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    <p class="text-muted my-3">{{translate('No_time_slots_added_yet')}}</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{$timeSlots->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        // Add new slot
        $('#slot-form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '{{route('provider.time_slots.store')}}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    toastr.success('{{translate('Time slot added successfully')}}');
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
            let slotId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            
            $.ajax({
                url: '{{route('provider.time_slots.update_status')}}',
                method: 'PUT',
                data: {
                    _token: '{{csrf_token()}}',
                    id: slotId,
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

        // Delete slot
        $('.delete-slot').on('click', function() {
            let slotId = $(this).data('id');
            
            Swal.fire({
                title: '{{translate('Are you sure')}}?',
                text: "{{translate('You will not be able to recover this time slot')}}!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{translate('Yes, delete it')}}!',
                cancelButtonText: '{{translate('Cancel')}}'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{route('provider.time_slots.destroy', ':id')}}'.replace(':id', slotId),
                        method: 'DELETE',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function(response) {
                            toastr.success('{{translate('Time slot deleted successfully')}}');
                            location.reload();
                        },
                        error: function() {
                            toastr.error('{{translate('Failed to delete time slot')}}');
                        }
                    });
                }
            });
        });
    </script>
@endpush
