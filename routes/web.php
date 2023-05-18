<?php

use App\Http\Controllers\ActivoController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\DepartamentosAuditoriaController;
use App\Http\Controllers\DocsPoliticasController;
use App\Http\Controllers\DocumentosCalificacionMesController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\LocalidadesController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\OrganigramaController;
use App\Http\Controllers\PlantillasAutorizadaController;
use App\Http\Controllers\PoliticController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\RecibosNominaController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use App\Models\DepartamentosAuditoria;
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
})->name('inicio')->middleware('guest');
Route::middleware('guest')->name('password.reset.')->group(function () {
    Route::get('/reset-password-fisrt/{token}', [ResetPasswordController::class, 'create'])->name('first');
    Route::post('/reset-password-fisrt', [ResetPasswordController::class, 'update'])->name('first.update');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $fechaActual = date('Y-m-d');

        $noticias = DB::table(DB::raw('noticias'))
            ->selectRaw(
                'noticias.id AS id,
              noticias.titulo AS titulo,
              noticias.image AS image,
              noticias.activo AS activo,
              noticia_descrs.descripcion AS descripcion,
              noticia_descrs.image AS mas'
            )
            ->join('noticia_descrs', 'noticia_descrs.noticia_id', 'noticias.id')
            ->where('noticias.activo', '=', '1')
            ->limit(3)
            ->get();


        $videos = DB::table(DB::raw('videos'))
            ->selectRaw('*')
            ->where('videos.activo', '=', '1')
            ->get();

        $menus = DB::table(DB::raw('menus'))
            ->selectRaw(
                '*'
            )
            ->where('menus.created_at', 'LIKE', '%' . $fechaActual . '%')
            ->get();

        $nominas = DB::table('nominas_empleados')->where('empleado_id', auth()->user()->id)->orderByDesc('fecha_doc')->orderByDesc('periodo')->paginate(5);
        return Inertia::render(
            'Dashboard',
            [
                'menus' => $menus,
                'noticias' => $noticias,
                'videos' => $videos,
                'nominas' => $nominas
            ]
        );
    })->name('dashboard');

    //Reenvio de correo de bienvenida
    Route::post('/welcome-password/{user}', [ResetPasswordController::class, 'store'])->name('welcome.password.first');

    Route::get('/control-interno/departamentos-aditorias', [DepartamentosAuditoriaController::class, 'index'])
        ->name('control-interno.departamentos-aditorias.index');

    Route::post('departamentos-aditorias/{departamentosAuditoria}/calificacion', [DepartamentosAuditoriaController::class, 'storeCalificacion'])
        ->name('departamentos-aditorias.calificacion.store')->middleware('can:calificacion.create');
    Route::delete('documentos-calificacion-mes/{documentosCalificacionMes}', [DocumentosCalificacionMesController::class, 'destroy'])
        ->name('documentos-calificacion-mes.destroy')->middleware('can:calificacion.delete');
    Route::post('/storeProceso', [DepartamentosAuditoriaController::class, 'storeProceso'])->name('storeProceso');
    Route::post('/storeDocumento', [DepartamentosAuditoriaController::class, 'storeDocumento'])->name('storeDocumento');
    Route::post('/updateDocumento/{idDocumento}', [DepartamentosAuditoriaController::class, 'updateDocumento'])->name('updateDocumento');
    Route::get('/getDocumentos/{proceco_id}', [DepartamentosAuditoriaController::class, 'getDocumentos'])->name('getDocumentos');
    Route::delete('documentos-mes/{documento}', [DepartamentosAuditoriaController::class, 'destroyDocumento'])->name('documentos-mes.destroy');
    Route::post('/storeRubro', [DepartamentosAuditoriaController::class, 'storeRubro'])->name('storeRubro');
    Route::get('/getRubros/{proceso_id}/{año}', [DepartamentosAuditoriaController::class, 'getRubros'])->name('getRubros');
    Route::post('/updateRubro/{rubro_id}', [DepartamentosAuditoriaController::class, 'updateRubro'])->name('updateRubro');
    Route::post('/storeCalf', [DepartamentosAuditoriaController::class, 'storeCalf'])->name('storeCalf');
    Route::get('/recuperarRubro/{categoria}/{mes}/{año}', [DepartamentosAuditoriaController::class, 'recuperarRubros'])->name('recuperarRubros');
    Route::get('/getAnterioresRubros/{departamento}/{mes}/{año}', [DepartamentosAuditoriaController::class, 'getRubrosAnterior'])->name('getRubrosAnterior');

    Route::apiResource('politics', PoliticController::class)->name('index', 'control-interno.politics.index');
    //Route::get('/docinternos', [PoliticController::class, 'docsinternos'])->name('control-interno.documentos-internos.index');
    Route::get('/docinternos/seccion/{seccion}', [PoliticController::class, 'docsinternos'])->name('documentos-internos.socios.index');

    Route::get('users/list', [UserController::class, 'list'])->name('users.list');
    Route::apiResource('roles', RoleController::class)->middleware('can:roles.manager');
    Route::get('role/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::put('role/{role}/permissions', [RoleController::class, 'setPermission'])->name('roles.permissions');

    Route::apiResource('/menu', MenuController::class);
    Route::apiResource('/noticia', NoticiaController::class);
    Route::apiResource('/video', VideoController::class);




    Route::apiResource('/puestos', PuestoController::class);
    Route::get('/departamentos/{departamento}/list-puestos', [DepartamentoController::class, 'listPuestoDep'])->name('departamento.puestos.list');
    Route::get('/departamentos/{departamento}/puestos', [DepartamentoController::class, 'puestosIndex'])->name('departamento.puestos.index');
    Route::put('/departamentos/{departamento}/puestos', [DepartamentoController::class, 'puestosUpdate'])->name('departamento.puestos.update')
        ->middleware('can:departamentos.update');
    Route::apiResource('/departamentos', DepartamentoController::class)->except('destroy');

    Route::post('ubicaciones', [UbicacionController::class, 'store'])->name('ubicaciones.store')->middleware('can:ubicaciones.create');
    Route::match(['put', 'patch'], 'ubicaciones/{ubicacion}', [UbicacionController::class, 'update'])->name('ubicaciones.update')->middleware('can:ubicaciones.update');

    Route::apiResource('plantillas-autorizadas', PlantillasAutorizadaController::class, [
        'names' => 'rh.plantillas-autorizadas'
    ])->except('destroy');

    Route::controller(EmpleadoController::class)->group(function () {
        Route::post('empleados/store', 'store')->name('empleado.store')->middleware('can:user-activos.create');
        Route::get('empleados/create', 'createNewEmpleado')->name('empleado.create')->middleware('can:user-activos.create');
        Route::get('/empleados/edit/{id}', 'edit')->name('empleado.edit');
        Route::post('empleados/{empleado}/update', 'update')->name('empleados.update');
        Route::get('empleados/{activo}', 'index')->name('empleado.indexmanual');
        Route::post('empleados/{empleado}/expediente', 'storeExpediente')->name('empleado.expediente')->middleware('can:user-activos.create');
        Route::get('/empleadosData', 'empleadosData')->name('empleadosData');
    });
});


