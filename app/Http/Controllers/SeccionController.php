<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Anio;
use App\Especialidad;
use App\Grado;
use App\Seccion;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SeccionController extends Controller
{
    protected $folderview      = 'app.seccion';
    protected $tituloAdmin     = 'Seccion';
    protected $tituloRegistrar = 'Registrar seccion';
    protected $tituloModificar = 'Modificar seccion';
    protected $tituloEliminar  = 'Eliminar seccion';
    protected $rutas           = array('create' => 'seccion.create', 
            'edit'   => 'seccion.edit', 
            'delete' => 'seccion.eliminar',
            'search' => 'seccion.buscar',
            'index'  => 'seccion.index',
        );


     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Mostrar el resultado de búsquedas
     * 
     * @return Response 
     */
    public function buscar(Request $request)
    {
        $anio = Anio::where('situacion','like','A')->first();
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Seccion';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Seccion::where('anio_id','=',$anio->id)
                                ->where('nombre', 'LIKE', '%'.strtoupper($nombre).'%');
        if($request->input('ciclo')!=""){
            $resultado = $resultado->where('grado_id','=',$request->input('ciclo'));
        }
        if($request->input('especialidad')!=""){
            $resultado = $resultado->where('especialidad_id','=',$request->input('especialidad'));
        }
        $lista            = $resultado->orderBy('nombre', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Especialidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ciclo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $ruta             = $this->rutas;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entidad          = 'Seccion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboEspecialidad = array('' => 'Todos');
        $especialidad = Especialidad::orderBy('nombre','asc')->get();
        foreach($especialidad as $k=>$v){
            $cboEspecialidad = $cboEspecialidad + array($v->id => $v->nombre);
        }
        $cboCiclo = array('' => 'Todos');
        $ciclo = Grado::orderBy('nombre','asc')->get();
        foreach($ciclo as $k=>$v){
            $cboCiclo = $cboCiclo + array($v->id => $v->nombre);
        }
        
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboEspecialidad', 'cboCiclo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Seccion';
        $seccion = null;
        $cboEspecialidad = array();
        $especialidad = Especialidad::orderBy('nombre','asc')->get();
        foreach($especialidad as $k=>$v){
            $cboEspecialidad = $cboEspecialidad + array($v->id => $v->nombre);
        }
        $cboCiclo = array();
        $ciclo = Grado::orderBy('nombre','asc')->get();
        foreach($ciclo as $k=>$v){
            $cboCiclo = $cboCiclo + array($v->id => $v->nombre);
        }
        
        $formData = array('seccion.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('seccion', 'formData', 'entidad', 'boton', 'listar', 'cboEspecialidad', 'cboCiclo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array('nombre' => 'required|max:50');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $anio = Anio::where('situacion','like','A')->first();
            $seccion = new Seccion();
            $seccion->nombre = strtoupper($request->input('nombre'));
            $seccion->grado_id = $request->input('grado_id');
            $seccion->especialidad_id = $request->input('especialidad_id');
            $seccion->anio_id = $anio->id;
            $seccion->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'seccion');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $seccion = Seccion::find($id);
        $cboEspecialidad = array();
        $especialidad = Especialidad::orderBy('nombre','asc')->get();
        foreach($especialidad as $k=>$v){
            $cboEspecialidad = $cboEspecialidad + array($v->id => $v->nombre);
        }
        $cboCiclo = array();
        $ciclo = Grado::orderBy('nombre','asc')->get();
        foreach($ciclo as $k=>$v){
            $cboCiclo = $cboCiclo + array($v->id => $v->nombre);
        }
        
        $entidad  = 'Seccion';
        $formData = array('seccion.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('seccion', 'formData', 'entidad', 'boton', 'listar', 'cboEspecialidad', 'cboCiclo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'seccion');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:50');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $anio = Anio::where('situacion','like','A')->first();
            $seccion = Seccion::find($id);
            $seccion->nombre = strtoupper($request->input('nombre'));
            $seccion->grado_id = $request->input('grado_id');
            $seccion->especialidad_id = $request->input('especialidad_id');
            $seccion->anio_id = $anio->id;
            $seccion->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'seccion');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $seccion = Seccion::find($id);
            $seccion->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'seccion');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Seccion::find($id);
        $entidad  = 'Seccion';
        $formData = array('route' => array('seccion.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
