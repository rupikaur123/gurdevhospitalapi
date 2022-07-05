<?php namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\StaticPages;

class StaticPagesService
{

    public function get($param = ''){
        if($param != ''){
            $search = $param['search'];
            $column = $param['column'];
            $order  = $param['order'];
            $rows   = $param['rows'];

            $StaticPages = StaticPages::select('*');

            if($search != ''){
                $StaticPages = $StaticPages->where('title', 'like', '%' . $search . '%')
                ->orwhere('content', 'like', '%' . $search . '%');
            }

            $StaticPages = $StaticPages->orderBy('latest_news.'.$column, $order);
            if($rows != ''){
                $StaticPages = $StaticPages->paginate($rows);
            }else{
                $StaticPages = $StaticPages->get();
            }

            return $StaticPages;
        }else{
            return StaticPages::where('status','1')->get();
        }
    }

}
