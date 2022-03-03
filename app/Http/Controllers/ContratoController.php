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
use App\Pago;
use App\Librerias\Libreria;
use App\Librerias\EnLetras;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;
use Word;
use Excel;

class ContratoController extends Controller
{
    protected $folderview      = 'app.contrato';
    protected $tituloAdmin     = 'Contrato';
    protected $tituloRegistrar = 'Registrar Contrato';
    protected $tituloModificar = 'Seguimiento Contrato';
    protected $tituloEliminar  = 'Anular Contrato';
    protected $tituloVer       = 'Ver Contrato';
    protected $rutas           = array('create' => 'contrato.create', 
            'edit'   => 'contrato.edit',
            'show'   => 'contrato.show', 
            'delete' => 'contrato.eliminar',
            'search' => 'contrato.buscar',
            'index'  => 'contrato.index'
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
        $entidad          = 'Contrato';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                                ->where('movimiento.tipomovimiento_id','=',6);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafin'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),DB::raw('m2.numero as numeroref'))->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro. Cotizacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cliente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Obra', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
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
        $entidad          = 'Contrato';
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
        $entidad  = 'Contrato';
        $movimiento = null;       
        $formData = array('contrato.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar'));
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
            $Venta->tipomovimiento_id=6;//CONTRATO
            $Venta->tipodocumento_id=11;
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='P';//Pendiente => P / Cobrado => C / Boleteado => B
            $Venta->comentario = $request->input('comentario');
            $Venta->tipo = $request->input('tipo');
            $Venta->incluye = $request->input('incluye');
            $Venta->responsable_id=$user->person_id;
            $Venta->movimiento_id=$request->input('contrato_id');
            $Venta->entregado=$request->input('entregado');
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
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $movimiento = Movimiento::find($id);
        $ventas = Movimiento::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                    ->where('movimiento.movimiento_id','=',$id)
                    ->where('movimiento.tipomovimiento_id','=',2)
                    ->whereNotIn('movimiento.situacion',['A'])
                    ->select('movimiento.*','detallemovimiento.producto','detallemovimiento.cantidad','detallemovimiento.unidad','detallemovimiento.precioventa')
                    ->get();
        $pagos = Pago::join('movimiento','movimiento.id','=','pago.movimiento_id')
                    ->where('movimiento.movimiento_id','=',$id)
                    ->where('movimiento.tipomovimiento_id','=',2)
                    ->whereNotIn('movimiento.situacion',['A'])
                    ->select('pago.*')
                    ->get();
        $entidad  = 'Contrato';
        $formData = array('contrato.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Guardar';
        return view($this->folderview.'.seguimiento')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar','ventas', 'pagos'));
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
            'persona.required'         => 'Debe ingresar un cliente'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request, $id, $user, &$dat){
            $cotizacion = Movimiento::find($id);
            if($request->input('listVenta')!=""){
                $arr=explode(",",$request->input('listVenta'));
                for($c=0;$c<count($arr);$c++){
                    $band=false;
                    $Venta = Movimiento::where('numero','like',$request->input('txtNumeroF'.$arr[$c]))->where('tipomovimiento_id','=',2)->where('tipodocumento_id','=',4)->whereNotIn('situacion',['A'])->first();
                    if(is_null($Venta)){
                        $Venta       = new Movimiento();
                    }else{
                        $band=true;
                    }
                    $Venta->fecha = $request->input('txtFechaF'.$arr[$c]);
                    $Venta->numero = $request->input('txtNumeroF'.$arr[$c]);
                    if($band){
                        $Venta->subtotal = $request->input('txtSubtotalF'.$arr[$c]) + $Venta->subtotal;
                        $Venta->igv = $request->input('txtIgvF'.$arr[$c]) +  $Venta->igv;
                        $Venta->total = $request->input('txtTotalF'.$arr[$c]) +  $Venta->total; 
                    }else{
                        $Venta->subtotal = $request->input('txtSubtotalF'.$arr[$c]);
                        $Venta->igv = $request->input('txtIgvF'.$arr[$c]);
                        $Venta->total = $request->input('txtTotalF'.$arr[$c]); 
                    }
                    $Venta->tipomovimiento_id=2;//VENTA
                    $Venta->tipodocumento_id=4;
                    $Venta->persona_id = $cotizacion->persona_id;
                    $Venta->situacion='P';//Pendiente => P / Cobrado => C / Boleteado => B
                    $Venta->comentario = '';
                    $Venta->tipo = $request->input('cboTipoF'.$arr[$c]);
                    $Venta->responsable_id=$user->person_id;
                    $Venta->movimiento_id=$cotizacion->id;
                    $Venta->incluye = $request->input('txtDetraccionF'.$arr[$c]);
                    if($Venta->incluye=='S'){
                        //if($band){
                            $Venta->detraccion = $request->input('txtMontoDetraccionF'.$arr[$c])*$Venta->total/100;
                        //}
                        $Venta->nrooperacion = 'Detraccion';
                    }
                    $Venta->moneda = $request->input('txtMonedaF'.$arr[$c]);
                    if($Venta->moneda=='D'){
                        $Venta->tipocambio = $request->input('txtTipoCambioF'.$arr[$c]);
                    }
                    $Venta->save();
                    $Detalle = new Detallemovimiento();
                    $Detalle->movimiento_id=$Venta->id;
                    $Detalle->cantidad=$request->input('txtCantF'.$arr[$c]);
                    $Detalle->precioventa=$request->input('txtPrecioF'.$arr[$c])*1.18;
                    $Detalle->producto=$request->input('txtDescripcionF'.$arr[$c]);
                    $Detalle->unidad=$request->input('txtUnidadF'.$arr[$c]);
                    $Detalle->preciocompra=0;
                    $Detalle->save();
                }
            }
            $listapago="";
            if($request->input('listAvance')!=""){
                $arr=explode(",",$request->input('listAvance'));
                for($c=0;$c<count($arr);$c++){
                    $listapago.=$request->input('txtFechaV'.$arr[$c])."|".$request->input('txtM3V'.$arr[$c])."|".$request->input('txtM2V'.$arr[$c])."|".$request->input('txtProductoV'.$arr[$c])."@";
                }
                $listapago = substr($listapago,0,strlen($listapago)-1);
            }
            $cotizacion->comentario=$request->input('comentario');
            $cotizacion->listapago=$listapago;
            $cotizacion->save();
            $dat[0]=array("respuesta"=>"OK");
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
        $numeroventa = Movimiento::NumeroSigue2(6,11);
        echo $numeroventa.date('-Y');
    }

