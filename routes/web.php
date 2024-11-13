<?php

use App\Http\Controllers\PelangganController;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('pelanggans', PelangganController::class)->middleware('auth');
Route::put('/updateExcel/{pelanggan}', [PelangganController::class, 'updateExcel'])->name('pelanggans.updateExcel')->middleware('auth');
