<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
 */

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
 */

Route::group(['middleware' => ['api'], 'prefix' => 'api1'], function () {
    //
    Route::get('/home', ['middleware' => 'auth:api', function () {
        var_dump("expression");
        dd(Auth::user());
    }]);

    Route::post('/sign/out', [
        'as'   => 'api1.sigin.out',
        'uses' => 'Api1\SignController@out',
    ]);

    Route::post('/sign/up', [
        'as'   => 'api1.sigin.up',
        'uses' => 'Api1\SignController@up',
    ]);

    Route::post('/sign/in', [
        'as'   => 'api1.sigin.in',
        'uses' => 'Api1\SignController@in',
    ]);

    Route::auth();

});

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    Route::get('/home', 'HomeController@index');
});
