<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\StaticPages;
use Helper;

class StaticPagesTransformer extends TransformerAbstract
{
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(StaticPages $StaticPages)
    {
        $api_url = env('APP_URL');
        return [
            'id'                => Helper::customCrypt($StaticPages->id),
			'title'             => $StaticPages->title,
			'content'           => $StaticPages->content,
			'image'             => $api_url.'/'.$StaticPages->image_path.$StaticPages->image,
			'status'            => $StaticPages->status,
            'meta_title'        => $StaticPages->meta_title,
			'meta_description'  => $StaticPages->meta_description,
			'meta_keyword'      => $StaticPages->meta_keyword,
        ];
    }
}
