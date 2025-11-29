<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::controller(HomeController::class)->group(function () {
    Route::get('/dashboard', 'dashboard')->name('dashboard');
});

Route::controller(PostController::class)->group(function () {
    Route::get('/posts', 'index')->name('posts.index');
    Route::get('/posts/create', 'create')->name('post.create');
    Route::post('/posts/create', 'store')->name('post.store');
    Route::get('/posts/edit/{id}', 'edit')->name('post.edit');
    Route::PUT('/posts/{id}/update', 'update')->name('post.update');
    Route::get('/posts-api','getPosts')->name('posts.api');
    Route::patch('/posts/{post}/activate','activate')->name('post.activate');
    Route::patch('/posts/{post}/deactivate','deactivate')->name('post.deactivate');
});


Route::controller(VisitorController::class)->group(function () {
    Route::get('/visitors', 'index')->name('visitors.index');
    Route::get('/get-visitor-api','getVisitors')->name('visitors.api');
    Route::get('/visitor/{id}/edit','edit')->name('visitor.edit');
    Route::put('/visitor/{visitor}/update','update')->name('visitor.update');
    Route::patch('/visitor/{visitor}/activate','activate')->name('visitor.activate');
    Route::patch('/visitor/{visitor}/deactivate','deactivate')->name('visitor.deactivate');
    Route::get('/visitor/create','create')->name('visitor.create');
    Route::post('/visitor/create','store')->name('visitor.store');
});

Route::controller(\App\Http\Controllers\OfficerController::class)->group(function (){
    Route::get('/officers','index')->name('officers.index');
    Route::get('/officers-api','getOfficers')->name('officers.api');
    Route::get('/officer/{id}/edit','edit')->name('officer.edit');
    Route::put('/officer/{officer}/update','update')->name('officer.update');
    Route::patch('/officer/{officer}/activate','activate')->name('officer.activate');
    Route::patch('/officer/{officer}/deactivate','deactivate')->name('officer.deactivate');
    Route::get('/officer/create','create')->name('officer.create');
    Route::post('/officer/create','store')->name('officer.store');

});
