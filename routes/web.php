<?php
use Illuminate\Support\Facades\Route;

//inicio
Route::get('/', function () {
    return view('home.index');
});

Route::get('/contacto',function(){
    return view('home.contactos');
})->name('contacto');
