<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 18.04.2019 14:32
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Geo extends Model
{
    protected $table = 'geo';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'name_uz',
        'name_ru',
        'geometry_type',
        'geometry_cordinates',
        'parent_id',
        'user_id',
        'status',
        'created_date',
        'update_date',

    ];

    protected $casts = [
        'type' =>'string',
        'name_uz' =>'string',
        'name_ru' =>'string',
        'geometry_type' =>'string',
        'geometry_cordinates' =>'string',
        'parent_id' =>'int',
        'user_id' =>'int',
        'status' =>'int',
        'created_date' =>'int',
        'update_date' =>'int',

    ];

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public static function sum($user_id)
    {
        return self::where('user_id',$user_id)->sum('id');
    }

    public static function getSub($biz_id,$cat_id)
    {
        return self::where('category_id',$cat_id)->where('bussiness_id',$biz_id)->get();
    }
}
