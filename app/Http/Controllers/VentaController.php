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
use App\Pago;
use App\Banco;
use App\Stockproducto;
use App\Detallemovimiento;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
use Elibyy\TCPDF\Facades\TCPDF;
use Excel;

class VentaController extends Controller
{
    protected $folderview      = 'app.venta';
    protected $tituloAdmin     = 'Venta';
    protected $tituloRegistrar = 'Registrar venta';
    protected $tituloModificar = 'Modificar venta';
    protected $tituloEliminar  = 'Anular venta';
    protected $tituloVer       = 'Ver Venta';
    protected $rutas           = array('create' => 'venta.create', 
            'edit'   => 'venta.edit',
            'show'   => 'venta.show2', 
            'delete' => 'venta.eliminar',
            'search' => 'venta.buscar',
            'index'  => 'venta.index',
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
        $entidad          = 'Venta';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',2);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','like','%'.$request->input('numero').'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('tipo')!=""){
            $resultado = $resultado->where('movimiento.tipo','like','%'.$request->input('tipo').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Hora', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cliente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Detraccion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Monto Detrac.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
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
        $entidad          = 'Venta';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoDocumento = array('' => 'Todos');
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',2)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }
        $cboTipo = array(''=>'Todos','CON EL ESTADO'=>'CON EL ESTADO','SUBCONTRATA CON EL ESTADO'=>'SUBCONTRATA CON EL ESTADO','PRIVADO'=>'PRIVADO');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoDocumento', 'cboTipo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Venta';
        $movimiento = null;
        $cboTipoDocumento = array();
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',2)->orderBy('nombre','desc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }        
        $cboDetraccion = array('S'=>'SI','N'=>'NO');
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $cboTipo = array('CON EL ESTADO'=>'CON EL ESTADO','SUBCONTRATA CON EL ESTADO'=>'SUBCONTRATA CON EL ESTADO','PRIVADO'=>'PRIVADO');
        $formData = array('venta.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'cboTipoDocumento', 'cboDetraccion','cboTipo','cboMoneda'));
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
            'nombre.required'         => 'Debe ingresar un cliente'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $dat=array();
        $rst  = Movimiento::where('tipomovimiento_id','=',4)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(is_null($rst)){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->conceptopago_id;
        }
        if($conceptopago_id==2){
            $dat[0]=array("respuesta"=>"ERROR","msg"=>"Caja cerrada");
            return json_encode($dat);
        }
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Venta       = new Movimiento();
            $Venta->fecha = $request->input('fecha');
            $Venta->numero = $request->input('numero');
            if($request->input('tipodocumento')=="4"){//FACTURA
                $Venta->subtotal = round($request->input('total')/1.18,2);
                $Venta->igv = round($request->input('total') - $Venta->subtotal,2);
            }else{
                $Venta->subtotal = $request->input('total');
                $Venta->igv = 0;
            }
            $Venta->total = str_replace(",","",$request->input('total')); 
            $Venta->tipomovimiento_id=2;//VENTA
            $Venta->tipodocumento_id=$request->input('tipodocumento');
            $Venta->tipo=$request->input('tipo');
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='P';//Pendiente => P / Cobrado => C / Autodetraccion => D
            $Venta->comentario = '';
            $Venta->responsable_id=$user->person_id;
            $Venta->incluye = $request->input('detraccion');
            $Venta->moneda=$request->input('moneda');
            if($request->input('moneda')=='D'){
                $Venta->tipocambio=$request->input('tipocambio');
            }
            if($Venta->incluye=='S'){
                $Venta->detraccion = $request->input('montodetraccion');
                $Venta->nrooperacion = 'Detraccion';
            }
            if($Venta->tipodocumento_id=="26" || $Venta->tipodocumento_id=="27"){
                $Venta->movimientoref_id=$request->input('movimientoref_id');
                $Venta->condiciones=$request->input('numeroref');
                if($Venta->tipodocumento_id=="26"){
                    $Ventaref = Movimiento::find($Venta->movimientoref_id);
                    if(!is_null($Ventaref)){
                        $Ventaref->comentario='Pagado con NC '.$Venta->numero.' del '.date("d/m/Y",strtotime($Venta->fecha));
                        $Ventaref->situacion = 'C';
                        $Ventaref->save();
                    }
                }
            }
            $Venta->save();
            if($Venta->tipodocumento_id=="26" || $Venta->tipodocumento_id=="27"){
                $Venta->situacion='C';
                $Venta->save();
            }
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id=$Venta->id;
                $Detalle->producto_id=$request->input('txtIdProducto'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=$request->input('txtPrecio'.$arr[$c])*1.18;
                $Detalle->preciocompra=$request->input('txtPrecioCompra'.$arr[$c]);
                $Detalle->save();

                $stock = Stockproducto::where('producto_id','=',$Detalle->producto_id)->first();
                if(!is_null($stock)){
                    $stock->cantidad = $stock->cantidad - $Detalle->cantidad;
                    $stock->save();
                }else{
                    $stock = new Stockproducto();
                    $stock->producto_id = $Detalle->producto_id;
                    $stock->cantidad = $Detalle->cantidad*(-1);
                    $stock->save();
                }
            }
            /*$movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d");
            $movimiento->numero= Movimiento::NumeroSigue(4,6);
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$request->input('persona_id')=="0"?1:$request->input('person_id'); 
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->tipomovimiento_id=4;
            $movimiento->tipodocumento_id=6;
            $movimiento->concepto_id=3;
            $movimiento->comentario='Pago de Documento de Venta '.$Venta->numero;
            $movimiento->situacion='N';
            $movimiento->movimiento_id=$Venta->id;
            $movimiento->save();*/
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
    public function show2(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $venta = Movimiento::find($id);
        $entidad             = 'Venta';
        $cboTipoDocumento        = Tipodocumento::where('tipomovimiento_id','=',2)->lists('nombre', 'id')->all();
        $formData            = array('venta.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        $cboDetraccion = array('N'=>'NO','S'=>'SI');
        if($venta->tipodocumento_id==26 || $venta->tipodocumento_id==27){
            $nota = Movimiento::find($venta->movimientoref_id)->numero;
        }else{
            $nota = "";
        }
        $pagos = Pago::where('movimiento_id','=',$id)->get();
        //$cuenta = Cuenta::where('movimiento_id','=',$compra->id)->orderBy('id','ASC')->first();
        //$fechapago =  Date::createFromFormat('Y-m-d', $cuenta->fecha)->format('d/m/Y');
        $detalles = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')->where('movimiento_id','=',$venta->id)->select('detallemovimiento.*',DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto2'))->get();
        //$numerocuotas = count($cuentas);
        return view($this->folderview.'.mantView')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboTipoDocumento','detalles', 'cboDetraccion', 'pagos', 'nota'));
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
        $venta = Movimiento::find($id);
        $cboTipoDocumento        = Tipodocumento::where('tipomovimiento_id','=',2)->lists('nombre', 'id')->all();
        $cboBanco = Banco::lists('nombre','id')->all();
        $cboDetraccion = array('N'=>'NO','S'=>'SI');
        $pagos = Pago::where('movimiento_id','=',$id)->get();
        $entidad  = 'Venta';
        $formData = array('venta.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Pagar';
        return view($this->folderview.'.pagar')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboTipoDocumento','pagos', 'cboDetraccion', 'cboBanco'));
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
        $dat=array();
        $error = DB::transaction(function() use($request, $id, &$dat){
            $venta = Movimiento::find($id);
            if($request->input('listPago')!=""){
                $arr=explode(",",$request->input('listPago'));
                for($c=0;$c<count($arr);$c++){
                    $pago = new Pago();
                    $pago->fecha = $request->input('txtFechaP'.$arr[$c]);
                    $pago->banco_id = $request->input('cboBancoP'.$arr[$c]);
                    $pago->formapago = $request->input('txtFormaP'.$arr[$c]);
                    $pago->monto = $request->input('txtTotalP'.$arr[$c]);
                    $pago->movimiento_id = $id;
                    $pago->save();
                }
            }
            $venta->totalpagado = $request->input('pagado');
            if($venta->totalpagado==$venta->total){
                $venta->situacion='C';
            }
            $venta->entregado = $request->input('entregado');
            $venta->nrooperacion = $request->input('nrooperacion');
            $venta->save();
            $dat[0]=array("respuesta"=>"OK","venta_id"=>$venta->id);
        });
        return is_null($error) ? json_encode($dat) : $error;
    }
    
    public function eliminarPago(Request $request)
    {
        $id = $request->input('id');
        $idventa = $request->input('idventa');
        $existe = Libreria::verificarExistencia($id, 'pago');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id,$idventa){
            $pago = Pago::find($id);
            $venta = Movimiento::find($idventa);
            $venta->totalpagado = $venta->totalpagado - $pago->monto;
            $venta->situacion='P';
            $venta->save();
            
            $pago->delete();
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
            $lst = Detallemovimiento::where('movimiento_id','=',$id)->get();
            foreach ($lst as $key => $Detalle) {
                if($Detalle->producto_id>0){
                    $stock = Stockproducto::where('producto_id','=',$Detalle->producto_id)->first();
                    if(!is_null($stock)){
                        $stock->cantidad = $stock->cantidad + $Detalle->cantidad;
                        $stock->save();
                    }else{
                        $stock = new Stockproducto();
                        $stock->producto_id = $Detalle->producto_id;
                        $stock->cantidad = $Detalle->cantidad;
                        $stock->save();
                    }        
                }
            }
            /*$caja = Movimiento::where('movimiento_id','=',$venta->id)->first();
            $caja->situacion='A';
            $caja->save();*/

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
        $entidad  = 'Venta';
        $formData = array('route' => array('venta.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
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
    
    public function generarNumero(Request $request){
        $numeroventa = Movimiento::NumeroSigue(2,$request->input('tipodocumento'));
        echo "001-".$numeroventa;
    }

    public function personautocompletar($searching)
    {
        $resultado        = Person::join('rolpersona','rolpersona.person_id','=','person.id')->where('rolpersona.rol_id','=',3)
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
                        );
        }
        return json_encode($data);
    }
    
    public function numeroautocompletar($searching)
    {
        $resultado        = Movimiento::whereNull('deleted_at')
                            ->where('tipomovimiento_id','=',2)
                            ->whereNotIn('situacion',['A'])
                            ->whereNotIn('tipodocumento_id',[26,27])
                            ->where('numero','like','%'.$searching.'%')
                            ->orderBy('numero', 'ASC');
        $list      = $resultado->select('movimiento.*')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => trim($value->numero),
                            'id'    => $value->id,
                            'value' => trim($value->numero),
                        );
        }
        return json_encode($data);
    }

    public function imprimirVenta(Request $request){
        $venta = Movimiento::find($request->input('id'));
        $connector = new WindowsPrintConnector("CAJA");
        $printer = new Printer($connector);
        $printer -> setJustification(Printer::JUSTIFY_CENTER);
        //$printer -> bitImage($tux,Printer::IMG_DOUBLE_WIDTH | Printer::IMG_DOUBLE_HEIGHT);
        if($venta->idtipodocumento=="3"){//BOLETA
            $printer -> text("LA NUEVA ESTACION");
            $printer -> feed();
            $printer -> text("DE: PERALTA WALTER ELISA CLAUDIA");
            $printer -> feed();
            $printer -> text("CALLE QUIÑONES NRO 046");
            $printer -> feed();
            $printer -> text("PIMENTEL-CHICLAYO-LAMBAYEQUE");
            $printer -> feed();
            $printer -> text("RUC:10165933317");
            $printer -> feed();
        }else{
            $printer -> text("LA NUEVA ESTACION");
            $printer -> feed();
            $printer -> text("CALLE QUIÑONES NRO 046");
            $printer -> feed();
            $printer -> text("PIMENTEL-CHICLAYO-LAMBAYEQUE");
            $printer -> feed();
        }
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        if($venta->idtipodocumento=="3"){
            $printer -> text("Boleta Electronica: ".substr($venta->numero,0,13));
            $printer -> feed();
        }else{
            $printer -> text("Ticket: ".substr($venta->numero,0,13));
            $printer -> feed();
        }
        $printer -> text("Fecha: ".substr($venta->fecha,0,10));
        $printer -> feed();
        if($venta->nombres!="VARIOS"){
            $printer -> text("Cliente: ".$venta->apellidos." ".$venta->nombres);
            $printer -> feed();
            $printer -> text("Dir.: ".$venta->direccion);
            $printer -> feed();
            if($venta->tipopersona=="JURIDICA" && $venta->idtipodocumento=="4"){
                $printer -> text("RUC/DNI: 0");
            }else{
                $printer -> text("RUC/DNI: ".$venta->nrodoc);
            }
            $printer -> feed();
        }else{
            $printer -> text("Cliente: ");
            $printer -> feed();
            $printer -> text("Dir.: SIN DOMICILIO");
            $printer -> feed();
            $printer -> text("RUC/DNI: 0");
            $printer -> feed();
        }
        $printer -> text("---------------------------------------------"."\n");
        $printer -> text("Cant.  Producto                       Importe");
        $printer -> feed();
        $printer -> text("---------------------------------------------"."\n");
        
        $lst = Detallemovimiento::where('movimiento_id','=',$request->input('id'))->get();
        foreach ($lst as $key => $Detalle) {
            $printer -> text(number_format($Detalle->cantidad,0,'.','')."  ".str_pad(($Detalle->producto->nombre),35," ")." ".number_format($Detalle->cantidad*$Detalle->precioventa,2,'.',' ')."\n");
        }
    
        $printer -> text("---------------------------------------------"."\n");
        if($venta->idtipodocumento=="3"){//BOLETA
            $printer -> text(str_pad("Op. Gravada:",37," "));
            $printer -> text(number_format($venta->subtotal,2,'.',' ')."\n");
            $printer -> text(str_pad("I.G.V. (18%)",37," "));
            $printer -> text(number_format($venta->igv,2,'.',' ')."\n");
            $printer -> text(str_pad("Op. Inafecta:",37," "));
            $printer -> text(number_format(0,2,'.',' ')."\n");
            $printer -> text(str_pad("Op. Exonerada:",37," "));
            $printer -> text(number_format(0,2,'.',' ')."\n");
        }else{
            $printer -> text(str_pad("TOTAL S/ ",37," "));
            $printer -> text(number_format($venta->total,2,'.',' ')."\n");
        }
        $printer -> text("---------------------------------------------"."\n");
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> text("Hora: ".date("H:i:s")."\n");
        if($venta->responsable_id==2){//NANCY
            $caja = "CAJA 1";
        }elseif($venta->responsable_id==25){//LISSETH
            $caja = "CAJA 2";
        }elseif($venta->responsable_id==26){//MAYRA
            $caja = "CAJA 3";
        }else{
            $caja = "CAJA 4";
        }
        $printer -> text("$caja \n");
        $printer -> text("\n");
        $printer -> text("           GRACIAS POR SU PREFERENCIA"."\n");
        $printer -> text("\n");
        $printer -> feed();
        $printer -> feed();
        $printer -> cut();
        
        /* Close printer */
        $printer -> close();       
    }
    
    public function pdf2(Request $request){
        $tipo = $request->input('tipo');
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::SetTitle('Reporte de Ventas');
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(0,8,utf8_decode("REPORTE DE VENTAS TIPO ".($tipo==''?'TODOS':$tipo)),0,0,'C');
        $pdf::Ln(); 
        $pdf::Ln(); 
        
        $pdf::SetFont('helvetica','B',9.5);
        $pdf::Cell(23,5,"TIPO DOC",1,0,'C');
        $pdf::Cell(23,5,"NUMERO",1,0,'C');
        $pdf::Cell(18,5,"FECHA",1,0,'C');
        $pdf::Cell(20,5,"RUC",1,0,'C');
        $pdf::Cell(60,5,"CLIENTE",1,0,'C');
        $pdf::Cell(75,5,"DETALLE",1,0,'C');
        $pdf::Cell(20,5,"SUBTOTAL",1,0,'C');
        $pdf::Cell(22,5,"TOTAL",1,0,'C');
        $pdf::Cell(20,5,"SITUACION",1,0,'C');
        $pdf::Ln();
        
        $resultado = Movimiento::join('person','person.id','=','movimiento.persona_id')
                    ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                    ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                    ->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                    ->leftjoin('unidad','unidad.id','=','producto.unidad_id')
                    ->where('tipomovimiento_id','=',2)
                    ->whereNotIn('movimiento.situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('numero','like','%'.$request->input('numero').'%');
        }
        if($request->input('tipo')!=""){
            $resultado = $resultado->where('movimiento.tipo','like','%'.$request->input('tipo').'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        $lista            = $resultado->select('movimiento.*','person.ruc',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa',DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto2'),DB::raw('case when detallemovimiento.producto_id>0 then unidad.nombre else detallemovimiento.unidad end as unidad2'))->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();
        $total=0;$numeroant='';
        foreach($lista as $key=>$value){
            $pdf::SetFont('helvetica','',8.5);
            $alto=$pdf::getNumLines($value->persona->razonsocial, 50)*4;
            $alto1=$pdf::getNumLines(number_format($value->cantidad,2,'.',',').'|'.$value->producto2.'|'.$value->unidad2.'|'.number_format($value->precioventa,2,'.',','), 75)*4;
            if($alto1>$alto){$alto=$alto1;}
            $band=false;
            if($numeroant!=$value->numero){
                $pdf::Cell(23,$alto,substr($value->tipodocumento->nombre,0,11),'LRT',0,'L');
                $pdf::Cell(23,$alto,$value->numero,'LRT',0,'L');
                $pdf::Cell(18,$alto,date("d/m/Y",strtotime($value->fecha)),'LRT',0,'L');
                $pdf::Cell(20,$alto,$value->ruc,'LRT',0,'L');
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::Multicell(60,3.5,$value->persona->razonsocial,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(60,$alto,'','LRT',0,'L');
                $numeroant=$value->numero;
                $band=true;
            }else{
                $pdf::Cell(23,$alto,'','LR',0,'L');
                $pdf::Cell(23,$alto,'','LR',0,'L');
                $pdf::Cell(18,$alto,'','LR',0,'L');
                $pdf::Cell(20,$alto,'','LR',0,'L');
                $pdf::Cell(60,$alto,'','LR',0,'L');
            }
            $x=$pdf::GetX();
            $y=$pdf::GetY(); 
            $pdf::Multicell(75,3.5,number_format($value->cantidad,2,'.',',').' | '.$value->producto2.' | '.$value->unidad2.' | '.number_format($value->precioventa,2,'.',','),0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(75,$alto,'',1,0,'L'); 
            if($band){
                if($value->moneda=="D"){
                    $subtotal1 = round($value->subtotal*$value->tipocambio,2);
                    $total1 = round($value->total*$value->tipocambio,2);
                }else{
                    $subtotal1 = $value->subtotal;
                    $total1 = $value->total;
                }
                $pdf::Cell(20,$alto,number_format($subtotal1,2,'.',','),'LRT',0,'R');
                $pdf::Cell(22,$alto,number_format($total1,2,'.',','),'LRT',0,'R');
                if($value->situacion=='A'){
                    $situacion='Anulado';
                }elseif($value->situacion=='P'){
                    $situacion='Pendiente';
                }elseif($value->situacion=='D'){
                    $situacion='Autodetraccion';
                }elseif($value->situacion=='C'){
                    $situacion='Cancelado';
                }
                $pdf::Cell(20,$alto,$situacion,'LRT',0,'C');
                if($value->situacion!="A"){
                    $total = $total + $total1;
                }
            }else{
                $pdf::Cell(20,$alto,'','LR',0,'L');
                $pdf::Cell(22,$alto,'','LR',0,'L');
                $pdf::Cell(20,$alto,'','LR',0,'L');
            }
            $pdf::Ln();
        }
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(219,5,"",'T',0,'C');
        $pdf::Cell(20,5,"TOTAL",1,0,'C');
        $pdf::Cell(22,5,number_format($total,2,'.',','),1,0,'C');
        $pdf::Cell(25,$alto,'','T',0,'L');
        $pdf::Ln();
        $pdf::Output('ReporteCotizacion.pdf');
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado = Movimiento::join('person','person.id','=','movimiento.persona_id')
                    ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                    ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                    ->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                    ->leftjoin('unidad','unidad.id','=','producto.unidad_id')
                    ->where('tipomovimiento_id','=',2)
                    ->whereNotIn('movimiento.situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('numero','like','%'.$request->input('numero').'%');
        }
        if($request->input('tipo')!=""){
            $resultado = $resultado->where('movimiento.tipo','like','%'.$request->input('tipo').'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        $resultado = $resultado->select('movimiento.*','person.ruc',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa',DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto2'),DB::raw('case when detallemovimiento.producto_id>0 then unidad.nombre else detallemovimiento.unidad end as unidad2'))->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();
        
        Excel::create('ExcelVentas', function($excel) use($resultado,$request) {
 
            $excel->sheet('Ventas', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Tipo";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Numero";
                $cabecera[] = "Fecha";
                $cabecera[] = "RUC";
                $cabecera[] = "Cliente";
                $cabecera[] = "Detalle";
                $cabecera[] = "Subtotal";
                $cabecera[] = "Total";
                $cabecera[] = "Situacion";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $subtotal=0;
                $igv=0;
                $total=0;
                $totalpagado=0;
                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->tipo;
                    $detalle[] = $value->tipodocumento->nombre;
                    $detalle[] = $value->numero;
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->ruc;
                    $detalle[] = $value->persona->razonsocial;
                    $detalle[] = number_format($value->cantidad,2,'.',',').' | '.$value->producto2.' | '.$value->unidad2.' | '.number_format($value->precioventa,2,'.',',');
                    $detalle[] = number_format($value->subtotal,2,'.','');
                    $detalle[] = number_format($value->total,2,'.','');
                    if($value->situacion=='A'){
                        $situacion='Anulado';
                    }elseif($value->situacion=='P'){
                        $situacion='Pendiente';
                    }elseif($value->situacion=='D'){
                        $situacion='Autodetraccion';
                    }elseif($value->situacion=='C'){
                        $situacion='Cancelado';
                    }
                    $detalle[] = $situacion;
                    $total=$total+number_format($value->total,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
                $cabecera = array();
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "Total";
                $cabecera[] = number_format($total,2,'.','');
                //$cabecera[] = number_format($totalpagado,2,'.','');
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