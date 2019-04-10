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
    return view('addLinks');
})->middleware('auth');

Route::get('/addLinks', function(){
    return view('addLinks');
})->name('addLinks')->middleware('auth');

Route::get('showLinks','UrlRequestController@showLinks')->middleware('auth');

Route::post('saveLink','UrlRequestController@saveLink');

Route::get('deleteLink/{id}','UrlRequestController@deleteLink')->middleware('auth');

Route::get('saveLink', function(){
    return view('addLinks');
})->middleware('auth');

Route::get('/changedLinks','UrlRequestController@changedLinks')->middleware('auth');

Route::get('/showDiff/{id}','UrlRequestController@showDiff')->middleware('auth');

Route::get('/abc', function(){
    return view('file34_diff');
});

Auth::routes();