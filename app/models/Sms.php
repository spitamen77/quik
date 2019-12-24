<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 17.12.2019 15:06
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Clients;

class Sms extends Model
{
    protected $table = 'sms';

    public $timestamps = false;

    protected $fillable = [
        'to_phone',
        'from_method',
        'text',
        'status'
    ];

    protected $casts = [
        'to_phone' =>'int',
        'from_method' =>'string',
        'text' =>'string',
        'status' =>'int'
    ];
}
