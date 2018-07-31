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
Route::post('/register', "ApiController@register");
Route::post('/invitation_code/verify', "ApiController@ApiInvCodeVerify");


Route::middleware(['auth'])->get('/home', function () {
    return view('home');
});

Route::get('/avatar/{username}', function () {
    return \Intervention\Image\Facades\Image::make(storage_path('app/public/user.png'))->response();
});