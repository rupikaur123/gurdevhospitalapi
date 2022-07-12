<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\LatestNews;
use Helper;


class LatestNewsTransformer extends TransformerAbstract
{
    
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(LatestNews $LatestNews)
    {

        $api_url = env('APP_URL');
        return [
            'id' => Helper::customCrypt($LatestNews->id),
			'title' => $LatestNews->title,
			'content' => $LatestNews->content,
			'date' => $LatestNews->date,
			'image' => $api_url.$LatestNews->image_path.$LatestNews->image,
			'status' => $LatestNews->status
        ];
    }
}
