<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Cuenta;
use App\Banco;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CuentaController extends Controller
{
    protected $folderview      = 'app.cuenta';
    protected $tituloAdmin     = 'Cuenta';
    protected $tituloRegistrar = 'Registrar cuenta';
    protected $tituloModificar = 'Modificar cuenta';
    protected $tituloEliminar  = 'Eliminar cuenta';
    protected $rutas           = array('create' => 'cuenta.create', 
            'edit'   => 'cuenta.edit', 
            'delete' => 'cuenta.eliminar',
            'search' => 'cuenta.buscar',
            'index'  => 'cuenta.index',
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
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Cuenta';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Cuenta::join('banco','banco.id','=','cuenta.banco_id')->where('numero', 'LIKE', '%'.strtoupper($nombre).'%')->orderBy('banco.nombre','ASC')->orderBy('cuenta.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Banco', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Moneda', 'numero' => '1');
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
        $entidad          = 'Cuenta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Cuenta';
        $cuenta = null;
        $cboBanco = array();
        $banco = Banco::orderBy('nombre','asc')->get();
        foreach($banco as $k=>$v){
            $cboBanco = $cboBanco + array($v->id => $v->nombre);
        }
        $cboMoneda = array('SOLES'=>'SOLES','DOLARES'=>'DOLARES');
        $formData = array('cuenta.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('cuenta', 'formData', 'entidad', 'boton', 'listar', 'cboBanco', 'cboMoneda'));
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
        $reglas     = array('numero' => 'required|max:50');
        $mensajes = array(
            'numero.required'         => 'Debe ingresar un numero'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $cuenta = new Cuenta();
            $cuenta->numero= strtoupper($request->input('numero'));
            $cuenta->banco_id = $request->input('banco_id');
            $cuenta->moneda = $request->input('moneda');
            $cuenta->save();
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
        $existe = Libreria::verificarExistencia($id, 'cuenta');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $cuenta = Cuenta::find($id);
        $entidad  = 'Cuenta';
        $cboBanco = array();
        $banco = Banco::orderBy('nombre','asc')->get();
        foreach($banco as $k=>$v){
            $cboBanco = $cboBanco + array($v->id => $v->nombre);
        }
        $cboMoneda = array('SOLES'=>'SOLES','DOLARES'=>'DOLARES');
        $formData = array('cuenta.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('cuenta', 'formData', 'entidad', 'boton', 'listar', 'cboBanco', 'cboMoneda'));
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
        $existe = Libreria::verificarExistencia($id, 'cuenta');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('numero' => 'required|max:50');
        $mensajes = array(
            'numero.required'         => 'Debe ingresar un numero'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $cuenta = Cuenta::find($id);
            $cuenta->numero= strtoupper($request->input('numero'));
            $cuenta->banco_id = $request->input('banco_id');
            $cuenta->moneda = $request->input('moneda');
            $cuenta->save();
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
        $existe = Libreria::verificarExistencia($id, 'cuenta');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $cuenta = Cuenta::find($id);
            $cuenta->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'cuenta');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Cuenta::find($id);
        $entidad  = 'Cuenta';
        $formData = array('route' => array('cuenta.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
}
