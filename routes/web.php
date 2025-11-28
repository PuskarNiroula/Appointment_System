<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
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
