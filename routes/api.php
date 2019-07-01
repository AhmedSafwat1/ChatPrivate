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
Route::get("/changeStatus",function (Request $request){
    $user = \App\User::find($request->user_id);
    $user->online =$request["status"];
    $user->save();
    return response()->json(["value"=>1,"msg"=>"chnge"]);

});
Route::post("/getRom","ChatRoomController@getRom");