<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\request\PasswordResetController;
use App\Http\Controllers\Actions\ProfileController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\Actions\CitasController;
use App\Http\Controllers\Actions\PacientesController;
use App\Http\Controllers\Actions\HistoriaClinicaController;
use App\Http\Controllers\Actions\AdminController;
use App\Http\Controllers\recepcionista\RecepcionistaController;
use App\Http\Controllers\Actions\DoctorController;
use App\Http\Controllers\Actions\AuditorController;

//inicio
Route::get('/', function () {
    return view('home.index');
})->name('home');

Route::get('/contacto', function () {
    return view('home.contactos');
})->name('contacto');

Route::get('/servicios', function () {
    return view('home.servicios');
})->name('servicios');

Route::middleware('guest')->group(function () {
    //solo devolver una vizta
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    //procesar el login
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

//cerrar sesion de emergencia
Route::get('/salir-prueba', [AuthController::class, 'logout']);

//Rutas protegidas ,requerimiento: tener una sesion iniciada
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Route Dispatcher based on Role    
    Route::get('/home', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('usuario')) {
            return redirect()->route('usuario.index');
        } elseif ($user->hasRole('recepcionista')) {
            return redirect()->route('recepcionista.index');
        } elseif ($user->hasRole('auditor')) {
            return redirect()->route('auditor.index');
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.index');
        } elseif ($user->hasRole('doctor')) {
            return redirect()->route('doctor.index');
        }

        return redirect('/');
    })->name('home');

    // Rutas para cada rol    
    Route::get('/usuario', function () {
        return view('usuario.index');
    })->name('usuario.index');
    
    Route::get('/recepcionista', [RecepcionistaController::class, 'index'])->name('recepcionista.index');
    
    Route::get('/auditor', [AuditorController::class, 'index'])->name('auditor.index');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/doctor', [DoctorController::class, 'index'])->name('doctor.index');

    // Editar perfil
    Route::get('/perfil', [ProfileController::class, 'showProfile'])
        ->name('perfil.index');

    Route::get('/perfil/editar', [ProfileController::class, 'editProfile'])
        ->name('perfil.edit');

    Route::put('/perfil/actualizar', [ProfileController::class, 'updateProfile'])
        ->name('perfil.update');

    // Rutas de perfil para gestionar 2FA
    Route::get('/profile/2fa', [ProfileController::class, 'show2FA'])->name('profile.2fa');
    Route::post('/profile/2fa/enable', [ProfileController::class, 'enable2FA'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/disable', [ProfileController::class, 'disable2FA'])->name('profile.2fa.disable');
    Route::post('/profile/2fa/forget-devices', [ProfileController::class, 'forgetDevices'])->name('profile.2fa.forget-devices');

    //Citas
    Route::middleware(['can:ver-citas'])->group(function () {
        Route::get('/citas', [CitasController::class, 'citasIndex'])->name('citas.index');
    });
    Route::middleware(['can:crear-citas'])->group(function () {
        Route::post('/citas', [CitasController::class, 'citasStore'])->name('citas.store');
    });
    Route::middleware(['can:editar-citas'])->group(function () {
        Route::put('/citas/{cita}', [CitasController::class, 'citasUpdate'])->name('citas.update');
    });

    //Pacientes
    Route::middleware(['can:ver-pacientes'])->group(function () {
        Route::get('/pacientes', [PacientesController::class, 'pacientesIndex'])->name('pacientes.index');
        Route::get('/pacientes/{paciente}/citas', [PacientesController::class, 'pacientesCitas'])->name('pacientes.citas');
        Route::get('/pacientes/{paciente}/historia', [PacientesController::class, 'pacientesHistoria'])->name('pacientes.historia');
    });
    Route::middleware(['can:crear-pacientes'])->group(function () {
        Route::post('/pacientes', [PacientesController::class, 'pacientesStore'])->name('pacientes.store');
        Route::get('/pacientes/crear', [PacientesController::class, 'pacientesCreate'])->name('pacientes.create');
    });
    Route::middleware(['can:editar-pacientes'])->group(function () {
        Route::post('/pacientes', [PacientesController::class, 'pacientesStore'])->name('pacientes.store');
        Route::put('/pacientes/{paciente}', [PacientesController::class, 'pacientesUpdate'])->name('pacientes.update');
    });

    // Historias Clínicas
    Route::middleware(['can:editar-historia'])->group(function () {
        Route::get('/historia-clinica/crear', [HistoriaClinicaController::class, 'create'])->name('historia_clinica.create');
        Route::post('/historia-clinica', [HistoriaClinicaController::class, 'store'])->name('historia_clinica.store');
        Route::get('/historia-clinica/odontograma/{id}', [HistoriaClinicaController::class, 'editarOdontograma'])->name('historia_clinica.odontograma')->whereNumber('id');
        Route::post('/historia-clinica/odontograma/{id}', [HistoriaClinicaController::class, 'guardarOdontograma'])->name('historia_clinica.odontograma.guardar')->whereNumber('id');
        Route::post('/historia-clinica/{id}/diagnostico', [HistoriaClinicaController::class, 'agregarDiagnostico'])->name('historia_clinica.diagnostico.store')->whereNumber('id');
        Route::post('/historia-clinica/{id}/tratamiento', [HistoriaClinicaController::class, 'agregarTratamiento'])->name('historia_clinica.tratamiento.store')->whereNumber('id');
    });

    Route::middleware(['can:ver-historia'])->group(function () {
        Route::get('/historia-clinica', [HistoriaClinicaController::class, 'index'])->name('historia_clinica.index');
        Route::get('/historia-clinica/{id}', [HistoriaClinicaController::class, 'show'])->name('historia_clinica.show')->whereNumber('id');
        Route::get('/historia-clinica/odontograma/{id}/json', [HistoriaClinicaController::class, 'obtenerOdontogramaJSON'])->name('historia_clinica.odontograma.json')->whereNumber('id');
    });

    // Auditoria
    Route::middleware(['can:ver-auditoria'])->group(function () {
        Route::get('/auditoria/{tabla}', [AuditorController::class, 'verTabla'])->name('auditor.tablas.show');
    });
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
//cerrar sesion 
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//Desbloquear cuenta
Route::get('/unlock', [AuthController::class, 'unlockForm'])->name('lock.form');
Route::post('/unlock', [AuthController::class, 'unlock'])->name('lock.verify');

// Rutas 2FA (fuera de guest para que funcionen después del primer login)
Route::get('/2fa/verify', [AuthController::class, 'showTwoFactorForm'])->name('2fa.verify');
Route::post('/2fa/verify', [AuthController::class, 'verifyTwoFactor'])->name('2fa.verify.post');
Route::post('/2fa/resend', [AuthController::class, 'resendTwoFactorCode'])->name('2fa.resend');

//-----------------------------------------------------------Roles y acciones

Route::middleware(['auth', 'role:recepcionista'])->group(function () {});

Route::middleware(['auth', 'role:admin|Super_admin'])->group(function () {
    // Especialidades CRUD
    Route::resource('especialidades', EspecialidadesController::class)->except(['show']);
});

// Gestión de Usuarios (Reutilizable: accesible por cualquier usuario con los permisos otorgados)
Route::middleware(['auth'])->group(function () {
    Route::middleware(['can:ver-usuarios'])->group(function () {
        Route::get('/gestion-usuarios', [AdminController::class, 'usuariosIndex'])->name('admin.usuarios.index');
    });

    Route::middleware(['can:crear-usuarios'])->group(function () {
        Route::post('/gestion-usuarios', [AdminController::class, 'usuariosStore'])->name('admin.usuarios.store');
    });

    Route::middleware(['can:editar-usuarios'])->group(function () {
        Route::put('/gestion-usuarios/{usuario}', [AdminController::class, 'usuariosUpdate'])->name('admin.usuarios.update');
        Route::patch('/gestion-usuarios/{usuario}/toggle', [AdminController::class, 'usuariosToggleEstado'])->name('admin.usuarios.toggle');
    });
});
