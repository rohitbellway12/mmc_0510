@extends('adminmodule::layouts.master')

@section('title', 'Edit Car Model')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb-4">
            <h2>Edit Car Model</h2>
            <a href="{{ route('admin.car-models.index') }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.car-models.update', $model->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Car Type</label>
                            <select id="car_type_select" class="form-control">
                                @foreach ($car_types as $type)
                                    <option value="{{ $type->id }}"
                                        {{ $model->brand->car_type_id == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Brand</label>
                            <select name="brand_id" id="brand_select" class="form-control" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Model Name</label>
                            <input name="name" type="text" class="form-control" value="{{ $model->name }}" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <input type="checkbox" name="status" {{ $model->status ? 'checked' : '' }}> Active
                        </div>

                    </div>

                    <button class="btn btn-success">Update</button>
                </form>

            </div>
        </div>

    </div>
@endsection


@push('script')
    <script>
        function loadBrands(typeId, selected = null) {
            $.get('{{ route('admin.ajax.brands.by_type', '') }}/' + typeId, function(data) {
                let html = '<option value="">Select Brand</option>';
                data.forEach(row => {
                    html +=
                        `<option value="${row.id}" ${selected == row.id ? 'selected':''}>${row.name}</option>`;
                });
                $('#brand_select').html(html);
            });
        }

        let typeId = $('#car_type_select').val();
        loadBrands(typeId, "{{ $model->brand_id }}");

        $('#car_type_select').on('change', function() {
            loadBrands($(this).val());
        });
    </script>
@endpush
