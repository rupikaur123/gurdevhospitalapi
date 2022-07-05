<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    /**
     * Register api testetstse d
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
       
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['name'] =  $user->name;
       
            return $this->sendResponse($success, 'User register successfully.');
            
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
        
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|exists:users,email',
                'password' => 'required',
            ]);
       
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
    
    
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
                $user = Auth::user(); 
                $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
                $success['name'] =  $user->name;
       
                return $this->sendResponse($success, 'User login successfully.');
            } 
            else{ 
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
            }
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }

         
    }
}
