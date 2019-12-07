<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Date: 04.04.2019 11:01
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 */

namespace App\Http\Controllers\Api;


use App\Business;
use App\BusinessCategory;
use App\BusinessDays;
use App\BusinessImage;
use App\Favs;
use App\Notification;
use App\Product;
use App\Reviews;
use App\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;
use phpseclib\Net\SFTP;


class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except(['index','show']);
    }

    public function index()
    {
        return "api";
    }

    public function update(Request $request)
    {
        $this->validate($request,[
            'image' => 'mimes:png,jpg,jpeg,svg,gif'
        ]);
        $user = User::find(Auth::user()->id);
        $rasm = $user->image;
            $user->update([
                'name' => ($request->name==null)?$user->name:$request->name,
                'address' => ($request->address==null)?$user->address:$request->address,
                'city' => ($request->city==null)?$user->city:$request->city,
                'sex' => ($request->sex==null)?$user->sex:$request->sex,
                'email' => ($request->email==null)?$user->email:$request->email,
//                'description' => ($request->description==null)?$user->description:$request->description,
//                'notify' =>($request->notify==null)?$user->notify: $request->notify,
//                'notify_cat' =>($request->notify==0)?null:$request->notify_cat,
//                'facebook' =>($request->facebook==null)?$user->facebook:$request->facebook,
//                'twitter' =>($request->twitter==null)?$user->twitter:$request->twitter,
//                'instagram' =>($request->instagram==null)?$user->instagram:$request->instagram,
//                'youtube' =>($request->youtube==null)?$user->youtube:$request->youtube
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

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = Auth::user()->username."_".time() . '.' . $image->getClientOriginalExtension();
            $location = '../storage/app/public/'. $filename;
            Image::make($image)->save($location);
            $user->image =  "/storage/profile/".$filename;
            $user->save();

            $upload = Queque::upload();
            $upload->chdir('profile'); // open directory 'test'
            $link = 'https://api.my-city.uz/storage/'.$filename;
            $upload->put($filename, $link,SFTP::SOURCE_LOCAL_FILE);
            $pieces = explode("/", $rasm);
            $upload->delete($pieces[3]);
            $upload->_disconnect(true);
            @unlink($location);
        }
        return response()->json([
            'code' => 0,
            'user_image' => $user->image,
            'message' => 'Профиль успешно обновлен'
        ]);
    }

    public function passwordOld(Request $request)
    {
        $this->validate($request,[
            'passwordold' =>'required',
        ]);
        $user = User::find(Auth::user()->id);
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
        $user = User::find(Auth::user()->id);

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

    public function product()
    {
        $product = Business::select('id','user_id','logotype','address','title', 'description','phone','admin_seen','created_at')
        ->where('user_id',Auth::user()->id)->orderBy('id', 'desc')->get()->toArray();
        $data=[];
        if (isset($product)){
            foreach ($product as $item){
                $category = BusinessCategory::where('bussiness_id',$item['id'])->get()->toArray();
                $kalit=[];
                foreach ($category as $key) /* Unikal qilib olinyapti */
                {
                    if (in_array($key['category_id'], $kalit)) continue;
                    else $kalit[]=$key['category_id'];
                }
                $days = BusinessDays::where('business_id',$item['id'])
                    ->select('open_time','close_time','day')->get();
                $sum = Reviews::where('productID',$item['id'])
                    ->where('type',Config::get('constants.business'))
                    ->sum('rating');
                $count = Reviews::where('productID',$item['id'])
                    ->where('type',Config::get('constants.business'))
                    ->count();
                $images = BusinessImage::where('image_id',$item['id'])
                    ->where('type',Config::get('constants.business'))->get();
                if ($count==0) $soni = 0;
                else $soni = ceil($sum/$count);
                $item['created_at'] = strtotime($item['created_at']);
                $item['category']=$kalit;
                $item['days']=$days;
                $item['rating']= $soni;
                $item['rating_count'] = $count;
                $item['images'] = $images;
                $data[]=$item;
            }
            return response()->json([
                'code' => 0,
                'business' => $data
            ]);
        }
        else {
            return response()->json([
                'code' => 0,
                'product' => (object)[]
            ]);
        }

    }

    public function userBusiness()
    {
        $posts = Product::select('id','user_id','admin_seen','bussiness_id','product_name','description','category','sub_category','price','type','screen_shot')
         ->where('user_id',Auth::user()->id)
         ->orderBy('id', 'desc')->get()->toArray();

        $res=[];
        foreach ($posts as $item) {

//            $days = BusinessDays::where('business_id', $item['bussiness_id'])
//                ->select('open_time', 'close_time', 'day')->get();
            $sum = Reviews::where('productID', $item['id'])
                ->where('type', Config::get('constants.product'))
                ->sum('rating');
            $count = Reviews::where('productID', $item['id'])
                ->where('type', Config::get('constants.product'))
                ->count();
            if ($count == 0) $soni = 0;
            else $soni = ceil($sum / $count);
            $images = BusinessImage::where('image_id',$item['id'])
                ->where('type',Config::get('constants.product'))->get();

            $item['rating'] = $soni;
            $item['rating_count'] = $count;
            $item['images'] = $images;
            $res[] = $item;
        }
        return response()->json([
            'code' => 0,
            'service' => $res
        ]);

    }

    public function getUser(Request $request)
    {
        $user = User::where('id',$request->user_id)->select('id','image','name')->first();
        return response()->json([
            'code' => 0,
            'user' => $user
        ]);
    }

    public function addFavorite(Request $request)
    {
        $busines = Business::where('id',$request->business_id)
            ->where('user_id',Auth::user()->id)->first();
        if (isset($busines)) return response()->json([
            'code' => 2,
            'message' => 'Авторам запрещено'
        ]);
        $favorite = Favs::where('business_id',$request->business_id)
            ->where('user_id',Auth::user()->id)->first();
        if (isset($favorite))
            return response()->json([
            'code' => 1,
            'message' => 'Уже добавлено в избранное'
        ]);
        $phn = Favs::create([
            'user_id'=>Auth::user()->id,
            'business_id'=>$request->business_id,
        ]);
        return response()->json([
            'code' => 0,
            'message' => 'Успешно добавлено в избранное'
        ]);
    }

    public function delFavorite(Request $request)
    {
        $favorite = Favs::where('business_id',$request->business_id)
            ->where('user_id',Auth::user()->id)->first();
        $favorite->delete();
        return response()->json([
            'code' => 0,
            'message' => 'Успешно удалена'
        ]);
    }

}
