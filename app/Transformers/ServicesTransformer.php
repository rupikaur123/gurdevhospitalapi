<?php namespace App\Transformers;

use App\Models\Services;
use League\Fractal\TransformerAbstract;
use Helper;

class ServicesTransformer extends TransformerAbstract
{
    /**
     * Turn Services object into custom array.
     *
     * @param Services $services
     * @return array
     */
    public function transform(Services $services)
    {
        $api_url = env('APP_URL');
        return [
            'id' => Helper::customCrypt($services->id),
			'name' => $services->name,
			'description' => strip_tags($services->description),
			'alies_name' => $services->alies_name,
			'image' => $api_url.'/'.$services->image_path.$services->image,
			'banner_image' => $api_url.'/'.$services->image_path.$services->banner_image,
			'status' => $services->status
        ];
    }
}
