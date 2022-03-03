<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Anio;
use App\Especialidad;
use App\Grado;
use App\Seccion;
use App\Profesor;
use App\Curso;
use App\Asignacioncurso;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AsignacioncursoController extends Controller
{
    protected $folderview      = 'app.asignacioncurso';
    protected $tituloAdmin     = 'Asignacion Curso';
    protected $tituloRegistrar = 'Registrar asignacioncurso';
    protected $tituloModificar = 'Modificar asignacioncurso';
    protected $tituloEliminar  = 'Eliminar asignacioncurso';
    protected $rutas           = array('create' => 'asignacioncurso.create', 
            'edit'   => 'seccion.edit', 
            'delete' => 'seccion.eliminar',
            'search' => 'asignacioncurso.buscar',
            'index'  => 'asignacioncurso.index',
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
        $entidad          = 'Asignacioncurso';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $first            = Curso::join('grado','grado.id','=','curso.grado_id')
                                ->join('especialidad','curso.especialidad_id','=','especialidad.id')
                                ->join('asignacioncurso','asignacioncurso.curso_id','=','curso.id')
                                ->join('seccion','seccion.id','=','asignacioncurso.seccion_id')
                                ->where('seccion.anio_id','=',$anio->id)
                                ->where('curso.anio_id','=',$anio->id)
                                ->where('asignacioncurso.anio_id','=',$anio->id)
                                ->whereNull('asignacioncurso.deleted_at')
                                ->where('asignacioncurso.profesor_id','=',$request->input('profesor'))
                                ->where('curso.especialidad_id','=',$request->input('especialidad'));
        if($request->input('ciclo')!=""){
            $first = $first->where('grado.id','=',$request->input('ciclo'));
        }
        $first = $first->select('asignacioncurso.id','especialidad.nombre as especialidad2','curso.nombre as curso2','curso.codigo','grado.nombre as grado2','seccion.nombre as seccion2','curso.horas','curso.id as curso_id','seccion.id as seccion_id',DB::raw($request->input('profesor').' as profesor_id'));
        
        $resultado            = Curso::join('grado','grado.id','=','curso.grado_id')
                                ->join('especialidad','curso.especialidad_id','=','especialidad.id')
                                ->join('seccion',function($join) use($anio,$request){
                                    $join->on('seccion.anio_id','=',DB::raw($anio->id))
                                        ->on('seccion.especialidad_id','=',DB::raw($request->input('especialidad')))
                                        ->on('seccion.grado_id','=','curso.grado_id');
                                })
                                ->where('seccion.anio_id','=',$anio->id)
                                ->where('curso.anio_id','=',$anio->id)
                                ->where('curso.especialidad_id','=',$request->input('especialidad'))
                                ->whereNotIn('curso.id',function($query) use($anio,$request){
                                    $query->select('curso_id')->from('asignacioncurso')
                                            ->where('anio_id','=',$anio->id)
                                            ->whereNull('deleted_at');
                                });
        if($request->input('ciclo')!=""){
            $resultado = $resultado->where('grado.id','=',$request->input('ciclo'));
        }
        $resultado = $resultado->select(DB::raw('0 as id'),'especialidad.nombre as especialidad2','curso.nombre as curso2','curso.codigo','grado.nombre as grado2','seccion.nombre as seccion2','curso.horas','curso.id as curso_id','seccion.id as seccion_id',DB::raw($request->input('profesor').' as profesor_id'));
         
        $querySql = $resultado->unionAll($first)->toSql();
        $binding  = $resultado->getBindings();
        $resultado = DB::table(DB::raw("($querySql) as a order by especialidad2,grado2,curso2,seccion2 desc"))->addBinding($binding);
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Especialidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ciclo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Curso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Grupo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Horas', 'numero' => '1');
        
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
        $entidad          = 'Asignacioncurso';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboEspecialidad = array();
        $especialidad = Especialidad::orderBy('nombre','asc')->get();
        foreach($especialidad as $k=>$v){
            $cboEspecialidad = $cboEspecialidad + array($v->id => $v->nombre);
        }
        $cboCiclo = array('' => 'Todos');
        $ciclo = Grado::orderBy('nombre','asc')->get();
        foreach($ciclo as $k=>$v){
            $cboCiclo = $cboCiclo + array($v->id => $v->nombre);
        }
        $cboProfesor = array();
        $profesor = Profesor::join('person','person.id','=','profesor.person_id')
                        ->orderBy('person.apellidopaterno','asc')
                        ->select(DB::raw("concat(person.apellidopaterno,' ',person.apellidomaterno,' ',person.nombres) as profesor2"),'profesor.id')
                        ->get();
        foreach($profesor as $k=>$v){
            $cboProfesor = $cboProfesor + array($v->id => $v->profesor2);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboEspecialidad', 'cboCiclo', 'cboProfesor'));
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

    public function asignar(Request $request)
    {
        $error = DB::transaction(function() use($request){
            if($request->input('check')=="false"){
                $asignacioncurso = Asignacioncurso::find($request->input('id'));
                $asignacioncurso->delete();
            }else{
                $anio = Anio::where('situacion','like','A')->first();
                $asignacioncurso = new Asignacioncurso();
                $asignacioncurso->curso_id = $request->input('curso_id');
                $asignacioncurso->seccion_id = $request->input('seccion_id');
                $asignacioncurso->profesor_id = $request->input('profesor_id');
                $asignacioncurso->anio_id = $anio->id;
                $asignacioncurso->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }

}
