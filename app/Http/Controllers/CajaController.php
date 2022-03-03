<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Convenio;
use App\Caja;
use App\Person;
use App\Venta;
use App\Movimiento;
use App\Detallemovimiento;
use App\Tipodocumento;
use App\Concepto;
use App\Detallemovcaja;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;
use Mail;

class MTCPDF extends TCPDF {

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(190, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

class CajaController extends Controller
{
    protected $folderview      = 'app.caja';
    protected $tituloAdmin     = 'Caja';
    protected $tituloRegistrar = 'Registrar Movimiento de Caja';
    protected $tituloModificar = 'Modificar Caja';
    protected $tituloEliminar  = 'Eliminar Caja';
    protected $rutas           = array('create' => 'caja.create', 
            'edit'   => 'caja.edit', 
            'edit2'   => 'caja.edit2', 
            'delete' => 'caja.eliminar',
            'search' => 'caja.buscar',
            'index'  => 'caja.index',
            'pdfListar'  => 'caja.pdfListar',
            'apertura' => 'caja.apertura',
            'cierre' => 'caja.cierre',
            'acept' => 'caja.acept',
            'reject' => 'caja.reject',
            'imprimir' => 'caja.imprimir',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar el resultado de b�squedas
     * 
     * @return Response 
     */
    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Caja';
        $rst  = Movimiento::where('tipomovimiento_id','=',4)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(is_null($rst)){
            $conceptopago_id=2;
        }else{ 
            $conceptopago_id=$rst->concepto_id;
        }
        $titulo_registrar = $this->tituloRegistrar;
        $titulo_apertura  = 'Apertura';
        $titulo_cierre    = 'Cierre'; 
        
        $rst              = Movimiento::where('tipomovimiento_id','=',4)->where('concepto_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(is_null($rst)){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('concepto','concepto.id','=','movimiento.concepto_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->whereNull('movimiento.cajaapertura_id')
                            ->where('movimiento.tipomovimiento_id','=',4)
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.situacion','<>','A');
        $resultado        = $resultado->select('movimiento.*',DB::raw('case when movimiento.moneda=\'D\' then movimiento.total*movimiento.detraccion else movimiento.total end as total2'),'m2.situacion as situacion2',DB::raw('case when paciente.razonsocial is null or paciente.razonsocial like "" then CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        
        $cabecera         = array();
        //$cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        //$cabecera[]       = array('valor' => 'Tipo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Concepto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Moneda', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ingreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Egreso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Modo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_anular    = "Anular";
        $ruta             = $this->rutas;
        $user = Auth::user();
        $ingreso=0;$egreso=0;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            foreach($lista as $k=>$v){
                if($v->concepto_id<>2 && $v->situacion<>'A'){
                    if($v->concepto->tipo=="I"){
                        $ingreso = $ingreso + $v->total2;    
                    }else{
                        $egreso  = $egreso + $v->total;
                    }
                }
            }
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'conceptopago_id', 'titulo_registrar', 'titulo_apertura', 'titulo_cierre', 'ingreso', 'egreso', 'titulo_anular','user' ));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad', 'conceptopago_id', 'titulo_registrar', 'titulo_apertura', 'titulo_cierre', 'ruta', 'ingreso', 'egreso','visa', 'master'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $entidad          = 'Caja';
        $title            = $this->tituloAdmin;
        $ruta             = $this->rutas;
        $user = Auth::user();
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'ruta', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Caja';
        $caja = null;
        $cboTipoDoc = array();
        $rs = Tipodocumento::where(DB::raw('1'),'=','1')->where('tipomovimiento_id','=',4)->orderBy('nombre','DESC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }
        $cboConcepto = array();
        $rs = Concepto::where(DB::raw('1'),'=','1')->where('tipo','LIKE','I')->where('id','<>','1')->where('id','<>',3)->orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $user = Auth::user();
        $cboModo = array('Contable'=>'Contable','No Contable'=>'No Contable');
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $formData            = array('caja.store');
        $numero              = Movimiento::NumeroSigue(4,6);//movimiento caja y documento ingreso
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');

        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('caja', 'formData', 'entidad', 'boton', 'listar', 'cboTipoDoc', 'numero', 'cboConcepto', 'user', 'cboModo', 'cboMoneda'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'total'          => 'required',
                );
        $mensajes = array(
            'total.required'         => 'Debe tener un monto',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request,$user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d H:i:s");
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$request->input('person_id');    
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->tipomovimiento_id=4;
            $movimiento->tipodocumento_id=$request->input('tipodocumento');
            $movimiento->concepto_id=$request->input('concepto');
            $movimiento->comentario=$request->input('comentario');
            $movimiento->tipo=$request->input('modo');
            $movimiento->moneda=$request->input('moneda');
            if($request->input('moneda')=="D"){
                $movimiento->detraccion = $request->input('tipocambio');
            }
            $movimiento->situacion='N';
            $movimiento->entregado = $request->input('referencia');
            $movimiento->save();
            $idref=$movimiento->id;
            if($request->input('tipodocumento')=='7'){
                $resultado  = Movimiento::join('person','person.id','=','movimiento.persona_id')
                        ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                        ->where('tipomovimiento_id','=',1)
                        ->whereNotIn('situacion',['A'])
                        ->where('movimiento.total','>',DB::raw('case when movimiento.totalpagado is null then 0 else movimiento.totalpagado end'))
                        ->where('movimiento.persona_id','=',$request->input('person_id'));
                $resultado = $resultado->select('movimiento.*',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('cliente','asc')->orderBy('fecha', 'ASC')->get();
                $listapago='';
                foreach($resultado as $k=>$v){
                    if(($request->input('txtPago'.$v->id)+0)>0){
                        $listapago.=$v->id."@".$v->numero."@".$request->input('txtPago'.$v->id)."|";
                        if($v->listapago!=""){
                            $v->listapago.="|".date("Y-m-d")."@".$request->input('txtPago'.$v->id)."@EFECTIVO";
                        }else{
                            $v->listapago.=date("Y-m-d")."@".$request->input('txtPago'.$v->id)."@EFECTIVO";
                        }
                        $v->totalpagado = $v->totalpagado + $request->input('txtPago'.$v->id);
                        $v->save();
                    }
                }
                $movimiento->listapago=substr($listapago,0,strlen($listapago)-1);
                $movimiento->save();
            }
        });
        return is_null($error) ? "OK" : $error;
    }

    public function show($id)
    {
        //
    }

    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $caja = Movimiento::find($id);
        $entidad             = 'Caja';
        $cboTipoDoc = array();
        $rs = Tipodocumento::where(DB::raw('1'),'=','1')->where('tipomovimiento_id','=',4)->orderBy('nombre','DESC')->get();
        foreach ($rs as $key => $value) {
            $cboTipoDoc = $cboTipoDoc + array($value->id => $value->nombre);
        }
        $cboConcepto = array();
        if($caja->tipodocumento_id==6){
            $rs = Concepto::where('tipo','like','I')->where('id','<>',1)->where('id','<>',3)->orderBy('nombre','ASC')->get();
        }else{
            $rs = Concepto::where('tipo','like','E')->where('id','<>',2)->orderBy('nombre','ASC')->get();
        }
        foreach ($rs as $key => $value) {
            $cboConcepto = $cboConcepto + array($value->id => $value->nombre);
        }
        $user = Auth::user();
        $cboModo = array('Contable'=>'Contable','No Contable'=>'No Contable');
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $numero              = $caja->numero;
        $formData            = array('caja.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('caja', 'formData', 'entidad', 'boton', 'listar', 'cboTipoDoc', 'numero', 'cboConcepto', 'user', 'cboModo', 'cboMoneda'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array(
                'total'          => 'required',
                );
        $mensajes = array(
            'total.required'         => 'Debe tener un monto',
        );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $id, $user){
            $movimiento = Movimiento::find($id);
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$request->input('person_id');    
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->tipomovimiento_id=4;
            $movimiento->tipodocumento_id=$request->input('tipodocumento');
            $movimiento->concepto_id=$request->input('concepto');
            $movimiento->comentario=$request->input('comentario');
            $movimiento->tipo=$request->input('modo');
            $movimiento->moneda=$request->input('moneda');
            if($request->input('moneda')=="D"){
                $movimiento->detraccion = $request->input('tipocambio');
            }
            $movimiento->situacion='N';
            $movimiento->entregado = $request->input('referencia');
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->situacion="A";//Anulado
            $Caja->save();
            if($Caja->listapago!=""){
                $ids = explode("|",$Caja->listapago);
                for($w=0;$w<count($ids);$w++){
                    $idss = explode("@",$ids[$w]);
                    $compra = Movimiento::where('id','=',$idss[0])->first();
                    if(!is_null($compra)){
                        $compra->totalpagado = $compra->totalpagado - $idss[2];
                        $forma="";
                        $data = explode("|",$compra->listapago);
                        for($z=0;$z<count($data);$z++){
                            $dat=explode("@",$data[$z]);
                            if($dat[0]!=$compra->fecha && ($dat[1]+0)!=($idss[2]+0) && $dat[2]!="EFECTIVO"){
                                $forma=$dat[0]."@".$dat[1]."@".$dat[2]."|";
                            }
                        }
                        if(trim($forma)!=""){
                            $compra->listapago=substr($forma,0,strlen($forma)-1);
                        }else{
                            $compra->listapago="";
                        }
                        
                        $compra->save();
                    }
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
        $entidad  = 'Caja';
        $formData = array('route' => array('caja.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    

   	public function pdfCierre(Request $request){
        $rst  = Movimiento::where('tipomovimiento_id','=',4)->orderBy('movimiento.id','DESC')->limit(1)->first();
        if(!is_null($rst)){
            $conceptopago_id=2;
        }else{
            $conceptopago_id=$rst->concepto_id;
        }
        
        $rst              = Movimiento::where('tipomovimiento_id','=',4)->where('concepto_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(!is_null($rst)){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('concepto','concepto.id','=','movimiento.concepto_id')
                            ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->whereNotIn('movimiento.situacion',['A']);
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('case when paciente.razonsocial is null or paciente.razonsocial like "" then  CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'))
                            ->orderBy('concepto.tipo','desc')
                            ->orderBy('movimiento.tipo','asc')
                            ->orderBy('movimiento.id', 'asc');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();

            $pdf::setFooterCallback(function($pdf) {
                $pdf->SetY(-15);
                // Set font
                $pdf->SetFont('helvetica', 'I', 8);
                // Page number
                $pdf->Cell(0, 10, 'Pag. '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

            });
            $resultado        = Movimiento::join('concepto','concepto.id','=','movimiento.concepto_id')
                            ->where('movimiento.id', '>=', $movimiento_mayor)
                            ->where('movimiento.moneda','like','D')
                            ->whereNotIn('movimiento.situacion',['A'])
                            ->first();
            $tipocambio = 0;
            if(!is_null($resultado)){
                $tipocambio = $resultado->detraccion;
            }
            $pdf::SetTitle('Cierre de Caja');
            $pdf::AddPage();
            $ingresoc=0;$egresoc=0;$ingresonc=0;$egresonc=0;$master=0;$d=0;$xx=0;$modo='';
            foreach ($lista as $key => $value){$d=$d+1;
                if($value->concepto_id!=1){
                    $xx=$xx+1;
                }
                if($value->concepto->tipo=='I'){
                    if($value->concepto_id==1){
                        $pdf::SetFont('helvetica','B',12);
                        $pdf::Cell(0,10,utf8_decode("CORPORACION ASFALTOS Y PAVIMENTOS CASTILLO SAC"),0,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',10);
                        $pdf::Cell(50,10,"FECHA: ".date("d/m/Y H:i:s"),0,0,'L');
                        $pdf::Cell(0,10,"TIPO DE CAMBIO: ".number_format($tipocambio,2,'.',','),0,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(5,5,utf8_decode("#"),1,0,'C');
                        $pdf::Cell(40,5,utf8_decode("BANCO"),1,0,'C');
                        $pdf::SetFont('helvetica','B',10);
                        $pdf::Cell(105,5,utf8_decode("INGRESOS DEL DIA"),1,0,'C');
                        $pdf::SetFont('helvetica','B',8);
                        $pdf::Cell(20,5,utf8_decode("DOLARES  $"),1,0,'C');
                        $pdf::Cell(20,5,utf8_decode("SOLES  S/"),1,0,'C');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','',8);        
                        $pdf::Cell(45,5,utf8_decode("SALDO INICIAL"),1,0,'C');
                        $pdf::Cell(105,5,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,5,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,5,number_format($value->total,2,'.',','),1,0,'R');
                        $pdf::Ln();
                        $modo = $value->tipo;
                        $ingresoc = $ingresoc + $value->total;
                    }else{
                        if($modo!=$value->tipo){
                            $pdf::SetFont('helvetica','B',10);        
                            $pdf::Cell(45,6,utf8_decode(""),1,0,'C');
                            $pdf::Cell(105,6,utf8_decode("TOTAL INGRESOS CONTABLES"),1,0,'C');
                            $pdf::Cell(20,6,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,6,number_format($ingresoc,2,'.',','),1,0,'C');
                            $pdf::Ln();
                            $pdf::SetFont('helvetica','B',8);        
                            $pdf::Cell(45,6,utf8_decode("INGRESOS NO CONTABLES"),1,0,'C');
                            //$pdf::Cell(30,6,utf8_decode("CONCEPTO"),1,0,'C');
                            $pdf::Cell(105,6,utf8_decode("DESCRIPCION"),1,0,'C');
                            $pdf::Cell(20,6,utf8_decode("DOLARES  $"),1,0,'C');
                            $pdf::Cell(20,6,("SOLES  S/"),1,0,'C');
                            $pdf::Ln();
                            $modo = $value->tipo;    
                        }
                    }
                }else{
                    if($d==2){
                        $pdf::SetFont('helvetica','B',10);        
                        $pdf::Cell(45,5,utf8_decode(""),1,0,'C');
                        $pdf::Cell(105,5,utf8_decode("TOTAL INGRESOS CONTABLES"),1,0,'C');
                        $pdf::Cell(20,5,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,5,number_format($ingresoc,2,'.',','),1,0,'R');
                        $pdf::Ln();
                    }
                    if($egresoc==0 && $egresonc==0){
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',10);
                        $pdf::Cell(190,6,utf8_decode("EGRESOS DEL DIA"),1,0,'C');
                        $pdf::Ln();
                        $xx=1;
                        $pdf::SetFont('helvetica','B',8);        
                        $pdf::Cell(45,6,utf8_decode("GASTOS ".strtoupper($value->tipo)),1,0,'C');
                        //$pdf::Cell(30,6,utf8_decode("CONCEPTO"),1,0,'C');
                        $pdf::Cell(105,6,utf8_decode("DESCRIPCION"),1,0,'C');
                        $pdf::Cell(20,6,utf8_decode("DOLARES  $"),1,0,'C');
                        $pdf::Cell(20,6,("SOLES  S/"),1,0,'C');
                        $pdf::Ln();
                        $modo = $value->tipo;
                    }elseif($modo!=$value->tipo){
                        $pdf::SetFont('helvetica','B',10);        
                        $pdf::Cell(45,5,utf8_decode(""),1,0,'C');
                        $pdf::Cell(105,5,utf8_decode("TOTAL GASTOS CONTABLES"),1,0,'C');
                        $pdf::Cell(20,5,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,5,number_format($egresoc,2,'.',','),1,0,'R');
                        $pdf::Ln();
                        $pdf::SetFont('helvetica','B',8);        
                        $pdf::Cell(45,6,utf8_decode("GASTOS NO CONTABLES"),1,0,'C');
                        //$pdf::Cell(30,6,utf8_decode("CONCEPTO"),1,0,'C');
                        $pdf::Cell(105,6,utf8_decode("DESCRIPCION"),1,0,'C');
                        $pdf::Cell(20,6,utf8_decode("DOLARES  $"),1,0,'C');
                        $pdf::Cell(20,6,("SOLES  S/"),1,0,'C');
                        $pdf::Ln();
                        $xx=1;
                        $modo = $value->tipo;
                    }
                }
                if($value->concepto_id!=2 && $value->concepto_id!=1){
                    $comentario='';
                    if($value->listapago!=""){
                        $ids = explode("|",$value->listapago);
                        for($w=0;$w<count($ids);$w++){
                            $idss = explode("@",$ids[$w]);
                            $detallecompra = Detallemovimiento::where('movimiento_id','=',$idss[0])->select('detallemovimiento.producto as producto2')->first();
                            if(!is_null($detallecompra)){
                                $comentario.="* ".$idss[1]." / ".$detallecompra->producto2." / ".number_format($idss[2],2,'.','')." \n";
                            }
                        }
                    }
                    if($value->comentario!=""){
                        $comentario.="\n".$value->comentario;
                    }
                    if($value->entregado!=""){
                        $cliente=$value->cliente."\n".$value->entregado;
                    }else{
                        $cliente=$value->cliente;
                    }
                    $alto=$pdf::getNumLines($comentario, 105)*3.5;
                    $alto1=$pdf::getNumLines($cliente, 40)*3.5;
                    if($alto1>$alto) $alto=$alto1;
                    $pdf::SetFont('helvetica','B',8);
                    $pdf::Cell(5,$alto,$xx,1,0,'R');  
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();                    
                    $pdf::Multicell(40,3,($cliente),0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(40,$alto,"",1,0,'C');
                    $pdf::SetFont('helvetica','',8);
                    /*if(strlen($value->concepto->nombre)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(30,3,($value->concepto->nombre),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(30,6,"",1,0,'C');
                    }else{
                        $pdf::Cell(30,6,($value->concepto->nombre),1,0,'L');
                    }*/
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();
                    $pdf::Multicell(105,3,($comentario),0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(105,$alto,"",1,0,'C');
                    if($value->moneda=="S"){
                        $pdf::Cell(20,$alto,(""),1,0,'C');
                        $pdf::Cell(20,$alto,number_format($value->total,2,'.',','),1,0,'R');
                    }else{
                        $pdf::Cell(20,$alto,number_format($value->total,2,'.',','),1,0,'C');
                        $pdf::Cell(20,$alto,number_format($value->total*$value->detraccion,2,'.',','),1,0,'R');
                    }
                    $pdf::Ln();
                    if($value->concepto_id<>2 && $value->situacion<>'A'){
                        if($value->concepto->tipo=="I"){
                            if($value->tipo=="Contable"){
                                if($value->moneda=='S'){
                                    $ingresoc = $ingresoc + $value->total;
                                }else{
                                    $ingresoc = $ingresoc + round($value->detraccion*$value->total,2);
                                }
                            }else{
                                if($value->moneda=='S'){
                                    $ingresonc = $ingresonc + $value->total;
                                }else{
                                    $ingresonc = $ingresonc + round($value->detraccion*$value->total,2);
                                }
                            }
                        }else{
                            if(trim($value->tipo)=="Contable"){
                                if($value->moneda=='S'){
                                    $egresoc  = $egresoc + $value->total;
                                }else{
                                    $egresoc = $egresoc + round($value->detraccion*$value->total,2);
                                }
                            }else{
                                if($value->moneda=='S'){
                                    $egresonc  = $egresonc + $value->total;
                                }else{
                                    $egresonc = $egresonc + round($value->detraccion*$value->total,2);
                                }
                            }
                        }
                    }
                }
            }
            $pdf::SetFont('helvetica','B',10);        
            $pdf::Cell(45,5,utf8_decode(""),1,0,'C');
            $pdf::Cell(105,5,utf8_decode("TOTAL EGRESOS NO CONTABLES"),1,0,'C');
            $pdf::Cell(20,5,utf8_decode(""),1,0,'C');
            $pdf::Cell(20,5,number_format($egresonc,2,'.',','),1,0,'R');
            $pdf::Ln();
            
            $pdf::SetFont('helvetica','B',10);        
            $pdf::Cell(170,5,utf8_decode("SALDO FINAL"),1,0,'C');
            $pdf::Cell(20,5,number_format($ingresoc + $ingresonc - $egresoc - $egresonc,2,'.',','),1,0,'R');
            $pdf::Ln();
            $pdf::Output('CierreCaja.pdf');
        }
    }


    public function pdfDetalleCierreF(Request $request){
        $caja                = Caja::find($request->input('caja_id'));
        $f_inicial           = $request->input('fi');
        $f_final             = $request->input('ff');

        $aperturas = array();
        $cierres = array();

        $caja_id          = Libreria::getParam($request->input('caja_id'),'1');
        
        
        $rst        = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',1)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)->orderBy('id','ASC')->get();
        if(count($rst)>0){
            foreach ($rst as $key => $rvalue) {
                array_push($aperturas,$rvalue->id);
                $rvalue       = Movimiento::where('tipomovimiento_id','=',2)->where('caja_id','=',$caja_id)->where('conceptopago_id','=',2)->where('movimiento.fecha', '>=', $f_inicial)->where('movimiento.fecha', '<=', $f_final)
                ->where('movimiento.id', '>=', $rvalue->id)
                ->orderBy('id','ASC')->first();
                if(!is_null($rvalue)){
                    array_push($cierres,$rvalue->id);
                }else{
                    array_push($cierres,0);
                }

            }
            
        }else{
            $movimiento_mayor = 0;
        }
        
        $vmax = sizeof($aperturas);
        $pdf = new MTCPDF();
        $pdf::SetTitle('Detalle Cierre General');
        $pdf::setFooterCallback(function($pdf) {
                $pdf->SetY(-15);
                // Set font
                $pdf->SetFont('helvetica', 'I', 8);
                // Page number
                $pdf->Cell(0, 10, 'Pag. '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        });
        for ($valor=0; $valor < $vmax -1; $valor++) {
            //echo $aperturas[$valor].' - '.$cierres[$valor].' ';
            $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                                ->leftjoin('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                                ->where('movimiento.caja_id', '=', $caja_id)
                                ->where(function ($query) use($aperturas,$cierres,$valor) {
                                    $query->where(function($q) use($aperturas,$cierres,$valor){
                                            $q->where('movimiento.id', '>', $aperturas[$valor])
                                            ->where('movimiento.id', '<', $cierres[$valor])
                                            ->whereNull('movimiento.cajaapertura_id');
                                    })
                                          ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                                            $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                                            });//normal
                                })
                                //->where('movimiento.id', '>', $aperturas[$valor])
                                //->where('movimiento.id', '<', $cierres[$valor])
                                ->where('movimiento.situacion', '<>', 'A')->where('movimiento.situacion', '<>', 'R');
            $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2','responsable.nombres as responsable2')->orderBy('conceptopago.tipo', 'asc')->orderBy('conceptopago.orden', 'asc')->orderBy('conceptopago.id', 'asc')->orderBy('movimiento.tipotarjeta', 'asc')->orderBy('movimiento.numero', 'asc');
            $listConcepto     = array();
            $listConcepto2     = array();
            $listConcepto3     = array();
            $listConcepto4     = array();
            $listtarjeta      = array();
            $listConcepto[]   = 6;//TRANSF CAJA INGRESO
            $listConcepto[]   = 7;//TRANSF CAJA EGRESO
            $listConcepto2[]   = 8;//HONORARIOS MEDICOS
            $listConcepto[]   = 14;//TRANSF TARJETA EGRESO
            $listConcepto[]   = 15;//TRANSF TARJETA INGRESO
            $listConcepto[]   = 16;//TRANSF SOCIO EGRESO
            $listConcepto[]   = 17;//TRANSF SOCIO INGRESO
            $listConcepto3[]   = 24;//PAGO DE CONVENIO
            $listConcepto3[]   = 25;//PAGO DE SOCIO
            $listConcepto[]   = 20;//TRANSF BOLETEO EGRESO
            $listConcepto[]   = 21;//TRANSF BOLETEO INGRESO
            //$listConcepto[]   = 30;//DEVOLUCION GARANT�A CONTROL REMOTO
            $listConcepto4[]   = 31;//TRANSF FARMACIA EGRESO
            $listConcepto4[]   = 32;//TRANSF FARMACiA INGRESO
            $lista            = $resultado->get();

            if ($caja_id == 4) {
                $resultado2        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                                ->leftjoin('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->leftjoin('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                                ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                                //->where('movimiento.serie', '=', $caja_id)
                                ->where('movimiento.estadopago', '=', 'PP')
                                ->where('movimiento.tipomovimiento_id', '=', '4')
                                ->where(function ($query) use($aperturas,$cierres,$valor) {
                                    $query->where(function($q) use($aperturas,$cierres,$valor){
                                            $q->where('movimiento.id', '>', $aperturas[$valor])
                                            ->where('movimiento.id', '<', $cierres[$valor])
                                            ->whereNull('movimiento.cajaapertura_id');
                                    })
                                          ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                                            $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                                            });//normal
                                })
                                ->whereNull('movimiento.tipo')
                                ->where('movimiento.situacion', '<>', 'U')
                                ->where('movimiento.situacion', '<>', 'R');
                $resultado2        = $resultado2->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
                $listapendiente            = $resultado2->get();
            }
            if (count($lista) > 0) {
                $pdf::AddPage('L');
                $pdf::SetFont('helvetica','B',12);
                $pdf::Image('dist/img/logo.jpg',10,8,50,0);
                $pdf::Cell(0,15,utf8_decode("Detalle de Cierre de ".$caja->nombre." Desde ".$f_inicial. " hasta ".$f_final),0,0,'C');
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(15,7,utf8_decode("FECHA"),1,0,'C');
                $pdf::Cell(60,7,utf8_decode("PERSONA"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("NRO"),1,0,'C');
                $pdf::Cell(40,7,utf8_decode("EMPRESA"),1,0,'C');
                $pdf::Cell(70,7,utf8_decode("CONCEPTO"),1,0,'C');
                $pdf::Cell(18,7,utf8_decode("EGRESO"),1,0,'C');
                $pdf::Cell(18,7,utf8_decode("INGRESO"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("TARJETA"),1,0,'C');
                $pdf::Cell(20,7,utf8_decode("DOCTOR"),1,0,'C');
                $pdf::Ln();
                if($caja_id==1){//ADMISION 1
                    $serie=3;
                }elseif($caja_id==2){//ADMISION 2
                    $serie=7;
                }elseif($caja_id==3){//CONVENIOS
                    $serie=8;
                }elseif($caja_id==5){//EMERGENCIA
                    $serie=9;
                }elseif($caja_id==4){//FARMACIA
                    $serie=4;
                }
                $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                                ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                                ->where('movimiento.serie', '=', $serie)
                                ->where('movimiento.tipomovimiento_id', '=', 4)
                                ->where('movimiento.id', '>', $aperturas[$valor])
                                ->where('movimiento.id', '<', $cierres[$valor])
                                ->where('m2.situacion','like','B');
                                /*->whereNotIn('movimiento.id',function ($query) use ($aperturas,$valor,$cierres,$caja_id) {
                                    $query->select('movimiento_id')->from('movimiento')->where('id','>',$aperturas[$valor])->where('id','<',$cierres[$valor])->where('caja_id','=',$caja_id);
                                });*/
                $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
                
                $lista1           = $resultado1->get();
                //ECHO $aperturas[$valor]."-".$cierres[$valor]."-";
            if ($caja_id == 4) {
                $pendiente = 0;
                foreach ($listapendiente as $key => $value) {
                    if($pendiente==0 && $value->tipodocumento_id != 15){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                        $pdf::Ln();
                    }
                    if ($value->tipodocumento_id != 15) {
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                        $nombrepaciente = '';
                        $nombreempresa = '-';
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                        }else{
                            $nombrepaciente = trim($value->nombrepaciente);
                        }
                        if ($value->tipodocumento_id == 5) {
                            
                            
                        }else{
                            if ($value->empresa != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                            
                        }
                        if(strlen($nombrepaciente)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                        }
                        //$venta= Movimiento::find($value->id);
                        $pdf::Cell(8,7,($value->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value->serie.'-'.$value->numero,1,0,'C');

                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                        if($value->servicio_id>0){
                            if(strlen($value->servicio->nombre)>35){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(70,3,$value->servicio->nombre,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(70,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(70,7,$value->servicio->nombre,1,0,'L');    
                            }
                        }else{
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');    
                        }
                        $pdf::Cell(18,7,'',1,0,'C');
                        $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        if ($value->doctor_id != null) {
                            $pdf::Cell(20,7,substr($value->doctor->nombres,0,1).'. '.$value->doctor->apellidopaterno,1,0,'L');

                        }else{
                           $pdf::Cell(20,7," - ",1,0,'L'); 
                        }
                        
                        $pdf::Ln();
                        $pendiente=$pendiente + number_format($value->total,2,'.','');
                    }
                    
                }
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }
            

            if(count($lista1)>0){
                $pendiente=0;
                foreach($lista1 as $key1 => $value1){
                    /*$rs = Detallemovcaja::where("movimiento_id",'=',$value1->movimiento_id)->get();
                    foreach ($rs as $k => $v){*/
                        if($pendiente==0){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("PENDIENTE"),1,0,'L');
                            $pdf::Ln();
                        }
                        $pdf::SetFont('helvetica','',7.5);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                        if($value1->tipodocumento_id==5){//BOLETA
                            $nombre=$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                        }else{
                            $nombre=$value1->paciente2;
                            $empresa=$value1->persona->bussinesname;
                        }
                        if(strlen($nombre)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombre),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombre),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                        if($value1->tipodocumento_id==5){
                            $ticket= Movimiento::find($value1->movimiento_id);
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                        $v = Detallemovcaja::where("movimiento_id",'=',$value1->movimiento_id)->first();

                        if (isset($v->servicio_id)) {
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                        } else {
                            $pdf::Cell(70,7,"",1,0,'L');
                        }  
                        $pdf::Cell(18,7,'',1,0,'C');
                        //$pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                        $pdf::Cell(18,7,number_format($value1->total,2,'.',''),1,0,'R');
                        $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                        
                        if (isset($v->persona->nombres)) {
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();
                        //$pendiente=$pendiente + number_format($v->cantidad*$v->pagohospital,2,'.','');
                        $pendiente=$pendiente + number_format($value1->total,2,'.','');
                    //}
                }
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pendiente,2,'.',''),1,0,'R');
                $pdf::Ln();
            }

            $ingreso=0;$egreso=0;$transferenciai=0;$transferenciae=0;$garantia=0;$efectivo=0;$visa=0;$master=0;$pago=0;$tarjeta=0;$cobranza=0;$egreso1=0;$transferenciai=0;$cobranza=0;$ingresotarjeta=0;
            $bandpago=true;$bandegreso=true;$bandtransferenciae=true;$bandtarjeta=true;$bandtransferenciai=true;$bandcobranza=true;$bandingresotarjeta=true;
            foreach ($lista as $key => $value){
                if($ingreso==0){
                    $responsable=$value->responsable2;
                }
                
                if($value->conceptopago_id==3 && $value->tipotarjeta==''){
                    if ($caja_id == 4) {
                        //echo $value->movimiento_id."<br />";
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        //echo $value->movimiento_id."|".$value->id."@";
                        foreach ($rs as $k => $v) {
                            $pdf::SetTextColor(0,0,0);
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0 && $value->tipodocumento_id!=15){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                            }
                            if ($value->tipodocumento_id !== 15) {
                                $pdf::SetFont('helvetica','',7);
                                $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');

                                $nombrepaciente = '';
                                $nombreempresa = '-';
                                if ($value->persona_id !== NULL) {
                                    //echo 'entro'.$value->id;break;
                                    $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                                }else{
                                    $nombrepaciente = trim($value->nombrepaciente);
                                }
                                if ($value->tipodocumento_id == 5) {
                                    
                                    
                                }else{
                                    if ($value->empresa_id != null) {
                                        $nombreempresa = trim($value->empresa->bussinesname);
                                    }
                                    
                                }
                                

                                if(strlen($nombrepaciente)>30){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(60,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                                }
                                $venta= Movimiento::find($value->movimiento_id);
                                $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                                $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                                if ($venta->conveniofarmacia_id != null) {
                                    $nombreempresa = $venta->conveniofarmacia->nombre;
                                }
                                $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                                if($v->servicio_id>0){
                                    if(strlen($v->servicio->nombre)>35){
                                        $x=$pdf::GetX();
                                        $y=$pdf::GetY();                    
                                        $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                        $pdf::SetXY($x,$y);
                                        $pdf::Cell(70,7,"",1,0,'C');
                                    }else{
                                        $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                    }
                                }else{
                                    $pdf::Cell(70,7,$v->descripcion.'- MEDICINA',1,0,'L');    
                                }
                                $pdf::Cell(18,7,'',1,0,'C');
                                $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                                $pdf::Cell(20,7,utf8_decode('-'),1,0,'C');
                                if ($venta->doctor_id != null) {
                                    $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                                }else{
                                   $pdf::Cell(20,7," - ",1,0,'L'); 
                                }
                                
                                $pdf::Ln();
                                $pago=$pago + number_format($v->movimiento->total,2,'.','');
                            }
                            
                        }
                    }else{
                        //PARA PAGO DE CLIENTE, BUSCO TICKET
                        /*$rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){*/
                            $pdf::SetTextColor(0,0,0);
                            if($transferenciae>0 && $bandtransferenciae){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                                $bandtransferenciae=false;
                                $pdf::Ln(); 
                            }
                            if($egreso1>0 && $bandegreso){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(205,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                                $bandegreso=false;
                                $pdf::Ln(); 
                            }
                            if($pago==0){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                                $pdf::Ln();
                                if($caja_id==3){
                                    $pdf::SetFont('helvetica','B',7);
                                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                    $apert = Movimiento::find($aperturas[$valor]);
                                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                    $pago = $pago + $apert->total;
                                    $ingreso = $ingreso + $apert->total;
                                    $pdf::Ln();
                                }
                            }
                            $pdf::SetFont('helvetica','',7);
                            $venta= Movimiento::find($value->movimiento_id);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($venta->fecha)),1,0,'C');
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                            $v = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->first();
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            //$pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,number_format($venta->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode('-'),1,0,'C');
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            $pdf::Ln();
                            //$pago=$pago + number_format($v->cantidad*$v->pagohospital,2,'.','');
                            $pago=$pago + number_format($venta->total,2,'.','');
                        //}
                    }
                }elseif($value->conceptopago_id==3 && $value->tipotarjeta!=''){//PARA PAGO DE CLIENTE, BUSCO TICKET CON TARJETA
                    if ($caja_id == 4) {
                        $rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){
                            $pdf::SetTextColor(0,0,0);
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }

                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            
                            $nombrepaciente = '';
                            $nombreempresa = '-';
                            if ($value->persona_id !== NULL) {
                                //echo 'entro'.$value->id;break;
                                $nombrepaciente =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;

                            }else{
                                $nombrepaciente = trim($value->nombrepaciente);
                            }
                            if ($value->tipodocumento_id == 5) {
                                
                                
                            }else{
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                            if(strlen($nombrepaciente)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepaciente),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepaciente),1,0,'L');    
                            }
                            $venta= Movimiento::find($value->movimiento_id);
                            $ticket= Movimiento::find($v->movimiento_id);
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            if ($venta->conveniofarmacia_id != null) {
                                $nombreempresa = $venta->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            $tarjeta=$tarjeta + $v->movimiento->total;
                            $pdf::Cell(18,7,number_format($v->movimiento->total,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            if ($venta->doctor_id != null) {
                                $pdf::Cell(20,7,substr($venta->doctor->nombres,0,1).'. '.$venta->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $pdf::Ln();
                            //$pago=$pago + number_format($v->movimiento->total,2,'.','');
                            
                        }
                    }else{
                        /*$rs = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->get();
                        foreach ($rs as $k => $v){*/
                            $pdf::SetTextColor(0,0,0);
                            if($pago>0 && $bandpago){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                                $bandpago=false;
                                $pdf::Ln(); 
                            }
                            if($tarjeta==0){
                                $pdf::SetFont('helvetica','B',8.5);
                                $pdf::Cell(281,7,utf8_decode("TARJETA"),1,0,'L');
                                $pdf::Ln();
                            }
                            if($value->situacion<>'A'){
                                $pdf::SetTextColor(0,0,0);
                            }else{
                                $pdf::SetTextColor(255,0,0);
                            }
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                            $venta= Movimiento::find($value->movimiento_id);
                            $tarjeta=$tarjeta + number_format($value->total,2,'.','');
                            if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                            }
                            $v = Detallemovcaja::where("movimiento_id",'=',DB::raw('(select movimiento_id from movimiento where id='.$value->movimiento_id.')'))->first();
                            $ticket= Movimiento::find($v->movimiento_id);
                            if(!is_null($venta)){
                                $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                                $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                            }
                            $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            if($v->servicio_id>0){
                                if(strlen($v->servicio->nombre)>35){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(70,3,$v->servicio->nombre,0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(70,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(70,7,$v->servicio->nombre,1,0,'L');    
                                }
                            }else{
                                $pdf::Cell(70,7,$v->descripcion,1,0,'L');    
                            }
                            $pdf::Cell(18,7,'',1,0,'C');
                            //$pdf::Cell(18,7,number_format($v->cantidad*$v->pagohospital,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,number_format($venta->total,2,'.',''),1,0,'R');
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(20,7,"",1,0,'C');
                            $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                            $pdf::Ln();   
                            if($value->total!=$venta->total){
                                $pdf::SetFont('helvetica','',7);
                                $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                                if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                                    $x=$pdf::GetX();
                                    $y=$pdf::GetY();                    
                                    $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                                    $pdf::SetXY($x,$y);
                                    $pdf::Cell(60,7,"",1,0,'C');
                                }else{
                                    $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                                }
                                if(!is_null($venta)){
                                    $pdf::Cell(8,7,'R',1,0,'C');
                                    $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                                }
                                $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                                $pdf::Cell(70,7,'INGRESO POR DIFERENCIA TARJETA',1,0,'L');    
                                $pdf::Cell(18,7,'',1,0,'C');
                                $pdf::Cell(18,7,number_format($value->total-$venta->total,2,'.',''),1,0,'R');
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(20,3,$value->tipotarjeta." ".$value->tarjeta." / ".$value->voucher,0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(20,7,"",1,0,'C');
                                $pdf::Cell(20,7,substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno,1,0,'L');
                                $pdf::Ln();   
                            }
                        //}

                    }
                    
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA EGRESOS
                    $pdf::SetTextColor(0,0,0);
                    if($egreso1>0 && $bandegreso){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($egreso1,2,'.',''),1,0,'R');
                        $bandegreso=false;
                        $pdf::Ln(); 
                    }
                    if($transferenciae==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $list=explode(",",$value->listapago);
                    $transferenciae = $transferenciae + $value->total;
                    for($c=0;$c<count($list);$c++){
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $detalle = Detallemovcaja::find($list[$c]);
                        $ticket = Movimiento::where("id","=",$detalle->movimiento_id)->first();
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        if(strlen($ticket->persona->movimiento.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        if($venta->tipodocumento_id==4){
                            $pdf::Cell(40,7,$venta->persona->bussinesname,1,0,'L');
                        }else{
                            $pdf::Cell(40,7,"",1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $pdf::Cell(18,7,number_format($detalle->pagodoctor,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==16){//TRANSFERENCIA SOCIO
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==20){//BOLETEO TOTAL
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==14){//TARJETA
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();
                    }
                }elseif(in_array($value->conceptopago_id, $listConcepto) && $value->conceptopago->tipo=='I'){//CONCEPTOS QUE TIENEN LISTA INGRESOS
                    $pdf::SetTextColor(0,0,0);
                    if($pago>0 && $bandpago){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                        $bandpago=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($transferenciai==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $transferenciai = $transferenciai + $value->total;
                    $list=explode(",",$value->listapago);
                    for($c=0;$c<count($list);$c++){
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $detalle = Detallemovcaja::find($list[$c]);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        $ticket = Movimiento::find($detalle->movimiento_id);
                        if(!is_null($venta)){
                            if(strlen($ticket->persona->movimiento.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($ticket->persona->apellidopaterno.' '.$ticket->persona->apellidomaterno.' '.$ticket->persona->nombres),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        }
                        if($venta->tipodocumento_id==4){
                            $pdf::Cell(40,7,$venta->persona->bussinesname,1,0,'L');
                        }else{
                            $pdf::Cell(40,7,"",1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==21 ){//BOLETEO TOTAL
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==17){// TRANSFERENCIA SOCIO
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }elseif($value->conceptopago_id==15){//TARJETA
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(18,7,number_format($detalle->pagotarjeta,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();   
                    }
                }elseif(in_array($value->conceptopago_id, $listConcepto2) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA2
                    /*$pdf::SetTextColor(0,0,0);
                    if($egreso==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(279,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $list=explode(",",$value->listapago);//print_r($value->listapago."-");
                    for($c=0;$c<count($list);$c++){
                        $detalle = Detallemovcaja::find($list[$c]);
                        $venta = Movimiento::where("movimiento_id","=",$detalle->movimiento_id)->first();
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        if(strlen($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                        $pdf::Cell(10,7,$venta->serie.'-'.$venta->numero,1,0,'C');
                        $pdf::Cell(40,7,"",1,0,'L');
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $descripcion=$value->conceptopago->nombre.' - RH: '.$detalle->recibo;
                        }else{
                            $descripcion=$value->conceptopago->nombre;
                        }
                        if(strlen($descripcion)>40){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(70,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                        }
                        if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                            $pdf::Cell(18,7,number_format($detalle->pagodoctor*$detalle->cantidad,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }else{//SOCIO
                            $pdf::Cell(18,7,number_format($detalle->pagosocio,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                            $pdf::Cell(20,7,substr($value->persona->nombres,0,1).'. '.$value->persona->apellidopaterno,1,0,'L');
                        }
                        $pdf::Ln();   
                    }*/
                }elseif(in_array($value->conceptopago_id, $listConcepto3) && $value->conceptopago->tipo=='E'){//CONCEPTOS QUE TIENEN LISTA3
                    $pdf::SetTextColor(0,0,0);
                    if($egreso1==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESO"),1,0,'L');
                        $pdf::Ln();
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    if(strlen($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres),1,0,'L');    
                    }   
                    $pdf::Cell(8,7,'RH',1,0,'C');
                    if($value->voucher==""){
                        $list=explode(",",$value->listapago);
                        $detalle = Detallemovcaja::find($list[0]);
                        if($value->conceptopago_id==25)
                            $pdf::Cell(12,7,$detalle->recibo2,1,0,'C');
                        else
                            $pdf::Cell(12,7,$detalle->recibo,1,0,'C');
                    }else{
                        $pdf::Cell(12,7,$value->voucher,1,0,'C');
                    }
                    $pdf::Cell(40,7,"",1,0,'L');
                    $descripcion=$value->conceptopago->nombre;
                    if(strlen($descripcion)>40){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(70,3,utf8_decode($descripcion),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(70,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(70,7,utf8_decode($descripcion),1,0,'L');
                    }
                    $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                    $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                    $egreso1 = $egreso1 + $value->total;
                }elseif($value->conceptopago_id==23 || $value->conceptopago_id == 32){//COBRANZA
                    if ($caja_id == 4 && $value->conceptopago_id == 32) {
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimientodescarga_id','=',$value->id)->get();

                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $cobranza=$cobranza + $value6->total;
                            $pdf::Ln();
                        }
                    }elseif($caja_id == 4 && $value->conceptopago_id == 23){
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        $listventas = Movimiento::where('movimiento_id','=',$value->id)->get();
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        foreach ($listventas as $key6 => $value6) {
                            $pdf::SetFont('helvetica','',7);
                            $pdf::Cell(15,7,date("d/m/Y",strtotime($value6->fecha)),1,0,'C');
                            $nombrepersona = '-';
                            if ($value6->persona_id !== NULL) {
                                //echo 'entro'.$value6->id;break;
                                $nombrepersona =$value6->persona->apellidopaterno.' '.$value6->persona->apellidomaterno.' '.$value6->persona->nombres;

                            }else{
                                $nombrepersona = trim($value6->nombrepaciente);
                            }
                            if(strlen($nombrepersona)>30){
                                $x=$pdf::GetX();
                                $y=$pdf::GetY();                    
                                $pdf::Multicell(60,3,($nombrepersona),0,'L');
                                $pdf::SetXY($x,$y);
                                $pdf::Cell(60,7,"",1,0,'C');
                            }else{
                                $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                            }
                            $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':'F'),1,0,'C');
                            $pdf::Cell(12,7,$value6->serie.'-'.$value6->numero,1,0,'C');
                            $nombreempresa = '';
                            if ($value6->conveniofarmacia_id != null) {
                                $nombreempresa = $value6->conveniofarmacia->nombre;
                            }
                            $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                            $pdf::Cell(70,7,'MEDICINA',1,0,'L');
                            $pdf::Cell(18,7,'',1,0,'C');
                            $pdf::Cell(18,7,number_format($value6->total,2,'.',''),1,0,'R');
                            $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                            if ($value6->doctor_id != null) {
                                $pdf::Cell(20,7,substr($value6->doctor->nombres,0,1).'. '.$value6->doctor->apellidopaterno,1,0,'L');

                            }else{
                               $pdf::Cell(20,7," - ",1,0,'L'); 
                            }
                            $cobranza=$cobranza + $value6->total;
                            $pdf::Ln();
                        }
                    }else{

                        $pdf::SetTextColor(0,0,0);
                        if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }
                        if($tarjeta>0 && $bandtarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                            $bandtarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($ingresotarjeta>0 && $bandingresotarjeta){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                            $bandingresotarjeta=false;
                            $pdf::Ln(); 
                        }
                        if($cobranza==0 && $value->conceptopago->tipo=="I"){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(281,7,utf8_decode("COBRANZA"),1,0,'L');
                            $pdf::Ln();
                        }
                        if($value->situacion<>'A'){
                            $pdf::SetTextColor(0,0,0);
                        }else{
                            $pdf::SetTextColor(255,0,0);
                        }
                        $pdf::SetFont('helvetica','',7);
                        $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                        $nombrepersona = '-';
                        $venta = Movimiento::find($value->movimiento_id);
                        if ($value->persona_id !== NULL) {
                            //echo 'entro'.$value->id;break;
                            if($venta->tipodocumento_id==5){
                                $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                            }else{
                                $nombrepersona = $value->persona->bussinesname;
                            }
                        }else{
                            $nombrepersona = trim($value->nombrepaciente);
                        }
                        if(strlen($nombrepersona)>30){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();                    
                            $pdf::Multicell(60,3,($nombrepersona),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(60,7,"",1,0,'C');
                        }else{
                            $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                        }
                        $pdf::Cell(8,7,($venta->tipodocumento_id==5?'B':"F"),1,0,'C');
                        $pdf::Cell(12,7,utf8_decode($venta->serie.'-'.$venta->numero),1,0,'C');
                        if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario.' - RH: '.$value->voucher;
                        }else{
                            $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                        }
                        if(strlen($descripcion)>70){
                            $x=$pdf::GetX();
                            $y=$pdf::GetY();
                            $pdf::Multicell(110,3,($descripcion),0,'L');
                            $pdf::SetXY($x,$y);
                            $pdf::Cell(110,7,"",1,0,'L');
                        }else{
                            $pdf::Cell(110,7,($descripcion),1,0,'L');
                        }
                        if($value->situacion<>'R' && $value->situacion2<>'R'){
                            if($value->conceptopago->tipo=="I"){
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            }else{
                                $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                                $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                            }
                        }else{
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                            $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        }
                        $cobranza=$cobranza + $value->total;
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                        $pdf::Ln();
                    }
                }elseif($value->conceptopago_id==33){//PAGO DE FARMACIA
                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }
                    if($pago>0 && $bandpago){
                            $pdf::SetFont('helvetica','B',8.5);
                            $pdf::Cell(223,7,'TOTAL',1,0,'R');
                            $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                            $bandpago=false;
                            $pdf::Ln(); 
                        }

                    if($ingresotarjeta==0){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS POR TRANSFERENCIA"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id==31){
                        $pdf::Cell(8,7,'T',1,0,'C');
                    }else{
                        $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                        
                    }
                    $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                
                    $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $ingresotarjeta=$ingresotarjeta + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }elseif($value->conceptopago_id!=1 && $value->conceptopago_id!=2 && $value->conceptopago_id!=23 && $value->conceptopago_id!=10){
                    $pdf::SetTextColor(0,0,0);
                    if($transferenciae>0 && $bandtransferenciae){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(205,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($transferenciae,2,'.',''),1,0,'R');
                        $transferenciae=false;
                        $pdf::Ln(); 
                    }
                    if(($ingreso==0 || $pago==0) && $value->conceptopago->tipo=="I"){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                        $pdf::Ln();
                        if($pago==0){
                            if($caja_id==3){
                                $pdf::SetFont('helvetica','B',7);
                                $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                                $apert = Movimiento::find($aperturas[$valor]);
                                $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                                $pago = $pago + $apert->total;
                                $ingreso = $ingreso + $apert->total;
                                $pdf::Ln();
                            }
                        }
                    }elseif($egreso1==0 && $value->conceptopago->tipo=="E"){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(281,7,utf8_decode("EGRESOS"),1,0,'L');
                        $pdf::Ln();
                    }
                    if($value->situacion<>'A'){
                        $pdf::SetTextColor(0,0,0);
                    }else{
                        $pdf::SetTextColor(255,0,0);
                    }
                    $pdf::SetFont('helvetica','',7);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
                    $nombrepersona = '-';
                    if ($value->persona_id != NULL && !is_null($value->persona)) {
                        //echo 'entro'.$value->id;break;
                        if ($value->persona->bussinesname != null) {
                            $nombrepersona = $value->persona->bussinesname;
                        }else{
                            $nombrepersona =$value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres;
                        }
                        
                    }else{
                        $nombrepersona = trim($value->nombrepaciente);
                    }
                    if(strlen($nombrepersona)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombrepersona),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombrepersona),1,0,'L');    
                    }
                    if($value->conceptopago_id!=13){
                        if($value->conceptopago_id==31){
                            $pdf::Cell(8,7,'T',1,0,'C');
                        }else{
                            if ($caja_id == 4) {
                                if ($value->tipodocumento_id == 7) {
                                    $pdf::Cell(8,7,'BV',1,0,'C');
                                }elseif($value->tipodocumento_id == 6){
                                    $pdf::Cell(8,7,'FT',1,0,'C');
                                }else{
                                    $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                                }
                            }else{
                               $pdf::Cell(8,7,trim($value->formapago==''?'':$value->formapago),1,0,'C'); 
                            }
                            
                        }
                        $pdf::Cell(12,7,utf8_decode(trim($value->voucher)==''?$value->numero:$value->voucher),1,0,'C');
                    }else{//PARA ANULACION POR NOTA CREDITO
                        $pdf::Cell(8,7,'NA',1,0,'C');
                        //print_r($value->id);
                        $mov = Movimiento::find($value->movimiento_id);
                        $pdf::Cell(12,7,($mov->serie.'-'.$mov->numero),1,0,'C');
                    }

                    if($value->conceptopago_id==11){//PAGO A ENFERMERIA
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }else{
                        $descripcion=$value->conceptopago->nombre.': '.$value->comentario;
                    }
                    if(strlen($descripcion)>70){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();
                        $pdf::Multicell(110,3,SUBSTR($descripcion,0,150),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(110,7,"",1,0,'L');
                    }else{
                        $pdf::Cell(110,7,($descripcion),1,0,'L');
                    }
                    if($value->situacion<>'R' && $value->situacion2<>'R'){
                        if($value->conceptopago->tipo=="I"){
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'R');
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pago=$pago + $value->total;
                        }else{
                            $egreso1=$egreso1 + $value->total;
                            $pdf::Cell(18,7,number_format($value->total,2,'.',''),1,0,'R');
                            $pdf::Cell(18,7,utf8_decode(""),1,0,'C');
                        }
                    }else{
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                        $pdf::Cell(18,7,utf8_decode(" - "),1,0,'C');
                    }
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Cell(20,7,utf8_decode(""),1,0,'C');
                    $pdf::Ln();
                }
                
                if($value->conceptopago_id<>2 && $value->situacion<>'A'){
                    if($value->conceptopago->tipo=="I"){
                        if($value->conceptopago_id<>10){//GARANTIA
                            if($value->conceptopago_id<>15 && $value->conceptopago_id<>17 && $value->conceptopago_id<>19 && $value->conceptopago_id<>21){
                                if ($value->tipodocumento_id != 15) {
                                    //echo $value->total."@";
                                    $ingreso = $ingreso + $value->total;
                                }
                                    
                            }elseif(($value->conceptopago_id==15 || $value->conceptopago_id==17 || $value->conceptopago_id==19 || $value->conceptopago_id==21) && $value->situacion=='C'){
                                $ingreso = $ingreso + $value->total;    
                            }
                        }else{
                            $garantia = $garantia + $value->total;
                        }
                        if($value->conceptopago_id<>10){//GARANTIA
                            if($value->tipotarjeta=='VISA'){
                                    $visa = $visa + $value->total;
                            }elseif($value->tipotarjeta==''){
                                if ($value->tipodocumento_id != 15) {
                                    $efectivo = $efectivo + $value->total;
                                }
                            }else{
                                $master = $master + $value->total;
                            }
                        }
                    }else{
                        if($value->conceptopago_id<>14 && $value->conceptopago_id<>16 && $value->conceptopago_id<>18 && $value->conceptopago_id<>20){
                            if($value->conceptopago_id==8){//HONORARIOS MEDICOS
                                $ingreso  = $ingreso - $value->total;
                                $efectivo = $efectivo - $value->total;
                            }else{
                                $egreso  = $egreso + $value->total;
                            }
                        }elseif(($value->conceptopago_id==14 || $value->conceptopago_id==16 || $value->conceptopago_id==18 || $value->conceptopago_id==20) && $value->situacion2=='C'){
                            $egreso  = $egreso + $value->total;
                        }
                    }
                }
                $res=$value->responsable2;
                if ($caja_id == 4) {
                    /*if($tarjeta>0 && $bandtarjeta){
                        $pdf::SetFont('helvetica','B',8.5);
                        $pdf::Cell(223,7,'TOTAL',1,0,'R');
                        $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                        $bandtarjeta=false;
                        $pdf::Ln(); 
                    }*/
                }
            }
            if($ingresotarjeta>0 && $bandingresotarjeta){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($ingresotarjeta,2,'.',''),1,0,'R');
                $bandingresotarjeta=false;
                $pdf::Ln(); 
            }
            if($cobranza>0 && $bandcobranza){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($cobranza,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($transferenciai>0 && $bandtransferenciai){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($transferenciai,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($pago==0){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(281,7,utf8_decode("INGRESOS"),1,0,'L');
                $pdf::Ln();
                if($caja_id==3){
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(223,7,utf8_decode("SALDO INICIAL"),1,0,'L');
                    $apert = Movimiento::find($aperturas[$valor]);
                    $pdf::Cell(18,7,number_format($apert->total,2,'.',''),1,0,'R');
                    $pago = $pago + $apert->total;
                    $ingreso = $ingreso + $apert->total;
                    $pdf::Ln();
                }
            }
            if($pago>0 && $bandpago){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($pago,2,'.',''),1,0,'R');
                $bandpago=false;
                $pdf::Ln(); 
            }
            if($tarjeta>0 && $bandtarjeta){
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(223,7,'TOTAL',1,0,'R');
                $pdf::Cell(18,7,number_format($tarjeta,2,'.',''),1,0,'R');
                $bandtarjeta=false;
                $pdf::Ln(); 
            }
            $resultado1       = Movimiento::join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                                ->leftjoin('movimiento as m2','movimiento.movimiento_id','=','m2.id')
                                ->leftjoin('person as paciente', 'paciente.id', '=', 'm2.persona_id')
                                ->where('movimiento.serie', '=', $serie)
                                ->where('movimiento.tipomovimiento_id', '=', 4)
                                ->where('movimiento.tipodocumento_id', '<>', 15)
                                ->where(function ($query) use($aperturas,$cierres,$valor) {
                                    $query->where(function($q) use($aperturas,$cierres,$valor){
                                            $q->where('movimiento.id', '>', $aperturas[$valor])
                                            ->where('movimiento.id', '<', $cierres[$valor])
                                            ->whereNull('movimiento.cajaapertura_id');
                                    })
                                          ->orwhere(function ($query1) use($aperturas,$cierres,$valor){
                                            $query1->where('movimiento.cajaapertura_id','=',$aperturas[$valor]);
                                            });//normal
                                })
                                ->where('movimiento.situacion','like','U');
            $resultado1       = $resultado1->select('movimiento.*','m2.situacion as situacion2',DB::raw('concat(paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as paciente2'))->orderBy('movimiento.numero', 'asc');
            
            $lista1           = $resultado1->get();
            if(count($lista1)>0){
                //echo 'alert('.count($lista1).')';
                $anuladas=0;
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(281,7,utf8_decode("ANULADAS"),1,0,'L');
                $pdf::Ln();
                foreach($lista1 as $key1 => $value1){
                    $pdf::SetFont('helvetica','',7.5);
                    $pdf::Cell(15,7,date("d/m/Y",strtotime($value1->fecha)),1,0,'C');
                    if($value1->tipodocumento_id==5){//BOLETA}
                        $nombre='ANULADO';
                        //$value1->persona->apellidopaterno.' '.$value1->persona->apellidomaterno.' '.$value1->persona->nombres;
                    }else{
                        $nombre=$value1->paciente2;
                        if($value1->persona_id>0){
                            $empresa=$value1->persona->bussinesname;
                        }else{
                            $empresa='';
                        }
                    }
                    if(strlen($nombre)>30){
                        $x=$pdf::GetX();
                        $y=$pdf::GetY();                    
                        $pdf::Multicell(60,3,($nombre),0,'L');
                        $pdf::SetXY($x,$y);
                        $pdf::Cell(60,7,"",1,0,'C');
                    }else{
                        $pdf::Cell(60,7,($nombre),1,0,'L');    
                    }
                    $pdf::Cell(8,7,($value1->tipodocumento_id==5?'B':'F'),1,0,'C');
                    $pdf::Cell(12,7,$value1->serie.'-'.$value1->numero,1,0,'C');
                    if($caja_id==4){
                        $nombreempresa='-';
                        if ($value->tipodocumento_id != 5) {
                            if ($value->empresa != null) {
                                $nombreempresa = trim($value->empresa->bussinesname);
                            }
                        }
                        $pdf::Cell(40,7,substr($nombreempresa,0,23),1,0,'L');
                    }else{
                        if($value1->tipodocumento_id==5){
                            if($value1->movimiento_id>0){
                            $ticket= Movimiento::find($value1->movimiento_id);
                            if($ticket->plan_id>0)
                                $pdf::Cell(40,7,substr($ticket->plan->nombre,0,23),1,0,'L');
                            else
                                $pdf::Cell(40,7,"",1,0,'L');
                            }else{
                                $pdf::Cell(40,7,"",1,0,'L');
                            }
                        }else{
                            $pdf::Cell(40,7,substr($empresa,0,23),1,0,'L');
                        }
                    }
                    if($caja_id==4){
                        $pdf::Cell(70,7,"MEDICINA",1,0,'L');    
                    }else{
                        $pdf::Cell(70,7,"SERVICIOS",1,0,'L');    
                    }
                    $pdf::Cell(18,7,'',1,0,'C');
                    $pdf::Cell(18,7,number_format(0,2,'.',''),1,0,'R');
                    $pdf::Cell(20,7,utf8_decode(" - "),1,0,'C');
                    $pdf::Cell(20,7,'-',1,0,'L');
                    //substr($v->persona->nombres,0,1).'. '.$v->persona->apellidopaterno
                    $pdf::Ln();
                    $anuladas=$anuladas + number_format(0,2,'.','');
                }
            }
            $resp=Movimiento::find($cierres[$valor]);
            $pdf::Ln();
            $pdf::Cell(120,7,('RESPONSABLE: '.$resp->responsable->nombres),0,0,'L');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(50,7,utf8_decode("RESUMEN DE CAJA"),1,0,'C');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("INGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Efectivo :"),1,0,'L');
            $pdf::Cell(20,7,number_format($efectivo,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Master :"),1,0,'L');
            $pdf::Cell(20,7,number_format($master,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("Visa :"),1,0,'L');
            $pdf::Cell(20,7,number_format($visa,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("EGRESOS :"),1,0,'L');
            $pdf::Cell(20,7,number_format($egreso,2,'.',''),1,0,'R');
            $pdf::Ln();
            $pdf::Cell(120,7,utf8_decode(""),0,0,'C');
            $pdf::Cell(30,7,utf8_decode("SALDO :"),1,0,'L');
            $pdf::Cell(20,7,number_format($ingreso - $egreso - $visa - $master,2,'.',''),1,0,'R');
            $pdf::Ln();
            //$pdf::Output('ListaCaja.pdf');
                
            }

        }
        $pdf::Output('ListaCaja.pdf');
    }

    public function apertura(Request $request)
    {
        $entidad             = 'Caja';
        $formData            = array('caja.aperturar');
        $listar              = $request->input('listar');
        $numero              = Movimiento::NumeroSigue(4,6);//movimiento caja y documento ingreso
        $ultimo = Movimiento::where('concepto_id','=',2)  
                   ->orderBy('id','desc')->limit(1)->first();
        if(!is_null($ultimo)){
            $total=number_format($ultimo->total,2,'.','');
        }else{
            $total=0;    
        }
        $totalpagado=0;
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Aperturar';
        return view($this->folderview.'.apertura')->with(compact('formData', 'entidad', 'boton', 'listar', 'numero', 'total','totalpagado'));
    }
    
    public function aperturar(Request $request)
    {
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d H:i:s");
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->totalpagado=str_replace(",","",$request->input('totalpagado')); 
            $movimiento->tipomovimiento_id=4;
            $movimiento->tipodocumento_id=6;
            $movimiento->concepto_id=1;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->situacion='N';
            $movimiento->moneda='S';
            $movimiento->tipo='Contable';
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function edit2(Request $request)
    {
        $entidad             = 'Caja';
        $formData            = array('caja.editarapertura');
        $listar              = $request->input('listar');
        $ultimo = Movimiento::where('concepto_id','=',1)  
                   ->orderBy('id','desc')->limit(1)->first();
        if(!is_null($ultimo)){
            $total=number_format($ultimo->total,2,'.','');
            $totalpagado=number_format($ultimo->totalpagado,2,'.','');
            $numero = $ultimo->numero;
        }else{
            $total=0;    
            $numero="";
            $totalpagado=0;
        }
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Aperturar';
        return view($this->folderview.'.apertura')->with(compact('formData', 'entidad', 'boton', 'listar', 'numero', 'total','totalpagado'));
    }
    
    public function editarapertura(Request $request)
    {
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $movimiento  = Movimiento::where('concepto_id','=',1)  
                   ->orderBy('id','desc')->limit(1)->first();
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->totalpagado=str_replace(",","",$request->input('totalpagado')); 
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function generarConcepto(Request $request)
    {
        $tipodoc = $request->input("tipodocumento_id");
        if($tipodoc==6){
            $rst = Concepto::where('tipo','like','I')->where('id','<>',1)->where('id','<>',3)->orderBy('nombre','ASC')->get();
        }else{
            $rst = Concepto::where('tipo','like','E')->where('id','<>',2)->orderBy('nombre','ASC')->get();
        }
        $cbo="";
        foreach ($rst as $key => $value) {
            $cbo = $cbo."<option value='".$value->id."'>".$value->nombre."</option>";
        }
         
        return $cbo;
    }
        
    public function generarNumero(Request $request)
    {
        $tipodoc = $request->input("tipodocumento_id");
        $numero  = Movimiento::NumeroSigue(4,$tipodoc);
        return $numero;
    }
    
    public function personautocompletar($searching)
    {
        $resultado        = Person::where(DB::raw('TRIM(CONCAT(apellidopaterno," ",apellidomaterno," ",nombres))'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere('razonsocial', 'LIKE', '%'.strtoupper($searching).'%')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if (($value->razonsocial != null && trim($value->razonsocial)!="") || trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres)=="") {
                $name = $value->razonsocial;
            }else{
                $name = trim($value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres);
            }
            $data[] = array(
                            'label' => trim($name),
                            'id'    => $value->id,
                            'value' => trim($name),
                        );
        }
        return json_encode($data);
    }

    public function cierre(Request $request)
    {
        $entidad             = 'Caja';
        $formData            = array('caja.cerrar');
        $listar              = $request->input('listar');
        $numero              = Movimiento::NumeroSigue(4,7);//movimiento caja y documento egreso
        $rst              = Movimiento::where('tipomovimiento_id','=',4)->where('concepto_id','=',1)->orderBy('id','DESC')->limit(1)->first();
        if(!is_null($rst)){
            $movimiento_mayor = $rst->id;    
        }else{
            $movimiento_mayor = 0;
        }
        
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('concepto','concepto.id','=','movimiento.concepto_id')
                            ->leftjoin('movimiento as m2','movimiento.id','=','m2.movimiento_id')
                            ->whereNull('movimiento.cajaapertura_id')
                            ->where('movimiento.id', '>=', $movimiento_mayor);
        $resultado        = $resultado->select('movimiento.*','m2.situacion as situacion2',DB::raw('CONCAT(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) as paciente'),DB::raw('responsable.nombres as responsable'))->orderBy('movimiento.id', 'desc');
        $lista            = $resultado->get();
        
        $ingreso=0;$egreso=0;
        foreach($lista as $k=>$v){
            if($v->concepto_id<>2 && $v->situacion<>'A'){
                if($v->concepto->tipo=="I"){
                    $ingreso = $ingreso + $v->total;    
                }else{
                    $egreso  = $egreso + $v->total;
                }
            }
        }
        $tiempo=(strtotime('now') - strtotime($rst->created_at))/(60*60);
        $total               = number_format($ingreso - $egreso,2,'.','');
        $pago = number_format(round($tiempo*3.5,1),2,'.',''); 
        //$total = $saldo;
        $data = array();
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Cerrar';
        return view($this->folderview.'.cierre')->with(compact('formData', 'entidad', 'boton', 'listar', 'numero', 'total', 'pago'));
    }
    
    public function cerrar(Request $request)
    {
        
        $user = Auth::user();
        $error = DB::transaction(function() use($request, $user){
            $movimiento        = new Movimiento();
            $movimiento->fecha = date("Y-m-d H:i:s");
            $movimiento->numero= $request->input('numero');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->persona_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('monto')); 
            $movimiento->tipomovimiento_id=4;
            $movimiento->tipodocumento_id=6;
            $movimiento->concepto_id=2;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->situacion='N';
            $movimiento->moneda='S';
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function rechazar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->situacion="R";//Rechazado
            if ($Caja->caja_id == 4) {
                $listventas = Movimiento::where('movimientodescarga_id','=',$Caja->id)->get();
                foreach ($listventas as $key => $value) {
                    $value->movimientodescarga_id = null;
                    $value->formapago = 'P';
                    $value->save();
                }
            }else{
                $arr=explode(",",$Caja->listapago);
                for($c=0;$c<count($arr);$c++){
                    $Detalle = Detallemovcaja::find($arr[$c]);
                    if($Caja->conceptopago_id==6){//CAJA
                        $Detalle->situacion='N';//normal;
                    }elseif($Caja->conceptopago_id==17){//SOCIO
                        $Detalle->situacionsocio=null;//null
                    }elseif($Caja->conceptopago_id==15 || $Caja->conceptopago_id==21){//TARJETA Y BOLETEO TOTAL
                        $Detalle->situaciontarjeta=null;//null
                    }
                    $Detalle->save();
                }
            }
            

            $Caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function reject($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Caja::find($id);
        $entidad  = 'Caja';
        $formData = array('route' => array('caja.rechazar', $id), 'method' => 'Reject', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Rechazar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function aceptar($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $Caja = Movimiento::find($id);
            $Caja->situacion="C";//Aceptado
            $arr=explode(",",$Caja->listapago);
            for($c=0;$c<count($arr);$c++){
                $Detalle = Detallemovcaja::find($arr[$c]);
                if($Caja->conceptopago_id==6){//CAJA
                    $Detalle->situacion='C';//confirmado;
                }elseif($Caja->conceptopago_id==17){//SOCIO
                    $Detalle->situacion='C';//confirmado;
                }elseif($Caja->conceptopago_id==15 || $Caja->conceptopago_id==21){//TARJETA Y BOLETEO TOTAL
                    $Detalle->situacion='C';//confirmado;
                }
                $Detalle->save();
            }
            $Caja->save();
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function buscarCompras(Request $request)
    {
        $resultado  = Movimiento::join('person','person.id','=','movimiento.persona_id')
                        ->leftjoin('person as responsable','responsable.id','=','movimiento.responsable_id')
                        ->where('tipomovimiento_id','=',1)
                        ->whereNotIn('situacion',['A'])
                        ->where('movimiento.total','>',DB::raw('case when movimiento.totalpagado is null then 0 else movimiento.totalpagado end'))
                        ->where('movimiento.persona_id','=',$request->input('persona_id'));
        $resultado = $resultado->select('movimiento.*',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('cliente','asc')->orderBy('fecha', 'ASC')->get();
        $registro='';//echo $resultado->count();
        foreach($resultado as $k=>$v){
            $registro.="<tr>";
            $registro.="<td>$v->numero</td>";
            $registro.="<td>".number_format($v->total - $v->totalpagado,2,'.','')."</td>";
            $registro.="<td><input type='text' style='width:80px;' id='txtPago$v->id' name='txtPago$v->id' value='0' class='pago input-xs form-control' /></td>";
            $registro.="</tr>";
        }
        echo $registro;
    }

    public function acept($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Caja::find($id);
        $entidad  = 'Caja';
        $formData = array('route' => array('caja.aceptar', $id), 'method' => 'Acept', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Aceptar';
        return view('app.confirmar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }

}