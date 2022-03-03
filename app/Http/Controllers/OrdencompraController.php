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
use App\Obra;
use App\Maquinaria;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;

class OrdencompraController extends Controller
{
    protected $folderview      = 'app.ordencompra';
    protected $tituloAdmin     = 'Orden de Compra';
    protected $tituloRegistrar = 'Registrar Orden de Compra';
    protected $tituloModificar = 'Modificar Orden de Compra';
    protected $tituloEliminar  = 'Eliminar orden';
    protected $tituloVer       = 'Ver Orden Compra';
    protected $rutas           = array('create' => 'ordencompra.create', 
            'edit'   => 'ordencompra.edit',
            'show'   => 'ordencompra.show', 
            'delete' => 'ordencompra.eliminar',
            'search' => 'ordencompra.buscar',
            'index'  => 'ordencompra.index',
            'confirmar'  => 'ordencompra.confirmar',
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
        $entidad          = 'Ordencompra';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',12)
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like',"%".$request->input('persona')."%");
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('fecha', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Moneda', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
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
        $entidad          = 'Ordencompra';
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
        $entidad  = 'Ordencompra';
        $movimiento = null;
        $numero = Movimiento::NumeroSigue2(12,25);
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $cboMaquinaria = array(''=>'Ninguno');
        $maquinaria = Maquinaria::orderBy('nombre','asc')->get();
        foreach($maquinaria as $k=>$v){
            $cboMaquinaria = $cboMaquinaria + array($v->id => $v->nombre." / ".$v->placa." / ".$v->marca." / ".$v->modelo);
        }
        $cboObra = array(''=>'Ninguno');
        $obra = Obra::orderBy('nombre','asc')->get();
        foreach($obra as $k=>$v){
            $cboObra = $cboObra + array($v->id => $v->nombre);
        }
        $cboTipo = array('Producto'=>'Producto','Servicio'=>'Servicio');
        $formData = array('ordencompra.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'numero', 'cboMoneda','cboObra','cboMaquinaria','cboTipo'));
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
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Venta       = new Movimiento();
            $Venta->fecha = $request->input('fecha');
            $Venta->numero = $request->input('numero');
            $Venta->subtotal = $request->input('subtotal');
            $Venta->igv = $request->input('igv');
            $Venta->total = $request->input('total');
            $Venta->tipomovimiento_id=12;
            $Venta->tipodocumento_id=25;
            $Venta->moneda=$request->input('moneda');
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='P';
            $Venta->comentario = $request->input('comentario');
            $Venta->formapago = $request->input('formapago');
            $Venta->tipo = $request->input('tipo');
            $Venta->responsable_id=$user->person_id;
            if($request->input('obra_id')!="") $Venta->obra_id = $request->input('obra_id');
            if($request->input('maquinaria_id')!="") $Venta->maquinaria_id = $request->input('maquinaria_id');
            if($request->input('movimiento_id')!=""){
                $Venta->movimiento_id = $request->input('movimiento_id');
                $ref = Movimiento::find($Venta->movimiento_id);
                $ref->situacion='C';
                $ref->save();
            }
            $Venta->listamaquinaria = $request->input('listaMaquinaria');
            $Venta->save();
            
            $arr=explode(",",$request->input('listRequerimiento'));
            for($c=0;$c<count($arr);$c++){
                $ref = Movimiento::find($arr[$c]);
                $ref->situacion='C';
                $ref->movimiento_id = $Venta->id;
                $ref->save();
            }
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id=$Venta->id;
                $Detalle->producto_id=$request->input('txtIdProducto'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=0;
                $Detalle->preciocompra=$request->input('txtPrecio'.$arr[$c]);
                //$Detalle->producto = trim(strtoupper($request->input('txtProducto'.$arr[$c])));
                $Detalle->unidad = $request->input('txtUnidad'.$arr[$c]);
                $Detalle->save();
                
                /*$stock = Stockproducto::where('producto_id','=',$Detalle->producto_id)->where('almacen_id','=',1)->first();
                if(is_null($stock)){
                    $stock = new Stockproducto();
                    $stock->producto_id = $Detalle->producto_id;
                    $stock->almacen_id = 1;
                }
                $stock->cantidad = $stock->cantidad + $Detalle->cantidad;
                $stock->save();*/
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
        $entidad             = 'Requerimiento';
        $formData            = array('requerimiento.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        //$cuenta = Cuenta::where('movimiento_id','=',$compra->id)->orderBy('id','ASC')->first();
        //$fechapago =  Date::createFromFormat('Y-m-d', $cuenta->fecha)->format('d/m/Y');
        $persona = $venta->persona->razonsocial;
        $detalles = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')->where('movimiento_id','=',$venta->id)->select('detallemovimiento.*','producto.nombre as producto2')->get();
        //$numerocuotas = count($cuentas);
        return view($this->folderview.'.mantView')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','detalles', 'persona'));
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
        $numero = $movimiento->numero;
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $cboMaquinaria = array(''=>'Ninguno');
        $maquinaria = Maquinaria::orderBy('nombre','asc')->get();
        foreach($maquinaria as $k=>$v){
            $cboMaquinaria = $cboMaquinaria + array($v->id => $v->nombre." / ".$v->placa." / ".$v->marca." / ".$v->modelo);
        }
        $cboObra = array(''=>'Ninguno');
        $obra = Obra::orderBy('nombre','asc')->get();
        foreach($obra as $k=>$v){
            $cboObra = $cboObra + array($v->id => $v->nombre);
        }
        $cboTipo = array('Producto'=>'Producto','Servicio'=>'Servicio');
        $entidad  = 'Ordencompra';
        $formData = array('ordencompra.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'cboMaquinaria', 'cboObra', 'cboTipo', 'cboMoneda', 'numero'));
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
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request, $id,$user,&$dat){
            $Venta       = Movimiento::find($id);
            $Venta->fecha = $request->input('fecha');
            $Venta->numero = $request->input('numero');
            $Venta->subtotal = $request->input('subtotal');
            $Venta->igv = $request->input('igv');
            $Venta->total = $request->input('total');
            $Venta->tipomovimiento_id=12;
            $Venta->tipodocumento_id=25;
            $Venta->moneda=$request->input('moneda');
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='P';
            $Venta->comentario = $request->input('comentario');
            $Venta->formapago = $request->input('formapago');
            $Venta->tipo = $request->input('tipo');
            $Venta->responsable_id=$user->person_id;
            if($request->input('obra_id')!="") $Venta->obra_id = $request->input('obra_id');
            if($request->input('maquinaria_id')!="") $Venta->maquinaria_id = $request->input('maquinaria_id');
            if($request->input('movimiento_id')!=""){
                $Venta->movimiento_id = $request->input('movimiento_id');
                $ref = Movimiento::find($Venta->movimiento_id);
                $ref->situacion='C';
                $ref->save();
            }
            $Venta->save();
            
            $arr=explode(",",$request->input('listRequerimiento'));
            for($c=0;$c<count($arr);$c++){
                $ref = Movimiento::find($arr[$c]);
                $ref->situacion='C';
                $ref->movimiento_id = $Venta->id;
                $ref->save();
            }
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                if(!is_null($request->input('txtIdDetalle'.$arr[$c]))){
                    $Detalle = Detallemovimiento::find($request->input('txtIdDetalle'.$arr[$c]));
                    
                    /*$stock = Stockproducto::where(DB::raw('trim(producto)'),'like',trim($Detalle->producto))->where('almacen_id','=',1)->first();
                    if(is_null($stock)){
                        $stock = new Stockproducto();
                        $stock->producto = trim($Detalle->producto);
                        $stock->almacen_id = 1;
                    }
                    $stock->cantidad = $stock->cantidad - $Detalle->cantidad;
                    $stock->save();*/
                }else{
                    $Detalle = new Detallemovimiento();
                }
                $Detalle->movimiento_id=$Venta->id;
                $Detalle->producto_id=$request->input('txtIdProducto'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=0;
                $Detalle->preciocompra=$request->input('txtPrecio'.$arr[$c]);
                //$Detalle->producto = trim(strtoupper($request->input('txtProducto'.$arr[$c])));
                $Detalle->unidad = $request->input('txtUnidad'.$arr[$c]);
                $Detalle->save();
                
                /*$stock = Stockproducto::where(DB::raw('trim(producto)'),'like',trim($Detalle->producto))->where('almacen_id','=',1)->first();
                if(is_null($stock)){
                    $stock = new Stockproducto();
                    $stock->producto = trim($Detalle->producto);
                    $stock->almacen_id = 1;
                }
                $stock->cantidad = $stock->cantidad + $Detalle->cantidad;
                $stock->save();*/
            }
            $dat[0]=array("respuesta"=>"OK","venta_id"=>$Venta->id);
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
            $detalle = Detallemovimiento::where('movimiento_id','=',$id)->get();
            foreach($detalle as $k=>$v){
                $stock = Stockproducto::where(DB::raw('trim(producto)'),'like',trim($v->producto))->where('almacen_id','=',1)->first();
                if(is_null($stock)){
                    $stock = new Stockproducto();
                    $stock->producto = trim($v->producto);
                    $stock->almacen_id = 1;
                }
                $stock->cantidad = $stock->cantidad - $v->cantidad;
                $stock->save();
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
        $entidad  = 'Ordencompra';
        $formData = array('route' => array('ordencompra.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarAnular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function confirm($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $venta = Movimiento::find($id);
            $venta->situacion='C';
            $venta->save();
            $detalle = Detallemovimiento::where('movimiento_id','=',$id)->get();
            foreach($detalle as $k=>$v){
                $stock = Stockproducto::where('producto_id','=',$v->producto_id)->where('almacen_id','=',1)->first();
                if(is_null($stock)){
                    $stock = new Stockproducto();
                    $stock->producto_id = $v->producto_id;
                    $stock->almacen_id = 1;
                }
                $stock->cantidad = $stock->cantidad + $v->cantidad;
                $stock->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function confirmar($id, $listarLuego)
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
        $entidad  = 'Ordencompra';
        $formData = array('route' => array('ordencompra.confirm', $id), 'method' => 'Confirm', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Confirmar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
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
    
    public function requerimientoautocompletar($searching)
    {
        $resultado        = Movimiento::where('numero','like','%'.$searching.'%')
                            //->where('situacion','like','P')
                            ->where('tipomovimiento_id','=','8')->orderBy('numero', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            $data[] = array(
                            'label' => trim($value->numero),
                            'id'    => $value->id,
                            'value' => trim($value->numero),
                            'obra_id' => $value->obra_id,
                            'maquinaria_id' => $value->maquinaria_id
                        );
        }
        return json_encode($data);
    }
    
    public function productoautocompletar($searching)
    {
        $resultado        = Movimiento::join('detallemovimiento as dm','dm.movimiento_id','=','movimiento.id')
                            ->whereNotIn('situacion',['A'])
                            ->where('tipomovimiento_id','=','12')
                            ->groupBy('dm.producto');
        $list      = $resultado->select('dm.producto')->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            $data[] = array(
                            'label' => trim($value->producto),
                            'id'    => 0,
                            'value' => trim($value->producto),
                        );
        }
        return json_encode($data);
    }

    public function buscarmaquinaria(Request $request)
    {
        $resultado = Maquinaria::find($request->input('maquinaria_id'));
        $c=0;$data=array();
        if(!is_null($resultado)){
            $data[$c] = array(
                    'nombre' => $resultado->nombre." / ".$resultado->placa." / ".$resultado->marca." / ".$resultado->modelo
                );
            
        }else{         
            $data = array();
        }
        return json_encode($data);
    }



    public function agregarDetalle(Request $request){
        $list = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                        ->leftjoin('unidad','unidad.id','=','producto.unidad_id')
                        ->where('movimiento_id','=',$request->input('id'))
                        ->select('detallemovimiento.*','detallemovimiento.producto as producto2','producto.nombre as producto3','unidad.nombre as unidad2')
                        ->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array('idproducto'=>$value->producto_id,
                            'producto'=>$value->producto3,
                            'cantidad'=>$value->cantidad,
                            'precioventa'=>$value->precioventa,
                            'preciocompra'=>$value->preciocompra,
                            'unidad'=>$value->unidad2,
                            'id'=>$value->id,
                            'subtotal'=>round($value->cantidad*$value->precioventa,2));
        }
        return json_encode($data);
    }
    
    public function pdf(Request $request){
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
            $pdf2->Image("http://asfalpaca.com.pe/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
        });
        $pdf::setFooterCallback(function($pdf2) {
            $pdf2->Image("http://asfalpaca.com.pe/asfalpaca/dist/img/pie.png", 10, 267, 190, 23);
        });
        $cotizacion = Movimiento::find($request->input('id'));
        if($cotizacion->tipo!="PRODUCTO"){
            $tipo="Compra";
        }else{
            $tipo=$cotizacion->tipo;
        }
        $pdf::SetTitle('Orden '.($tipo)." ".$cotizacion->numero);
        $pdf::SetMargins(10, 40, 10);
        $pdf::SetFooterMargin(50);
        $pdf::SetAutoPageBreak(true, 30);
        $pdf::AddPage();
        $pdf::SetFont('helvetica','B',12);
        /*$pdf::Ln();
        $pdf::Cell(0,30,utf8_decode(''),0,0,'R');
        $pdf::Ln();*/
        //$pdf::Image("http://martinampuero.com/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
        $pdf::Cell(0,5,utf8_decode("ORDEN DE ".strtoupper($tipo)." NRO ".$cotizacion->numero),0,0,'C');
        $pdf::Ln(); 
        
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(28,5,utf8_decode("FECHA:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,utf8_decode(date("d/m/Y",strtotime($cotizacion->fecha))),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(28,5,("PROVEEDOR:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,trim(strtoupper($cotizacion->persona->razonsocial)),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(28,5,("RUC:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,trim(strtoupper($cotizacion->persona->ruc)),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(28,5,("DIRECCION:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Multicell(0,3,trim(strtoupper($cotizacion->persona->direccion)),0,'L');
        //$pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(28,5,("FORMA PAGO:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,trim(strtoupper($cotizacion->formapago)),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(28,5,("MONEDA:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,trim(strtoupper($cotizacion->moneda=='S'?'SOLES':'DOLARES')),0,0,'L');
        $pdf::Ln();
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(10,5,"Item",1,0,'C');
        $pdf::Cell(103,5,"Descripcion",1,0,'C');
        $pdf::Cell(17,5,"Cant.",1,0,'C');
        $pdf::Cell(15,5,"Uni.",1,0,'C');
        $pdf::Cell(17,5,"Precio",1,0,'C');
        $pdf::Cell(23,5,"Total",1,0,'C');
        $pdf::Ln();$c=0;
        $list = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                    ->where('movimiento_id','=',$cotizacion->id)
                    ->select('detallemovimiento.*','producto.nombre as producto2','detallemovimiento.unidad as unidad2')
                    ->get();
        foreach ($list as $key => $value) {$c=$c+1;
            if($value->producto_id>0){
                $producto=$value->producto2;
            }else{
                $producto=$value->producto;
            }
            $alto=$pdf::getNumLines($producto, 103)*4;
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(10,$alto,$c,1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(103,4,$producto,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(103,$alto,"",1,0,'L');
            $pdf::Cell(17,$alto,number_format($value->cantidad,2,'.',','),1,0,'C');
            $pdf::Cell(15,$alto,$value->unidad2,1,0,'C');
            $pdf::Cell(17,$alto,number_format($value->preciocompra,2,'.',','),1,0,'C');
            $pdf::Cell(23,$alto,number_format($value->preciocompra*$value->cantidad,2,'.',','),1,0,'R');
            $pdf::Ln();
        }
        $pdf::Cell(140,5,"",0,0,'C');
        $pdf::Cell(22,5,'Subtotal',1,0,'C');
        $pdf::Cell(23,5,number_format($cotizacion->subtotal,2,'.',','),1,0,'R');
        $pdf::Ln();
        $pdf::Cell(140,5,"",0,0,'C');
        $pdf::Cell(22,5,'IGV(18%)',1,0,'C');
        $pdf::Cell(23,5,number_format($cotizacion->igv,2,'.',','),1,0,'R');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(140,5,"",0,0,'C');
        $pdf::Cell(22,5,'Total',1,0,'C');
        $pdf::Cell(23,5,number_format($cotizacion->total,2,'.',','),1,0,'R');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(15,5,"SON:",0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $letra = new EnLetras();
        $letras = $letra->ValorEnLetras($cotizacion->total,($cotizacion->moneda=='S'?'SOLES':'DOLARES'));
        $pdf::Cell(0,5,strtoupper($letras),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,5,"Maquinaria:",0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $req = Movimiento::where('movimiento_id','=',$cotizacion->id)
                            ->where('tipomovimiento_id','=',8)
                            ->get();
        $x=0;
        foreach($req as $k=>$v){
            if($x>0){
                $pdf::Cell(20,5,"",0,0,'L');
            }
            $x=$x+1;
            $pdf::Cell(0,5,(is_null($cotizacion->movimientoref->maquinaria)?'':$cotizacion->movimientoref->maquinaria->nombre." / ".$cotizacion->maquinaria->marca." / ".$cotizacion->maquinaria->modelo),0,0,'L');
            $pdf::Ln();
        }
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(15,5,"Observ:",0,0,'L');
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(0,5,$cotizacion->comentario,0,0,'L');
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(20,5,"",0,0,'L');
        $pdf::Cell(60,5,"ADMINISTRADOR",'T',0,'C');
        
        $pdf::Cell(20,5,"",0,0,'L');
        $pdf::Cell(60,5,"LOGISTICA",'T',0,'C');
        
        $pdf::Output('OrdenCompra.pdf');
    }
    
        
    public function pdfReporte(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',12)
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like',"%".$request->input('persona')."%");
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('fecha', 'ASC')->get();
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
                // Set font
                $pdf2->SetFont('helvetica', 'B', 10);
                $pdf2->SetXY(100,3);
                $pdf2->Cell(0, 8, date("d/m/Y H:i"), 0, false, 'R');
        
        });
        $pdf->fechainicio =$request->input('fechainicio');
        $pdf->fechafin =$request->input('fechafin');
        $pdf::SetTitle('REPORTE DE ORDENES DE COMPRA DEL '.date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))));
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',14);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,'REPORTE DE ORDENES DE COMPRA DEL '.date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))),0,0,'C');
        $pdf::Ln(); 
        $pdf::SetFont('helvetica','',9);
        $pdf::Cell(8,8,"#",1,0,'C');
        $pdf::Cell(19,8,"FECHA",1,0,'C');
        $pdf::Cell(50,8,"SOLICITANTE",1,0,'C');
        $pdf::Cell(60,8,"PRODUCTO",1,0,'C');
        $pdf::Cell(20,8,"FOR PAGO",1,0,'C');
        $pdf::Cell(15,8,"CANT.",1,0,'C');
        $pdf::Cell(15,8,"UNI.",1,0,'C');
        $pdf::Cell(18,8,"P. COMP.",1,0,'C');
        $pdf::Cell(20,8,"TOTAL",1,0,'C');
        $pdf::Cell(50,8,"OBSERVACION",1,0,'C');
        $pdf::Ln();
        $c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;$vencido=0;
        foreach ($lista as $key => $value) {$c=$c+1;
            $pdf::SetFont('helvetica','',8.5);
            $pdf::SetTextColor(255,0,0);
            $pdf::Cell(8,8,$c,1,0,'C');
            if(!is_null($value->movimientoref->maquinaria)){
                $pdf::Cell(197,8,$value->movimientoref->maquinaria->nombre." / ".$value->maquinaria->marca." / ".$value->maquinaria->modelo,1,0,'L');
            }elseif(!is_null($value->movimientoref->obra)){
                $pdf::Cell(197,8,$value->movimientoref->obra->nombre,1,0,'L');
            }else{
                $pdf::Cell(197,8,$value->comentario,1,0,'L');
            }
            $pdf::Cell(20,8,"S/ ".number_format($value->total,2,'.',','),1,0,'R');
            $pdf::Ln();
            $pdf::SetTextColor(0,0,0);
            $detalle = Detallemovimiento::where('movimiento_id','=',$value->id)->get();
            $alto = count($detalle)*4;
            $pdf::Cell(8,$alto,"",1,0,'C');
            $pdf::Cell(19,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $pdf::Cell(50,$alto,$value->movimientoref->persona->apellidopaterno." ".$value->movimientoref->persona->apellidomaterno." ".$value->movimientoref->persona->nombres,1,0,'L');
            foreach($detalle as $k=>$v){
                $pdf::SetX(87);
                $alto=$pdf::getNumLines($v->producto, 60)*4;
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::Multicell(60,4,$v->producto,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(60,$alto,"",1,0,'L');
                $pdf::Cell(20,$alto,$value->formapago,1,0,'C');
                $pdf::Cell(15,$alto,number_format($v->cantidad,0),1,0,'C');
                $pdf::Cell(15,$alto,$v->unidad,1,0,'C');
                $pdf::Cell(18,$alto,"S/ ".number_format($v->preciocompra,2,'.',''),1,0,'R');
                $pdf::Cell(20,$alto,"S/ ".number_format($v->cantidad*$v->preciocompra,2,'.',''),1,0,'R');
                $pdf::Cell(50,$alto,$value->comentario,1,0,'L');
                $pdf::Ln();
            }
            $totalp = $totalp + number_format($value->total,2,'.','');
        }
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::SetTextColor(255,0,0);
        $pdf::SetX(197);
        $pdf::Cell(18,5,"TOTAL S/",0,0,'L');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(20,5,number_format($totalp,2,'.',','),'TR',0,'R');
        $pdf::Output('OrdenCompra.pdf');
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