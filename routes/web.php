<?php

use App\Http\Controllers\DocsPoliticasController;
use App\Http\Controllers\EmpleadoControlller;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\VideoController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $fechaActual = date('Y-m-d');

        $noticias = DB::table(DB::raw('noticias'))
        ->selectRaw('*')
        ->where('noticias.activo','=','1')
        ->get();


        $videos = DB::table(DB::raw('videos'))
        ->selectRaw('*')
        ->where('videos.activo','=','1')
        ->get();

        $menus = DB::table(DB::raw('menus'))
        ->selectRaw(
            '*'
        )
        ->where('menus.created_at','LIKE','%'.$fechaActual.'%')
        ->get();
        return Inertia::render('Dashboard', 
        ['menus' => $menus,
         'noticias' => $noticias,
         'videos' => $videos
        ]);
    })->name('dashboard');
});


Route::apiResource('/menu', MenuController::class);
Route::apiResource('/noticia', NoticiaController::class);
Route::apiResource('/video', VideoController::class);
Route::apiResource('/DocsPoliticas', DocsPoliticasController::class);
Route::get('empleados/{activo}', [EmpleadoControlller::class, 'index'])->name('empleado.indexmanual');
Route::get('empleados/create', [EmpleadoControlller::class, 'indexcreate'])->name('empleado.create');