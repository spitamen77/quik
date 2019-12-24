<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 24.12.2019 15:07
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class TransportModels extends Model
{
    protected $table = 'transport_models';

    public $timestamps = false;

    protected $fillable = [
        'mark_id',
        'name',

    ];

    protected $casts = [
        'mark_id' =>'int',
        'name' =>'string',

    ];

    public function marks()
    {
        return $this->hasOne(TransportMarks::class,'id','mark_id');
    }

    public function models()
    {
        return $this->hasMany(TransportModels::class,'mark_id','id');
    }
}
