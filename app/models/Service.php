<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 23.12.2019 16:36
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'service';

    public $timestamps = false;

    protected $fillable = [
        'name_uz',
        'name_ru',
        'description_uz',
        'description_ru',
        'position',
        'slug',
    ];

    protected $casts = [
        'name_uz' =>'string',
        'name_ru' =>'string',
        'description_uz' =>'string',
        'description_ru' =>'string',
        'position' =>'int',
        'slug' =>'string',
    ];


}