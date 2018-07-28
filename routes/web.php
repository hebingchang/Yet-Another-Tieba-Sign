<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return redirect()->to('/login');
});

Route::get('/login', ['as' => 'login', 'uses' => 'ApiController@showLogin']);
Route::post('/login', "ApiController@login");

Route::middleware(['auth'])->get('/home', function () {
    return view('home');
});

Route::group(['middleware' => 'auth', 'prefix' => '/api/v1'], function () {
    Route::get('/user', function (Request $request) {
        return \Illuminate\Support\Facades\Auth::user();
    });
    Route::post('/bduss/bind', "ApiController@ApiBindBDUSS");
    Route::get('/bduss/get', "ApiController@ApiGetBDUSS");
    Route::post('/bduss/delete', "ApiController@ApiDeleteBDUSS");
    Route::post('/forums/get', "ApiController@ApiGetForums");
    Route::post('/forums/update', "ApiController@ApiUpdateForums");
});
