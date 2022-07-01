<?php namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\LatestNews;
use App\Models\Gallery;
use App\Models\StaticPages;

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
}
