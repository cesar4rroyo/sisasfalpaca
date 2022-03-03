<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Tipodocumento;
use App\Tipomovimiento;
use App\Movimiento;
use App\User;
use App\Concepto;
use App\Producto;
use App\Detallemovimiento;
use App\Stockproducto;
use App\Person;
use App\Librerias\Libreria;
use App\Tipocambio;
use App\Maquinaria;
use App\Almacen;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;
use Excel;


class CompraController extends Controller
{
    protected $folderview      = 'app.compra';
    protected $tituloAdmin     = 'Compra';
    protected $tituloRegistrar = 'Registrar compra';
    protected $tituloModificar = 'Modificar compra';
    protected $tituloEliminar  = 'Eliminar compra';
    protected $tituloVer       = 'Ver Compra';
    protected $rutas           = array('create' => 'compras.create', 
            'edit'   => 'compras.edit',
            'show'   => 'compras.show', 
            'delete' => 'compras.eliminar',
            'search' => 'compras.buscar',
            'index'  => 'compras.index',
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
        $entidad          = 'Compra';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',1)
                                ->where('situacion','<>','A');
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','like','%'.trim($request->input('numero')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('almacen')!=""){
            $resultado = $resultado->where('movimiento.almacen_id','=',$request->input('almacen'));
        }
        if($request->input('situacion')!=""){
            if($request->input('situacion')=="P"){
                $resultado = $resultado->where(DB::raw('round(movimiento.total,2)'),'>',DB::raw('round(movimiento.totalpagado,2)'));    
            }else{
                $resultado = $resultado->where(DB::raw('round(movimiento.total,2)'),'=',DB::raw('round(movimiento.totalpagado,2)'));    
            }
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('fecha', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Almacen', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Venc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Forma Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Moneda', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total Pagado', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver       = $this->tituloVer;
        $ruta             = $this->rutas;
        $totals = 0;$totald = 0;
        foreach($lista as $k=>$v){
            if($v->situacion!="A"){
                if($v->moneda=='S'){
                    if($v->tipodocumento_id!=20){
                        $totals = $totals + $v->total - $v->totalpagado;
                    }else{
                        $totals = $totals - ($v->total - $v->totalpagado);
                    }
                }else{
                    $totald = $totald + $v->total - $v->totalpagado;
                }
            }
        }
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'titulo_ver', 'totals', 'totald'));
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
        $entidad          = 'Compra';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoDocumento = array('' => 'Todos');
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',1)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }
        $cboSituacion = array('' => 'Todos','P' =>'Pendiente','C' => 'Cancelado');
        $cboAlmacen = array('' => 'Todos');
        $almacen = Almacen::orderBy('id','asc')->get();
        foreach ($almacen as $key => $value) {
            $cboAlmacen = $cboAlmacen + array($value->id => $value->nombre);
        }
        $user = Auth::user();
        if($user->usertype_id=="8"){
            $cboAlmacen = array(2=>'Planta Industrial');
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoDocumento', 'cboSituacion','cboAlmacen'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Compra';
        $movimiento = null;
        $cboTipoDocumento = array();
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',1)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }        
        $cboAlmacen = array();
        $almacen = Almacen::orderBy('id','asc')->get();
        foreach ($almacen as $key => $value) {
            $cboAlmacen = $cboAlmacen + array($value->id => $value->nombre);
        }
        $cboFormaPago = array('A'=>'Contado','B'=>'Credito');
        $cboDetraccion = array('S'=>'SI','N'=>'NO');
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $formData = array('compras.store');
        $current_user     = Auth::User();
        $detalle = null;
        $user = Auth::user();
        if($user->usertype_id=="8"){
            $cboAlmacen = array(2=>'Planta Industrial');
            $cboFormaPago = array('A'=>'Contado');
        }
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off', 'enctype' => 'multipart/form-data');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'cboTipoDocumento', 'cboFormaPago', 'cboDetraccion', 'detalle', 'cboMoneda', 'current_user','cboAlmacen'));
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
            'persona.required'         => 'Debe ingresar un proveedor'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $dat=array();
        $rs = Movimiento::where('numero','like',$request->input('numero'))->where('persona_id','=',$request->input('persona_id'))
                ->where('tipodocumento_id','=',$request->input('tipodocumento'))->where('tipomovimiento_id','=',1)
                ->whereNotIn('situacion',['A'])
                ->first();
        if(!is_null($rs)){
            $dat[0]=array("respuesta"=>"ERROR","msg"=>"Duplicado numero de compra");
            return json_encode($dat);
        }
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Venta       = new Movimiento();
            $Venta->fecha = $request->input('fecha');
            $Venta->numero = $request->input('numero');
            if($request->input('tipodocumento')=="2"){//FACTURA
                $Venta->subtotal = round($request->input('total')/1.18,2);
                $Venta->igv = round($request->input('total') - $Venta->subtotal,2);
            }else{
                $Venta->subtotal = $request->input('total');
                $Venta->igv = 0;
            }
            $Venta->total = $request->input('total');
            $Venta->tipomovimiento_id=1;//VENTA
            $Venta->tipodocumento_id=$request->input('tipodocumento');
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            if($request->input('formapago')=="A"){
                $Venta->situacion='C';//Pendiente => P / Cobrado => C 
                $Venta->totalpagado = $request->input('total');
            }else{
                $Venta->situacion='P';
                $Venta->totalpagado = $request->input('totalpagado');
            }
            $Venta->formapago = $request->input('formapago');
            $Venta->incluye = $request->input('detraccion');
            if($Venta->incluye=='S') $Venta->detraccion = $request->input('montodetraccion');
            $Venta->fechavencimiento = $request->input('fechavencimiento');
            $Venta->comentario = $request->input('comentario');
            $Venta->responsable_id=$user->person_id;
            $Venta->moneda=$request->input('moneda');
            if($request->input('movimiento_id')!=""){
                $Venta->movimiento_id = $request->input('movimiento_id');
                /*$ref = Movimiento::find($Venta->movimiento_id);
                $ref->situacion='C';
                $ref->save();*/
            }
            $Venta->almacen_id = $request->input('almacen_id');
            $Venta->save();
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id=$Venta->id;
                //$Detalle->producto_id=null;
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=$request->input('txtPrecioVenta'.$arr[$c]);
                $Detalle->preciocompra=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->producto = $request->input('txtProducto'.$arr[$c]);
                $Detalle->save();
                
                /*$Producto = Producto::find($Detalle->producto_id);
                $Producto->preciocompra = $Detalle->preciocompra;
                $Producto->save();*/

                /*$stock = Stockproducto::where('producto_id','=',$Detalle->producto_id)->first();
                if(count($stock)>0){
                    $stock->cantidad = $stock->cantidad + $Detalle->cantidad;
                    $stock->save();
                }else{
                    $stock = new Stockproducto();
                    $stock->producto_id = $Detalle->producto_id;
                    $stock->cantidad = $Detalle->cantidad;
                    $stock->save();
                }*/
            }
            $lista="";
            $arr=explode(",",$request->input('listPago'));
            if($request->input('listPago')!=""){
                for($c=0;$c<count($arr);$c++){
                    $lista.=$request->input('txtFechaP'.$arr[$c])."@".$request->input('txtPago'.$arr[$c])."@".$request->input('txtForma'.$arr[$c])."|";
                }
                $Venta->listapago=substr($lista,0,strlen($lista)-1);
            }else{
                $Venta->listapago="";
            }
            $Venta->save();
            $dat[0]=array("respuesta"=>"OK","compra_id"=>$Venta->id);
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
        $entidad             = 'Compra';
        $cboTipoDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $formData            = array('venta.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        //$cuenta = Cuenta::where('movimiento_id','=',$compra->id)->orderBy('id','ASC')->first();
        //$fechapago =  Date::createFromFormat('Y-m-d', $cuenta->fecha)->format('d/m/Y');
        $detalles = Detallemovimiento::where('movimiento_id','=',$venta->id)->get();
        $persona = $venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres.' '.$venta->persona->razonsocial;
        //$numerocuotas = count($cuentas);
        return view($this->folderview.'.mantView')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboTipoDocumento','detalles','persona'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $movimiento = Movimiento::find($id);
        $cboTipoDocumento = array();
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',1)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }        
        $cboAlmacen = array();
        $almacen = Almacen::orderBy('id','asc')->get();
        foreach ($almacen as $key => $value) {
            $cboAlmacen = $cboAlmacen + array($value->id => $value->nombre);
        }
        $cboFormaPago = array('A'=>'Contado','B'=>'Credito');
        $cboDetraccion = array('S'=>'SI','N'=>'NO');
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $detalle = Detallemovimiento::where('movimiento_id','=',$id)->get();
        $entidad  = 'Compra';
        $current_user     = Auth::User();
        if($current_user->usertype_id=="8"){
            $cboAlmacen = array(2=>'Planta Industrial');
            $cboFormaPago = array('A'=>'Contado');
        }
        $formData = array('compras.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'cboDetraccion', 'cboFormaPago', 'cboTipoDocumento', 'detalle', 'cboMoneda','current_user','cboAlmacen'));
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('persona' => 'required|max:500');
        $mensajes = array(
            'persona.required'         => 'Debe ingresar un proveedor'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request, $id, $user, &$dat){
            $Venta = Movimiento::find($id);
            $Venta->fecha = $request->input('fecha');
            $Venta->numero = $request->input('numero');
            if($request->input('tipodocumento')=="2"){//FACTURA
                $Venta->subtotal = round($request->input('total')/1.18,2);
                $Venta->igv = round($request->input('total') - $Venta->subtotal,2);
            }else{
                $Venta->subtotal = $request->input('total');
                $Venta->igv = 0;
            }
            $Venta->total = $request->input('total');
            $Venta->tipomovimiento_id=1;//VENTA
            $Venta->tipodocumento_id=$request->input('tipodocumento');
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            if($request->input('formapago')=="A"){
                $Venta->situacion='C';//Pendiente => P / Cobrado => C 
                $Venta->totalpagado = $request->input('total');
            }else{
                $Venta->situacion='P';
                $Venta->totalpagado = $request->input('totalpagado');
            }
            $Venta->formapago = $request->input('formapago');
            $Venta->incluye = $request->input('detraccion');
            if($Venta->incluye=='S') $Venta->detraccion = $request->input('montodetraccion');
            $Venta->fechavencimiento = $request->input('fechavencimiento');
            $Venta->comentario = $request->input('comentario');
            $Venta->responsable_id=$user->person_id;
            $Venta->moneda=$request->input('moneda');
            $Venta->almacen_id = $request->input('almacen_id');
            $Venta->save();
            
            $detalle = Detallemovimiento::where('movimiento_id','=',$id)->get();
            foreach($detalle as $k=>$v){
                $v->delete();
            }
            
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id=$Venta->id;
                //$Detalle->producto_id=null;
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=$request->input('txtPrecioVenta'.$arr[$c]);
                $Detalle->preciocompra=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->producto = $request->input('txtProducto'.$arr[$c]);
                $Detalle->save();
            }
            $lista="";
            $arr=explode(",",$request->input('listPago'));
            if($request->input('listPago')!=""){
                for($c=0;$c<count($arr);$c++){
                    $lista.=$request->input('txtFechaP'.$arr[$c])."@".$request->input('txtPago'.$arr[$c])."@".$request->input('txtForma'.$arr[$c])."|";
                }
                $Venta->listapago=substr($lista,0,strlen($lista)-1);
            }else{
                $Venta->listapago="";
            }
            $Venta->save();
           
            $dat[0]=array("respuesta"=>"OK","compra_id"=>$Venta->id);
        });
        return is_null($error) ? json_encode($dat) : $error;
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
            /*$lst = Detallemovimiento::where('movimiento_id','=',$id)->get();
            foreach ($lst as $key => $Detalle) {
                $stock = Stockproducto::where('producto_id','=',$Detalle->producto_id)->first();
                if(count($stock)>0){
                    $stock->cantidad = $stock->cantidad - $Detalle->cantidad;
                    $stock->save();
                }else{
                    $stock = new Stockproducto();
                    $stock->producto_id = $Detalle->producto_id;
                    $stock->cantidad = $Detalle->cantidad*(-1);
                    $stock->save();
                }        
            }*/
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
        $entidad  = 'Compra';
        $formData = array('route' => array('compras.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarAnular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function buscarproducto(Request $request)
    {
        $descripcion = $request->input("descripcion");
        $resultado = Producto::leftjoin('stockproducto','stockproducto.producto_id','=','producto.id')->where('nombre','like','%'.strtoupper($descripcion).'%')->select('producto.*','stockproducto.cantidad')->get();
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
        $resultado        = Person::join('rolpersona','rolpersona.person_id','=','person.id')->where('rolpersona.rol_id','=',2)
                            ->where(function($sql) use($searching){
                                $sql->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere(DB::raw('CONCAT(ruc," ",razonsocial)'), 'LIKE', '%'.strtoupper($searching).'%');
                            })
                            ->whereNull('person.deleted_at')->whereNull('rolpersona.deleted_at')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->select('person.*')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->razonsocial != null) {
                $name = $value->ruc." ".$value->razonsocial;
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
    
    public function ordenautocompletar($searching)
    {
        $resultado        = Movimiento::where('numero','like','%'.$searching.'%')
                            ->whereIn('situacion',['P','C'])
                            ->where('tipomovimiento_id','=','12')->orderBy('numero', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $maquinaria = '';
            if(!is_null($value->listamaquinaria) && $value->listamaquinaria!=""){
                $listaMaquinaria = explode(",",$value->listamaquinaria);
                for ($i=0; $i < count($listaMaquinaria); $i++) { 
                    $maq = Maquinaria::find($listaMaquinaria[$i]);
                    $maquinaria.=$maq->nombre." / ".$maq->placa." / ".$maq->marca." / ".$maq->modelo." \n";
                }
                $maquinaria=substr($maquinaria, 0, strlen($maquinaria)-3);
            }
            $data[] = array(
                            'label' => trim($value->numero),
                            'id'    => $value->id,
                            'value' => trim($value->numero),
                            'persona' => trim($value->persona->razonsocial),
                            'persona_id' => trim($value->persona_id),
                            'obra_id' => $value->obra_id,
                            'maquinaria_id' => $value->maquinaria_id,
                            'maquinaria' => $maquinaria,
                            'moneda' => $value->moneda,
                        );
        }
        return json_encode($data);
    }


    public function agregarDetalle(Request $request){
        $list = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                        ->where('movimiento_id','=',$request->input('id'))
                        ->select('detallemovimiento.*','producto.nombre as producto2','detallemovimiento.unidad as unidad2')
                        ->get();
        $data = array();$producto="";$total=0;
        foreach ($list as $key => $value) {
            $producto.=$value->cantidad." | ".($value->producto_id>0?$value->producto2:$value->producto)." | ".$value->preciocompra." \n";
            $total = $total + $value->cantidad*$value->preciocompra;
            /*$data[] = array('idproducto'=>round(rand()*100,0),
                            'producto'=>$value->producto2,
                            'cantidad'=>$value->cantidad,
                            'precioventa'=>$value->precioventa,
                            'preciocompra'=>$value->preciocompra,
                            'unidad'=>$value->unidad2,
                            'subtotal'=>round($value->cantidad*$value->precioventa,2));*/
            
        }
        $data[] = array('idproducto'=>round(rand()*100,0),
                            'producto'=>substr($producto,0,strlen($producto)-3),
                            'cantidad'=>1,
                            'precioventa'=>0,
                            'preciocompra'=>$total,
                            'unidad'=>'UNI',
                            'subtotal'=>round($total,2));
        return json_encode($data);
    }

    public function archivos(Request $request){
        //obtenemos el campo file definido en el formulario
        $file = $request->file('file-0');

        //obtenemos el nombre del archivo
        $nombre = $file->getClientOriginalName();

        $path = public_path('compra/'.$request->input('id').'-'.$nombre);

        $file->move('compra', $request->input('id').'-'.$nombre);
 
        //indicamos que queremos guardar un nuevo archivo en el disco local
        //$path = public_path('avatar/'.$filename);
        $carpeta = '/'.$request->input('id');
        if (!file_exists($carpeta)) {
            \Storage::makeDirectory($carpeta);
        }
        \Storage::disk('local')->put($nombre,  \File::get($file));
        \Storage::move($nombre, $request->input('id').'/'.$nombre);
        print_r(\Storage::get($request->input('id').'/'.$nombre)->url());
        //$file->move('compra',$nombre);
 
       return "archivo guardado";
    }
    
    public function pdf(Request $request){
        setlocale(LC_TIME, 'spanish');
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->where('situacion','<>','A');
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','like','%'.trim($request->input('numero')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('almacen')!=""){
            $resultado = $resultado->where('movimiento.almacen_id','=',$request->input('almacen'));
        }
        $resultado            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.producto')->orderBy('fecha', 'ASC')->get();
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
            // Set font
            $pdf2->SetFont('helvetica', 'B', 10);
            $pdf2->SetXY(100,3);
            $pdf2->Cell(0, 8, date("d/m/Y H:i"), 0, false, 'R');
        });
        $pdf::setFooterCallback(function($pdf2){
            $pdf2->SetY(-15);
            // Set font
            $pdf2->SetFont('helvetica', 'I', 8);
            // Page number
            $pdf2->Cell(0, 10, 'Page '.$pdf2->getAliasNumPage().'/'.$pdf2->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M'); 
        });
        $pdf->fechainicio =$request->input('fechainicio');
        $pdf->fechafin =$request->input('fechafin');

        $pdf::SetTitle('Cuentas por Pagar');
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',14);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,"RESUMEN DE COMPRAS DEL ".date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))),0,0,'C');
        $pdf::Ln(); 
        $c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;$vencido=0;
        foreach ($resultado as $key => $value) {
            if($proveedor!=$value->cliente){
                if($proveedor!=""){
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::SetTextColor(255,0,0);
                    $pdf::Cell(37,5,"DEUDA VENCIDA S/",0,0,'L');
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Cell(17,5,number_format($vencido,2,'.',','),'TBR',0,'R');
                    $pdf::SetTextColor(0,0,0);
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::Cell(163,5,"TOTAL S/",0,0,'R');
                    $pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
                    $pdf::Cell(20,5,number_format($totalpagadop,2,'.',','),1,0,'C');
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::Cell(22,5,number_format($totalp - $totalpagadop,2,'.',','),1,0,'C');
                    $pdf::Ln();
                    $totalp=0;$totalpagadop=0;$vencido=0;
                }
                $pdf::Ln();
                if($pdf::GetY()>160){
                    $pdf::AddPage('L');
                    $pdf::SetFont('helvetica','B',10);
                    $pdf::Cell(0,10,"CUENTAS POR PAGAR",0,0,'C');
                    $pdf::Ln(); 
                }
                $c=$c+1;
                $z=0;
                $pdf::SetFont('helvetica','B',14);
                $pdf::Cell(279,7,$c.". ".$value->cliente,1,0,'L');    
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(5,8,"#",1,0,'C');
                $pdf::Cell(17,8,"Fecha",1,0,'C');
                $pdf::Cell(17,8,"Fech Venc",1,0,'C');
                $pdf::Cell(15,8,"Forma P.",1,0,'C');
                $pdf::Cell(50,8,"Maquinaria",1,0,'C');
                $pdf::Cell(70,8,"Detalle",1,0,'C');
                $pdf::Cell(23,8,"Tipo Doc.",1,0,'C');
                $pdf::Cell(20,8,"Nro",1,0,'C');
                //$pdf::Cell(15,8,"Moneda",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(20,8,"Pago a Cta.",1,0,'C');
                $pdf::Cell(22,8,"Saldo",1,0,'C');
                $pdf::Ln();
                $proveedor=$value->cliente;
            }
            if($value->tipodocumento_id==20) $value->total=$value->total*(-1);
            if($value->moneda=="D"){
                $tipocambio = Tipocambio::where('fecha','=',$value->fecha)->first();
                if(!is_null($tipocambio)){
                    $value->total = $value->total*$tipocambio->monto;
                    $value->totalpagado = $value->totalpagado*$tipocambio->monto;
                }else{
                    $value->total = 0;
                    $value->totalpagado = 0;
                }
            }
            $pdf::SetFont('helvetica','',8.5);
            $alto=$pdf::getNumLines($value->comentario, 50)*4;
            $alto1=$pdf::getNumLines($value->producto, 70)*4;
            if($alto1>$alto) $alto=$alto1;
            $z=$z+1;
            if(($pdf::GetY() + $alto) > 172){
                $pdf::AddPage('L');
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(5,8,"#",1,0,'C');
                $pdf::Cell(17,8,"Fecha",1,0,'C');
                $pdf::Cell(17,8,"Fech Venc",1,0,'C');
                $pdf::Cell(15,8,"Forma P.",1,0,'C');
                $pdf::Cell(50,8,"Maquinaria",1,0,'C');
                $pdf::Cell(70,8,"Detalle",1,0,'C');
                $pdf::Cell(23,8,"Tipo Doc.",1,0,'C');
                $pdf::Cell(20,8,"Nro",1,0,'C');
                //$pdf::Cell(15,8,"Moneda",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(20,8,"Pago a Cta.",1,0,'C');
                $pdf::Cell(22,8,"Saldo",1,0,'C');
                $pdf::Ln();
            }
            $pdf::SetFont('helvetica','',8.5);
            $pdf::Cell(5,$alto,$z,1,0,'C');
            $pdf::Cell(17,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            if(strtotime($value->fechavencimiento)< strtotime('now')){
                $pdf::SetTextColor(255,0,0);
                $vencido = $vencido + $value->total - $value->totalpagado;
            }   
            $pdf::Cell(17,$alto,date("d/m/Y",strtotime($value->fechavencimiento)),1,0,'C');
            $pdf::SetTextColor(0,0,0);
            $pdf::Cell(15,$alto,$value->formapago=='A'?'Contado':'Credito',1,0,'L');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(50,3.5,$value->comentario,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(50,$alto,'',1,0,'L');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(70,3.5,$value->producto,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(70,$alto,'',1,0,'L');
            $pdf::Cell(23,$alto,substr($value->tipodocumento->nombre,0,7),1,0,'L');
            $pdf::Cell(20,$alto,$value->numero,1,0,'L');
            //$pdf::Cell(15,$alto,$value->moneda=='S'?'Soles':'Dolares',1,0,'C');
            $pdf::Cell(20,$alto,number_format($value->total,2,'.',','),1,0,'C');
            $pdf::Cell(20,$alto,number_format($value->totalpagado,2,'.',','),1,0,'C');
            $pdf::Cell(22,$alto,number_format($value->total - $value->totalpagado,2,'.',','),1,0,'C');
            $pdf::Ln();
            $total = $total + number_format($value->total,2,'.','');
            $totalpagado = $totalpagado + number_format($value->totalpagado,2,'.','');
            $totalp = $totalp + number_format($value->total,2,'.','');
            $totalpagadop = $totalpagadop + number_format($value->totalpagado,2,'.','');
        }
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::SetTextColor(255,0,0);
        $pdf::Cell(37,5,"DEUDA VENCIDA S/",0,0,'L');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(17,5,number_format($vencido,2,'.',','),'TR',0,'R');
        $pdf::SetTextColor(0,0,0);
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::Cell(163,5,"TOTAL S/",0,0,'R');
        $pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
        $pdf::Cell(20,5,number_format($totalpagadop,2,'.',','),1,0,'C');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(22,5,number_format($totalp - $totalpagadop,2,'.',','),1,0,'C');
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        if($total>$totalp){
            $totalp=0;$totalpagadop=0;
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(217,5,"TOTAL GENERAL S/",0,0,'R');
            $pdf::Cell(20,5,number_format($total,2,'.',','),1,0,'C');
            $pdf::Cell(20,5,number_format($totalpagado,2,'.',','),1,0,'C');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(22,5,number_format($total - $totalpagado,2,'.',','),1,0,'C');
            $pdf::Ln();
        }
        $pdf::Output('Cuentasporpagar.pdf');
    }

    public function pdfResumen(Request $request){
        setlocale(LC_TIME, 'spanish');
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->where('situacion','<>','A');
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','like','%'.trim($request->input('numero')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('almacen')!=""){
            $resultado = $resultado->where('movimiento.almacen_id','=',$request->input('almacen'));
        }
        $resultado            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.producto')->orderBy(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'), 'ASC')->orderBy('fecha', 'ASC')->get();
        
        $pdf = new TCPDF();
        $pdf::SetTitle('Compras');
        $pdf::AddPage('P');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(0,10,"RESUMEN DE COMPRAS DEL ".date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))),0,0,'C');
        $pdf::Ln(); 
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(10,8,"#",1,0,'C');
        $pdf::Cell(150,8,"PROVEEDOR",1,0,'C');
        //$pdf::Cell(20,8,"Importe",1,0,'C');
        //$pdf::Cell(20,8,"Pago a Cta.",1,0,'C');
        $pdf::Cell(20,8,"Saldo",1,0,'C');
        $pdf::Ln();$c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;
        foreach ($resultado as $key => $value) {
            if($proveedor!=$value->cliente){
                if($proveedor!=""){$c=$c+1;
                    $pdf::Cell(10,5,$c,1,0,'L');
                    $pdf::Cell(150,5,$proveedor,1,0,'L');
                    //$pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
                    //$pdf::Cell(20,5,number_format($totalpagadop,2,'.',','),1,0,'C');
                    $pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
                    $pdf::Ln();
                    $totalp=0;$totalpagadop=0;
                }
                $proveedor=$value->cliente;
            }
            $pdf::SetFont('helvetica','',8.5);
            if($value->tipodocumento_id==20) $value->total=$value->total*(-1);
            if($value->moneda=="D"){
                $tipocambio = Tipocambio::where('fecha','=',$value->fecha)->first();
                if(!is_null($tipocambio)){
                    $value->total = $value->total*$tipocambio->monto;
                    $value->totalpagado = $value->totalpagado*$tipocambio->monto;
                }else{
                    $value->total = 0;
                    $value->totalpagado = 0;
                }
            }
            $total = $total + number_format($value->total,2,'.','');
            $totalpagado = $totalpagado + number_format($value->totalpagado,2,'.','');
            $totalp = $totalp + number_format($value->total,2,'.','');
            $totalpagadop = $totalpagadop + number_format($value->totalpagado,2,'.','');
        }
        $c=$c+1;
        $pdf::Cell(10,5,$c,1,0,'L');
        $pdf::Cell(150,5,$proveedor,1,0,'L');
        //$pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
        //$pdf::Cell(20,5,number_format($totalpagadop,2,'.',','),1,0,'C');
        $pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
        $pdf::Ln();
        $totalp=0;$totalpagadop=0;
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(160,5,"TOTAL GENERAL S/",0,0,'R');
        //$pdf::Cell(20,5,number_format($total,2,'.',','),1,0,'C');
        //$pdf::Cell(20,5,number_format($totalpagado,2,'.',','),1,0,'C');
        $pdf::Cell(20,5,number_format($total,2,'.',','),1,0,'C');
        $pdf::Ln();
        $pdf::Output('Cuentasporpagar.pdf');
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->where('situacion','<>','A');
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','like','%'.trim($request->input('numero')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('almacen')!=""){
            $resultado = $resultado->where('movimiento.almacen_id','=',$request->input('almacen'));
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.producto')->orderBy('fecha', 'ASC')->get();
        
        
        Excel::create('ExcelCompra', function($excel) use($lista,$request) {
 
            $excel->sheet('Compra', function($sheet) use($lista,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Proveedor";
                $cabecera[] = "Fecha";
                $cabecera[] = "Fecha Venc.";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Maquinaria";
                $cabecera[] = "Detalle";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Nro.";
                $cabecera[] = "Importe";
                $cabecera[] = "Pago a Cta.";
                $cabecera[] = "Saldo";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $subtotal=0;
                $igv=0;
                $total=0;
                $totalpagado=0;
                foreach ($lista as $key => $value){
                    if($value->tipodocumento_id==20) $value->total=$value->total*(-1);
                    if($value->moneda=="D"){
                        $tipocambio = Tipocambio::where('fecha','=',$value->fecha)->first();
                        if(!is_null($tipocambio)){
                            $value->total = $value->total*$tipocambio->monto;
                            $value->totalpagado = $value->totalpagado*$tipocambio->monto;
                        }else{
                            $value->total = 0;
                            $value->totalpagado = 0;
                        }
                    }
                    $detalle = array();
                    $detalle[] = $value->cliente;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = date("d/m/Y",strtotime($value->fechavencimiento));
                    $detalle[] = $value->formapago=='A'?'Contado':'Credito';
                    $detalle[] = $value->comentario;
                    $detalle[] = $value->producto;
                    $detalle[] = $value->tipodocumento->nombre;
                    $detalle[] = $value->numero;
                    $detalle[] = number_format($value->total,2,'.','');
                    $detalle[] = number_format($value->totalpagado,2,'.','');
                    $detalle[] = number_format($value->total - $value->totalpagado,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                /*$cabecera = array();
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "Total";
                $cabecera[] = number_format($total,2,'.','');*/
                $sheet->row($c,$cabecera);
            });
        })->export('xls');
    }

}

class MYPDF extends TCPDF {
    // Page footer
    public function Footer() {
        // Position at 25 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}