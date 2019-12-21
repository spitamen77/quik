<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 21.12.2019 16:39
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Regions extends Model
{
    protected $table = 'regions';

    public $timestamps = false;

    protected $fillable = [
        'name_uz',
        'name_ru',
        'coordinates',
    ];

    protected $casts = [
        'name_uz' =>'string',
        'name_ru' =>'string',
        'coordinates' =>'string',
    ];


}