<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Services\CommonService;
use App\Transformers\LatestNewsTransformer;
use Spatie\Fractal\Fractal;
use Validator;
use File;
use Helper;
use App\Models\LatestNews;
use App\Models\Gallery;

class CommonController extends BaseController
{
    public function __construct(CommonService $service)
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
            return fractal($data, new LatestNewsTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required|min:5|max:50',
                'content' => 'required|min:10|max:500',
                'date' => 'required|unique:services,alies_name',
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
                $path = 'uploaded/latestnews_images/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-latestnews-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                $input['image'] = $newname;
                $input['image_path'] = $path;

            }

            $LatestNews = LatestNews::create($input);

            return $this->sendResponse($LatestNews, 'Latest News created successfully.');

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
            $LatestNews = LatestNews::find($id);

            return fractal($LatestNews, new LatestNewsTransformer());
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }


    public function update(Request $request, $id)
    {
        try{
            $id = Helper::customDecrypt($id);
            $validator = Validator::make($request->all(), [
                'title' => 'required|min:5|max:50',
                'content' => 'required|min:10|max:500',
                'date' => 'required|unique:services,alies_name',
                'image' => 'file|mimes:jpeg,png,jpg',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            $input = $request->all();

            $get_news = LatestNews::find($id);
            if(!empty($get_news)){
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $path = 'uploaded/latestnews_images/';
                    if(!File::exists($path)) {
                        File::makeDirectory($path, $mode = 0777, true, true);
                    }
                    $randm = rand(10,1000);
                    $newname = $randm.time().'-latestnews-'.$filename.'.'.$file_ext;
                    $newname = str_replace(" ","_",$newname);
                    $destinationPath = public_path($path);
                    $file->move($destinationPath, $newname);
    
                    $input['image'] = $newname;
                    $input['image_path'] = $path;
    
                    if($get_news->image != '' && $get_news->image != null && $get_news->image != 'null'){
                        File::delete($destinationPath.$get_news->image);
                    }
                }

                $get_news->update($input);
                return $this->sendResponse($get_news, 'News updated successfully.');
            }else{
                return $this->sendError('Please check news id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function changeLatestNewsStatus(Request $request){
        try{
            $request['id'] = Helper::customDecrypt($request['id']);
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:latest_news,id',
                'status' => 'required|in:0,1',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            LatestNews::where('id',$request->id)->update(['status'=>$request->status]);
            return $this->sendResponse(array(), 'News Status Changed successfully.');
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function getLatestNews($id = ''){
        try{

            if($id == ''){
                $param = '';
                $data = $this->service->get($param);
                
            }else{
                $id = Helper::customDecrypt($id);
                $data = LatestNews::find($id);
            }

            return fractal($data, new LatestNewsTransformer());
            
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
        
    }

    public function addGalleryImage(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'image' => 'required|file|mimes:jpeg,png,jpg',
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
                $path = 'uploaded/gallery_images/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-gallery-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                $input['image'] = $newname;
                $input['image_path'] = $path;

            }

            $Gallery = Gallery::create($input);

            return $this->sendResponse($Gallery, 'Gallery Image Added successfully.');

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function getGalleryList(Request $request){
        try{
            if(isset($request->rows)){
                $gallery_list = Gallery::paginate($request->rows);
            }else{
                $gallery_list = Gallery::where('status','1')->get();
            }

            return $this->sendResponse($gallery_list, 'Gallery List Get successfully.');

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function changeStatusGalleryImg(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:gallery,id',
                'status' => 'required|in:0,1',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            Gallery::where('id',$request->id)->update(['status'=>$request->status]);
            return $this->sendResponse(array(), 'Gallery image Status Changed successfully.');
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    public function deleteGalleryImg(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:gallery,id'
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            $gallery_img = Gallery::find($request->id);
            if(!empty($gallery_img)){
               
                if($gallery_img->image != '' && $gallery_img->image != null && $gallery_img->image != 'null'){
                    $path = 'uploaded/gallery_images/';
                    $destinationPath = public_path($path);
                    File::delete($destinationPath.$gallery_img->image);
                }
                $gallery_img->delete();
                return $this->sendResponse(array(), 'Gallery Image Deleted successfully.');
            }else{
                return $this->sendError('Please check gallery id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
}
