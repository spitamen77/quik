<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 25.12.2019 12:56
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'region_id',
        'client_id',
        'creator_type',
        'creator_id',
        'create_date',
        'status_id',

    ];

    protected $casts = [
        'service_id'=>'int',
        'region_id'=>'int',
        'client_id'=>'int',
        'creator_type'=>'int',
        'creator_id'=>'int',
        'create_date'=>'int',
        'status_id'=>'int',

    ];

    public function service()
    {
        return $this->hasOne(Service::class,'id','service_id');
    }

    public function region()
    {
        return $this->hasOne(Regions::class,'id','region_id');
    }

    public function status()
    {
        return $this->hasOne(Status::class,'id','status_id');
    }

}