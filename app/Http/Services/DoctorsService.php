<?php namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\Doctors;

class DoctorsService
{

    public function get($param = ''){
        if($param != ''){
            $search = $param['search'];
            $column = $param['column'];
            $order  = $param['order'];
            $rows   = $param['rows'];

            $Doctors = Doctors::select('*');

            if($search != ''){
                $Doctors = $Doctors->where('first_name', 'like', '%' . $search . '%')
                ->orwhere('last_name', 'like', '%' . $search . '%')
                ->orwhere('email', 'like', '%' . $search . '%')
                ->orwhere('phone_number', 'like', '%' . $search . '%')
                ->orwhere('profession', 'like', '%' . $search . '%')
                ->orwhere('qualification', 'like', '%' . $search . '%');
            }

            $Doctors = $Doctors->orderBy('doctors.'.$column, $order);
            if($rows != ''){
                $Doctors = $Doctors->paginate($rows);
            }else{
                $Doctors = $Doctors->get();
            }

            return $Doctors;
        }else{
            return Doctors::where('status','1')->get();
        }
    }

}
