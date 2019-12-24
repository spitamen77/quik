<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 24.12.2019 15:05
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Transports extends Model
{
    protected $table = 'transport';

    public $timestamps = false;

    protected $fillable = [
        'mark_id',
        'model_id',
        'number',

    ];

    protected $casts = [
        'mark_id' =>'int',
        'model_id' =>'int',
        'number' =>'string',

    ];

    public function marks()
    {
        return $this->hasOne(TransportMarks::class,'id','mark_id');
    }

    public function models()
    {
        return $this->hasOne(TransportModels::class,'id','model_id');
    }
}
