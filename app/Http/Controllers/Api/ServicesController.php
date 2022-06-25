<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Services;
use App\Http\Services\ServicesService;
use App\Transformers\ServicesTransformer;
use Spatie\Fractal\Fractal;
use Validator;
use File;
use Helper;

class ServicesController extends BaseController
{

    public function __construct(ServicesService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try{
            $param = '';
            if(isset($request->search) || isset($request->column) || isset($request->order) || isset($request->rows)){
                $param = [
                    'search' => ($request->search)?$request->search:'',
                    'column' => ($request->column)?$request->column:'id',
                    'order' => ($request->order)?$request->order:'desc',
                    'rows' => ($request->rows)?$request->rows:'',
                ];
            }

            $data = $this->service->get($param);
            return fractal($data, new ServicesTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:5|max:50',
                'description' => 'required|min:10|max:500',
                'alies_name' => 'required|unique:services,alies_name',
                'image' => 'file|mimes:jpeg,png,jpg',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            $input = $request->all();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $path = 'uploaded/services_images/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-serviceimg-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                $input['image'] = $newname;
                $input['image_path'] = $path;

            }

            $Services = Services::create($input);

            return $this->sendResponse($Services, 'Services created successfully.');

        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /**
     * @param $id
     *
     * @return Fractal
     */
    public function edit($id)
    {

        try{
            $id = Helper::customDecrypt($id);
            $Services = Services::find($id);

            return fractal($Services, new ServicesTransformer());
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }


    public function update(Request $request, $id)
    {
        try{
            $id = Helper::customDecrypt($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'description' => 'required',
                'alies_name' => 'required|unique:services,alies_name,'.$id,
                'image' => 'file|mimes:jpeg,png,jpg',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            $input = $request->all();

            $get_service = Services::find($id);
            if(!empty($get_service)){
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $path = 'uploaded/services_images/';
                    if(!File::exists($path)) {
                        File::makeDirectory($path, $mode = 0777, true, true);
                    }
                    $randm = rand(10,1000);
                    $newname = $randm.time().'-serviceimg-'.$filename.'.'.$file_ext;
                    $newname = str_replace(" ","_",$newname);
                    $destinationPath = public_path($path);
                    $file->move($destinationPath, $newname);
    
                    $input['image'] = $newname;
                    $input['image_path'] = $path;
    
                    if($get_service->image != '' && $get_service->image != null && $get_service->image != 'null'){
                        File::delete($destinationPath.$get_service->image);
                    }
                }

                $get_service->update($input);
                return $this->sendResponse($get_service, 'Services updated successfully.');
            }else{
                return $this->sendError('Please check service id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function changeServiceStatus(Request $request){
        try{
            $request['id'] = Helper::customDecrypt($request['id']);
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:services,id',
                'status' => 'required|in:0,1',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            Services::where('id',$request->id)->update(['status'=>$request->status]);
            return $this->sendResponse(array(), 'Services Status Changed successfully.');
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
}
