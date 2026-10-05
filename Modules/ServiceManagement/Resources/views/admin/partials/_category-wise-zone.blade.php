<tr>
    <th scope="col">{{ translate('variations') }}</th>
    <th scope="col" class="d-none">{{ translate('default_price') }}</th>
    @foreach ($zones as $zone)
        <th scope="col" class="d-none">{{ $zone->name }}</th>
    @endforeach
    <th scope="col">{{ translate('action') }}</th>
</tr>
