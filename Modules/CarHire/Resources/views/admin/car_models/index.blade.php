@extends('adminmodule::layouts.master')

@section('title', 'Car Models')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb-4">
            <h2>Car Models</h2>
            <a href="{{ route('admin.car-models.create') }}" class="btn btn--primary">Add Model</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body p-3">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SNo.</th>
                            <th>Model</th>
                            <th>Brand</th>
                            <th>Car Type</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($models as $k => $model)
                            <tr>
                                <td>{{ $models->firstItem() + $k }}</td>
                                <td>{{ $model->name }}</td>
                                <td>{{ $model->brand->name }}</td>
                                <td>{{ $model->brand->carType->name }}</td>
                                <td>{{ $model->slug }}</td>

                                <td>
                                    <a href="{{ route('admin.car-models.status', $model->id) }}"
                                        class="badge bg-{{ $model->status ? 'success' : 'danger' }}">
                                        {{ $model->status ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>

                                <td>
                                    <div class="justify-content-center d-flex gap-2">
                                        <a href="{{ route('admin.car-models.edit', $model->id) }}"
                                            class="action-btn btn--light-primary demo_check" style="--size: 30px"><span
                                                class="material-icons">edit</span></a>

                                        <form method="POST" action="{{ route('admin.car-models.delete', $model->id) }}"
                                            style="display:inline-block">
                                            @csrf @method('DELETE')
                                            <button onclick="return confirm('Delete?')"
                                                class="action-btn btn--danger demo_check" style="--size: 30px"><span
                                                    class="material-symbols-outlined">delete</span></button>
                                        </form>

                                        <label class="switch">
                                            <input type="checkbox" class="status-toggle"
                                                data-url="{{ route('admin.car-models.status', $model->id) }}"
                                                {{ $model->status ? 'checked' : '' }}>
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

                {{ $models->links() }}

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
