<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Profesor;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProfesorController extends Controller
{
    protected $folderview      = 'app.profesor';
    protected $tituloAdmin     = 'Profesor';
    protected $tituloRegistrar = 'Registrar profesor';
    protected $tituloModificar = 'Modificar profesor';
    protected $tituloEliminar  = 'Eliminar profesor';
    protected $rutas           = array('create' => 'profesor.create', 
            'edit'   => 'profesor.edit', 
            'delete' => 'profesor.eliminar',
            'search' => 'profesor.buscar',
            'index'  => 'profesor.index',
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
        $entidad          = 'Profesor';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $resultado        = Profesor::join('person','person.id','=','profesor.person_id')
                                ->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'LIKE', '%'.strtoupper($nombre).'%')
                                ->orderBy('person.apellidopaterno', 'ASC')
                                ->select('profesor.*');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Apellidos y Nombres', 'numero' => '1');
        $cabecera[]       = array('valor' => 'DNI', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Direccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Telefono', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Correo', 'numero' => '1');
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
        $entidad          = 'Profesor';
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
        $entidad  = 'Profesor';
        $profesor = null;
        $formData = array('profesor.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('profesor', 'formData', 'entidad', 'boton', 'listar'));
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
        $reglas     = array('nombres' => 'required|max:50');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $profesor = new Profesor();
            $person = Person::where('dni','like',$request->input('dni'))->first();
            if(is_null($person)){
                $person = new Person();
                $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
                $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
                $person->nombres = strtoupper($request->input('nombres'));
                $person->dni = strtoupper($request->input('dni'));
                $person->direccion = strtoupper($request->input('direccion'));
                $person->email = strtoupper($request->input('email'));
                $person->telefono = strtoupper($request->input('telefono'));
                $person->save();
            }else{
                $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
                $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
                $person->nombres = strtoupper($request->input('nombres'));
                $person->dni = strtoupper($request->input('dni'));
                $person->direccion = strtoupper($request->input('direccion'));
                $person->email = strtoupper($request->input('email'));
                $person->telefono = strtoupper($request->input('telefono'));
                $person->save();
            }
            $profesor->person_id = $person->id;
            $profesor->save();
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
        $existe = Libreria::verificarExistencia($id, 'profesor');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $profesor = Profesor::find($id);
        $entidad  = 'Profesor';
        $formData = array('profesor.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('profesor', 'formData', 'entidad', 'boton', 'listar'));
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
        $existe = Libreria::verificarExistencia($id, 'profesor');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombres' => 'required|max:50');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $profesor= Profesor::find($id);
            $person = Person::where('dni','like',$request->input('dni'))->first();
            if(is_null($person)){
                $person = new Person();
                $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
                $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
                $person->nombres = strtoupper($request->input('nombres'));
                $person->dni = strtoupper($request->input('dni'));
                $person->direccion = strtoupper($request->input('direccion'));
                $person->email = strtoupper($request->input('email'));
                $person->telefono = strtoupper($request->input('telefono'));
                $person->save();
            }else{
                $person->apellidopaterno = strtoupper($request->input('apellidopaterno'));
                $person->apellidomaterno = strtoupper($request->input('apellidomaterno'));
                $person->nombres = strtoupper($request->input('nombres'));
                $person->dni = strtoupper($request->input('dni'));
                $person->direccion = strtoupper($request->input('direccion'));
                $person->email = strtoupper($request->input('email'));
                $person->telefono = strtoupper($request->input('telefono'));
                $person->save();
            }
            $profesor->person_id = $person->id;
            $profesor->save();
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
        $existe = Libreria::verificarExistencia($id, 'profesor');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $profesor = Profesor::find($id);
            $profesor->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'profesor');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Profesor::find($id);
        $entidad  = 'Profesor';
        $formData = array('route' => array('profesor.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
