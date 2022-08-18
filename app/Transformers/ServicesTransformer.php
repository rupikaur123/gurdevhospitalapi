<?php namespace App\Transformers;

use App\Models\Services;
use App\Models\DocService;
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

        $doctors = array();
        $doctorslist = DocService::with('doctor_detail')->where('service_id',$services->id)->get();
        if(!empty($doctorslist)){
            foreach($doctorslist as $key=>$value){
                $doctors[$key]['first_name'] = $value['doctor_detail']['first_name'];
                $doctors[$key]['last_name'] = $value['doctor_detail']['last_name'];
                $doctors[$key]['email'] = $value['doctor_detail']['email'];
                $doctors[$key]['image'] = $api_url.'/'.$value['doctor_detail']['image_path'].$value['doctor_detail']['image'];
                $doctors[$key]['profession'] = $value['doctor_detail']['profession'];
                $doctors[$key]['qualification'] = $value['doctor_detail']['qualification'];
            }
        }

        return [
            'id'                => Helper::customCrypt($services->id),
			'name'              => $services->name,
			'description'       => $services->description,
			'alies_name'        => $services->alies_name,
			'image'             => $api_url.'/'.$services->image_path.$services->image,
			'banner_image'      => $api_url.'/'.$services->image_path.$services->banner_image,
			'status'            => $services->status,
			'meta_title'        => $services->meta_title,
			'meta_description'  => $services->meta_description,
			'meta_keyword'      => $services->meta_keyword,
			'doctors'           => $doctors,
        ];
    }
}
