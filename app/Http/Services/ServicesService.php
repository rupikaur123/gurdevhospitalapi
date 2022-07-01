<?php namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\Services;

class ServicesService
{

    public function get($param = ''){
        if($param != ''){
            $search = $param['search'];
            $column = $param['column'];
            $order  = $param['order'];
            $rows   = $param['rows'];

            $services = Services::select('*');

            if($search != ''){
                $services = $services->where('name', 'like', '%' . $search . '%')
                ->orwhere('description', 'like', '%' . $search . '%')
                ->orwhere('alies_name', 'like', '%' . $search . '%');
            }

            $services = $services->orderBy('services.'.$column, $order);
            if($rows != ''){
                $services = $services->paginate($rows);
            }else{
                $services = $services->get();
            }

            return $services;
        }else{
            return Services::where('status','1')->get();
        }
    }
}
