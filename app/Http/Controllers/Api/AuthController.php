<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;

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
                return $this->sendError($validator->errors()->first(), $validator->errors());       
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
                return $this->sendError($validator->errors()->first(), $validator->errors());       
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

    /**
     *
     * @return string|null
     * @throws \Exception
     */
    public function generateVerificationToken()
    {
       // $token = str_random(50);
        $token = bin2hex(random_bytes(25));
        if (DB::table('password_resets')->where('token', $token)->count() === 0) {
            return $token;
        }

        return $this->generateVerificationToken();
    }

    public function resetPassword(Request $request)
    {
        DB::beginTransaction();
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            $token = $this->generateVerificationToken();
            $check_existing = DB::table('password_resets')->where([ 'email' => $request->email ])->first();
            if(!empty($check_existing)){
                DB::table('password_resets')->where([ 'email' => $request->email ])->update(['token' => $token]);
            }else{
                DB::table('password_resets')->insert([ 'email' => $request->email, 'token' => $token,'created_at'=>Carbon::now()]);
            }
            
            $resetlink = env('frontendURL').'/reset-password/'.$token;

            $input["email_to"] = $request->email;
            
	        $image_url = [
	            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
	            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
	        ];

            //$resetlink = "<a href=".$resetlink.">Click here</a>";
            $input["resetlink"] = $resetlink;
            
	        Mail::send('emails.SendTokenMailTemplate', ['data' => $input, 'image_url'=>$image_url], function ($m) use($input) {
	            $m->from('gurdevhospitals@gmail.com','Gurdev Hospital');
	            $m->to($input["email_to"])->subject('Reset Password');
	        });
            DB::commit();
            return $this->sendResponse(array('token'=>$token), 'The token has been sent to your mail successfully');
  
        }catch(\Throwable $th){
            DB::rollback();
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }

    }

  
    public function confirmResetPassword($token)
    {
        try{
            $expiredAt = Carbon::now()->subDays(1);
            DB::table('password_resets')->where('created_at', '<', $expiredAt)->delete();

            $token_exists = DB::table('password_resets')->where('token', $token)->first();
            
            if(!empty($token_exists)){
                return $this->sendResponse(array(), 'The token is valid');
            }else{
                return $this->sendError('error', ['error'=>'The token is not valid']);
            }
        }catch(\Throwable $th){
            return $this->sendError($th->getMessage(),['error_line' => $th->getLine(),'error_file' => $th->getFile()]);
        }
    }
    public function createNewPassword(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'token'    => 'required',
                'password' => 'required|min:8|max:255',
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors()->first(), $validator->errors());       
            }

            $token_exist = DB::table('password_resets')->where('token', $request->token)->first();
            if(!empty($token_exist)){
                $password = bcrypt($request['password']);
                User::where('email',$token_exist->email)->update(['password'=>$password]);
                DB::table('password_resets')->where('email', $token_exist->email)->delete();
                return $this->sendResponse(array(), 'Password changed successfully');
            }
           
            return $this->sendError('error.', ['error'=>'Something Went wrong']);
        }  catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
        
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
