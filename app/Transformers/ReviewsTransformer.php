<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Reviews;
use Helper;

class ReviewsTransformer extends TransformerAbstract
{
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Reviews $Reviews)
    {
        $api_url = env('APP_URL');
        return [
            'id' => Helper::customCrypt($Reviews->id),
			'review' => strip_tags($Reviews->review),
			'image' => $api_url.'/'.$Reviews->image_path.$Reviews->image,
			'status' => $Reviews->status
        ];
    }
}
