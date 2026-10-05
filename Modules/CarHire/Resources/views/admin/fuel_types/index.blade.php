@extends('adminmodule::layouts.master')

@section('title', 'Fuel Types')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb-3">
            <h4>Fuel Types</h4>
            <a href="{{ route('admin.fuel_types.create') }}" class="btn btn--primary">Add Fuel Type</a>
        </div>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fuelTypes as $key => $fuel)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $fuel->name }}</td>
                                <td>
                                    <a href="{{ route('admin.fuel_types.status', $fuel->id) }}"
                                        class="badge bg-{{ $fuel->status ? 'success' : 'danger' }}">
                                        {{ $fuel->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                {{-- <td>
                                    <a href="{{ route('admin.fuel_types.edit', $fuel->id) }}"
                                        class="btn btn-sm btn-warning">Edit</a>

                                    <a href="{{ route('admin.fuel_types.delete', $fuel->id) }}"
                                        onclick="return confirm('Delete this fuel type?')"
                                        class="btn btn-sm btn-danger">Delete</a>
                                </td> --}}

                                <td>
                                    <div class="justify-content-center d-flex gap-2">
                                        <a href="{{ route('admin.fuel_types.edit', $fuel->id) }}"
                                            class="action-btn btn--light-primary demo_check" style="--size: 30px"><span
                                                class="material-icons">edit</span></a>

                                        <form method="POST" action="{{ route('admin.fuel_types.delete', $fuel->id) }}"
                                            style="display:inline-block">
                                            @csrf @method('DELETE')
                                            <button onclick="return confirm('Delete this fuel type?')"
                                                class="action-btn btn--danger demo_check" style="--size: 30px"><span
                                                    class="material-symbols-outlined">delete</span></button>
                                        </form>

                                        <label class="switch">
                                            <input type="checkbox" class="status-toggle"
                                                data-url="{{ route('admin.fuel_types.status', $fuel->id) }}"
                                                {{ $fuel->status ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>

                                    </div>

                                    <style>
                                        .switch {
                                            position: relative;
                                            display: inline-block;
                                            width: 48px;
                                            height: 24px;
                                        }

                                        .switch input {
                                            display: none;
                                        }

                                        .slider {
                                            position: absolute;
                                            cursor: pointer;
                                            top: 0;
                                            left: 0;
                                            right: 0;
                                            bottom: 0;
                                            background-color: #ccc;
                                            transition: .4s;
                                            border-radius: 24px;
                                        }

                                        .slider:before {
                                            position: absolute;
                                            content: "";
                                            height: 18px;
                                            width: 18px;
                                            left: 3px;
                                            bottom: 3px;
                                            background-color: white;
                                            transition: .4s;
                                            border-radius: 50%;
                                        }

                                        input:checked+.slider {
                                            background-color: #FAD293;
                                        }

                                        input:checked+.slider:before {
                                            transform: translateX(24px);
                                        }
                                    </style>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function() {

        document.querySelectorAll(".status-toggle").forEach(function(toggle) {

            toggle.addEventListener("change", function() {
                let url = this.dataset.url;

                // Debug Check (remove later)
                console.log("Toggle URL:", url);

                if (url) {
                    window.location.href = url;
                }
            });

        });
    });
</script>
