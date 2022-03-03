<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Tipodocumento;
use App\Tipomovimiento;
use App\Movimiento;
use App\Concepto;
use App\Producto;
use App\Detallemovimiento;
use App\Stockproducto;
use App\Person;
use App\Almacen;
use App\Maquinaria;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MovimientoalmacenController extends Controller
{
    protected $folderview      = 'app.movimientoalmacen';
    protected $tituloAdmin     = 'Doc. Almacen';
    protected $tituloRegistrar = 'Registrar Doc. Almacen';
    protected $tituloModificar = 'Modificar compra';
    protected $tituloEliminar  = 'Eliminar Doc. Almacen';
    protected $tituloVer       = 'Ver Doc. Almacen';
    protected $rutas           = array('create' => 'movimientoalmacen.create', 
            'edit'   => 'movimientoalmacen.edit',
            'show'   => 'movimientoalmacen.show', 
            'delete' => 'movimientoalmacen.eliminar',
            'search' => 'movimientoalmacen.buscar',
            'index'  => 'movimientoalmacen.index',
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
        $entidad          = 'Movimientoalmacen';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::leftjoin('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',3);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('almacen')!=""){
            $resultado = $resultado->where('almacen_id','=',$request->input('almacen'));
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('fecha', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Almacen', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Responsable', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Maquinaria', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '2');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver       = $this->tituloVer;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'titulo_ver'));
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
        $entidad          = 'Movimientoalmacen';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoDocumento = array('' => 'Todos');
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',3)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }
        $cboAlmacen = array('' => 'Todos');
        $almacen = Almacen::orderBy('nombre','asc')->get();
        foreach($almacen as $k=>$v){
            $cboAlmacen = $cboAlmacen + array($v->id => $v->nombre);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoDocumento','cboAlmacen'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Movimientoalmacen';
        $movimiento = null;
        $cboTipoDocumento = array();
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',3)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }   
        $cboAlmacen = array();
        $cboDestino = array('0'=>'Ninguno');
        $almacen = Almacen::get();
        foreach($almacen as $k=>$v){
            $cboDestino = $cboDestino + array($v->id => $v->nombre);
            $cboAlmacen = $cboAlmacen + array($v->id => $v->nombre);
        }
        $cboMaquinaria = array(''=>'Ninguno');
        $maquinaria = Maquinaria::orderBy('nombre','asc')->get();
        foreach($maquinaria as $k=>$v){
            $cboMaquinaria = $cboMaquinaria + array($v->id => $v->nombre." / ".$v->placa." / ".$v->marca." / ".$v->modelo);
        }
        $formData = array('movimientoalmacen.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'cboTipoDocumento','cboDestino','cboAlmacen','cboMaquinaria'));
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
        $reglas     = array('persona' => 'required|max:500');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un proveedor'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Venta       = new Movimiento();
            $Venta->fecha = $request->input('fecha');
            $Venta->numero = $request->input('numero');
            $Venta->subtotal = $request->input('total');
            $Venta->igv = 0;
            $Venta->total = $request->input('total');
            $Venta->tipomovimiento_id=3;//ALMACEN
            $Venta->tipodocumento_id=$request->input('tipodocumento');
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='C';//Pendiente => P / Cobrado => C / Boleteado => B
            $Venta->comentario = $request->input('comentario');
            $Venta->responsable_id=$user->person_id;
            $Venta->almacen_id = $request->input('almacen_id');
            if($request->input('maquinaria_id')!="") $Venta->maquinaria_id = $request->input('maquinaria_id');
            $Venta->save();
            
            if($Venta->tipodocumento_id=="9" && $request->input('destino')!="0"){
                $Ventaref       = new Movimiento();
                $Ventaref->fecha = $request->input('fecha');
                $Ventaref->numero = $request->input('numero');
                $Ventaref->subtotal = $request->input('total');
                $Ventaref->igv = 0;
                $Ventaref->total = $request->input('total');
                $Ventaref->tipomovimiento_id=3;//ALMACEN
                $Ventaref->tipodocumento_id=8;
                $Ventaref->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
                $Ventaref->situacion='C';//Pendiente => P / Cobrado => C / Boleteado => B
                $Ventaref->comentario = $request->input('comentario');
                $Ventaref->responsable_id=$user->person_id;
                $Ventaref->movimiento_id=$Venta->id;
                $Ventaref->almacen_id = $request->input('destino');
                $Ventaref->save();
            }
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $stock = Stockproducto::where('id','=',$request->input('txtIdProducto'.$arr[$c]))->select('producto_id','cantidad','almacen_id','id')->first();
                
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id=$Venta->id;
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=$request->input('txtPrecioVenta'.$arr[$c]);
                $Detalle->preciocompra=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                $Detalle->save();
                
                if(!is_null($stock)){
                    $stock = Stockproducto::find($stock->id);
                    if($Venta->tipodocumento_id==8){//INGRESO
                        $stock->cantidad = $stock->cantidad + $Detalle->cantidad;
                    }else{
                        $stock->cantidad = $stock->cantidad - $Detalle->cantidad;
                    }
                    $stock->save();
                }else{
                    $stock = new Stockproducto();
                    $stock->producto_id = $Detalle->producto_id;
                    if($Venta->tipodocumento_id==8){//INGRESO
                        $stock->cantidad = $Detalle->cantidad;
                    }else{
                        $stock->cantidad = $Detalle->cantidad*(-1);
                    }
                    $stock->save();
                }
                
                if($Venta->tipodocumento_id=="9" && $request->input('destino')!="0"){
                    $Detalle = new Detallemovimiento();
                    $Detalle->movimiento_id=$Ventaref->id;
                    $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                    $Detalle->precioventa=$request->input('txtPrecioVenta'.$arr[$c]);
                    $Detalle->preciocompra=$request->input('txtPrecio'.$arr[$c]);
                    $Detalle->producto_id = $request->input('txtIdProducto'.$arr[$c]);
                    $Detalle->save();
                    
                    $stock1 = Stockproducto::where('almacen_id','=',$request->input('destino'))->where('producto_id','=',$Detalle->producto_id)->first();
                    
                    if(!is_null($stock1)){
                        $stock1->cantidad = $stock1->cantidad + $Detalle->cantidad;
                        $stock1->save();
                    }else{
                        $stock1 = new Stockproducto();
                        $stock1->producto_id = $Detalle->producto_id;
                        $stock1->cantidad = $Detalle->cantidad;
                        $stock1->almacen_id = $request->input('destino');
                        $stock1->save();
                    }
                }
            }
            $dat[0]=array("respuesta"=>"OK","venta_id"=>$Venta->id);
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $venta = Movimiento::find($id);
        $entidad             = 'Movimientoalmacen';
        $cboTipoDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $formData            = array('venta.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        $cboAlmacen = array();
        $cboDestino = array('0'=>'Ninguno');
        $almacen = Almacen::get();
        foreach($almacen as $k=>$v){
            $cboDestino = $cboDestino + array($v->id => $v->nombre);
            $cboAlmacen = $cboAlmacen + array($v->id => $v->nombre);
        }
        //$cuenta = Cuenta::where('movimiento_id','=',$compra->id)->orderBy('id','ASC')->first();
        //$fechapago =  Date::createFromFormat('Y-m-d', $cuenta->fecha)->format('d/m/Y');
        $detalles = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')->where('movimiento_id','=',$venta->id)->select('detallemovimiento.producto','producto.nombre as producto2','detallemovimiento.cantidad','detallemovimiento.preciocompra','detallemovimiento.producto_id')->get();
        //$numerocuotas = count($cuentas);
        return view($this->folderview.'.mantView')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboTipoDocumento','detalles','cboAlmacen'));
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $venta = Movimiento::find($id);
            $venta->situacion='A';
            $venta->save();
            $lst = Detallemovimiento::where('movimiento_id','=',$id)->select('detallemovimiento.producto_id','detallemovimiento.cantidad')->get();
            foreach ($lst as $key => $Detalle) {
                $stock = Stockproducto::where('producto_id','=',$Detalle->producto_id)->where('almacen_id','=',$venta->almacen_id)->first();
                if(!is_null($stock)){
                    if($venta->tipodocumento_id==8){//INGRESO
                        $stock->cantidad = $stock->cantidad - $Detalle->cantidad;
                    }else{
                        $stock->cantidad = $stock->cantidad + $Detalle->cantidad;
                    }
                    $stock->save();
                }else{
                    $stock = new Stockproducto();
                    $stock->producto_id = $Detalle->producto_id;
                    $stock->almacen_id = $venta->almacen_id;
                    if($venta->tipodocumento_id==8){//INGRESO
                        $stock->cantidad = $Detalle->cantidad*(-1);
                    }else{
                        $stock->cantidad = $Detalle->cantidad;
                    }
                    $stock->save();
                }        
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Movimiento::find($id);
        $entidad  = 'Movimientoalmacen';
        $formData = array('route' => array('movimientoalmacen.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarAnular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function buscarproducto(Request $request)
    {
        $descripcion = $request->input("descripcion");
        $resultado = Producto::leftjoin('stockproducto','stockproducto.producto_id','=','producto.id')->where(DB::raw('trim(nombre)'),'like','%'.trim($descripcion).'%')->select('producto.*','stockproducto.cantidad')->get();
        $c=0;$data=array();
        if(count($resultado)>0){
            foreach ($resultado as $key => $value){
                $data[$c] = array(
                        'producto' => $value->nombre,
                        'precioventa' => 0,
                        'preciocompra' => 0,
                        'idproducto' => $value->id,
                        'stock' => round($value->cantidad,2),
                    );
                $c++;                
            }
        }else{         
            $data = array();
        }
        return json_encode($data);
    }
    
    public function buscarproductobarra(Request $request)
    {
        $codigobarra = $request->input("codigobarra");
        $resultado = Producto::leftjoin('stockproducto','stockproducto.producto_id','=','producto.id')->where(DB::raw('trim(codigobarra)'),'like',trim($codigobarra))->select('producto.*','stockproducto.cantidad')->get();
        $c=0;$data=array();
        if(count($resultado)>0){
            foreach ($resultado as $key => $value){
                $data[$c] = array(
                        'producto' => $value->nombre,
                        'codigobarra' => $value->codigobarra,
                        'precioventa' => $value->precioventa,
                        'preciocompra' => $value->preciocompra,
                        'idproducto' => $value->id,
                        'stock' => round($value->cantidad,2),
                    );
                $c++;                
            }
        }else{         
            $data = array();
        }
        return json_encode($data);
    }
    
    public function personautocompletar($searching)
    {
        $resultado        = Person::join('rolpersona','rolpersona.person_id','=','person.id')->whereIn('rolpersona.rol_id',[2,1])
                            ->where(function($sql) use($searching){
                                $sql->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere('razonsocial', 'LIKE', '%'.strtoupper($searching).'%');
                            })
                            ->whereNull('person.deleted_at')->whereNull('rolpersona.deleted_at')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->select('person.*')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->razonsocial != null) {
                $name = $value->razonsocial;
            }else{
                $name = $value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres;
            }
            $data[] = array(
                            'label' => trim($name),
                            'id'    => $value->id,
                            'value' => trim($name),
                            'ruc'   => $value->ruc,
                        );
        }
        return json_encode($data);
    }

    public function generarNumero(Request $request){
        $numeroventa = Movimiento::NumeroSigue(3,$request->input('tipodocumento'));
        echo "001-".$numeroventa;
    }
}
