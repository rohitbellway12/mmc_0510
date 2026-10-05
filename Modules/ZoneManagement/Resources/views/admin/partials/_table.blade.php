<div class="table-responsive">
    <table id="example" class="table align-middle">
        <thead class="align-middle title-color">
        <tr>
            <th class="title-color">{{translate('SL')}}</th>
            <th class="title-color">{{translate('zone_name')}}</th>
            <th class="title-color">{{translate('providers')}}</th>
            <th class="title-color">{{translate('Category')}}</th>
            @can('zone_manage_status')
                <th class="title-color">{{translate('status')}}</th>
            @endcan
            @canany(['zone_delete', 'zone_update'])
                <th class="title-color text-center">{{translate('action')}}</th>
            @endcan
        </tr>
        </thead>
        <tbody>
        @foreach($zones as $key=>$zone)
            <tr>
                <td class="title-color">{{$key+$zones->firstItem()}}</td>
                <td class="title-color fw-medium">{{$zone->name}}</td>
                <td class="title-color">{{$zone->providers_count}}</td>
                <td class="title-color">{{$zone->categories_count}}</td>
                @can('zone_manage_status')
                    <td>
                        <label class="switcher">
                            <input class="switcher_input status-update"
                                   data-id="{{$zone->id}}"
                                   type="checkbox" {{$zone->is_active?'checked':''}}>
                            <span class="switcher_control"></span>
                        </label>
                    </td>
                @endcan
                @canany(['zone_delete', 'zone_update'])
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            @can('zone_update')
                                <a href="{{route('admin.zone.edit',[$zone->id])}}"
                                   class="action-btn btn--light-primary demo_check"
                                   style="--size: 30px">
                                    <span class="material-icons">edit</span>
                                </a>
                            @endcan
                            @can('zone_delete')
                                <button type="button"
                                        data-id="delete-{{$zone->id}}"
                                        data-message="{{translate('want_to_delete_this_zone')}}?"
                                        class="action-btn btn--danger {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                        style="--size: 30px">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                                <form
                                    action="{{route('admin.zone.delete',[$zone->id])}}"
                                    method="post" id="delete-{{$zone->id}}"
                                    class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                @endcan
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    {!! $zones->links() !!}
</div>
