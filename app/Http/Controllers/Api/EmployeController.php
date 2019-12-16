<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: blog
 * Date: 06.12.2019 15:53
 */

namespace App\Http\Controllers\Api;

use App\Employees;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EmployeController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth:employee', ['except' => ['login']]);
        $this->middleware('auth:employee')->except(['index','login']);
    }

    protected function guard()
    {
        return Auth::guard('employee');
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = request(['login', 'password']);

        if (! $token = $this->guard()->attempt($credentials)) {
//            return response()->json(['error' => 'Unauthorized'], 401);
            return response()->json([
                'user' =>(object)[],
                'code' => 1,
                'message' => 'Неверное телефон или пароль'
            ], 401);
        }
        $body= $this->guard()->user();
        $date = $body->created_at;
        unset($body->email_verified_at,
            $body->updated_at,
            $body->created_at
        );
        $body->reg_date = strtotime($date);
        $body->access_token=$token;
        $body->token_type='Bearer';
        $body->expires_at = auth()->factory()->getTTL() * 60;

        return response()->json(
            [
                'user'=>$body,
                'code' => 0,
                'message' => trans('lang.success')
            ]);

//        return $this->respondWithToken($token);
    }

    /**
     * User registration
     */
    public function registration(Request $request)
    {
//        $name = request('name');
//        $email = request('email');
//        $password = request('password');
        if (Auth::user()->role==1){
            $mail = Employees::where('login',$request->login)->first();
            if (isset($mail)) return response()->json([
                'code' => 1,
                'message' => trans('lang.error_phone')
            ]);

            $user = new Employees();
            $user->name = $request->name;
            $user->login = $request->login;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'code' => 0,
                'message' => trans('lang.account')
            ], 201);
        }
        return response()->json([
            'code' => 1,
            'message' => "Sizga mumkinmas"
        ], 201);
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