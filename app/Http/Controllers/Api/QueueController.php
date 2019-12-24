<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 11.04.2019 19:49
 */

namespace App\Http\Controllers\Api;

use App\Clients;
use App\models\Carriers;
use App\models\Service;
use App\models\Transports;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:employee')->except(['showServices','login']);
    }

    protected function guard()
    {
        return Auth::guard('employee');
    }

    public function showServices(Request $request)
    {
        if ($request->lang=='all'){
            $reg = Service::orderBy('position', 'asc')->get();
        }else{
            $lang = $request->header('X-localization');
            $reg = Service::select('id','name_'.$lang,'description_'.$lang,'position')->orderBy('position', 'asc')->get();
        }

        return response()->json([
            'code' => 0,
            'services' => $reg
        ]);
    }

    public function getCarriers(Request $request)
    {
        $limit = $request->perpage;
        if ($request->page==0) $offset=0;
        else $offset = $request->page-1;
        if (($limit==null) && ($offset==null)) {
            $offset=0; $limit=50;
        }
        $list = Carriers::select('id','first_name','last_name','middle_name','called','mobile');
        if (isset($request->search)){
            $list = $list->where(function ($query) use ($request) {
                $query->orWhere('carriers.first_name', 'LIKE', "%{$request->search}%")
                    ->orWhere('carriers.last_name','LIKE', "%{$request->search}%")
                    ->orWhere('carriers.mobile','LIKE', "%{$request->search}%")
                    ->orWhere('carriers.middle_name','LIKE', "%{$request->search}%");
            });
        }
        $paginate = $list;
        $list = $list->orderBy('id', 'desc')
            ->skip($offset*$limit)->take($limit)
            ->get();
        $compa = [];
        foreach ($list as $item){
            $date = $item->created_at;
            unset($item->created_at);
            $item->reg_date = strtotime($date);
            if ($item->block){}
            else $item->block = null;
            $compa[] = $item;
        }
        $pager = [];
        $pager['currentPage']=$offset+1;
        $pager['perpage']=$limit;
        $pager['total']= $paginate->paginate($limit)->total();
        return response()->json([
            'code' => 0,
            'clients' => $compa,
            'pager' => $pager
        ]);
    }

    public function storeCarrier(Request $request)
    {
        if($request->transport_id=="create"){
            $trans = Transports::create([
                'mark_id' => $request->mark_id,
                'model_id' => $request->model_id,
                'number' => $request->number
            ]);
            $transport = $trans->id;
        }else $transport = $request->transport_id;

        $phn = Clients::create([
            'mobile' => $request->mobile,
            'first_name' => ($request->first_name==null)?null:$request->first_name,
            'last_name' => ($request->last_name==null)?null:$request->last_name,
            'region_id' =>($request->region_id==null)?null: $request->region_id,
            'called'=>$request->called,
            'middle_name'=>$request->middle_name,
            'birth_day'=>$request->birth_day,
            'passport_number'=>$request->passport_number,
            'passport_date'=>$request->passport_date,
            'passport_until'=>$request->passport_until,
            'address'=>$request->address,
            'license_number'=>$request->license_number,
            'license_date'=>$request->license_date,
            'license_until'=>$request->license_until,
            'license_class'=>$request->license_class,
            'phones'=>$request->phones,
            'data_reg'=>time(),
            'transport_id'=>$transport,
        ]);
        return response()->json([
            'code' => 0,
            'client_id' => $phn->id,
            'message' => trans('lang.success')
        ],201);
    }

    public function showCarries($id)
    {
        $reg = Carriers::where('id',$id)->first();

        return response()->json([
            'code' => 0,
            'region' => $reg
        ]);
    }

    public function updateCarrier(Request $request)
    {
        $trans = Carriers::find($request->id);
        $trans->update([
            'mobile' => ($request->mobile==null)?$trans->mobile:$request->mobile,
            'first_name' => ($request->first_name==null)?null:$request->first_name,
            'last_name' => ($request->last_name==null)?null:$request->last_name,
            'region_id' =>($request->region_id==null)?null: $request->region_id,
            'called'=>($request->called==null)?$trans->called:$request->called,
            'middle_name'=>($request->middle_name==null)?$trans->middle_name:$request->middle_name,
            'birth_day'=>($request->birth_day==null)?$trans->birth_day:$request->birth_day,
            'passport_number'=>($request->passport_number==null)?$trans->passport_number:$request->passport_number,
            'passport_date'=>($request->passport_date==null)?$trans->passport_date:$request->passport_date,
            'passport_until'=>($request->passport_until==null)?$trans->passport_until:$request->passport_until,
            'address'=>($request->address==null)?$trans->address:$request->address,
            'license_number'=>($request->license_number==null)?$trans->license_number:$request->license_number,
            'license_date'=>($request->license_date==null)?$trans->license_date:$request->license_date,
            'license_until'=>($request->license_until==null)?$trans->license_until:$request->license_until,
            'license_class'=>($request->license_class==null)?$trans->license_class:$request->license_class,
            'phones'=>($request->phones==null)?$trans->phones:$request->phones,
            'transport_id'=>($request->transport_id==null)?$trans->transport_id:$request->transport_id,
        ]);
        return response()->json([
            'code' => 0,
            'message' => trans('lang.success'),
        ]);
    }


}
