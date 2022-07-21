<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Services\DoctorsService;
use App\Transformers\DoctorsTransformer;
use Spatie\Fractal\Fractal;
use Validator;
use File;
use Helper;
use App\Models\Doctors;
use Illuminate\Support\Facades\Mail;

class DoctorsController extends BaseController
{
    public function __construct(DoctorsService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try{
            
            $param = [
                'search' => ($request->search)?$request->search:'',
                'column' => ($request->column)?$request->column:'id',
                'order' => ($request->order)?$request->order:'desc',
                'rows' => ($request->rows)?$request->rows:'',
            ];
            

            $data = $this->service->get($param);
            return fractal($data, new DoctorsTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|min:3|max:50',
                'last_name' => 'required|min:3|max:50',
                //'email' => 'required|email|unique:doctors,email',
                //'phone_number' => 'required|numeric|digits:10',
                'image' => 'file|mimes:jpeg,png,jpg',
                'profession' => 'required|min:3|max:50',
                'qualification' => 'required|min:2|max:50',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            $input = $request->all();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $path = 'uploaded/doctors_images/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-doctors-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                $input['image'] = $newname;
                $input['image_path'] = $path;

            }

            $Doctors = Doctors::create($input);

            return $this->sendResponse(array(), 'Doctor created successfully.');

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
            $Doctors = Doctors::find($id);

            return fractal($Doctors, new DoctorsTransformer());
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }


    public function update(Request $request, $id)
    {
        try{
            $id = Helper::customDecrypt($id);
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|min:3|max:50',
                'last_name' => 'required|min:3|max:50',
                //'email' => 'required|email|unique:doctors,email,'.$id,
                //'phone_number' => 'required|numeric|digits:10',
                'image' => 'file|mimes:jpeg,png,jpg',
                'profession' => 'required|min:3|max:50',
                'qualification' => 'required|min:2|max:50',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            $input = $request->all();

            $Doctors = Doctors::find($id);
            if(!empty($Doctors)){
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $path = 'uploaded/doctors_images/';
                    if(!File::exists($path)) {
                        File::makeDirectory($path, $mode = 0777, true, true);
                    }
                    $randm = rand(10,1000);
                    $newname = $randm.time().'-doctors-'.$filename.'.'.$file_ext;
                    $newname = str_replace(" ","_",$newname);
                    $destinationPath = public_path($path);
                    $file->move($destinationPath, $newname);
    
                    $input['image'] = $newname;
                    $input['image_path'] = $path;
    
                    if($Doctors->image != '' && $Doctors->image != null && $Doctors->image != 'null'){
                        File::delete($destinationPath.$Doctors->image);
                    }
                }

                $Doctors->update($input);
                return $this->sendResponse($Doctors, 'Doctor updated successfully.');
            }else{
                return $this->sendError('Please check id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function changeDoctorStatus(Request $request){
        try{
            $request['id'] = Helper::customDecrypt($request['id']);
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:doctors,id',
                'status' => 'required|in:0,1',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            Doctors::where('id',$request->id)->update(['status'=>$request->status]);
            return $this->sendResponse(array(), 'Doctor Status Changed successfully.');
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function getDoctorsFrontend($id = ''){
        try{

            if($id == ''){
                $param = '';
                $data = $this->service->get($param);
                
            }else{
                $id = Helper::customDecrypt($id);
                $data = Doctors::find($id);
            }

            return fractal($data, new DoctorsTransformer());
            
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
        
    }
}
