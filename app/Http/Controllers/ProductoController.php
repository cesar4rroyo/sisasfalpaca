<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Producto;
use App\Unidad;
use App\Categoria;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    protected $folderview      = 'app.producto';
    protected $tituloAdmin     = 'Productos / Servicios';
    protected $tituloRegistrar = 'Registrar Producto/Servicio';
    protected $tituloModificar = 'Modificar Producto/Servicio';
    protected $tituloEliminar  = 'Eliminar Producto/Servicio';
    protected $rutas           = array('create' => 'producto.create', 
            'edit'   => 'producto.edit', 
            'delete' => 'producto.eliminar',
            'search' => 'producto.buscar',
            'index'  => 'producto.index',
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
        $entidad          = 'Producto';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $codigobarra      = Libreria::getParam($request->input('codigobarra'));
        $resultado        = Producto::join('unidad','unidad.id','=','producto.unidad_id')
                                ->leftjoin('categoria','categoria.id','=','producto.categoria_id')
                                ->where('producto.nombre','like','%'.strtoupper($nombre).'%')
                                ->whereIn('producto.tipo',['P','S']);
        if($request->input('categoria')!=""){
            $resultado = $resultado->where('categoria.id','=',$request->input('categoria'));
        }
        if($request->input('tipo')!=""){
            $resultado = $resultado->where('producto.tipo','like',$request->input('tipo'));
        }
        $resultado = $resultado->orderBy('producto.nombre','asc')
                            ->select('producto.*','categoria.nombre as categoria2','unidad.nombre as unidad2');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Producto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Categoria', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Unidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'P. Compra', 'numero' => '1');
        $cabecera[]       = array('valor' => 'P. Venta', 'numero' => '1');
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

    public function buscar2(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Producto';
        $nombre           = Libreria::getParam($request->input('nombre'));
        $codigobarra      = Libreria::getParam($request->input('codigobarra'));
        $resultado        = Producto::join('unidad','unidad.id','=','producto.unidad_id')
                                ->leftjoin('categoria','categoria.id','=','producto.categoria_id')
                                ->where('producto.nombre','like','%'.strtoupper($nombre).'%')
                                ->whereIn('producto.tipo',['L']);
        if($request->input('categoria')!=""){
            $resultado = $resultado->where('categoria.id','=',$request->input('categoria'));
        }
        if($request->input('tipo')!=""){
            $resultado = $resultado->where('producto.tipo','like',$request->input('tipo'));
        }
        $resultado = $resultado->orderBy('producto.nombre','asc')
                            ->select('producto.*','categoria.nombre as categoria2','unidad.nombre as unidad2');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Producto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Categoria', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Unidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'P. Compra', 'numero' => '1');
        $cabecera[]       = array('valor' => 'P. Venta', 'numero' => '1');
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
        $entidad          = 'Producto';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboCategoria = array('' => 'Todos');
        $categoria = Categoria::orderBy('nombre','asc')->get();
        foreach($categoria as $k=>$v){
            $cboCategoria = $cboCategoria + array($v->id => $v->nombre);
        }
        $cboTipo = array('' => 'Todos', 'P' => 'Productos', 'S' => 'Servicios');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboCategoria', 'cboTipo'));
    }

    public function index2()
    {
        $entidad          = 'Producto';
        $title            = 'Productos Logistica';
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboCategoria = array('' => 'Todos');
        $categoria = Categoria::orderBy('nombre','asc')->get();
        foreach($categoria as $k=>$v){
            $cboCategoria = $cboCategoria + array($v->id => $v->nombre);
        }
        $cboTipo = array('' => 'Todos', 'L' => 'Logistica');
        return view($this->folderview.'.admin2')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboCategoria', 'cboTipo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Producto';
        $producto = null;
        $cboCategoria = array('0' => 'Ninguna');
        $categoria = Categoria::orderBy('nombre','asc')->get();
        foreach($categoria as $k=>$v){
            $cboCategoria = $cboCategoria + array($v->id => $v->nombre);
        }        
        $cboUnidad = array();
        $unidad = Unidad::orderBy('nombre','asc')->get();
        foreach($unidad as $k=>$v){
            $cboUnidad = $cboUnidad + array($v->id => $v->nombre);
        }
        $cboTipo = array('P' => 'Productos', 'S' => 'Servicios');
        $formData = array('producto.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('producto', 'formData', 'entidad', 'boton', 'listar', 'cboUnidad', 'cboTipo', 'cboCategoria'));
    }

    public function create2(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Producto';
        $producto = null;
        $cboCategoria = array('0' => 'Ninguna');
        $categoria = Categoria::orderBy('nombre','asc')->get();
        foreach($categoria as $k=>$v){
            $cboCategoria = $cboCategoria + array($v->id => $v->nombre);
        }        
        $cboUnidad = array();
        $unidad = Unidad::orderBy('nombre','asc')->get();
        foreach($unidad as $k=>$v){
            $cboUnidad = $cboUnidad + array($v->id => $v->nombre);
        }
        $cboTipo = array('L'=>'Logistica');
        $formData = array('producto.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('producto', 'formData', 'entidad', 'boton', 'listar', 'cboUnidad', 'cboTipo', 'cboCategoria'));
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
        $reglas     = array('nombre' => 'required|max:500',
                            'preciocompra' => 'required',
                            'precioventa' => 'required');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'preciocompra.required'         => 'Debe ingresar un precio de compra',
            'precioventa.required'         => 'Debe ingresar un precio de venta',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $producto = new Producto();
            $producto->tipo = $request->input('tipo');
            $producto->nombre = $request->input('nombre');
            $producto->unidad_id = $request->input('unidad_id');
            $producto->categoria_id = $request->input('categoria_id');
            $producto->preciocompra = $request->input('preciocompra');
            $producto->precioventa = $request->input('precioventa');
            $producto->save();
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
        $existe = Libreria::verificarExistencia($id, 'producto');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $producto = Producto::find($id);
        $cboCategoria = array('0' => 'Ninguna');
        $categoria = Categoria::orderBy('nombre','asc')->get();
        foreach($categoria as $k=>$v){
            $cboCategoria = $cboCategoria + array($v->id => $v->nombre);
        }
        $cboTipo = array('P' => 'Productos', 'S' => 'Servicios');
        $cboUnidad = array();
        $unidad = Unidad::orderBy('nombre','asc')->get();
        foreach($unidad as $k=>$v){
            $cboUnidad = $cboUnidad + array($v->id => $v->nombre);
        }
        
        $entidad  = 'Producto';
        $formData = array('producto.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('producto', 'formData', 'entidad', 'boton', 'listar', 'cboCategoria', 'cboTipo', 'cboUnidad'));
    }

    public function edit2($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'producto');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $producto = Producto::find($id);
        $cboCategoria = array('0' => 'Ninguna');
        $categoria = Categoria::orderBy('nombre','asc')->get();
        foreach($categoria as $k=>$v){
            $cboCategoria = $cboCategoria + array($v->id => $v->nombre);
        }
        $cboTipo = array('L'=>'Logistica');
        $cboUnidad = array();
        $unidad = Unidad::orderBy('nombre','asc')->get();
        foreach($unidad as $k=>$v){
            $cboUnidad = $cboUnidad + array($v->id => $v->nombre);
        }
        
        $entidad  = 'Producto';
        $formData = array('producto.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('producto', 'formData', 'entidad', 'boton', 'listar', 'cboCategoria', 'cboTipo', 'cboUnidad'));
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
        $existe = Libreria::verificarExistencia($id, 'producto');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:500',
                            'preciocompra' => 'required',
                            'precioventa' => 'required');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            'preciocompra.required'         => 'Debe ingresar un precio de compra',
            'precioventa.required'         => 'Debe ingresar un precio de venta'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $error = DB::transaction(function() use($request, $id){
            $producto = Producto::find($id);
            $producto->tipo = $request->input('tipo');
            $producto->nombre = $request->input('nombre');
            $producto->unidad_id = $request->input('unidad_id');
            $producto->categoria_id = $request->input('categoria_id');
            $producto->preciocompra = $request->input('preciocompra');
            $producto->precioventa = $request->input('precioventa');
            $producto->save();
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
        $existe = Libreria::verificarExistencia($id, 'producto');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $producto = Producto::find($id);
            $producto->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'producto');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Producto::find($id);
        $entidad  = 'Producto';
        $formData = array('route' => array('producto.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
/*- EN MARCA INDICAR EL PROVEEDOR PARA QUE LA BUSQUEDA SE VEA SOLO ESE PROVEEDOR.
- 3.50 LA HORA PARA CALCULAR PAGO DE PERSONAL(REDONDEAR A 0.50 O AUN SOL
PARA ARRIBA)
- TABLAS DE FECHAS PARA FERIADOS.
*/