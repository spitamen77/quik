<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 17.12.2019 17:21
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class ConfirmClient extends Model
{
    protected $table = 'confirm_client';

    public $timestamps = false;

    protected $fillable = [
        'mobile',
        'code',
        'created_at',
//        'status',
    ];

    protected $casts = [
        'mobile' =>'string',
        'code' =>'int',
        'created_at' =>'int'
    ];


}