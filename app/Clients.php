<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://www.websar.uz
 * Project: quik
 * Date: 17.12.2019 11:20
 */

namespace App;

use App\models\Sms;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Clients extends Authenticatable implements JWTSubject
{
    use Notifiable;

//    protected $table = 'geo';
    protected $fillable = [
        'mobile',
        'telegram_id',
        'first_name',
        'last_name',
        'gender',
        'data_birthday',
        'photo',
        'language',
        'registration_platform',
        'last_region',
        'last_visit',
        'black_list',
        'status',
        'created_at',
        'updated_at',

    ];

    protected $casts = [
        'gender' =>'string',
        'first_name' =>'string',
        'last_name' =>'string',
        'photo' =>'string',
        'language' =>'string',
        'mobile' =>'int',
        'telegram_id' =>'int',
        'status' =>'int',
        'data_birthday' =>'int',
        'registration_platform' =>'string',
        'last_region' =>'int',
        'last_visit' =>'int',
        'black_list' =>'int',
    ];

    public function sms()
    {
        return $this->hasOne(Sms::class,'sms_id','id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
