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
use App\models\TransportMarks;
use App\models\TransportModels;
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
            if (!$lang) $lang='ru';
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
        $new_phone = preg_replace('/\s|\+|-|@|#|&|%|$|=|_|:|;|!|\'|"|\(|\)/', '', $request->mobile);
        $pozivnoy = Carriers::where('called',$request->called)->orWhere('mobile',$new_phone)->first();
        if (isset($pozivnoy)) return response()->json([
            'code' => 1,
            'carrier_id' => null,
            'message' => trans('lang.duplicate')
        ],400);
        $pattern = "/^[8-9]{3}[0-9]{9}$/";
        if (preg_match($pattern, $new_phone, $out)) {
            if ($request->transport_id == "create") {
                $trans = Transports::create([
                    'mark_id' => $request->mark_id,
                    'model_id' => $request->model_id,
                    'number' => $request->number
                ]);
                $transport = $trans->id;
            } else $transport = $request->transport_id;

            $phn = Carriers::create([
                'mobile' => $new_phone,
                'first_name' => ($request->first_name == null) ? null : $request->first_name,
                'last_name' => ($request->last_name == null) ? null : $request->last_name,
                'region_id' => ($request->region_id == null) ? null : $request->region_id,
                'called' => $request->called,
                'middle_name' => $request->middle_name,
                'birth_day' => $request->birth_day,
                'passport_number' => $request->passport_number,
                'passport_date' => $request->passport_date,
                'passport_until' => $request->passport_until,
                'address' => $request->address,
                'license_number' => $request->license_number,
                'license_date' => $request->license_date,
                'license_until' => $request->license_until,
                'license_class' => $request->license_class,
                'phones' => $request->phones,
                'data_reg' => time(),
                'transport_id' => $transport,
            ]);
            return response()->json([
                'code' => 0,
                'carrier_id' => $phn->id,
                'message' => trans('lang.success')
            ], 201);
        }else return response()->json([
            'code' => 1,
            'carrier_id' => null,
            'message' => trans('lang.error')
        ],400);
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

    public function showTransports(Request $request)
    {
        $limit = $request->perpage;
        if ($request->page==0) $offset=0;
        else $offset = $request->page-1;
        if (($limit==null) && ($offset==null)) {
            $offset=0; $limit=50;
        }
        $list = Transports::select('id','mark_id','model_id','number');
        if (isset($request->search)){
            $list = $list->where(function ($query) use ($request) {
                $query->orWhere('transport.number', 'LIKE', "%{$request->search}%");
            });
        }
        if (isset($request->mark)){
            $list = $list->where('mark_id',$request->mark);
        }
        if (isset($request->model)){
            $list = $list->where('model_id',$request->model);
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
            'transports' => $compa,
            'pager' => $pager
        ]);
    }

    public function getTransport($id)
    {
        $reg = Transports::where('id',$id)->first();
        $trans=[];
        $trans['id']=$reg->id;
        $trans['mark']=$reg->marks->name;
        $trans['model']=$reg->models->name;
        $trans['number']=$reg->number;
        return response()->json([
        'code' => 0,
        'transport' => $trans
        ]);
    }

    public function storeTransport(Request $request)
    {
        $trans = Transports::create([
            'mark_id' => $request->mark_id,
            'model_id' => $request->model_id,
            'number' => $request->number
        ]);
        return response()->json([
            'code' => 0,
            'transport_id' => $trans->id
        ]);
    }

    public function updateTransport(Request $request)
    {
        $trans = Transports::find($request->id);
        $trans->update([
            'mark_id' => ($request->mark_id==null)?$trans->mark_id:$request->mark_id,
            'model_id' => ($request->model_id==null)?null:$request->model_id,
            'number' => ($request->number==null)?null:$request->number,
        ]);
        return response()->json([
            'code' => 0,
            'message' => trans('lang.success'),
        ]);
    }

    public function getTransMarks()
    {
        $marks = TransportMarks::all();
        return response()->json([
            'code' => 0,
            'marks' => $marks,
        ]);
    }

    public function getTransModel(Request $request)
    {
        if ($request->mark!=null){
            $model = TransportModels::where('mark_id',$request->mark)->get();
            return response()->json([
                'code' => 0,
                'models' => $model,
            ]);
        }
    }
}
