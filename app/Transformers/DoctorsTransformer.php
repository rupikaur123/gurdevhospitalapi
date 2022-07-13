<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Doctors;
use Helper;

class DoctorsTransformer extends TransformerAbstract
{
    
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Doctors $Doctors)
    {
        $api_url = env('APP_URL');
        return [
            'id'                => Helper::customCrypt($Doctors->id),
            'first_name'      => $Doctors->first_name,
            'last_name'  => $Doctors->last_name,
           // 'email'       => $Doctors->email,
            'dob'           => $Doctors->dob,
            'image'             => $api_url.'/'.$Doctors->image_path.$Doctors->image,
            //'phone_number'         => '+91'.$Doctors->phone_number,
            'profession'    => $Doctors->profession,
            'qualification'           => $Doctors->qualification,
            'status'           => $Doctors->status,
        ];
    }
}
