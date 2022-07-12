<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Gallery;

class GalleryTransformer extends TransformerAbstract
{
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Gallery $Gallery)
    {
        $api_url = env('APP_URL');
        return [
            'id'                => $Gallery->id,
            'image'             => $api_url.'/'.$Gallery->image_path.$Gallery->image,
        ];
    }
}
