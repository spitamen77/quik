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
use App\Employees;
use App\models\ClientBlacklist;
use App\models\Regions;
use App\models\Service;
use Illuminate\Support\Facades\Hash;
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
            'regions' => $reg
        ]);
    }


}
