<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Services\StaticPagesService;
use App\Models\StaticPages;
use Spatie\Fractal\Fractal;
use Validator;
use File;
use Helper;
use Illuminate\Support\Facades\Mail;
use App\Transformers\StaticPagesTransformer;

class StaticPagesController extends BaseController
{
    public function __construct(StaticPagesService $service)
    {
        $this->service = $service;
    }

    /*****************************
     * fn to get pages list in dashboard
     */
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
            return fractal($data, new StaticPagesTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
    /******************************
     * to create page dashboard
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required|min:5|max:50',
                'content' => 'required|min:10|max:1500',
                'image' => 'file|mimes:jpeg,png,jpg',
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
                $path = 'uploaded/staticpages_images/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-staticpages-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                $input['image'] = $newname;
                $input['image_path'] = $path;

            }

            $StaticPages = StaticPages::create($input);

            return $this->sendResponse(array(), 'Page created successfully.');

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
            $StaticPages = StaticPages::find($id);

            return fractal($StaticPages, new StaticPagesTransformer());
        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /*****************************
     * to update any page dashbord
     */
    public function update(Request $request, $id)
    {
        try{
            $id = Helper::customDecrypt($id);
            $validator = Validator::make($request->all(), [
                'title' => 'required|min:5|max:50',
                'content' => 'required|min:10|max:500',
                'image' => 'file|mimes:jpeg,png,jpg',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            $input = $request->all();

            $page = StaticPages::find($id);
            if(!empty($page)){
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    
                    $file_name = $file->getClientOriginalName();
                    $file_ext = $file->getClientOriginalExtension();
                    $fileInfo = pathinfo($file_name);
                    $filename = $fileInfo['filename'];
                    $path = 'uploaded/staticpages_images/';
                    if(!File::exists($path)) {
                        File::makeDirectory($path, $mode = 0777, true, true);
                    }
                    $randm = rand(10,1000);
                    $newname = $randm.time().'-staticpages-'.$filename.'.'.$file_ext;
                    $newname = str_replace(" ","_",$newname);
                    $destinationPath = public_path($path);
                    $file->move($destinationPath, $newname);
    
                    $input['image'] = $newname;
                    $input['image_path'] = $path;
    
                    if($page->image != '' && $page->image != null && $page->image != 'null'){
                        File::delete($destinationPath.$page->image);
                    }
                }

                $page->update($input);
                return $this->sendResponse(array(), 'Page updated successfully.');
            }else{
                return $this->sendError('Please check news id',['error' => 'error']);
            }

        }catch (\Throwable $th) {
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }

    /*********************
     * to get static pages
     * Frontend
     */

    public function getPagesFrontend($page_id = ''){
        try{
            if($page_id == ''){
                $data = $this->service->get();
            }else{
                //$id = Helper::customDecrypt($page_id);
                $data = StaticPages::find($page_id);
            }
            
            return fractal($data, new StaticPagesTransformer());
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
}
