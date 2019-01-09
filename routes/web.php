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

Route::get('/', 'PagesController@root');

Auth::routes(['verify' => true]);

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('user/addresses', 'UserAddressesController@index')->name('user.addresses.index');
    Route::get('user/addresses/create', 'UserAddressesController@create')->name('user.addresses.create');
    Route::post('user/addresses', 'UserAddressesController@store')->name('user.addresses.store');
    Route::get('user/addresses/{address}', 'UserAddressesController@edit')->name('user.addresses.edit');
    Route::put('user/addresses/{address}', 'UserAddressesController@update')->name('user.addresses.update');
    Route::delete('user/addresses/{address}', 'UserAddressesController@destroy')->name('user.addresses.destroy');

    // 收藏商品
    Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
    Route::get('products/favorites', 'ProductsController@favoritesIndex')->name('products.favorites');
});

Route::redirect('/', '/products')->name('root');
Route::get('products', 'ProductsController@index')->name('products.index');
Route::get('products/{product}', 'ProductsController@show')->name('products.show')
    ->where('product', '\d+');
