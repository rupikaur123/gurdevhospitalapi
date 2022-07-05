<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Appointments;
use Helper;

class AppointmentsTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Appointments $Appointments)
    {
        return [
            'id'                => Helper::customCrypt($Appointments->id),
            'service_id'        => Helper::customCrypt($Appointments->service_id),
            'service_name'      => $Appointments->service->name,
            'appointment_date'  => $Appointments->appointment_date,
            'u_full_name'       => $Appointments->u_full_name,
            'u_email'           => $Appointments->u_email,
            'u_dob'             => $Appointments->u_dob,
            'u_address'         => $Appointments->u_address,
            'u_phone_number'    => '+91'.$Appointments->u_phone_number,
            'comment'           => $Appointments->comment,
        ];
    }
}
