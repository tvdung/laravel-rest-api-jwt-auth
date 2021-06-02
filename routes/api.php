<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post("login", "AuthController@login");
Route::post("register", "AuthController@register");
// Password reset link request routes...
Route::get('password/email', 'ForgotPasswordController@showLinkRequestForm')->name('password.email');
Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.request');
Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset');

Route::group(["middleware" => "auth.jwt"], function () {
    Route::get("logout", "AuthController@logout");
    Route::get('user', 'AuthController@getAuthenticatedUser');
    Route::post("updateuser/{id}", "AuthController@updateUser");
    Route::post("changepassword", "AuthController@changePassword");
    Route::get('searchuser', 'AuthController@searchUser');
});
