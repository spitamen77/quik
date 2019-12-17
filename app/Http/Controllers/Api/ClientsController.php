<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 17.12.2019 12:28
 */

namespace App\Http\Controllers\Api;

use App\Clients;
use App\Employees;
use App\models\ConfirmClient;
use App\models\Sms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ClientsController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:client')->except(['createSms','login']);
    }

    protected function guard()
    {
        return Auth::guard('client');
    }

    /**
     * User registration
     */
    public function createSms(Request $request)
    {

        $botText = preg_replace('/\s|\+|-|@|#|&|%|$|=|_|:|;|!|\'|"|\(|\)/', '', $request->mobile);
        $pattern = "/^[8-9]{3}[0-9]{9}$/";
        if (preg_match($pattern, $botText, $out)) {
            $code = ConfirmClient::where('mobile',$request->mobile)->first();
            if ($code){
                // shu yerda yana sms yuboraman
                $vercode = substr(rand(), 0, 4);
            }else {
                $vercode = substr(rand(), 0, 4);

                $phn = ConfirmClient::create([
                    'code'=>$vercode,
                    'mobile' => $request->mobile,
                    'created_at' =>time()
                ]);
            }

            return response()->json([
                'code' => 0,
                'message' => trans('lang.account')
            ], 200);
        }else{
            return response()->json([
                'code' => 1,
                'message' => trans('lang.wrong_phone')
            ], 200);
        }


    }

    public function getCode(Request $request)
    {
        $phone = preg_replace('/\s|\+|-|@|#|&|%|$|=|_|:|;|!|\'|"|\(|\)/', '', $request->mobile);
        $sms_code = ConfirmClient::where('mobile',$request->mobile)->where('code',$request->code)->first();
        if (isset($sms_code)){
            $user = Clients::where('mobile',$phone)->first();
            if (isset($user)){
                $token = JWTAuth::fromUser($user);
                if (!$token) {
                    return response()->json(['error' => 'invalid_credentials'], 401);
                }
//            return response()->json(compact('userToken'));
                $date = $user->created_at;
                unset($user->email_verified_at,
                    $user->updated_at,
                    $user->created_at
                );
                $user->reg_date = strtotime($date);
                $user->access_token=$token;
                $user->token_type='Bearer';
                $user->expires_at = auth()->factory()->getTTL() * 60;
                return response()->json([
                    'code' => 0,
                    'client' => $user
                ], 200);
            }else{
                $phn = Clients::create([
                    'mobile' => $phone,
                    'photo'=>"/storage/client/default.png",
                    'language'=>$request->lang,
                    'registration_platform'=>$request->platform,
                ]);
                $sms_code->delete();
                $user = Clients::where('mobile', $phone)->first();
                $token = JWTAuth::fromUser($user);
                if (!$token) {
                    return response()->json(['error' => 'invalid_credentials'], 401);
                }
//            return response()->json(compact('userToken'));
                $date = $user->created_at;
                unset($user->email_verified_at,
                    $user->updated_at,
                    $user->created_at
                );
                $user->reg_date = strtotime($date);
                $user->access_token=$token;
                $user->token_type='Bearer';
                $user->expires_at = auth()->factory()->getTTL() * 60;
                return response()->json([
                    'code' => 0,
                    'client' => $user
                ], 200);
            }
        }else{
            return response()->json([
                'code' => 1,
                'message' => trans('lang.wrong_code')
            ], 200);
        }
    }

    public function update(Request $request)
    {

        if (Auth::user()->role==1){
            $user = Employees::find($request->user_id);
            $password = Hash::make($request->password);
            $user->name = ($request->name==null)?$user->name:$request->name;
            $user->password = $password;
            $user->save();
        }else {
            $user = Employees::find(Auth::user()->id);
            $password = Hash::make($request->password);
            $user->name = ($request->name==null)?$user->name:$request->name;
            $user->password = $password;
            $user->save();
        }

        return response()->json([
            'code' => 0,
            'message' => trans('lang.update_success')
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->perpage;
        $offset = $request->page-1;
        if (($limit==null) || ($offset==null)) {
            $offset=0; $limit=50;
        }
//        $user = Employees::where('id',Auth::user()->id)->first();
        if (Auth::user()->role==1){
            $list = Employees::select('id', 'name','login','role','created_at')
                ->where('role','!=' , 1)
                ->orderBy('id', 'desc')
                ->skip($offset*$limit)->take($limit)
                ->get()->toArray();

            return response()->json([
                'code' => 0,
                'users' => $list
            ]);
        }
    }

    public function delete($id)
    {
        if (Auth::user()->role==1){
            $user = Employees::find($id);
            $user->delete();
            return response()->json([
                'code' => 0,
                'message' => "success"
            ]);
        }
        return response()->json([
            'code' => 0,
            'message' => "no delete"
        ]);
    }

    public function getUser($id)
    {
        if (Auth::user()->role==1){
            $user = Employees::select('id', 'name','login','role','created_at')
                ->where('id',$id)->first();
            if (isset($user)){
                $date = $user->created_at;
                unset($user->created_at);
                $user->reg_date = strtotime($date);

                return response()->json([
                    'code' => 0,
                    'user' => $user
                ]);
            }else{
                return response()->json([
                    'code' => 0,
                    'user' => (object)[]
                ]);
            }
        }else {
            $user = Employees::select('id', 'name','login','role','created_at')
                ->where('id',Auth::user()->id)->first();
            $date = $user->created_at;
            unset($user->created_at);
            $user->reg_date = strtotime($date);

            return response()->json([
                'code' => 0,
                'user' => $user
            ]);
        }
    }

    public function passwordOld(Request $request)
    {
        $this->validate($request,[
            'passwordold' =>'required',
        ]);
        $user = Employees::find(Auth::user()->id);
        if(Hash::check($request->passwordold, $user->password)){
            return response()->json([
                'code' => 0,
                'message' => 'Пароль совпадают'
            ]);
        }else{
            return response()->json([
                'code' => 1,
                'message' => 'Пароли не совпадают!'
            ]);
        }
    }

    public function passwordChange(Request $request)
    {
        $this->validate($request,[
            'passwordold' =>'required',
            'password' => 'required|min:4'
        ]);
        $user = Employees::find(Auth::user()->id);

        if(Hash::check($request->passwordold, $user->password)){
            $password = Hash::make($request->password);
            $user->password = $password;
            $user->save();

            return response()->json([
                'code' => 0,
                'message' => trans('lang.password_correct')
            ]);
        }else{
            return response()->json([
                'code' => 1,
                'message' => trans('lang.password_incorrect')
            ]);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'code' => 0,
            'message' => trans('lang.logout')
        ]);
//        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}