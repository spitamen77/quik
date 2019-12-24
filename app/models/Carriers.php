<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 24.12.2019 16:06
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Carriers extends Model
{
    protected $table = 'carriers';

    public $timestamps = false;

    protected $fillable = [
        'called',
        'first_name',
        'last_name',
        'middle_name',
        'birth_day',
        'passport_number',
        'passport_date',
        'passport_until',
        'address',
        'license_number',
        'license_date',
        'license_until',
        'license_class',
        'mobile',
        'phones',
        'data_reg',
        'region_id',
        'transport_id',

    ];

    protected $casts = [
        'called'=>'int',
        'first_name'=>'string',
        'last_name'=>'string',
        'middle_name'=>'string',
        'birth_day'=>'string',
        'passport_number'=>'string',
        'passport_date'=>'string',
        'passport_until'=>'string',
        'address'=>'string',
        'license_number'=>'string',
        'license_date'=>'string',
        'license_until'=>'string',
        'license_class'=>'string',
        'mobile'=>'string',
        'phones'=>'string',
        'data_reg'=>'int',
        'region_id'=>'int',
        'transport_id'=>'int',

    ];

    public function transport()
    {
        return $this->hasOne(Transports::class,'id','transport_id');
    }

}