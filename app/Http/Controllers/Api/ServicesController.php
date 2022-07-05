<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Services;
use App\Models\Reviews;
use App\Http\Services\ServicesService;
use App\Transformers\ServicesTransformer;
use App\Transformers\ReviewsTransformer;
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

    /***********************************
     * fn to get all services/Treatments
     ***********************************/
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

    /*******************************
     * fn to create service/treatment
     ********************************/
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

            return $this->sendResponse(array(), 'Service created successfully.');

        }catch(\Throwable $th){
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
                return $this->sendResponse(array(), 'Service updated successfully.');
            }else{
                return $this->sendError('Please check service id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
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
                return $this->sendError('Validation Error.', $validator->errors());       
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
            $param = '';
            if(isset($request->search) || isset($request->column) || isset($request->order) || isset($request->rows)){
                $param = [
                    'search' => ($request->search)?$request->search:'',
                    'column' => ($request->column)?$request->column:'id',
                    'order' => ($request->order)?$request->order:'desc',
                    'rows' => ($request->rows)?$request->rows:'',
                ];
            }

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
                'image' => 'file|mimes:jpeg,png,jpg',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
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
                return $this->sendError('Validation Error.', $validator->errors());       
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
                return $this->sendError('Validation Error.', $validator->errors());       
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
}
