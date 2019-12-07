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
        $this->middleware('auth:employee', ['except' => ['login', 'registration']]);
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
        $mail = Employees::where('login',$request->login)->first();
        if (isset($mail)) return response()->json([
            'code' => 1,
            'message' => 'Такой телефон уже зарегистрирован'
        ]);

        $user = new Employees();
        $user->name = $request->name;
        $user->login = $request->login;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'code' => 0,
            'message' => 'Аккаунт успешно создан.'
        ], 201);
    }

    public function update(Request $request)
    {
//        $this->validate($request,[
//            'image' => 'mimes:png,jpg,jpeg,svg,gif'
//        ]);
        $user = Employees::find(Auth::user()->id);
        $user->update([
            'name' => ($request->name==null)?$user->name:$request->name,
//            'address' => ($request->address==null)?$user->address:$request->address,
        ]);
//        if ($request->notify==1){
//            if ($request->notify_cat!=null){
//                $pieces = explode(",", $request->notify_cat);
//                foreach ($pieces as $item){
//                    $notific = new Notification([
//                        'user_id' => $user->id,
//                        'cat_id' => $item,
//                        'user_email' => $user->email,
//                    ]);
//                    $notific->save();
//                }
//            }
//            else {
//                $notific = Notification::where('user_id',$user->id)->get();
//                foreach ($notific as $item){
//                    $item->user_id =0;
//                    $item->save();
//                }
//            }
//        }

//        if ($request->hasFile('image')) {
//            $image = $request->file('image');
//            $filename = Auth::user()->username."_".time() . '.' . $image->getClientOriginalExtension();
//            $location = '../storage/app/public/'. $filename;
//            Image::make($image)->save($location);
//            $user->image =  "/storage/profile/".$filename;
//            $user->save();
//
//            $upload = Queque::upload();
//            $upload->chdir('profile'); // open directory 'test'
//            $link = 'https://api.my-city.uz/storage/'.$filename;
//            $upload->put($filename, $link,SFTP::SOURCE_LOCAL_FILE);
//            $pieces = explode("/", $rasm);
//            $upload->delete($pieces[3]);
//            $upload->_disconnect(true);
//            @unlink($location);
//        }
        return response()->json([
            'code' => 0,
            'message' => 'Профиль успешно обновлен'
        ]);
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
                'message' => 'Пароль успешно изменен'
            ]);
        }else{
            return response()->json([
                'code' => 1,
                'message' => 'Пароли не совпадают!'
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