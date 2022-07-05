<?php namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\LatestNews;
use App\Models\Gallery;
use App\Models\StaticPages;
use App\Models\Appointments;

class CommonService
{

    public function get($param = ''){
        if($param != ''){
            $search = $param['search'];
            $column = $param['column'];
            $order  = $param['order'];
            $rows   = $param['rows'];

            $LatestNews = LatestNews::select('*');

            if($search != ''){
                $LatestNews = $LatestNews->where('title', 'like', '%' . $search . '%')
                ->orwhere('content', 'like', '%' . $search . '%');
            }

            $LatestNews = $LatestNews->orderBy('latest_news.'.$column, $order);
            if($rows != ''){
                $LatestNews = $LatestNews->paginate($rows);
            }else{
                $LatestNews = $LatestNews->get();
            }

            return $LatestNews;
        }else{
            return LatestNews::where('status','1')->get();
        }
    }

    public function getAppointments($param = ''){
        if($param != ''){
            $search = $param['search'];
            $column = $param['column'];
            $order  = $param['order'];
            $rows   = $param['rows'];
            $appointment_id   = $param['appointment_id'];

            $Appointments = Appointments::select('*');

            if($appointment_id != ''){
                $Appointments = $Appointments->where('id',$appointment_id);
            }

            if($search != ''){
                $Appointments = $Appointments->where('u_full_name', 'like', '%' . $search . '%')
                ->orwhere('u_email', 'like', '%' . $search . '%')
                ->orwhere('u_dob', 'like', '%' . $search . '%')
                ->orwhere('u_address', 'like', '%' . $search . '%')
                ->orwhere('u_phone_number', 'like', '%' . $search . '%');
            }

            $Appointments = $Appointments->orderBy('appointments.'.$column, $order);

            if($appointment_id != ''){
                $Appointments = $Appointments->first();
            }else{
                if($rows != ''){
                    $Appointments = $Appointments->paginate($rows);
                }else{
                    $Appointments = $Appointments->get();
                }
            }
            

            return $Appointments;
        }else{
            return Appointments::where('status','1')->get();
        }
    }
}