Route::controller(ActivoController::class)->group(function () {
    Route::get('activos/index', 'index')->name('activo.index');
    Route::get('/activosxtipo/{idTipo}', 'activos');
    Route::post('/storeCategory', 'storeCategory')->name('storeCategory');
    Route::post('/storeItem', 'storeItem')->name('storeItem');
    Route::post('/storeTipoEvidencia', 'storeTipoEvidencia')->name('storeTipoEvidencia');
    Route::get('/getTipoEvidencia', 'getTipoEvidencia')->name('getTipoEvidencia');
    Route::post('/EvidenciaActivoUser', 'EvidenciaActivoUser')->name('EvidenciaActivoUser');
    Route::get('/getImages/{id}/{activoid}', 'getImages')->name('getImages');
    Route::get('/getInfoEmpleado/{id}', 'getInfoEmpleado')->name('getInfoEmpleado');
    Route::post('/changeStatusActivoEmpleado/{id}/{activo_id}', 'changeStatusActivoEmpleado')->name('changeStatusActivoEmpleado');
    Route::post('/changeStatusActivoItem/{id}', 'changeStatusActivoItem')->name('changeStatusActivoItem');
    Route::get('/getAllCampos/{id}', 'getAllCampos')->name('getAllCampos');
    Route::post('/saveEditCampos/{id}', 'saveEditCampos')->name('saveEditCampos');
    Route::post('/saveEvidencias', 'saveEvidencias')->name('saveEvidencias');
    Route::post('/changeStatusActivoItemLibre/{id}', 'changeStatusActivoItemLibre')->name('changeStatusActivoItemLibre');

    Route::get('/valorCampo/{id}', 'valorCampo')->name('valorCampo');
    Route::post('/storeColum', 'storeColum')->name('storeColum');
    Route::post('/updateColum', 'updateColum')->name('updateColum');
    Route::get('/getCampos/{idCampo}/{tipoActivo}', 'getCampos')->name('getCampos');
    Route::get('/columnasxCampo/{campo}/{idActivo}', 'columasCampos')->name('columnas.campo');

    Route::get('/columnasxCampoFila/{campo}/{idActivo}/{fila}', 'columasCamposFila')->name('columnas.campo.fila');

    Route::post('/newFila', 'newFila')->name('newFila');

    Route::post('/saveNewValorColum', 'saveNewValorColum')->name('new.valor.campo');
});



Route::apiResource('/DocsPoliticas', DocsPoliticasController::class);

Route::get('/catalogos/formulario/empelado', [CatalogoController::class, 'getEstados'])->name('catalogos.estados'); //ruta para los diferentes catalogos
Route::get('/estados/{estado}/municipios', [MunicipioController::class, 'getMunicipiosEstado'])->name('municipos.estado');
Route::get('/municipios/{municipio}/localidades', [LocalidadesController::class, 'getLocalidades'])->name('localidades.municipio');




Route::get('users/export/{activo}', [UserController::class, 'export'])->name('export.empleados');

Route::get('card/user/{numero_empleado}', [UserController::class, 'viewCard'])->name('view.card');

Route::get('user/puesto/{id}', [UserController::class, 'getPuesto'])->name('getPuesto');

Route::controller(RecibosNominaController::class)->group(function () {
    Route::post('nominas/post', 'store')->name('nomina.upload');
});

Route::controller(OrganigramaController::class)->group(function () {
    Route::get('/organigrama', 'index')->name('organigrama.index');
    Route::post('/organigrama/relacion', 'relacion')->name('organigrama.relacion');
    Route::post('/organigrama/destroy', 'destroy')->name('organigrama.destroy');
    Route::post('/organigrama/area', 'area')->name('organigrama.area');
    Route::post('/organigrama/remove', 'remove')->name('organigrama.remove');
});
Route::controller(AreaController::class)->group(function () {
    Route::post('area/addArea', 'store')->name('area.addArea');
    Route::post('area/relacion', 'relacion')->name('area.relacion');
    Route::post('area/destroy', 'destroy')->name('area.destroy');
});
