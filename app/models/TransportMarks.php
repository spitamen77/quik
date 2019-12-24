<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 24.12.2019 15:08
 */

namespace App\models;

use Illuminate\Database\Eloquent\Model;


class TransportMarks extends Model
{
    protected $table = 'transport_marks';

    public $timestamps = false;

    protected $fillable = [
        'name',

    ];

    protected $casts = [
        'name' =>'string',

    ];

    public function models()
    {
        return $this->hasMany(TransportModels::class,'mark_id','id');
    }

    public function transports()
    {
        return $this->hasMany(Transports::class,'mark_id','id');
    }
}
