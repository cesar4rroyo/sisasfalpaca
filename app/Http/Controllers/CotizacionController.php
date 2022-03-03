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
use App\Stockproducto;
use App\Detallemovimiento;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;
use Excel;

class CotizacionController extends Controller
{
    protected $folderview      = 'app.cotizacion';
    protected $tituloAdmin     = 'Cotizacion';
    protected $tituloRegistrar = 'Registrar Cotizacion';
    protected $tituloModificar = 'Modificar Cotizacion';
    protected $tituloEliminar  = 'Anular Cotizacion';
    protected $tituloVer       = 'Ver Cotizacion';
    protected $rutas           = array('create' => 'cotizacion.create', 
            'edit'   => 'cotizacion.edit',
            'show'   => 'cotizacion.show', 
            'delete' => 'cotizacion.eliminar',
            'search' => 'cotizacion.buscar',
            'index'  => 'cotizacion.index',
            'confirmar' => 'cotizacion.confirmar',
            'rechazar' => 'cotizacion.rechazar',
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
        $entidad          = 'Cotizacion';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',5);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('numero','like','%'.$request->input('numero').'%');
        }
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('situacion','like',$request->input('situacion'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cliente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '4');
        
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
        $entidad          = 'Cotizacion';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboSituacion = array(''=>'Todos','P'=>'Pendiente','C'=>'Confirmado','R'=>'Desistido','A'=>'Anulado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboSituacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Cotizacion';
        $movimiento = null;       
        $formData = array('cotizacion.store');
        $cboTipo = array('Construccion' => 'Construccion', 'Venta de mezcla' => 'Venta de mezcla', 'Venta de pre-mezclados' => 'Venta de pre-mezclados', 'Alquiler de Vehiculos y Maquinarias' => 'Alquiler de Vehiculos y Maquinarias', 'Venta de maquinaria' => 'Venta de maquinaria');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'cboTipo'));
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
        $error = DB::transaction(function() use($request,$user,&$dat){
            $Venta       = new Movimiento();
            $Venta->fecha = $request->input('fecha');
            $Venta->numero = $request->input('numero');
            $Venta->subtotal = $request->input('total');
            $Venta->igv = 0;
            $Venta->total = str_replace(",","",$request->input('total')); 
            $Venta->tipomovimiento_id=5;//COTIZACION
            $Venta->tipodocumento_id=10;
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='P';//Pendiente => P / Cobrado => C / Boleteado => B
            $Venta->comentario = $request->input('comentario');
            $Venta->entregado = $request->input('entregado');
            $Venta->tipo = $request->input('tipo');
            $Venta->incluye = $request->input('incluye');
            $Venta->responsable_id=$user->person_id;
            $Venta->formapago = $request->input('formapago');
            $Venta->condiciones = $request->input('cuentas');
            $Venta->detraccion = str_replace(",","",$request->input('gastos')); 
            $Venta->totalpagado = str_replace(",","",$request->input('utilidades')); 
            $Venta->save();
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id=$Venta->id;
                $Detalle->producto_id=$request->input('txtIdProducto'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=$request->input('txtPrecio'.$arr[$c]);
                $Detalle->preciocompra=$request->input('txtPrecioCompra'.$arr[$c]);
                $Detalle->save();
            }
            $dat[0]=array("respuesta"=>"OK","cotizacion_id"=>$Venta->id);
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
        $entidad             = 'Cotizacion';
        $cboTipoDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $formData            = array('venta.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        //$cuenta = Cuenta::where('movimiento_id','=',$compra->id)->orderBy('id','ASC')->first();
        //$fechapago =  Date::createFromFormat('Y-m-d', $cuenta->fecha)->format('d/m/Y');
        $detalles = Detallemovimiento::where('movimiento_id','=',$venta->id)->get();
        //$numerocuotas = count($cuentas);
        return view($this->folderview.'.mantView')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboTipoDocumento','detalles'));
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
        $entidad  = 'Cotizacion';
        $formData = array('route' => array('cotizacion.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmarAnular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function buscarproducto(Request $request)
    {
        $descripcion = $request->input("descripcion");
        $resultado = Producto::where('nombre','like','%'.strtoupper($descripcion).'%')->select('producto.*')->get();
        $c=0;$data=array();
        if(count($resultado)>0){
            foreach ($resultado as $key => $value){
                $data[$c] = array(
                        'producto' => $value->nombre,
                        'precioventa' => $value->precioventa,
                        'preciocompra' => $value->preciocompra,
                        'idproducto' => $value->id,
                        'unidad' => $value->unidad->nombre,
                    );
                $c++;                
            }
        }else{         
            $data = array();
        }
        return json_encode($data);
    }
    
    public function generarNumero(Request $request){
        $numeroventa = Movimiento::NumeroSigue2(5,10);
        echo $numeroventa.date('-Y');
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

    public function pdf(Request $request){
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
            $pdf2->Image("http://martinampuero.com/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
        });
        $pdf::setFooterCallback(function($pdf2) {
            $pdf2->Image("http://martinampuero.com/asfalpaca/dist/img/pie.png", 10, 267, 190, 23);
        });
        $cotizacion = Movimiento::find($request->input('id'));
        $pdf::SetTitle('Cotización '.$cotizacion->numero);
        $pdf::SetMargins(10, 40, 10);
        $pdf::SetFooterMargin(50);
        $pdf::SetAutoPageBreak(true, 30);
        $pdf::AddPage();
        $pdf::SetFont('helvetica','B',12);
        /*$pdf::Ln();
        $pdf::Cell(0,30,utf8_decode(''),0,0,'R');
        $pdf::Ln();*/
        //$pdf::Image("http://martinampuero.com/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
        $pdf::Cell(0,5,utf8_decode("COTIZACION NRO ".$cotizacion->numero),0,0,'R');
        $pdf::SetFont('helvetica','',10);
        $pdf::Ln(); 
        $pdf::Cell(0,5,utf8_decode("Chiclayo, ".date("d/m/Y",strtotime($cotizacion->fecha))),0,0,'R');
        $pdf::Ln();
        
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(0,5,("Señor:"),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(0,5,utf8_decode($cotizacion->persona->razonsocial),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(0,5,utf8_decode("RUC: ".$cotizacion->persona->ruc),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(0,5,trim(strtoupper($cotizacion->persona->apellidopaterno." ".$cotizacion->persona->apellidomaterno." ".$cotizacion->persona->nombres)),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(0,5,utf8_decode("Telefono: ".$cotizacion->persona->telefono),0,0,'L');
        $pdf::Ln();
        $pdf::Cell(0,5,"",'B',0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','',10);
        $pdf::Multicell(0,5,"En atención a su solicitud, les hacemos llegar la siguiente cotización: ".$cotizacion->entregado,0,'L');
        $list = Detallemovimiento::join('producto','producto.id','=','detallemovimiento.producto_id')
                    ->join('unidad','unidad.id','=','producto.unidad_id')
                    ->where('movimiento_id','=',$cotizacion->id)
                    ->select('detallemovimiento.*','producto.nombre as producto2','unidad.nombre as unidad2')
                    ->get();
        //$pdf::Ln();
        $pdf::Ln();
        if($cotizacion->tipo!="Alquiler de Vehiculos y Maquinarias" && $cotizacion->tipo!="Venta de mezcla" && $cotizacion->tipo!="Construccion"){
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(10,5,"Item",1,0,'C');
            $pdf::Cell(120,5,"Descripcion",1,0,'C');
            $pdf::Cell(17,5,"Cant.",1,0,'C');
            $pdf::Cell(15,5,"Unid",1,0,'C');
            //$pdf::Cell(15,5,"Cant.",1,0,'C');
            $pdf::Cell(23,5,"Valor Venta",1,0,'C');
            $pdf::Ln();$c=0;
            foreach ($list as $key => $value) {$c=$c+1;
                $alto=$pdf::getNumLines($value->producto2, 120)*4;
                $pdf::SetFont('helvetica','',9);
                $pdf::Cell(10,$alto,$c,1,0,'C');
                $pdf::Cell(120,$alto,$value->producto2,1,0,'L');
                $pdf::Cell(17,$alto,number_format($value->cantidad,2,'.',','),1,0,'C');
                $pdf::Cell(15,$alto,$value->unidad2,1,0,'C');
                $pdf::Cell(23,$alto,number_format($value->precioventa*$value->cantidad,2,'.',','),1,0,'R');
                $pdf::Ln();
            }
            if($cotizacion->incluye=="S"){
                $pdf::Cell(132,5,"",0,0,'C');
                $pdf::Cell(28,5,'Subtotal',1,0,'C');
                $pdf::Cell(25,5,number_format($cotizacion->total,2,'.',','),1,0,'R');
                $pdf::Ln();
                $subtotal=number_format($cotizacion->total,2,'.','');
                if($cotizacion->detraccion>0){
                    $pdf::Cell(132,5,"",0,0,'C');
                    $pdf::Cell(28,5,'Gast. Gen.('.number_format($cotizacion->detraccion,1,'.',',').'%)',1,0,'C');
                    $pdf::Cell(25,5,number_format($cotizacion->total*$cotizacion->detraccion/100,2,'.',','),1,0,'R');
                    $pdf::Ln();
                    $subtotal=$subtotal + number_format($cotizacion->total*$cotizacion->detraccion/100,2,'.','');
                }
                if($cotizacion->totalpagado>0){
                    $pdf::Cell(132,5,"",0,0,'C');
                    $pdf::Cell(28,5,'Utilidad('.number_format($cotizacion->totalpagado,1,'.',',').'%)',1,0,'C');
                    $pdf::Cell(25,5,number_format($cotizacion->total*$cotizacion->totalpagado/100,2,'.',','),1,0,'R');
                    $pdf::Ln();
                    $subtotal=$subtotal + number_format($cotizacion->total*$cotizacion->totalpagado/100,2,'.','');
                }
                $pdf::Cell(132,5,"",0,0,'C');
                $pdf::Cell(28,5,'IGV(18%)',1,0,'C');
                $pdf::Cell(25,5,number_format($subtotal*0.18,2,'.',','),1,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',10);
                $pdf::Cell(132,5,"",0,0,'C');
                $pdf::Cell(28,5,'Total',1,0,'C');
                $pdf::Cell(25,5,number_format($subtotal*1.18,2,'.',','),1,0,'R');
                $pdf::Ln();
            }
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',11);
            if($cotizacion->incluye=="S"){
                //$pdf::Cell(0,5,"LOS PRECIOS INCLUYE IGV",0,0,'C');
            }else{
                $pdf::Cell(0,5,"LOS PRECIOS NO INCLUYE IGV",0,0,'C');
                $pdf::Ln();
            }
        }else{
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(10,5,"Item",1,0,'C');
            $pdf::Cell(100,5,"Descripcion",1,0,'C');
            $pdf::Cell(17,5,"Cant.",1,0,'C');
            $pdf::Cell(20,5,"Unid",1,0,'C');
            $pdf::Cell(23,5,"Valor Venta",1,0,'C');
            $pdf::Cell(20,5,"Valor Total",1,0,'C');
            $pdf::Ln();$c=0;
            foreach ($list as $key => $value) {$c=$c+1;
                $pdf::SetFont('helvetica','',9);
                $alto=$pdf::getNumLines($value->producto2, 100)*5;
                $pdf::Cell(10,$alto,$c,1,0,'C');
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::Multicell(100,3.5,$value->producto2,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(100,$alto,'',1,0,'L');
                $pdf::Cell(17,$alto,number_format($value->cantidad,2,'.',','),1,0,'C');
                $pdf::Cell(20,$alto,$value->unidad2,1,0,'C');
                $pdf::Cell(23,$alto,number_format($value->precioventa,2,'.',','),1,0,'R');
                $pdf::Cell(20,$alto,number_format($value->precioventa*$value->cantidad,2,'.',','),1,0,'R');
                $pdf::Ln();
            }
            if($cotizacion->incluye=="S"){
                $pdf::Cell(132,5,"",0,0,'C');
                $pdf::Cell(38,5,'Subtotal',1,0,'C');
                $pdf::Cell(20,5,number_format($cotizacion->total,2,'.',','),1,0,'R');
                $pdf::Ln();
                $subtotal=number_format($cotizacion->total,2,'.','');
                if($cotizacion->detraccion>0){
                    $pdf::Cell(132,5,"",0,0,'C');
                    $pdf::Cell(38,5,'Gast. Gen.('.number_format($cotizacion->detraccion,1,'.',',').'%)',1,0,'C');
                    $pdf::Cell(20,5,number_format($cotizacion->total*$cotizacion->detraccion/100,2,'.',','),1,0,'R');
                    $pdf::Ln();
                    $subtotal=$subtotal + number_format($cotizacion->total*$cotizacion->detraccion/100,2,'.','');
                }
                if($cotizacion->totalpagado>0){
                    $pdf::Cell(132,5,"",0,0,'C');
                    $pdf::Cell(38,5,'Utilidad('.number_format($cotizacion->totalpagado,1,'.',',').'%)',1,0,'C');
                    $pdf::Cell(20,5,number_format($cotizacion->total*$cotizacion->totalpagado/100,2,'.',','),1,0,'R');
                    $pdf::Ln();
                    $subtotal=$subtotal + number_format($cotizacion->total*$cotizacion->totalpagado/100,2,'.','');
                }
                $pdf::Cell(132,5,"",0,0,'C');
                $pdf::Cell(38,5,'IGV(18%)',1,0,'C');
                $pdf::Cell(20,5,number_format($subtotal*0.18,2,'.',','),1,0,'R');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',10);
                $pdf::Cell(132,5,"",0,0,'C');
                $pdf::Cell(38,5,'Total',1,0,'C');
                $pdf::Cell(20,5,number_format($subtotal*1.18,2,'.',','),1,0,'R');
                $pdf::Ln();
            }else{
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',11);
                if($cotizacion->incluye=="S"){
                    //$pdf::Cell(0,5,"LOS PRECIOS INCLUYE IGV",0,0,'C');
                }else{
                    $pdf::Cell(0,5,"LOS PRECIOS NO INCLUYE IGV",0,0,'C');
                }
                $pdf::SetFont('helvetica','B',10);
                $pdf::Cell(132,5,"",0,0,'C');
                $pdf::Cell(38,5,'Total',1,0,'C');
                $pdf::Cell(20,5,number_format($cotizacion->total,2,'.',','),1,0,'R');
                $pdf::Ln();
            }
            
        }
        $pdf::Ln();
        /*if($cotizacion->tipo!="Venta de pre-mezclados"){
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(80,5,"Validez de Cotización: 15 días",0,0,'L');
            $pdf::Cell(80,5,"Disponibilidad:   Inmediata(Equipos Propios)",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Multicell(180,5,"Forma de Pago: ".$cotizacion->formapago,0,'L');
            //$pdf::Ln();
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,"Cuenta corriente para abono:",0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(60,5,"Banco Continental",0,0,'L');
            $pdf::Cell(50,5,"0011-0287-0100022143",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(60,5,"Banco de la Nación",0,0,'L');
            $pdf::Cell(50,5,"00-231-081739",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(60,5,"Banco Interbank",0,0,'L');
            $pdf::Cell(50,5,"700-3000425-197",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(60,5,"Banco de Crédito del Perú",0,0,'L');
            $pdf::Cell(50,5,"305-1764536-002",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(60,5,"Banco Scotiabank",0,0,'L');
            $pdf::Cell(50,5,"000-2325977",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(60,5,"Banco Pichincha",0,0,'L');
            $pdf::Cell(50,5,"110-0003223-99246",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(60,5,"Banco Banbif",0,0,'L');
            $pdf::Cell(50,5,"007000222018",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(60,5,"CUENTA DE DETRACCIONES",0,0,'L');
            $pdf::Cell(50,5,"00-231-059822",0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
        }else{*/
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'CONDICIONES DE VENTA',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'',0,0,'L');
            if($cotizacion->tipo=="Venta de maquinaria"){
                $pdf::Cell(50,5,'1. La cotización está dada en Dólares Americanos.',0,0,'L');
            }else{
                $pdf::Cell(50,5,'1. La cotización está dada en Soles.',0,0,'L');
            }
            $pdf::Ln();
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(50,5,'2. La cotización será validad durante 15 días contabilizados a partir de la fecha de emisión.',0,0,'L');
            $pdf::Ln();
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(0,5,'3. Forma de Pago:',0,'L');
            $pdf::Ln();
            $pdf::Cell(15,5,'',0,0,'L');
            $pdf::Multicell(0,5,$cotizacion->formapago,0,'L');
            $pdf::Cell(10,5,'',0,0,'L');
            if($cotizacion->tipo=="Venta de maquinaria"){
                $pdf::Cell(50,5,'4. Cuentas corrientes bancarias para abono en SOLES:',0,0,'L');
            }else{
                $pdf::Cell(50,5,'4. Cuentas corrientes bancarias para abono:',0,0,'L');
            }
            $pdf::Ln();
            if($cotizacion->condiciones==""){
                $pdf::SetFont('helvetica','',11);
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Banco Continental",0,0,'L');
                $pdf::Cell(50,5,"0011-0287-0100022143",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Banco de Crédito del Perú",0,0,'L');
                $pdf::Cell(50,5,"305-1764536-002",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Banco Interbank",0,0,'L');
                $pdf::Cell(50,5,"700-3000425-197",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Banco Scotiabank",0,0,'L');
                $pdf::Cell(50,5,"000-2325977",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Banco Pichincha",0,0,'L');
                $pdf::Cell(50,5,"110-0003223-99246",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Banco Banbif",0,0,'L');
                $pdf::Cell(50,5,"007000222018",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Banco de la Nación",0,0,'L');
                $pdf::Cell(50,5,"00-231-081739",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::Cell(50,5,"Cuenta de Detracciones",0,0,'L');
                $pdf::Cell(50,5,"00-231-059822",0,0,'L');
                $pdf::Ln();
                $pdf::Cell(10,5,'',0,0,'L');
                if($cotizacion->tipo=="Venta de maquinaria"){
                    $pdf::Cell(50,5,'5. Cuentas corrientes bancarias para abono en DOLARES:',0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','',11);
                    $pdf::Cell(20,5,'',0,0,'L');
                    $pdf::Cell(50,5,"Banco Continental",0,0,'L');
                    $pdf::Cell(50,5,"0011-0287-0100025169",0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(20,5,'',0,0,'L');
                    $pdf::Cell(50,5,"Banco Pichincha",0,0,'L');
                    $pdf::Cell(50,5,"110-0005359-90154",0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(20,5,'',0,0,'L');
                    $pdf::Cell(50,5,"Banco Interbank",0,0,'L');
                    $pdf::Cell(50,5,"768-3001144-423",0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(10,5,'',0,0,'L');    
                    $pdf::Cell(50,5,'6. Contamos con Disponibilidad Inmediata.',0,0,'L');
                }else{
                    $pdf::Cell(50,5,'5. Contamos con Disponibilidad Inmediata.',0,0,'L');
                }
                $pdf::Ln();
                $pdf::Ln();
            }else{
                $pdf::SetFont('helvetica','',11);
                $pdf::Cell(20,5,'',0,0,'L');
                $pdf::MultiCell(0,5,trim($cotizacion->condiciones),0,'L',0);
                $pdf::Cell(10,5,'',0,0,'L');
                if($cotizacion->tipo=="Venta de maquinaria"){
                    $pdf::Cell(50,5,'5. Cuentas corrientes bancarias para abono en DOLARES:',0,0,'L');
                    $pdf::Ln();
                    $pdf::SetFont('helvetica','',11);
                    $pdf::Cell(20,5,'',0,0,'L');
                    $pdf::Cell(50,5,"Banco Continental",0,0,'L');
                    $pdf::Cell(50,5,"0011-0287-0100025169",0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(20,5,'',0,0,'L');
                    $pdf::Cell(50,5,"Banco Pichincha",0,0,'L');
                    $pdf::Cell(50,5,"110-0005359-90154",0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(20,5,'',0,0,'L');
                    $pdf::Cell(50,5,"Banco Interbank",0,0,'L');
                    $pdf::Cell(50,5,"768-3001144-423",0,0,'L');
                    $pdf::Ln();
                    $pdf::Cell(10,5,'',0,0,'L');    
                    $pdf::Cell(50,5,'6. Contamos con Disponibilidad Inmediata.',0,0,'L');
                }else{
                    $pdf::Cell(50,5,'5. Contamos con Disponibilidad Inmediata.',0,0,'L');
                }
                $pdf::Ln(5);
                $pdf::Ln(5);
            }
            
            
        
        if($cotizacion->tipo=="Construccion"){
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'CONDICIONES DEL SERVICIO',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'1.',0,0,'R');
            $pdf::Multicell(0,5,"Nuestra Empresa cuenta con EQUIPOS PROPIOS",0,'L');
            $pdf::Cell(10,5,'2.',0,0,'R');
            $pdf::Multicell(0,5,"Los trabajadores cuentan con seguro SCTR y nuestras máquinas con seguro TREC y responsabilidad Civil.",0,'L');
            $pdf::Cell(10,5,'3.',0,0,'R');
            $pdf::Multicell(0,5,"Los Lavados de Mezcla Asfáltica se entregarán con fecha de despacho por cada 70 m3.",0,'L');
            $pdf::Cell(10,5,'4.',0,0,'R');
            $pdf::Multicell(0,5,"Se iniciarán las labores una vez recibida la declaración jurada de habilitación del terreno y la orden de compra y/o servicio previa coordinación de la fecha.",0,'L');
            $pdf::Cell(10,5,'5.',0,0,'R');
            $pdf::Multicell(0,5,"Se realizará visita al terreno por parte de nuestro ingeniero para verificar los niveles de base se encuentren 100% conforme.",0,'L');
            $pdf::Cell(10,5,'6.',0,0,'R');
            $pdf::Multicell(0,5,"Una vez concluido los trabajos se nos entregará un ACTA DE RECEPCIÓN DE OBRA Y ACTA DE CONFORMIDAD por la obra ejecutada.",0,'L');
            $pdf::Cell(10,5,'7.',0,0,'R');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Multicell(0,5,"Los acuerdos con el Sindicato de Construcción Civil serán asumidos por EL CONTRATANTE.",0,'L');
            $pdf::Cell(10,5,'8.',0,0,'R');
            $pdf::Multicell(0,5,"La seguridad y señalizaciones para el control de tránsito correrá por cuenta de EL CONTRATANTE.",0,'L');
            $pdf::Cell(10,5,'9.',0,0,'R');
            $pdf::Multicell(0,5,"El CONTRATANTE proporcionará arenilla para el sellado asfáltico y agua para la permeabilizacion del rodillo liso.",0,'L');
            $pdf::Cell(10,5,'10.',0,0,'R');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(0,5,"De ser el caso, que la obra en mención sea pública, EL CONTRATANTE, deberá solicitar a la ENTIDAD una constancia que permita a EL CONTRATISTA el acceso para ejecutar dicha obra, así como la anotación respectiva en el cuaderno de obras, debiendo proporcionar a EL CONTRATISTA una copia del asiento respectivo.",0,'L');
            $pdf::Cell(10,5,'',0,0,'R');
            $pdf::Multicell(0,5,$cotizacion->comentario,0,'L');
            
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'NUESTRAS CERTIFICACIONES',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::Cell(87,5,"- Registro OSINERGMIN N° 108889-112-250514",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(80,5,"de fecha 25/05/2014 como Consumidor",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(12,5,"",0,0,'L');
            $pdf::Multicell(160,5," Directo de Combustibles Líquidos y Otros Productos Derivados de los Hidrocarburos. (Siendo la única empresa que cuenta con esta autorización a nivel Norte).",0,'L');
            $pdf::Ln(5);
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(132,5,"- Diseño de Mezcla Asfáltica en Caliente del mes de OCTUBRE del 2019",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(30,5,", elaborado por la",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::Cell(0,5,"  empresa Servicios de Laboratorios de Suelos y Pavimentos S.A.C.",0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(132,5,"- La Calibración de Planta de Asfalto - 2019",0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(3,5,"- ",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(160,5,"Certificado emitido por la Dirección General de Caminos y Ferrocarriles –MTC mediante Oficio Nº024-2012-MTC/14.01; de los ensayos de laboratorio de la muestra de los agregados perteneciente a la Cantera Tres Tomas–Ferreñafe–Lambayeque para nuestro diseño de mezcla.",0,'L');
            //$pdf::Image("http://martinampuero.com/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(3,5,"- ",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(160,5,"Certificado emitido por la Dirección General de Caminos y Ferrocarriles –MTC mediante Oficio Nº229-2012-MTC/14.01; de los ensayos de laboratorio realizados al asfalto sólido PEN 60/70 de Refinería Talara Petroperú y Aditivo Mejorador de Adherencia AR-RED RADICOTE para nuestro diseño de mezcla.",0,'L');
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(3,5,"- ",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(160,5,"Certificado de la calidad del Asfalto Solido PEN 60/70 y Asfalto Liquido MC-30, emitida por Refinería Talara-Petroperú (donde se verifica la fecha de compra de Asfaltos realizada por nuestra empresa).",0,'L');
            $pdf::Ln(5);
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(170,5,"La empresa garantiza no solo la capacidad y experiencia del personal asignado sino además la calidad de los productos ofrecidos a través de los certificados de ensayo del asfalto proporcionados por la empresa PETROLEOS DEL PERU SA tanto, así como los diseños de mezcla por un laboratorio independiente SERVICIO DE LABORATORIOS DE SUELOS Y PAVIMENTOS SAC y la calibración de los instrumentos y un moderno y eficiente tren de asfalto.",0,"L");
        }
        
        if($cotizacion->tipo=="Venta de pre-mezclados"){    
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'1. Condiciones del Suministro',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(50,5,'1.1. El suministro de Concreto es conforme a la NTP 339.114 Concreto Premezclado.',0,0,'L');
            $pdf::Ln();
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(8,5,'1.2.',0,0,'L');
            $pdf::Multicell(165,5,'La conformidad de la resistencia a la compresión se realizará a los 28 días y será medida en probetas elaboradas, curadas y ensayadas en condiciones normalizadas por la empresa SERVICIOS DE LABORATORIOS DE SUELOS Y PAVIMENTOS S.A.C',0,'L');
            $pdf::Ln(5);
            
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'2. Condiciones del Servicio',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(8,5,'2.1.',0,0,'L');
            $pdf::Multicell(165,5,'La venta se efectuará en metros cúbicos de concreto fresco, considerándose el volumen de concreto entregado y no el que se coloca debido a los desperdicios, sobre excavaciones, etc',0,'L');
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(8,5,'2.2.',0,0,'L');
            $pdf::Multicell(165,5,'El cliente dispone de 30 minutos desde la llegada de la unidad (mixer) a obra, para iniciar la descarga del concreto suministrado. Luego de este tiempo CORP. ASFALPACA S.A.C. no se responsabiliza por la pérdida de asentamiento, ni por el incremento de temperatura.',0,'L');
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Cell(8,5,'2.3.',0,0,'L');
            $pdf::Multicell(165,5,'El cliente deberá enviar la Orden de Compra con tres (03) días de anticipación para la atención del suministro.',0,'L');
            $pdf::Ln();
            
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'3. Calidad',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Multicell(165,5,'El concreto se produce con CEMENTO PACASMAYO y agregados de buena calidad, los cuales cumplen con los requisitos de las siguientes normas:',0,'L');
            $pdf::Ln(5);
            $pdf::Cell(15,5,'',0,0,'L');
            $pdf::Multicell(165,5,'* Cementos: Tipo I (NTP 334.009 / ASTM C-150), Tipo MS (NTP 334.082 / ASTM C-1157)',0,'L');
            $pdf::Cell(15,5,'',0,0,'L');
            $pdf::Multicell(165,5,'* Agregados: NTP 400.037 / ASTM C33',0,'L');
            $pdf::Cell(15,5,'',0,0,'L');
            $pdf::Multicell(165,5,'* Agua: NTP 339.088 / ASTM C160',0,'L');
            $pdf::Ln();
        }
        
        if($cotizacion->tipo=="Alquiler de Vehiculos y Maquinarias"){
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'CONDICIONES DEL SERVICIO',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'1.',0,0,'R');
            $pdf::Multicell(0,5,"Nuestra Empresa cuenta con EQUIPOS PROPIOS",0,'L');
            $pdf::Cell(10,5,'2.',0,0,'R');
            $pdf::Multicell(0,5,"Los trabajadores cuentan con seguro SCTR y nuestras máquinas con seguro TREC y responsabilidad Civil.",0,'L');
            $pdf::Cell(10,5,'3.',0,0,'R');
            $pdf::Multicell(0,5,$cotizacion->comentario,0,'L');
            $pdf::Ln(5);
            
        }
        
        if($cotizacion->tipo=="Venta de mezcla"){
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'OTRAS CONDICIONES',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'1.',0,0,'R');
            $pdf::Multicell(0,5,"Nuestra Empresa cuenta con EQUIPOS PROPIOS",0,'L');
            $pdf::Cell(10,5,'2.',0,0,'R');
            $pdf::Multicell(0,5,"Nuestra planta de asfalto queda ubicada en Cantera Tres Tomas, distrito de Mesones Muro, provincia de Ferreñafe.",0,'L');
            $pdf::Cell(10,5,'3.',0,0,'R');
            $pdf::Multicell(0,5,"Los Lavados de Mezcla Asfáltica se entregarán con fecha de despacho por cada 70 m3.",0,'L');
            $pdf::Cell(10,5,'4.',0,0,'R');
            $pdf::Multicell(0,5,$cotizacion->comentario,0,'L');
            $pdf::Ln(5);
            
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'NUESTRAS CERTIFICACIONES',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::Cell(87,5,"- Registro OSINERGMIN N° 108889-112-250514",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(80,5,"de fecha 25/05/2014 como Consumidor",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(12,5,"",0,0,'L');
            $pdf::Multicell(160,5," Directo de Combustibles Líquidos y Otros Productos Derivados de los Hidrocarburos. (Siendo la única empresa que cuenta con esta autorización a nivel Norte).",0,'L');
            $pdf::Ln(5);
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(132,5,"- Diseño de Mezcla Asfáltica en Caliente del mes de OCTUBRE del 2019",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(30,5,", elaborado por la",0,0,'L');
            $pdf::Ln();
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::Cell(0,5,"  empresa Servicios de Laboratorios de Suelos y Pavimentos S.A.C.",0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(132,5,"- La Calibración de Planta de Asfalto - 2019",0,0,'L');
            $pdf::Ln();
            $pdf::Ln();
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(3,5,"- ",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(160,5,"Certificado emitido por la Dirección General de Caminos y Ferrocarriles –MTC mediante Oficio Nº024-2012-MTC/14.01; de los ensayos de laboratorio de la muestra de los agregados perteneciente a la Cantera Tres Tomas–Ferreñafe–Lambayeque para nuestro diseño de mezcla.",0,'L');
            //$pdf::Image("http://martinampuero.com/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(3,5,"- ",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(160,5,"Certificado emitido por la Dirección General de Caminos y Ferrocarriles –MTC mediante Oficio Nº229-2012-MTC/14.01; de los ensayos de laboratorio realizados al asfalto sólido PEN 60/70 de Refinería Talara Petroperú y Aditivo Mejorador de Adherencia AR-RED RADICOTE para nuestro diseño de mezcla.",0,'L');
            $pdf::Cell(10,5,"",0,0,'L');
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(3,5,"- ",0,0,'L');
            $pdf::SetFont('helvetica','',11);
            $pdf::Multicell(160,5,"Certificado de la calidad del Asfalto Solido PEN 60/70 y Asfalto Liquido MC-30, emitida por Refinería Talara-Petroperú (donde se verifica la fecha de compra de Asfaltos realizada por nuestra empresa).",0,'L');
            $pdf::Ln(5);
        }
        
        if($cotizacion->tipo=="Venta de maquinaria"){
            $pdf::SetFont('helvetica','B',11);
            $pdf::Cell(50,5,'CARACTERISTICAS DEL BIEN',0,0,'L');
            $pdf::Ln();
            $pdf::SetFont('helvetica','',11);
            $pdf::Cell(10,5,'',0,0,'L');
            $pdf::Multicell(0,5,$cotizacion->comentario,0,'L');
        }
        
        $pdf::Cell(10,5,"",0,0,'L');
        $pdf::Cell(0,5,"Quedamos a la espera de su grata respuesta y a su disposición para cualquier consulta.",0,0,'L');
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Cell(10,5,"",0,0,'L');
        $pdf::Cell(0,5,"Atte.",0,0,'L');
        $pdf::Ln();
        $pdf::Image("http://martinampuero.com/asfalpaca/dist/img/firma.png", 40, $pdf::GetY(), 120, 25);
    
        $pdf::Output('Contrato.pdf');
    }

    public function Reporte(Request $request){
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::SetTitle('Reporte de Cotizaciones');
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(0,8,utf8_decode("REPORTE DE COTIZACIONES"),0,0,'C');
        $pdf::Ln(); 
        $pdf::Ln(); 
        
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(17,5,"NRO",1,0,'C');
        $pdf::Cell(50,5,"EMPRESA",1,0,'C');
        $pdf::Cell(40,5,"CONTACTO",1,0,'C');
        $pdf::Cell(18,5,"TELF",1,0,'C');
        $pdf::Cell(60,5,"OBRA",1,0,'C');
        $pdf::Cell(80,5,"SERVICIOS COTIZADOS",1,0,'C');
        $pdf::Cell(20,5,"SITUACION",1,0,'C');
        $pdf::Ln();
        
        $resultado = Movimiento::join('person','person.id','=','movimiento.persona_id')
                    ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                    ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                    ->join('producto','producto.id','=','detallemovimiento.producto_id')
                    ->join('unidad','unidad.id','=','producto.unidad_id')
                    ->where('tipomovimiento_id','=',5)
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
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('situacion','like',$request->input('situacion'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa','producto.nombre as producto2','unidad.nombre as unidad2')->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();
        $numeroant="";
        foreach($lista as $key=>$value){
            $pdf::SetFont('helvetica','',8.5);
            $alto=$pdf::getNumLines($value->persona->razonsocial, 50)*4;
            $alto1=$pdf::getNumLines($value->cliente, 40)*4;
            $alto2=$pdf::getNumLines($value->entregado, 60)*4;
            $alto3=$pdf::getNumLines(number_format($value->cantidad,2,'.',',').'|'.$value->producto2.'|'.$value->unidad2.'|'.number_format($value->precioventa,2,'.',','), 80)*4;
            if($alto1>$alto) $alto=$alto1;
            if($alto2>$alto) $alto=$alto2;
            if($alto3>$alto) $alto=$alto3;
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            if($y>170){
                $pdf::AddPage('L');
                $y=$pdf::GetY();
            }
            if($value->numero!=$numeroant){
                $pdf::Cell(17,$alto,$value->numero,'LRT',0,'L');
                $pdf::Multicell(50,3.5,$value->persona->razonsocial,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(67,$alto,'','LRT',0,'L');
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::Multicell(40,3.5,$value->cliente,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(40,$alto,'','LRT',0,'L');
                $pdf::Cell(18,$alto,$value->persona->telefono,'LRT',0,'C');
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::Multicell(60,3.5,$value->entregado,0,'L');
                $pdf::SetXY($x,$y);
                $pdf::Cell(60,$alto,'','LRT',0,'L');
                $pdf::Cell(80,$alto,'','LRT',0,'L');
                $pdf::Cell(20,$alto,$value->situacion=='P'?'PENDIENTE':($value->situacion=='C'?'CONFIRMADO':($value->situacion=='R'?'DESISTIDO':'ANULADO')),'LRT',0,'C');
                $pdf::SetX($pdf::GetX()-100);
                $numeroant = $value->numero;
            }else{
                $pdf::Cell(17,$alto,'','LR',0,'L');
                $pdf::Cell(50,$alto,'','LR',0,'L');
                $pdf::Cell(40,$alto,'','LR',0,'L');
                $pdf::Cell(18,$alto,'','LR',0,'L');
                $pdf::Cell(60,$alto,'','LR',0,'L');
                $pdf::Cell(80,$alto,'','LR',0,'L');
                $pdf::Cell(20,$alto,'','LR',0,'L');
                $pdf::SetX($pdf::GetX()-100);
            }
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(80,3.5,number_format($value->cantidad,2,'.',',').' | '.$value->producto2.' | '.$value->unidad2.' | '.number_format($value->precioventa,2,'.',','),0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(80,$alto,'',1,0,'L');
            $pdf::Ln();
        }
        $pdf::Cell(17,$alto,'','T',0,'L');
        $pdf::Cell(50,$alto,'','T',0,'L');
        $pdf::Cell(40,$alto,'','T',0,'L');
        $pdf::Cell(18,$alto,'','T',0,'L');
        $pdf::Cell(60,$alto,'','T',0,'L');
        $pdf::Cell(80,$alto,'','T',0,'L');
        $pdf::Cell(20,$alto,'','T',0,'L');
        $pdf::Ln();
        $pdf::Output('ReporteCotizacion.pdf');
    }

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado = Movimiento::join('person','person.id','=','movimiento.persona_id')
                    ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                    ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                    ->join('producto','producto.id','=','detallemovimiento.producto_id')
                    ->join('unidad','unidad.id','=','producto.unidad_id')
                    ->where('tipomovimiento_id','=',5)
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
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('situacion','like',$request->input('situacion'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa','producto.nombre as producto2','unidad.nombre as unidad2')->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();

        Excel::create('ExcelCotizacion', function($excel) use($lista,$request) {
 
            $excel->sheet('Cotizacion', function($sheet) use($lista,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "NUMERO";
                $cabecera[] = "EMPRESA";
                $cabecera[] = "CONTACTO";
                $cabecera[] = "TELEFONO";
                $cabecera[] = "OBRA";
                $cabecera[] = "SERVICIOS COTIZADOS";
                $cabecera[] = "SITUACION";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $subtotal=0;
                $igv=0;
                $total=0;
                $totalpagado=0;
                $numeroant="";
                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = $value->numero;
                    $detalle[] = $value->persona->razonsocial;
                    $detalle[] = $value->cliente;
                    $detalle[] = $value->persona->telefono;
                    $detalle[] = $value->entregado;
                    $detalle[] = number_format($value->cantidad,2,'.',',').' | '.$value->producto2.' | '.$value->unidad2.' | '.number_format($value->precioventa,2,'.',',');
                    if($value->situacion=='A'){
                        $situacion='Anulado';
                    }elseif($value->situacion=='P'){
                        $situacion='Pendiente';
                    }elseif($value->situacion=='R'){
                        $situacion='Desistido';
                    }elseif($value->situacion=='C'){
                        $situacion='Confirmado';
                    }
                    $detalle[] = $situacion;
                    $total=$total+number_format($value->total,2,'.','');
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
                $cabecera[] = number_format($total,2,'.','');
                //$cabecera[] = number_format($totalpagado,2,'.','');
                $sheet->row($c,$cabecera);*/
            });
        })->export('xls');
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
        $entidad  = 'Cotizacion';
        $formData = array('route' => array('cotizacion.confirm', $id), 'method' => 'Confirm', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Confirmar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function rechaza($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $venta = Movimiento::find($id);
            $venta->situacion='R';
            $venta->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function rechazar($id, $listarLuego)
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
        $entidad  = 'Cotizacion';
        $formData = array('route' => array('cotizacion.rechaza', $id), 'method' => 'Rechaza', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Desistir';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
}
/*
- si es venta pre-mezclado- Nº 136- 2018 
- construccion condiciones  Nº 259- 2018 
- alquiler condiciones  262-2018 
- venta de mezcla Nro 280
- Para contrato:precio,nombre cliente, detalle de la cotizacion, nombre de la obra, y forma de pago
*/

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