@extends('adminmodule::layouts.master')


@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Car Features</h4>
            <a href="{{ route('admin.car_features.create') }}" class="btn btn--primary">Add Feature</a>
        </div>


        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>S.no.</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($features as $feature)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $feature->name }}</td>
                        {{-- <td>
                            <a class="badge bg-{{ $feature->status ? 'success' : 'danger' }}">
                                {{ $feature->status ? 'Active' : 'Inactive' }}
                            </a>
                        </td> --}}

                        <td>
                            <a href="{{ route('admin.car_features.status', $feature->id) }}"
                                class="badge bg-{{ $feature->status ? 'success' : 'danger' }}">
                                {{ $feature->status ? 'Active' : 'Inactive' }}
                            </a>
                        </td>

                        {{-- <td>
                            <a href="{{ route('admin.car_features.edit', $feature->id) }}"
                                class="btn btn-sm btn-info">Edit</a>


                            <a href="{{ route('admin.car_features.toggle', $feature->id) }}" class="btn btn-sm btn-warning">
                                Toggle
                            </a>


                            <form action="{{ route('admin.car_features.delete', $feature->id) }}" method="POST"
                                style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td> --}}

                        <td>
                            <div class="justify-content-center d-flex gap-2">
                                <a href="{{ route('admin.car_features.edit', $feature->id) }}"
                                    class="action-btn btn--light-primary demo_check" style="--size: 30px"><span
                                        class="material-icons">edit</span></a>

                                <form method="POST" action="{{ route('admin.car_features.delete', $feature->id) }}"
                                    style="display:inline-block">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete?')" class="action-btn btn--danger demo_check"
                                        style="--size: 30px"><span class="material-symbols-outlined">delete</span></button>
                                </form>

                                <label class="switch">
                                    <input type="checkbox" class="status-toggle"
                                        data-url="{{ route('admin.car_features.status', $feature->id) }}"
                                        {{ $feature->status ? 'checked' : '' }}>
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
                @endforeach
            </tbody>
        </table>
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
