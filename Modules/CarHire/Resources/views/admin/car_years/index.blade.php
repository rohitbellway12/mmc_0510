@extends('adminmodule::layouts.master')

@section('title', 'Car Years')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb-4">
            <h2>Car Years</h2>
            <a href="{{ route('admin.car-years.create') }}" class="btn btn--primary">Add Year</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body p-3">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S No.</th>
                            <th>Car Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($years as $k => $year)
                            <tr>
                                <td>{{ $years->firstItem() + $k }}</td>
                                {{-- <td>{{ $year->model->type?->name ?? 'N/A' }}</td>
                                <td>{{ $year->model->brand->type?->name ?? 'N/A' }}</td> --}}
                                <td>{{ $year->model->brand->carType->name }}</td>
                                <td>{{ $year->model->brand?->name ?? 'N/A' }}</td>
                                <td>{{ $year->model->name }}</td>
                                <td>{{ $year->year }}</td>
                                <td>
                                    <a href="{{ route('admin.car-years.status', $year->id) }}"
                                        class="badge bg-{{ $year->status ? 'success' : 'danger' }}">
                                        {{ $year->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>

                                <td>
                                    <div class="justify-content-center d-flex gap-2">
                                        <a href="{{ route('admin.car-years.edit', $year->id) }}"
                                            class="action-btn btn--light-primary demo_check" style="--size: 30px"><span
                                                class="material-icons">edit</span></a>

                                        {{-- <form method="POST" action="{{ route('admin.car-years.delete', $year->id) }}"
                                            style="display:inline-block">
                                            @csrf @method('DELETE')
                                            <button onclick="return confirm('Delete?')"
                                                class="action-btn btn--danger demo_check" style="--size: 30px"><span
                                                    class="material-symbols-outlined">delete</span></button>
                                        </form> --}}

                                        <form method="POST" action="{{ route('admin.car-years.delete', $year->id) }}"
                                            style="display:inline-block">
                                            @csrf @method('DELETE')
                                            <button onclick="return confirm('Delete Year?')"
                                                class="action-btn btn--danger demo_check" style="--size: 30px"><span
                                                    class="material-symbols-outlined">delete</span></button>
                                        </form>

                                        <label class="switch">
                                            <input type="checkbox" class="status-toggle"
                                                data-url="{{ route('admin.car-years.status', $year->id) }}"
                                                {{ $year->status ? 'checked' : '' }}>
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

                {{ $years->links() }}

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
