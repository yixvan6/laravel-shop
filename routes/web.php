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

Route::get('/', 'PagesController@root')->name('root');

Auth::routes(['verify' => true]);

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('user/addresses', 'UserAddressesController@index')->name('user.addresses.index');
    Route::get('user/addresses/create', 'UserAddressesController@create')->name('user.addresses.create');
    Route::post('user/addresses', 'UserAddressesController@store')->name('user.addresses.store');
});