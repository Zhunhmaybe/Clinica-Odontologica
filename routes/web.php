<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;


//inicio
Route::get('/', function () {
    return view('home.index');
})->name('home');

Route::get('/contacto',function(){
    return view('home.contactos');
})->name('contacto');

Route::get('/servicios',function(){
    return view('home.servicios');
})->name('servicios');

Route::middleware('guest')->group(function(){
    //solo devolver una vizta
    Route::get('/login',[AuthController::class,'showLoginForm'])->name('login');
    //procesar el login
    Route::post('/login',[AuthController::class,'login']);
    Route::get('/register',[AuthController::class,'showRegisterForm'])->name('register');
    Route::post('/register',[AuthController::class,'register']);
});

Route::get('/salir-prueba', [AuthController::class, 'logout']);


Route::middleware(['auth'])->group(function(){
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Route Dispatcher based on Role    
    Route::get('/home', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if($user->hasRole('usuario')){
            return redirect()->route('usuario.index');
        }
        elseif($user->hasRole('')){
            return redirect()->route('usuario.index');
        }
        
        return redirect('/');
    })->name('home');

    // Rutas para cada rol    
    Route::get('/usuario', function() { return view('usuario.index'); })->name('usuario.index');
});