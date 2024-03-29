<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Services;
use App\Models\Reviews;
use App\Http\Services\ServicesService;
use App\Transformers\ServicesTransformer;
use App\Transformers\ReviewsTransformer;
use Spatie\Fractal\Fractal;
use App\Models\Gallery;
use App\Models\Appointments;
use App\Models\LatestNews;
use App\Models\DocService;
use Validator;
use File;
use Helper;
use DB;

class ServicesController extends BaseController
{

    public function __construct(ServicesService $service)
    {
        $this->service = $service;
    }

    /***********************************
     * fn to get all services/Treatments
     ***********************************/
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
            return fractal($data, new ServicesTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /*******************************
     * fn to create service/treatment
     ********************************/
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:services,name',
                'description' => 'required',
                //'alies_name' => 'required|unique:services,alies_name',
                'image' => 'file|mimes:jpeg,png,jpg',
                'banner_image' => 'file|mimes:jpeg,png,jpg',
               // 'meta_title' => 'required',
                //'meta_description' => 'required',
                //'meta_keyword' => 'required',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }
            
            $input = $request->all();

            $name = $input['name'];
            
            $string = str_replace(' ', '-', $name); // Replaces all spaces with hyphens.

            $alies_name = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
            
            $check_alies = Services::where('alies_name',$alies_name)->first();
           
            if(!empty($check_alies)){
                return $this->sendError('Please try with different service name', $validator->errors());   
            }

            $input['alies_name'] = $alies_name;

            if(!isset($input['meta_title']) || $input['meta_title'] == ''){
                $input['meta_title'] = $input['name'];
            }

            if(!isset($input['meta_description']) || $input['meta_description'] == ''){
                $input['meta_description'] = substr($input['description'], 0, 200);
            }

            if(!isset($input['meta_keyword']) || $input['meta_keyword'] == ''){
                $input['meta_keyword'] = $input['name'].' Hospital in Punjab';
            }

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

            if ($request->hasFile('banner_image')) {
                $file = $request->file('banner_image');
                
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $path = 'uploaded/services_images/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-servicebimg-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                $input['banner_image'] = $newname;
                $input['image_path'] = $path;

            }


            $Services = Services::create($input);
            
            if(isset($request['doctors']) && $request['doctors'] != ''){
                $all_doctors = explode(',',$request['doctors']);
                $service_id = $Services->id;
                $doc_service['service_id'] = $service_id;
                if(!empty($all_doctors)){
                    foreach($all_doctors as $key=>$value){
                        $doctor_id = Helper::customDecrypt($value);
                        $doc_service['doctor_id'] = $doctor_id;
                        DocService::create($doc_service);
                    }
                }
            }
            
            DB::commit();
            return $this->sendResponse(array(), 'Service created successfully.');

        }catch(\Throwable $th){
            DB::rollback();
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /**
     * @param $id
     *to edit service
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

    /********************
     * to update service
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try{
            $id = Helper::customDecrypt($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:services,name,'.$id,
                'description' => 'required',
                //'alies_name' => 'required|unique:services,alies_name,'.$id,
                'image' => 'file|mimes:jpeg,png,jpg',
                'banner_image' => 'file|mimes:jpeg,png,jpg',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            $input = $request->all();

            $get_service = Services::find($id);
            if(!empty($get_service)){

                $name = $input['name'];
            
                $string = str_replace(' ', '-', $name); // Replaces all spaces with hyphens.

                $alies_name = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
                
                $check_alies = Services::where('alies_name',$alies_name)->where('id','!=',$id)->first();
            
                if(!empty($check_alies)){
                    return $this->sendError('Please try with different service name', $validator->errors());   
                }

                $input['alies_name'] = $alies_name;

                if(!isset($input['meta_title']) || $input['meta_title'] == ''){
                    $input['meta_title'] = $input['name'];
                }

                if(!isset($input['meta_description']) || $input['meta_description'] == ''){
                    $input['meta_description'] = substr($input['description'], 0, 200);
                }

                if(!isset($input['meta_keyword']) || $input['meta_keyword'] == ''){
                    $input['meta_keyword'] = $input['name'].' Hospital in Punjab';
                }


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

                if ($request->hasFile('banner_image')) {
                    $file = $request->file('banner_image');
                    
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $path = 'uploaded/services_images/';
                    if(!File::exists($path)) {
                        File::makeDirectory($path, $mode = 0777, true, true);
                    }
                    $randm = rand(10,1000);
                    $newname = $randm.time().'-servicebimg-'.$filename.'.'.$file_ext;
                    $newname = str_replace(" ","_",$newname);
                    $destinationPath = public_path($path);
                    $file->move($destinationPath, $newname);
    
                    $input['banner_image'] = $newname;
                    $input['image_path'] = $path;
    
                    if($get_service->banner_image != '' && $get_service->banner_image != null && $get_service->banner_image != 'null'){
                        File::delete($destinationPath.$get_service->banner_image);
                    }
                }

                $get_service->update($input);
                DocService::where('service_id',$id)->delete();
                if(isset($request['doctors']) && $request['doctors'] != ''){
                    $all_doctors = explode(',',$request['doctors']);
                    $service_id = $id;
                    $doc_service['service_id'] = $service_id;
                    if(!empty($all_doctors)){
                        foreach($all_doctors as $key=>$value){
                            $doctor_id = Helper::customDecrypt($value);
                            $doc_service['doctor_id'] = $doctor_id;
                            DocService::create($doc_service);
                        }
                    }
                }
                
                DB::commit();
                

                return $this->sendResponse(array(), 'Service updated successfully.');
            }else{
                DB::rollback();
                return $this->sendError('Please check service id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
            DB::rollback();
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /*******************
     * to change status
     * of service to hide
     * from frontend but keep
     * in the record
     */
    public function changeServiceStatus(Request $request){
        try{
            $request['id'] = Helper::customDecrypt($request['id']);
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:services,id',
                'status' => 'required|in:0,1',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            Services::where('id',$request->id)->update(['status'=>$request->status]);
            return $this->sendResponse(array(), 'Service Status Changed successfully.');
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /***************************
     * get services/treatments
     * for frontend
     ************************/
    public function getServicesList($id = ''){
        try{
            
            if($id == ''){
                $param = '';
                $data = $this->service->get($param);
                
            }else{
                $id = Helper::customDecrypt($id);
                $data = Services::find($id);
            }

            return fractal($data, new ServicesTransformer());
            
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
        
    }

    /***********************
     * Api to get all reviews
     * Dashboard
     *************/
    public function getReviewsDashboard(Request $request)
    {
        try{
            
            $param = [
                'search' => ($request->search)?$request->search:'',
                'column' => ($request->column)?$request->column:'id',
                'order' => ($request->order)?$request->order:'desc',
                'rows' => ($request->rows)?$request->rows:'',
            ];
            

            $data = $this->service->getReviews($param);
            return fractal($data, new ReviewsTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
    /************************
     * fn to create any review
     */
    public function saveReviewsDashboard(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'review' => 'required|min:10|max:500',
                'image' => 'file|mimes:jpeg,png,jpg,webp',
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
                $path = 'uploaded/review_images/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-reviewimg-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                $input['image'] = $newname;
                $input['image_path'] = $path;

            }
            
            $Reviews = Reviews::create($input);

            return $this->sendResponse(array(), 'Review created successfully.');

        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /**
     * @param $id
     *
     * @return Fractal
     */
    public function getReviewsDetail($id)
    {

        try{
            $id = Helper::customDecrypt($id);
            $Reviews = Reviews::find($id);

            return fractal($Reviews, new ReviewsTransformer());
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /*************************
     * to update review dashboard
     */
    public function updateReviewsDashboard(Request $request, $review_id = '')
    {
        try{
            $id = Helper::customDecrypt($review_id);
            $validator = Validator::make($request->all(), [
                'review' => 'required',
                'image' => 'file|mimes:jpeg,png,jpg,webp',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            $input = $request->all();

            $get_review = Reviews::find($id);
            if(!empty($get_review)){
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $path = 'uploaded/review_images/';
                    if(!File::exists($path)) {
                        File::makeDirectory($path, $mode = 0777, true, true);
                    }
                    $randm = rand(10,1000);
                    $newname = $randm.time().'-reviewimg-'.$filename.'.'.$file_ext;
                    $newname = str_replace(" ","_",$newname);
                    $destinationPath = public_path($path);
                    $file->move($destinationPath, $newname);
    
                    $input['image'] = $newname;
                    $input['image_path'] = $path;
    
                    if($get_review->image != '' && $get_review->image != null && $get_review->image != 'null'){
                        File::delete($destinationPath.$get_review->image);
                    }
                }

                $get_review->update($input);
                return $this->sendResponse(array(), 'Review updated successfully.');
            }else{
                return $this->sendError('Please check review id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
    /***************************
     * to change review status
     */
    public function changeReviewStatus(Request $request){
        try{
            $request['id'] = Helper::customDecrypt($request['id']);
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:reviews,id',
                'status' => 'required|in:0,1',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            Reviews::where('id',$request->id)->update(['status'=>$request->status]);
            return $this->sendResponse(array(), 'Review Status Changed successfully.');
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
    /**************************
     * delete review dashboard
     */
    public function deleteReview(Request $request){
        try{
            $request['id'] = Helper::customDecrypt($request['id']);
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:reviews,id',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            Reviews::where('id',$request->id)->delete();
            return $this->sendResponse(array(), 'Review deleted successfully.');
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /*********************
     * api to get reviews
     * for frontend
     */
    public function getReviewsFrontend(){
        try{
            $param = '';
            $data = $this->service->getReviews($param);
            return fractal($data, new ReviewsTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /*****************************
     * get data to show dashboard
     */
    public function getDashboardData(){
        try{
            
            $data = array();
            $today_date = date('d-m-Y');
            $data['total_services'] = Services::count();
            $data['total_active_services'] = Services::where('status','1')->count();
            $data['total_appointments'] = Appointments::count();
            $data['total_todays_appointments'] = Appointments::where('appointment_date',$today_date)->count();
            $data['total_gallery_images'] = Gallery::count();
            $data['total_active_gallery_images'] = Gallery::where('status','1')->count();
            $data['latest_news'] = LatestNews::where('status','1')->get();
            return $this->sendResponse($data, 'Dashboard Data Get successfully.');
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
}