    public function cotizacionautocompletar($searching)
    {
        $resultado        = Movimiento::where('numero','like','%'.$searching.'%')
                            ->where('situacion','like','C')
                            ->where('tipomovimiento_id','=','5')->orderBy('numero', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            $data[] = array(
                            'label' => trim($value->numero),
                            'id'    => $value->id,
                            'value' => trim($value->numero),
                            'persona_id' => $value->persona_id,
                            'persona' => $value->persona->razonsocial, 
                            'entregado' => $value->entregado
                        );
        }
        return json_encode($data);
    }

    public function agregarDetalle(Request $request){
        $list = Detallemovimiento::join('producto','producto.id','=','detallemovimiento.producto_id')
                        ->join('unidad','unidad.id','=','producto.unidad_id')
                        ->where('movimiento_id','=',$request->input('id'))
                        ->select('detallemovimiento.*','producto.nombre as producto2','unidad.nombre as unidad2')
                        ->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array('idproducto'=>$value->producto_id,
                            'producto'=>$value->producto2,
                            'cantidad'=>$value->cantidad,
                            'precioventa'=>$value->precioventa,
                            'preciocompra'=>$value->preciocompra,
                            'unidad'=>$value->unidad2,
                            'subtotal'=>round($value->cantidad*$value->precioventa,2));
        }
        return json_encode($data);
    }

    public function word(Request $request){
        $contrato = Movimiento::find($request->input('id'));
        
        $phpWord = new Word();

        /* Note: any element you append to a document must reside inside of a Section. */

        // Adding an empty Section to the document...
        $section = $phpWord->addSection();
        // Adding Text element to the Section having font styled by default...
        $section->addText(
            'CONTRATO PRIVADO DE EJECUCIÓN DE OBRA N° '.$contrato->numero.'/ CORP.ASFALPACA.SAC',
            array('name' => 'Arial', 'size' => 11, 'bold' => true),
            array('align' => 'center')
        );
        $section->addTextBreak();
        $section->addText(
            'Conste por el presente documento, CONTRATO DE EJECUCION DE OBRA, que celebran la empresa '.$contrato->persona->razonsocial.' con RUC N° '.$contrato->persona->ruc.' con domicilio fiscal en '.$contrato->persona->direccion.', con correo electrónico: '.$contrato->persona->email.' representada por el Sr. '.$contrato->persona->apellidopaterno.' '.$contrato->persona->apellidomaterno.' '.$contrato->persona->nombres.' identificado con DNI Nº '.$contrato->persona->dni.', a quien en adelante se denominará EL CONTRATANTE, y, de otra parte, la empresa CORPORACION ASFALTOS Y PAVIMENTOS CASTILLO SAC. Con RUC N° 20314028164, con domicilio fiscal Mz. N° 19 - Lote N° 05 pueblo joven Chosica del Norte, distrito de La Victoria, provincia de Chiclayo, departamento de Lambayeque, con correo electrónico: comercialización.grupocastillo@hotmail.com, debidamente representada su apoderado ELIAS RAFAEL CASTILLO AYALA, Identificado con DNI Nº 16656066, con poderes inscritos en la partida electrónica Nº 02107776 del Registro de Personas Jurídicas de los Registros Públicos de la Oficina Registral de Chiclayo, a quien en adelante se le denominará EL CONTRATISTA en los términos y condiciones siguientes: ',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );

        //$section->addTextBreak();
        $section->addText(
            'PRIMERA: EL CONTRATANTE, contrata a EL CONTRATISTA para la construcción de carpeta asfáltica en caliente para la OBRA: “'.$contrato->comentario.'” distrito de José Leonardo Ortiz, provincia de Chiclayo, departamento Lambayeque, en adelante LA OBRA.  
EL CONTRATISTA es una empresa con Personería Jurídica con amplia experiencia en construcciones viales contando con: ',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );
        
        
        //$section->addTextBreak();
        $section->addText('    •Informe de DISEÑO DE MEZCLA ASFALTICA EN CALIENTE PEN 60/70 del mes de marzo del 2019, elaborado por la Empresa Servicios de Laboratorios de Suelos y Pavimentos S.A.C. y validada por la Dirección General de Caminos y Ferrocarriles – Ministerio de Transportes y Comunicaciones mediante el Oficio Nº024-2012-MTC/14 ensayo realizado a 03 muestras de agregados de nuestra Cantera “Tres Tomas”. ',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );
        $section->addText('    •Ficha Técnica que valida el aditivo Mejorador de Adherencia AR RED - RADICOTE para ser utilizado como adherente en la elaboración de la Mezcla Asfáltica en Caliente.  ',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );
        $section->addText('    •Una moderna Planta de Asfalto Ecológica, de Marca ABL INTERNATIONAL, modelo DT140, del año 2010, de procedencia Colombiana.',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );

        //$section->addTextBreak();
        $section->addText('SEGUNDA: Por el presente instrumento privado EL CONTRATANTE celebra un contrato en la modalidad de Precios Unitarios con EL CONTRATISTA, en virtud del cual, éste último deberá ejecutar los siguientes trabajos a todo costo:',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );

        $section->addText('2.1 CONSTRUCCION DE CARPETA ASFALTICA EN CALIENTE E= 2”',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );

        $section->addText('TERCERA: MONTO DEL CONTRATO ',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );
        
        $section->addText('El monto del contrato se realizará por la ejecución de:',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );
        
        $letras = new EnLetras();
        $valor=$letras->ValorEnLetras($contrato->total, "SOLES" );//letras
        $valor1=$letras->ValorEnLetras($contrato->total*0.7, "SOLES" );//letras
        $valor2=$letras->ValorEnLetras($contrato->total*0.3, "SOLES" );//letras
        
        $section->addText('
El monto total del contrato asciende a la suma de S/ '.number_format($contrato->total,2,'.',',').' ('.$valor.'), que incluye los impuestos de ley, mano de obra y cumplimientos de la Normatividad Laboral, pago a entidades de seguridad social, SENCICO, costo de equipos, maquinarias, herramientas, fletes, seguros, dirección técnica, gastos generales, utilidad e impuestos hasta la culminación de LA OBRA. La operación se encuentra sujeta al Sistema de Pago de Obligaciones Tributarias con el Gobierno Central de – SPOT (Sistema de Detracciones), regulado por el Decreto Legislativo N° 940. En caso de variación e incremento de precios de asfaltos, según Listas de Precios de PETROPERÚ, se actualizarán los precios de estos insumos, lo cual modificará el precio en soles del Monto Final del contrato de acuerdo a la Propuesta Económica (Anexo N° 1). 
CUARTA: La retribución que pagará EL CONTRATANTE en calidad de contraprestación por el servicio a realizar por EL CONTRATISTA, detallada en la cláusula tercera, será cancelado en la siguiente forma y oportunidad:  
1.	El importe de S/ '.number_format($contrato->total*0.7,2,'.',',').' ('.$valor1.') por adelanto del total de Obra correspondiente al 70% del Importe Neto, mediante abono en cuenta corriente de LA CONTRATISTA.
2.	El importe de S/ '.number_format($contrato->total*0.3,2,'.',',').' ('.$valor2.'), correspondiente al 30% del Importe Neto, mediante cheque diferido para ser cobrado el día 28 de junio, entregado a la suscripción de la presente.
3.	El importe de S/ 7,449.12 (SIETE MIL CUATROCIENTOS CUARENTA Y NUEVE CON 12/100 SOLES) por concepto de detracción, mediante abono en cuenta de detracciones de LA CONTRATISTA.',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );

        $section->addText('Asimismo, ambas partes acuerdan que el monto final del contrato está sujeto al metrado real ejecutado por lo que cualquier variación las partes brindan su asentimiento.
QUINTA: La ejecución del presente contrato se realizará de acuerdo a lo indicado en las órdenes de servicio, donde precisará importes, metrados, fecha de ejecución y demás condiciones a que se contrae. Asimismo, la ejecución estará sujeta a la suscripción de la (s) declaración (es) jurada (s) de habilitación del terreno para inicio de construcción de la carpeta asfáltica en caliente por cada orden de servicios, con el asentimiento de EL CONTRATISTA. 
EL CONTRATANTE se compromete a que la verificación de la rasante de la base granular, se halle al 100% correcta con respecto a los trazos (alineamientos, pendientes, bombeo) y que no presente deformaciones, ondulaciones, bacheo ni huecos.
El presente contrato se encuentra vigente desde su suscripción, hasta el cumplimiento total del objeto de este contrato y la cancelación efectiva del precio total acordado contenido en la cláusula cuarta, dentro de las condiciones normales.  
El plazo de ejecución podrá extenderse o reducirse por indicación de EL CONTRATANTE, siempre que dicha modificación de plazo no requiera de un aumento de recursos para la ejecución del contrato, y/o represente mayores costos que los considerados en el presupuesto contratado.
SEXTA: El presente contrato consta de un (01) anexo suscrito por ambas partes y que forma parte integral de este contrato, como se detalla:  
Anexo 1: Propuesta Técnica – Económica. (COTIZACIÓN Nº '.$contrato->movimientoref->numero.') 
SEPTIMA: En todo lo no previsto por las partes en el presente contrato, ambas se someten a lo establecido por los artículos 1351°, 1352°, 1353°, 1354° y 1772° del Código Civil y demás normas pertinentes. 
OCTAVA: El CONTRATISTA garantiza la perfecta ejecución de los trabajos en el cumplimiento de los plazos, asimismo planeará y será responsable por los métodos de trabajo y la eficiencia de los equipos empleados en la Ejecución de la Obra, los que deberán asegurar un ritmo apropiado y calidad satisfactoria. 
NOVENA: El CONTRATANTE, es responsable de las modificaciones que ordene y apruebe en los proyectos, estudios, informes o similares o de aquellos cambios que se generen debido a la necesidad de ejecución de los mismos, los cuales deberán ser informados a EL CONTRATISTA.
Adicionalmente, EL CONTRATANTE se obliga a:',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );

        $section->addText('-	La seguridad en obra, señalizaciones para el control de tránsito y seguridad vial.',
            array('name' => 'Arial', 'size' => 11, 'align' => 'center')
        );
        $section->addText('-	Los acuerdos relacionados con el Sindicato de Construcción Civil.',
            array('name' => 'Arial', 'size' => 11, 'align' => 'center')
        );
        $section->addText('-	EL CONTRATANTE proporcionará arenilla para el sello de la carpeta asfáltica en caliente y agua para la permeabilización del rodillo liso.',
            array('name' => 'Arial', 'size' => 11, 'align' => 'center')
        );
        $section->addText('-	EL CONTRATANTE proporcionará todos los frentes de trabajo disponibles necesarios para que EL CONTRATISTA trabaje las partidas contratadas.  ',
            array('name' => 'Arial', 'size' => 11, 'align' => 'center')
        );

        $section->addText('De ser el caso, que la obra en mención sea pública, EL CONTRATANTE, deberá solicitar a la ENTIDAD una CONSTANCIA que permita a EL CONTRATISTA el acceso para ejecutar dicha obra.  
EL CONTRATISTA es responsable de la ejecutar la totalidad de las obligaciones a su cargo a satisfacción de EL CONTRATANTE, de acuerdo a lo establecido en el contrato, planos de obra y/o modificaciones, debidamente comunicados; si existiera alguna deficiencia esta deberá ser levantada.
Cualquier observación realizada por EL CONTRATANTE en el proceso de ejecución de los trabajos será subsanada por EL CONTRATISTA en el plazo que esta lo señale, caso contrario dicha observación será asumida por EL CONTRATANTE y descontada del fondo de garantía retenido a EL CONTRATISTA, condición que también será aplicada para EL CONTRATANTE. 
DECIMA: EL CONTRATANTE entregará a EL CONTRATISTA como prueba de la ejecución y desarrollo de LA OBRA, lo siguiente: Carta de Recepción de obra y Carta de Conformidad de Obra; ambas instrumentales serán efectivos en un plazo no mayor de 7 días Calendario, contados a partir del día siguiente de la entrega de la obra materia del presente contrato; estos actos no enerva el derecho de EL CONTRATANTE a reclamar posteriormente por defectos o vicios ocultos; igualmente, no enervará el derecho de EL CONTRATISTA de solicitar la cancelación de los pagos pendientes y de la aplicación de los respectivos intereses hasta la cancelación total del monto pactado. 
DECIMA PRIMERA: Queda establecido que si en el curso de cinco (05) años posteriores a la conformidad de LA OBRA, ésta se destruye total o parcialmente por razones y/o defectos imputables a EL CONTRATISTA, procederá a las reparaciones que hubiere lugar, previo informe de las fallas o vicios advertidos por EL CONTRATANTE, sustentado en un ensayo de diamantina, ensayo de Marshall o prueba de densidad de campo y compactación, según corresponda, siendo de aplicación lo regulado en la cláusula decima cuarta.
DECIMA SEGUNDA: EL CONTRATANTE autoriza a EL CONTRATISTA para que pueda ceder la titularidad de los derechos crediticios que emanan del presente contrato de obra en favor de tercero, lo que incluye la facultad para cobrar el capital adeudado, intereses; obligándose en tal sentido EL CONTRATANTE, a cumplir todas y cada una de las prestaciones originales a su cargo, manifestando EL CONTRATISTA en armonía con el artículo 1435° del Código Civil, su conformidad con la cesión.
DECIMA TERCERA: Ambas partes acuerdan que en todo lo establecido expresamente en el Contrato se aplicarán las normas del Código Civil y demás legislación aplicable. 
DECIMA CUARTA: Las partes contratantes establecen que el cambio de domicilio mediante la suscripción de cláusula adicional, o, que se hubiera comunicado mediante Carta Notarial el cambio de domicilio, con una anticipación no menor de cinco (5) días hábiles, conforme a lo establecido en los artículos 34 y 40 del Código Civil. 
DECIMA QUINTA: Para efectos de cualquier controversia que se genere con motivo de la celebración y ejecución de éste contrato, las partes podrán resolverlos en una primera oportunidad mediante el trato directo, de mantenerse la controversia ambas partes renuncian al fuero de sus domicilios y se someten de manera expresa a la competencia territorial de los jueces y tribunales de Chiclayo; fijando con tal objeto como sus domicilios los consignados en la parte introductoria de este contrato, lugar donde además autorizan sean dejadas todas la notificaciones y avisos a que hubiere lugar.',
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'both')
        );
        
		$section->addText('Chosica del Norte, '.date('d').' de Agosto del '.date('Y'),
            array('name' => 'Arial', 'size' => 11),
            array('align' => 'right')
        );


        
        $header = $section->addHeader();
        // Add header for all other pages
        $header->addImage(__DIR__.'/logo.jpg', array('width' => 490, 'height' => 70));
        
        // Add footer
        $footer = $section->addFooter();
        $footer->addImage(__DIR__.'/pie.png', array('width' => 490, 'height' => 70));
        
        
        
        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $file = 'Contrato'.$contrato->contrato.'.docx';
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $objWriter->save("php://output");
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
    
    
    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                                ->where('movimiento.tipomovimiento_id','=',6);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafin'));
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','like','%'.$request->input('numero').'%');
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.$request->input('cliente').'%');
        }
        $resultado            = $resultado->select('movimiento.*',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),DB::raw('m2.numero as numeroref'))->orderBy('movimiento.id', 'desc')->orderBy('fecha', 'desc')->get();
        
        Excel::create('ExcelAvance', function($excel) use($resultado,$request) {
 
            $excel->sheet('Cuentas', function($sheet) use($resultado,$request) {
 
                $array = array();
                $band=true;
                $subtotal=0;
                $igv=0;
                $total=0;
                $totalpagado=0;
                $c=1;
                foreach ($resultado as $key => $value){
                    $cabecera = array();
                    $cabecera[] = "Fecha: ".date('d/m/Y',strtotime($value->fecha));
                    $sheet->row($c,$cabecera);
                    $celdas      = 'A'.$c.':F'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;
                    
                    $cabecera = array();
                    $cabecera[] = "Contrato: ".$value->numero;
                    $sheet->row($c,$cabecera);
                    $celdas      = 'A'.$c.':F'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;
                    
                    $cabecera = array();
                    $cabecera[] = "Cliente: ".$value->cliente;
                    $sheet->row($c,$cabecera);
                    $celdas      = 'A'.$c.':F'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;
                    
                    $cabecera = array();
                    $cabecera[] = "Obra: ".$value->comentario;
                    $sheet->row($c,$cabecera);
                    $celdas      = 'A'.$c.':F'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;
                    
                    $ventas = Movimiento::join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('movimiento.movimiento_id','=',$value->id)
                                ->where('movimiento.tipomovimiento_id','=',2)
                                ->whereNotIn('movimiento.situacion',['A'])
                                ->select('movimiento.*','detallemovimiento.producto','detallemovimiento.cantidad','detallemovimiento.unidad','detallemovimiento.precioventa')
                                ->get();
                    $cabecera = array();
                    $cabecera[] = "SERVICIO";
                    $cabecera[] = "METRADO";
                    $cabecera[] = "PRECIO";
                    $cabecera[] = "SUBTOTAL";
                    $cabecera[] = "IGV";
                    $cabecera[] = "TOTAL";
                    $sheet->row($c,$cabecera);
                    $celdas      = 'A'.$c.':F'.$c;
                    $sheet->cells($celdas, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;
                    $total = 0;
                    foreach($ventas as $k=>$v){
                        $cabecera = array();
                        $cabecera[] = $v->producto;
                        $cabecera[] = $v->cantidad;
                        $cabecera[] = number_format($v->precioventa/1.18,2,'.','');
                        $cabecera[] = number_format($v->cantidad*$v->precioventa/1.18,2,'.','');
                        $cabecera[] = number_format($v->precioventa*$v->cantidad - $v->precioventa*$v->cantidad/1.18,2,'.','');
                        $cabecera[] = number_format($v->cantidad*$v->precioventa,2,'.','');
                        $sheet->row($c,$cabecera);
                        $c=$c+1;
                        $total = $total + number_format($v->cantidad*$v->precioventa,2,'.','');
                    }
                    $cabecera = array();
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "TOTAL:";
                    $cabecera[] = number_format($total,2,'.','');
                    $sheet->row($c,$cabecera);
                    $celdas      = 'E'.$c.':F'.$c;
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('rigth');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+2;
                    
                    
                    $pagos = Pago::join('movimiento','movimiento.id','=','pago.movimiento_id')
                                ->where('movimiento.movimiento_id','=',$value->id)
                                ->where('movimiento.tipomovimiento_id','=',2)
                                ->whereNotIn('movimiento.situacion',['A'])
                                ->select('pago.*')
                                ->get();
                    $cabecera = array();
                    $cabecera[] = "FECHA";
                    $cabecera[] = "BANCO";
                    $cabecera[] = "FORMA PAGO";
                    $cabecera[] = "TOTAL";
                    $sheet->row($c,$cabecera);
                    $celdas      = 'A'.$c.':F'.$c;
                    $sheet->cells($celdas, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('center');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;     
                    $pagado = 0;
                    foreach($pagos as $k=>$v){
                        $cabecera = array();
                        $cabecera[] = date('d/m/Y',strtotime($v->fecha));
                        $cabecera[] = $v->banco->nombre;
                        $cabecera[] = $v->formapago;
                        $cabecera[] = number_format($v->monto,2,'.','');
                        $sheet->row($c,$cabecera);
                        $c=$c+1;
                        $pagado = $pagado + $v->monto;
                    }
                    $cabecera = array();
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "TOTAL";
                    $cabecera[] = number_format($pagado,2,'.','');
                    $sheet->row($c,$cabecera);
                    $celdas      = 'C'.$c.':D'.$c;
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('rigth');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;     
                    
                    $cabecera = array();
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "SALDO";
                    $cabecera[] = number_format($total - $pagado,2,'.','');
                    $sheet->row($c,$cabecera);
                    $celdas      = 'C'.$c.':D'.$c;
                    $sheet->cells($celdas, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('rigth');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+2;     
                    
                    $cabecera = array();
                    $cabecera[] = "AVANCE";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $cabecera[] = "";
                    $sheet->row($c,$cabecera);
                    $celdas      = 'A'.$c.':D'.$c;
                    $sheet->mergeCells($celdas);
                    $sheet->cells($celdas, function($cells) {
                        $cells->setAlignment('center');
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '11',
                            'bold'       =>  true
                            ));
                    });
                    $c=$c+1;
                    $detallee = Detallemovimiento::join('movimiento','movimiento.id','=','detallemovimiento.movimiento_id')
                        ->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                        ->leftjoin('unidad','unidad.id','=','producto.unidad_id')
                        ->where('movimiento.id','=',$value->id)
                        ->whereNotIn('movimiento.situacion',['A'])
                        ->whereNull('detallemovimiento.deleted_at')
                        ->select('detallemovimiento.*',DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto2'),DB::raw('case when detallemovimiento.producto_id>0 then unidad.nombre else detallemovimiento.unidad end as unidad2'))
                        ->get();
                    $productoant='';
                    foreach($detallee as $k=>$v){
                        $tot1=0;$tot2=0;
                        if($productoant!=$v->producto2){
                            if($value->listapago!=""){
                                $lista = explode("@",$value->listapago);
                                for($x=0;$x<count($lista);$x++){
                                    $dat=explode("|",$lista[$x]);
                                    if(strpos(($v->producto2),trim($dat[3]))!==false){
                                        $detalle = array();
                                        if($productoant!=$v->producto2){
                                            $detalle[] = $v->producto2;
                                            $productoant=$v->producto2;
                                        }else{
                                            $detalle[] = '';
                                            
                                        }
                                        $detalle[] = date('d/m/Y',strtotime($dat[0]));
                                        $detalle[] = $dat[1];
                                        $detalle[] = $dat[2];
                                        $sheet->row($c,$detalle);
                                        $tot1=$tot1+round($dat[1],2);
                                        $tot2=$tot2+$dat[2];
                                        $c=$c+1;
                                    }
                                }
                            }
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "TOTAL AVANCE";
                            $detalle[] = (trim($v->unidad2)=="M3"?($tot1):'');
                            $detalle[] = (trim($v->unidad2)=="M2"?($tot2):'');
                            $sheet->row($c,$detalle);
                            $celdas      = 'B'.$c.':D'.$c;
                            $sheet->cells($celdas, function($cells) {
                                $cells->setBorder('thin','thin','thin','thin');
                                $cells->setAlignment('rigth');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true
                                    ));
                            });
                            $c=$c+1;
                            
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "TOTAL COTIZ.";
                            $detalle[] = (trim($v->unidad2)=="M3"?($v->cantidad):'');
                            $detalle[] = (trim($v->unidad2)=="M2"?($v->cantidad):'');
                            $sheet->row($c,$detalle);
                            $celdas      = 'B'.$c.':D'.$c;
                            $sheet->cells($celdas, function($cells) {
                                $cells->setBorder('thin','thin','thin','thin');
                                $cells->setAlignment('rigth');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true
                                    ));
                            });
                            $c=$c+1;
                            
                            $detalle = array();
                            $detalle[] = "";
                            $detalle[] = "FALTA";
                            $detalle[] = (trim($v->unidad2)=="M3"?($v->cantidad-$tot1):'');
                            $detalle[] = (trim($v->unidad2)=="M2"?($v->cantidad-$tot2):'');
                            $sheet->row($c,$detalle);
                            $celdas      = 'B'.$c.':D'.$c;
                            $sheet->cells($celdas, function($cells) {
                                $cells->setBorder('thin','thin','thin','thin');
                                $cells->setAlignment('rigth');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '11',
                                    'bold'       =>  true
                                    ));
                            });
                            $c=$c+1;
                        }
                    }
                    $c=$c+2;
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
                $cabecera[] = number_format($totalpagado,2,'.','');
                $sheet->row($c,$cabecera);*/
            });
        })->export('xls');
    }
    
}
//PONER BOTON PARA GENERAR RESUMEN
//DETRACCION DEPENDE DEL TOTAL DE FACTURA, UN %
//