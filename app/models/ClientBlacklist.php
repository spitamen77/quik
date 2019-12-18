<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 18.12.2019 17:53
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class ClientBlacklist extends Model
{
    protected $table = 'client_blacklist';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'note',
        'created_at',
//        'status',
    ];

    protected $casts = [
        'note' =>'string',
        'client_id' =>'int',
        'created_at' =>'int'
    ];


}