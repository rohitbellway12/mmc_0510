@extends('adminmodule::layouts.master')

@section('title', 'Car Hire - Car List')

@section('content')
    <div class="container-fluid  ">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Car List</h2>

            <a href="{{ route('admin.carhire.create') }}" class="btn btn--primary">
                + Add New Car
            </a>
        </div>



        <div class="card">
            <div class="card-body table-responsive">

                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>S No.</th>
                            <th>Car Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Feature</th>
                            <th>Fuel</th>
                            <th>Transmission</th>
                            <th>Rent/Day</th>
                            <th>Images</th>
                            {{-- <th>Status</th> --}}
                            <th width="120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cars as $key => $car)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $car->type?->name }}</td>
                                {{-- <td>{{ $car->brand?->name }}</td>
                                <td>{{ $car->model?->name }}</td> --}}
                                <td>{{ $car->brand?->name ?? 'N/A' }}</td>
                                <td>{{ $car->model?->name ?? 'N/A' }}</td>
                                <td>{{ $car->year?->year ?? 'N/A' }}</td>
                                <td>{{ $car->feature?->name ?? 'N/A' }}</td>
                                {{-- <td>{{ $car->year }}</td> --}}
                                {{-- <td>{{ $car->fuel_type }}</td> --}}
                                <td>{{ $car->fuel_type?->name ?? 'N/A' }}</td>
                                {{-- <td>{{ $car->transmission }}</td> --}}
                                <td>{{ $car->transmission?->name ?? 'N/A' }}</td>
                                <td>₹{{ number_format($car->daily_rent, 2) }}</td>
                                {{-- <td>{{ $car->images->count() }}</td> --}}
                                <td>{{ count($car->images ?? []) }}</td>

                                {{-- <td>
                                    @if ($car->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td> --}}

                                {{-- <td>
                                    <a href="#" class="btn btn-sm btn-info">Edit</a>

                                    <form action="#" method="POST" onsubmit="return confirm('Delete this car?')"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td> --}}


                                <td>
                                    <div class="d-flex gap-2">

                                        <a href="{{ route('admin.carhire.edit', $car->id) }}"
                                            class="action-btn btn--light-primary demo_check" style="--size: 30px">
                                            <span class="material-icons">edit</span>
                                        </a>


                                        <a href="{{ route('admin.carhire.show', $car->id) }}"
                                            class="action-btn btn--light-primary demo_check" style="--size: 30px">
                                            <span class="material-icons">visibility</span>
                                        </a>

                                        <button type="button" data-id="delete-{{ $car->id }}"
                                            data-message="{{ translate('want_to_delete_this_car') }}?"
                                            class="action-btn btn--danger {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                            style="--size: 30px">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                        <form action="{{ route('admin.carhire.delete', $car->id) }}" method="post"
                                            id="delete-{{ $car->id }}" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No Cars Found</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

                <div class="mt-3">
                    {{ $cars->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
