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

use App\Clients;
use App\Employees;
use App\models\ClientBlacklist;
use App\models\Regions;
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
                'employee'=>$body,
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
            $user = Employees::find($request->id);
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
                'employees' => $list
            ]);
        }
    }

    public function delete($id)
    {
        if (Auth::user()->role==1){
            $user = Employees::find($id);
            if (isset($user)){
                $user->delete();
                return response()->json([
                    'code' => 0,
                    'message' => "success"
                ]);
            }else{
                return response()->json([
                    'code' => 0,
                    'message' => "no delete"
                ]);
            }
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
                    'employee' => $user
                ]);
            }else{
                return response()->json([
                    'code' => 0,
                    'employee' => (object)[]
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
                'employee' => $user
            ]);
        }
    }

    public function getClient($id) //bloced qilish
    {
        $user = Clients::where('id',$id)->first();
        if (isset($user)){
            $date = $user->created_at;
            unset($user->last_visit,
                $user->updated_at,
                $user->last_region,
                $user->created_at
            );
            $user->reg_date = strtotime($date);
            $block = ClientBlacklist::where('client_id',$id)->first();
            if (isset($block)){
                return response()->json([
                    'code' => 0,
                    'client' => $user,
                    'block' => 'yes',
                    'note' => $block->note
                ]);
            }
            return response()->json([
                'code' => 0,
                'client' => $user,
                'block' => 'no',
                'note' => ''
            ]);
        }else{
            return response()->json([
                'code' => 0,
                'client' => (object)[],
                'block' => 'no',
                'note' => ''
            ]);
        }

    }

    public function getClients(Request $request)
    {
        $limit = $request->perpage;
        $offset = $request->page-1;
        if (($limit==null) || ($offset==null)) {
            $offset=0; $limit=50;
        }

        $list = Clients::select('id','mobile','first_name','last_name','gender','data_birthday','photo','language','registration_platform','last_region','last_visit','created_at');
//            ->where('id','!=',0);
        if (isset($request->block)){
            $tags = ClientBlacklist::orderByDesc('id')->get();
            $tras=[];
            foreach ($tags as $item){
                $tras[]=$item->client_id;
            }
            if (is_array($tras)) {
                $list = $list->whereIn('clients.id', $tras);
            }
        }
        if (isset($request->search)){
            $list = $list->where(function ($query) use ($request) {
                $query->orWhere('clients.mobile', 'LIKE', "%{$request->search}%")
                    ->orWhere('clients.first_name','LIKE', "%{$request->search}%")
                    ->orWhere('clients.last_name','LIKE', "%{$request->search}%");
            });
        }

        $list = $list->orderBy('id', 'desc')
            ->skip($offset*$limit)->take($limit)
            ->get();
//        dd($list->toSql());
        $compa = [];
        foreach ($list as $item){
            $date = $item->created_at;
            unset($item->created_at);
            $item->reg_date = strtotime($date);
            if ($item->block){}
            else $item->block = null;
            $compa[] = $item;
        }

        return response()->json([
            'code' => 0,
            'clients' => $compa
        ]);

    }

    public function updateClient(Request $request)
    {
//        var_dump($request->id); exit('adasd');
        $this->validate($request,[
            'image' => 'mimes:png,jpg,jpeg,svg,gif'
        ]);
        $user = Clients::find($request->id);
        $user->update([
//            'telegram_id' => ($request->telegram==null)?$user->telegram:$request->telegram,
            'first_name' => ($request->first_name==null)?$user->first_name:$request->first_name,
            'last_name' => ($request->last_name==null)?$user->last_name:$request->last_name,
            'gender' => ($request->gender==null)?$user->gender:$request->gender,
            'data_birthday' => ($request->birthday==null)?$user->data_birthday:$request->birthday,
            'language' => ($request->language==null)?$user->language:$request->language,
            'last_region' =>($request->region==null)?$user->last_region: $request->region,
        ]);
        if ($request->ban!=null){
            if ($request->note!=null){
                if ($request->ban=="yes"){
                    $phn = ClientBlacklist::create([
                        'client_id' => $user->id,
                        'note'=>$request->note,
                        'created_at'=>time()
                    ]);
                    return response()->json([
                        'code' => 1,
                        'user_image' => $user->photo,
                        'message' => trans('lang.add_ban')
                    ],201);
                }else{
                    $ban = ClientBlacklist::find($request->id);
                    $ban->delete();
                    return response()->json([
                        'code' => 0,
                        'user_image' => $user->photo,
                        'message' => trans('lang.del_ban')
                    ]);
                }
            }else return response()->json([
                'code' => 1,
                'user_image' => $user->photo,
                'message' => trans('lang.add_note')
            ],200);
        }

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

    public function storeRegion(Request $request)
    {
        $phn = Regions::create([
            'name_ru' => $request->name_ru,
            'name_uz'=>$request->name_uz,
            'coordinates'=>$request->coordinates
        ]);
        return response()->json([
            'code' => 0,
            'region_id' => $phn->id,
            'message' => trans('lang.success')
        ],201);
    }

    public function updateRegion(Request $request)
    {
        $user = Regions::find($request->id);
        $user->update([
            'name_ru' => ($request->name_ru==null)?$user->name_ru:$request->name_ru,
            'name_uz' => ($request->name_uz==null)?$user->name_uz:$request->name_uz,
            'coordinates' => ($request->coordinates==null)?$user->coordinates:$request->coordinates,
        ]);
        return response()->json([
            'code' => 0,
            'message' => trans('lang.success'),
        ]);
    }

    public function deleteRegion($id)
    {
        $user = Regions::find($id);
        if (isset($user)){
            $user->delete();
            return response()->json([
                'code' => 0,
                'message' => trans('lang.success_delete'),
            ],204);
        }else{
            return response()->json([
                'code' => 1,
                'message' => trans('lang.no_object')
            ]);
        }
    }

    public function showRegions(Request $request)
    {
        if ($request->lang=='all'){
            $reg = Regions::all();
        }else{
            $lang = $request->header('X-localization');
            $reg = Regions::select('id','name_'.$lang,'coordinates')->get();
        }

        return response()->json([
            'code' => 0,
            'regions' => $reg
        ]);
    }

    public function getRegion(Request $request)
    {
        if ($request->lang=='all'){
            $reg = Regions::where('id',$request->id)->first();
        }else{
            $lang = $request->header('X-localization');
            $reg = Regions::select('id','name_'.$lang,'coordinates')->where('id',$request->id)->first();
        }
        return response()->json([
            'code' => 0,
            'region' => $reg
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