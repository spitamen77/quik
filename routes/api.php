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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::group([
//    'prefix' => 'auth'
//], function () {
//    Route::post('login', 'Api\AuthController@login');
//    Route::post('registration', 'Api\AuthController@registration');
//    Route::post('logout', 'Api\AuthController@logout');
//    Route::post('refresh', 'Api\AuthController@refresh');
//    Route::post('me', 'Api\AuthController@me');
//});


Route::group([
    'prefix' => 'v1'
], function () {
    Route::group([
        'prefix' => 'private'
    ], function () {
        Route::post('employee/login', 'Api\EmployeController@login')->middleware('localization');
    });

    Route::group([
        'middleware' => 'auth:employee'
    ], function() {
        Route::group([
            'prefix' => 'private'
        ], function () {
            Route::post('employee', 'Api\EmployeController@registration')->middleware('localization');
            Route::post('employee/logout', 'Api\EmployeController@logout')->middleware('localization');
            Route::put('employee/{id}', 'Api\EmployeController@update')->middleware('localization');
            Route::get('employees', 'Api\EmployeController@list')->middleware('localization');
            Route::get('refresh', 'Api\EmployeController@refresh')->middleware('localization');
            Route::delete('employee/{id}', 'Api\EmployeController@delete')->middleware('localization');
            Route::get('employee/{id}', 'Api\EmployeController@getUser')->middleware('localization');
            Route::post('client', 'Api\EmployeController@storeClient')->middleware('localization');
            Route::get('client/{id}', 'Api\EmployeController@getClient')->middleware('localization');
            Route::get('clients', 'Api\EmployeController@getClients')->middleware('localization');
            Route::put('client/{id}', 'Api\EmployeController@updateClient')->middleware('localization'); //shu PUT bo`lishi kerak edi
            Route::get('regions', 'Api\EmployeController@showRegions')->middleware('localization');
            Route::get('region/{id}', 'Api\EmployeController@getRegion')->middleware('localization');
            Route::post('region', 'Api\EmployeController@storeRegion')->middleware('localization');
            Route::put('region/{id}', 'Api\EmployeController@updateRegion')->middleware('localization');
            Route::delete('region/{id}', 'Api\EmployeController@deleteRegion')->middleware('localization');
        });
    });
});

Route::group([
    'prefix' => 'v1'
], function () {
    Route::post('client', 'Api\ClientsController@createSms')->middleware('localization');
    Route::post('client/code', 'Api\ClientsController@getCode')->middleware('localization');

    Route::group([
        'middleware' => 'auth:client'
    ], function() {

        Route::post('client/logout', 'Api\ClientsController@logout')->middleware('localization');
        Route::put('client/{id}', 'Api\ClientsController@update')->middleware('localization'); //shu PUT bo`lishi kerak edi
        Route::get('client/{id}', 'Api\ClientsController@getClient')->middleware('localization');
        Route::put('client/{id}/change-phone', 'Api\ClientsController@changePhone')->middleware('localization');
        Route::get('refresh', 'Api\ClientsController@refresh')->middleware('localization');
        Route::get('regions', 'Api\ClientsController@showRegions')->middleware('localization');
        Route::get('region/{id}', 'Api\ClientsController@getRegion')->middleware('localization');
    });
});

//Route::group(['middleware' => 'auth:api'], function() {
//    Route::get('articles', 'ArticleController@index');
//    Route::get('articles/{article}', 'ArticleController@show');
//    Route::post('articles', 'ArticleController@store');
//    Route::put('articles/{article}', 'ArticleController@update');
//    Route::delete('articles/{article}', 'ArticleController@delete');
//});

Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact Telegram @phpunit'], 404);
});