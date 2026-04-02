<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\request\PasswordResetController;
use App\Http\Controllers\Actions\ProfileController;


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
        elseif($user->hasRole('recepcionista')){
            return redirect()->route('recepcionista.index');
        }
        elseif($user->hasRole('auditor')){
            return redirect()->route('auditor.index');
        }
        elseif($user->hasRole('admin')){
            return redirect()->route('admin.index');
        }
        elseif($user->hasRole('doctor')){
            return redirect()->route('doctor.index');
        }
        
        return redirect('/');
    })->name('home');

    // Rutas para cada rol    
    Route::get('/usuario', function() { return view('usuario.index'); })->name('usuario.index');
    Route::get('/recepcionista',function(){return view('recepcionista.index');})->name('recepcionista.index');
    Route::get('/auditor',function(){return view('auditor.index');})->name('auditor.index');
    Route::get('/admin',function(){return view('admin.index');})->name('admin.index');
    Route::get('/doctor',function(){return view('doctor.index');})->name('doctor.index');

    // Editar perfil
    Route::get('/perfil/editar', [AuthController::class, 'editProfile'])
        ->name('recepcionista.edit');

    Route::put('/perfil/actualizar', [AuthController::class, 'updateProfile'])
        ->name('perfil.update');

    // Rutas de perfil para gestionar 2FA
    Route::get('/profile/2fa', [ProfileController::class, 'show2FA'])->name('profile.2fa');
    Route::post('/profile/2fa/enable', [ProfileController::class, 'enable2FA'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/disable', [ProfileController::class, 'disable2FA'])->name('profile.2fa.disable');

});

//recuperar contrasena
Route::get('/forgot-password', [PasswordResetController::class, 'form'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetController::class, 'send'])
    ->name('password.email');

Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])
    ->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'update'])
    ->name('password.update');

//Desbloquear cuenta
Route::get('/unlock', [AuthController::class, 'unlockForm'])->name('lock.form');
Route::post('/unlock', [AuthController::class, 'unlock'])->name('lock.verify');

// Rutas 2FA (fuera de guest para que funcionen después del primer login)
Route::get('/2fa/verify', [AuthController::class, 'showTwoFactorForm'])->name('2fa.verify');
Route::post('/2fa/verify', [AuthController::class, 'verifyTwoFactor'])->name('2fa.verify.post');
Route::post('/2fa/resend', [AuthController::class, 'resendTwoFactorCode'])->name('2fa.resend');

//-----------------------------------------------------------Roles y acciones

Route::middleware(['auth','role:recepcionista'])->group(function(){
    
});