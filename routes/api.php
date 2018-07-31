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
    return Response::json([
        "success" => true,
        "data" => $request->user()
    ]);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post("/logout", "ApiController@logout");

    Route::post('/bduss/bind', "ApiController@ApiBindBDUSS");
    Route::get('/bduss/get', "ApiController@ApiGetBDUSS");
    Route::post('/bduss/delete', "ApiController@ApiDeleteBDUSS");
    Route::post('/forums/get', "ApiController@ApiGetForums");
    Route::post('/forums/update', "ApiController@ApiUpdateForums");
    Route::get('/sign/record/{bduss_id}/{date}', "ApiController@ApiSignRecord");

    Route::get('/bduss/{bduss_id}/sign', "ApiController@ApiBDUSSSign");

    Route::get('/queue/status/{job_id}', "ApiController@ApiJobStatus");
    Route::get('/queue/list/{bduss_id}', "ApiController@ApiListJobs");
    Route::get('/queue/list/{bduss_id}/ongoing', "ApiController@ApiListOngoingJobs");

    Route::post('/password/update', "ApiController@ApiChangePassword");
    Route::get('/invitation_code/get', "ApiController@ApiInvCodeList");
    Route::get('/invitation_code/add', "ApiController@ApiInvCodeAdd");

});