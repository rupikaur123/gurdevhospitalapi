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
        return [
            'id' => Helper::customCrypt($services->id),
			'name' => $services->name,
			'description' => $services->description,
			'alies_name' => $services->alies_name,
			'image' => $services->image,
			'image_path' => $services->image_path,
			'status' => $services->status,
			'created_at' => $services->created_at,
			'updated_at' => $services->updated_at
        ];
    }
}
