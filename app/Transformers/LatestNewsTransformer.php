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

        $api_url = env('API_URL');
        return [
            'id' => Helper::customCrypt($LatestNews->id),
			'title' => $LatestNews->name,
			'content' => $LatestNews->description,
			'date' => $LatestNews->alies_name,
			'image' => $api_url.$LatestNews->image_path.$LatestNews->image,
			'status' => $LatestNews->status
        ];
    }
}
