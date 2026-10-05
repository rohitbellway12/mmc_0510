@extends('adminmodule::layouts.master')

@section('title', 'Car Brands')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Car Brands</h2>
            <a href="{{ route('admin.car-brands.create') }}" class="btn btn--primary">Add Brand</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body p-3">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Brand</th>
                            <th>Car Type</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($brands as $k => $brand)
                            <tr>
                                <td>{{ $brands->firstItem() + $k }}</td>
                                <td>{{ $brand->name }}</td>
                                <td>{{ $brand->carType->name ?? '-' }}</td>
                                <td>{{ $brand->slug }}</td>
                                <td>
                                    @if ($brand->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>

                                    <div class="justify-content-center d-flex gap-2">

                                        <a href="{{ route('admin.car-brands.edit', $brand->id) }}"
                                            class="action-btn btn--light-primary demo_check" style="--size: 30px"><span
                                                class="material-icons">edit</span></a>

                                        {{-- <a href="{{ route('admin.car-brands.status', $brand->id) }}"
                                            class="btn btn-sm btn-warning">
                                            {{ $brand->status ? 'Deactivate' : 'Activate' }}
                                        </a> --}}

                                        <form action="{{ route('admin.car-brands.delete', $brand->id) }}" method="POST"
                                            style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Delete this brand?')"
                                                class="action-btn btn--danger demo_check" style="--size: 30px"><span
                                                    class="material-symbols-outlined">delete</span></button>
                                        </form>

                                        <label class="switch">
                                            <input type="checkbox" class="status-toggle"
                                                data-url="{{ route('admin.car-brands.status', $brand->id) }}"
                                                {{ $brand->status ? 'checked' : '' }}>
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

                {{ $brands->links() }}
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
