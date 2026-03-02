<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\EtlController;
use Illuminate\Support\Facades\Route;

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

// Route::get('/dashboard', function () {
//     return view('admin/dashboard');
// });

// Route::get('/upload_data', function () {
//     return view('admin/upload/upload_data');
// });

// Route::get('/', function () {
//     return view('auth/login');
// });

Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'authLogin'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tambah_data', [DataController::class, 'tambah_data'])->name('tambah_data');
    Route::post('/simpan-data', [DataController::class, 'simpan_data'])->name('simpan.data');
    Route::get('/chart-data', [DashboardController::class, 'get_data_bbm_produksi'])->name('chart_data');
    Route::get('/chart-data2', [DashboardController::class, 'pendapatan_vs_biaya'])->name('chart_data2');
    Route::get('/chart-data3', [DashboardController::class, 'laba_per_bulan'])->name('chart_data3');
    Route::get('/chart-data-pie', [DashboardController::class, 'get_data_aktivitas'])->name('chart_data_aktivitas');
    Route::get('/upload-data', [DataController::class, 'uploadData'])->name('upload_data');
    Route::post('/upload-file', [DataController::class, 'uploadFile'])->name('upload_file');
    Route::get('/update-data', [DataController::class, 'updateData'])->name('update_data');
    Route::post('/update-file', [DataController::class, 'updateFile'])->name('update_file');
    Route::get('/tampil-etl', [EtlController::class, 'index'])->name('tampil_etl');
    Route::post('/etl-preview', [ETLController::class, 'preview'])->name('etl.preview');
    Route::post('/etl-preview2', [ETLController::class, 'preview2'])->name('etl.preview2');
    Route::post('/etl-save', [ETLController::class, 'save'])->name('etl.save');
    Route::post('/etl-save2', [ETLController::class, 'save2'])->name('etl.save2');
    Route::put('/update-password', [AuthController::class, 'updatePassword'])->name('update.password');
    Route::get('/get_pendapatan/{year}', [DashboardController::class, 'getPendapatan'])->name('get_pendapatan');
    Route::get('/get_pendapatan2/{year}', [DashboardController::class, 'getPendapatan2'])->name('get_pendapatan2');
    Route::get('/get_produksi/{year}', [DashboardController::class, 'getProduksi'])->name('get_Produksi');
    Route::get('/get_produksi2/{year}', [DashboardController::class, 'getProduksi2'])->name('get_Produksi2');
    Route::get('/edit-data/{id}', [DataController::class, 'editData'])->name('edit.data');
    Route::post('/update-data/{id}', [DataController::class, 'updateDataAction'])->name('update.data.action');
});
