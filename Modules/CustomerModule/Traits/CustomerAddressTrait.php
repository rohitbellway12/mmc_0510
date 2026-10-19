<?php

namespace Modules\CustomerModule\Traits;

use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;

trait CustomerAddressTrait
{
    public function add_address($service_address, $user_id, $is_guest = 0, $service_location = 'customer')
    {
        $zone_id = null;

        if ($service_location == 'customer' && !empty($service_address->lat) && !empty($service_address->lon)){
            $point = new Point($service_address->lat, $service_address->lon);

            $zone = Zone::whereContains('coordinates', $point)->ofStatus(1)->latest()->first();
            if ($zone) {
                $zone_id = $zone->id;
            }
        }

        $address = new UserAddress;
        $address->user_id = $user_id;
        $address->lat = $service_address->lat ?? null;
        $address->lon = $service_address->lon ?? null;
        $address->city = $service_address->city ?? null;
        $address->street = $service_address->street ?? null;
        $address->zip_code = $service_address->zip_code ?? null;
        $address->country = $service_address->country ?? null;
        $address->address = $service_address->address ?? null;
        $address->zone_id = $zone_id;
        $address->address_type = $service_address->address_type ?? 'service';
        $address->contact_person_name = $service_address->contact_person_name ?? null;
        $address->contact_person_number = $service_address->contact_person_number ?? null;
        $address->address_label = $service_address->address_label ?? null;
        $address->house = $service_address->house ?? null;
        $address->floor = $service_address->floor ?? null;
        $address->is_guest = $is_guest;
        $address->save();

        return $address->id ?? null;
    }

}
