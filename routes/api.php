<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('registration', 'Api\AuthController@registration');
    Route::post('logout', 'Api\AuthController@logout');
    Route::post('refresh', 'Api\AuthController@refresh');
    Route::post('me', 'Api\AuthController@me');
});


Route::group([
    'prefix' => 'employees'
], function () {
    Route::post('login', 'Api\EmployeController@login')->middleware('localization');

    Route::group([
        'middleware' => 'auth:employee'
    ], function() {
        Route::post('create', 'Api\EmployeController@registration')->middleware('localization');
        Route::post('logout', 'Api\EmployeController@logout')->middleware('localization');
        Route::put('update', 'Api\EmployeController@update')->middleware('localization');
        Route::get('list', 'Api\EmployeController@list')->middleware('localization');
        Route::delete('{id}', 'Api\EmployeController@delete')->middleware('localization');
        Route::post('{id}', 'Api\EmployeController@getUser')->middleware('localization');
    });
});

Route::group([
    'prefix' => 'clients'
], function () {
    Route::post('login', 'Api\EmployeController@login')->middleware('localization');

    Route::group([
        'middleware' => 'auth:client'
    ], function() {
        Route::post('create', 'Api\EmployeController@registration')->middleware('localization');
        Route::post('logout', 'Api\EmployeController@logout')->middleware('localization');
        Route::put('update', 'Api\EmployeController@update')->middleware('localization');
        Route::get('list', 'Api\EmployeController@list')->middleware('localization');
        Route::delete('{id}', 'Api\EmployeController@delete')->middleware('localization');
        Route::post('{id}', 'Api\EmployeController@getUser')->middleware('localization');
    });
});

Route::group([
    'prefix' => 'users'
], function () {
    Route::get('index', 'Api\UserController@index');
    Route::get('data', 'Api\UserController@data');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::post('update', 'Api\UserController@update');
        Route::post('password-change', 'Api\UserController@passwordChange');
        Route::post('password-old', 'Api\UserController@passwordOld');
        Route::get('company', 'Api\UserController@product');
        Route::get('getuser', 'Api\UserController@getUser');
        Route::get('favorite', 'Api\CompanyController@myFavorites');
        Route::post('favorite-add', 'Api\UserController@addFavorite');
        Route::get('favorite-delete', 'Api\UserController@delFavorite');
        Route::get('service', 'Api\UserController@userBusiness');  // buni tekshirishim kerak
    });
});

Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact Telegram @phpunit'], 404);
});