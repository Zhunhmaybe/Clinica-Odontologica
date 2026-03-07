<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

//inicio
Route::get('/', function () {
    return view('home.index');
});

Route::get('/contacto',function(){
    return view('home.contactos');
})->name('contacto');

Route::get('/servicios',function(){
    return view('home.servicios');
})->name('servicios');

Route::middleware('guest')->group(function(){
    Route::get('/login', [AuthController::class,'showLoginForm'])->name('login');
});