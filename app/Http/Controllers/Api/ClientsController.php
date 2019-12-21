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
use App\models\ClientBlacklist;
use App\models\ConfirmClient;
use App\models\Regions;
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
        $this->middleware('auth:client')->except(['createSms','login','getCode']);
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
        $sms_code = ConfirmClient::where('mobile',$request->mobile)->where('code','=',$request->code)->first();
        if (isset($sms_code)){
            $user = Clients::where('mobile',$phone)->first();
            if (isset($user)){
                $sms_code->delete();
                $token = JWTAuth::fromUser($user);
                if (!$token) {
                    return response()->json([
                        'code' => 1,
                        'client' => (object)[],
                        'message' => 'Error'
                    ], 401);
                }
                $user->last_visit = time();
                $user->save();
                $date = $user->created_at;
                unset($user->last_visit,
                    $user->updated_at,
                    $user->last_region,
                    $user->created_at
                );
                $user->reg_date = strtotime($date);
                $user->access_token=$token;
                $user->token_type='Bearer';
                $user->expires_at = auth()->factory()->getTTL() * 60;
                return response()->json([
                    'code' => 0,
                    'client' => $user,
                    'message' => 'Success'
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
                    return response()->json([
                        'code' => 1,
                        'client' => (object)[],
                        'message' => 'Error'
                    ], 401);
                }
//            return response()->json(compact('userToken'));
                $date = $user->created_at;
                unset($user->last_visit,
                    $user->updated_at,
                    $user->last_region,
                    $user->created_at
                );
                $user->reg_date = strtotime($date);
                $user->access_token=$token;
                $user->token_type='Bearer';
                $user->expires_at = auth()->factory()->getTTL() * 60;
                return response()->json([
                    'code' => 0,
                    'client' => $user,
                    'message' => 'Success'
                ], 200);
            }
        }else{
            return response()->json([
                'code' => 1,
                'client' => (object)[],
                'message' => trans('lang.wrong_code')
            ], 200);
        }
    }

    public function changePhone(Request $request)
    {
        $new_phone = preg_replace('/\s|\+|-|@|#|&|%|$|=|_|:|;|!|\'|"|\(|\)/', '', $request->new_phone);
        $old_phone = preg_replace('/\s|\+|-|@|#|&|%|$|=|_|:|;|!|\'|"|\(|\)/', '', $request->old_phone);
        $sms_code = ConfirmClient::where('mobile',$new_phone)->where('code','=',$request->code)->first();
        if (isset($sms_code)){
            $old = Clients::where('mobile', $old_phone)->first();
            if (isset($old)){
                $sms_code->delete();
                $old->update([
                    'mobile' => $new_phone,
                ]);
                return response()->json([
                    'code' => 0,
                    'client' => Clients::where('mobile', $new_phone)->first(),
                    'message' => trans('lang.success')
                ], 200);
            }else{
                return response()->json([
                    'code' => 1,
                    'client' => (object)[],
                    'message' => trans('lang.wrong_number')
                ], 200);
            }
        }else{
            return response()->json([
                'code' => 1,
                'client' => (object)[],
                'message' => trans('lang.wrong_code')
            ], 200);
        }
    }

    public function update(Request $request)
    {
//        var_dump($request->id); exit('adasd');
        $this->validate($request,[
            'image' => 'mimes:png,jpg,jpeg,svg,gif'
        ]);
        $user = Clients::find(Auth::user()->id);
        $user->update([
//            'telegram_id' => ($request->telegram==null)?$user->telegram:$request->telegram,
            'first_name' => ($request->first_name==null)?$user->first_name:$request->first_name,
            'last_name' => ($request->last_name==null)?$user->last_name:$request->last_name,
            'gender' => ($request->gender==null)?$user->gender:$request->gender,
            'data_birthday' => ($request->birthday==null)?$user->data_birthday:$request->birthday,
            'language' => ($request->language==null)?$user->language:$request->language,
            'last_region' =>($request->region==null)?$user->last_region: $request->region,
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = Auth::user()->id."_".time() . '.' . $image->getClientOriginalExtension();
            $location = '../storage/app/client/'. $filename;
            Image::make($image)->save($location);
            $user->photo =  "/storage/client/".$filename;
            $user->save();
        }
        return response()->json([
            'code' => 0,
            'user_image' => $user->photo,
            'message' => trans('lang.update_success')
        ]);

    }

    public function getClient($id)
    {
        $user = Clients::where('id',Auth::user()->id)->first();
        $date = $user->created_at;
        unset($user->last_visit,
            $user->updated_at,
            $user->last_region,
            $user->created_at
        );
        $user->reg_date = strtotime($date);

        return response()->json([
            'code' => 0,
            'client' => $user,
        ]);
    }

    public function showRegions(Request $request)
    {
        if ($request->lang=='all'){
            $reg = Regions::select('id','name_ru','name_uz')->get();
        }else{
            $lang = $request->header('X-localization');
            $reg = Regions::select('id','name_'.$lang)->get();
        }
        return response()->json([
            'code' => 0,
            'regions' => $reg
        ]);
    }

    public function getRegion(Request $request)
    {
        if ($request->lang=='all'){
            $reg = Regions::select('id','name_ru','name_uz')->where('id',$request->id)->first();
        }else{
            $lang = $request->header('X-localization');
            $reg = Regions::select('id','name_'.$lang)->where('id',$request->id)->first();
        }
        return response()->json([
            'code' => 0,
            'region' => $reg
        ]);
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