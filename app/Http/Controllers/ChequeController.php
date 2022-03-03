<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Historia;
use App\Cuenta;
use App\Tipodocumento;
use App\Movimiento;
use App\Detallemovimiento;
use App\Person;
use App\Concepto;
use App\Caja;
use App\Banco;
use App\Tipocambio;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Detallemovcaja;
use App\Librerias\EnLetras;
use Illuminate\Support\Facades\Auth;
use Excel;

class ChequeController extends Controller
{
    protected $folderview      = 'app.cheque';
    protected $tituloAdmin     = 'Cheques';
    protected $tituloRegistrar = 'Registrar Cheque';
    protected $tituloModificar = 'Modificar Cheque';
    protected $tituloCobrar = 'Pagar Cheque';
    protected $tituloEliminar  = 'Eliminar Registro';
    protected $rutas           = array('create' => 'cheque.create', 
            'edit'   => 'cheque.edit', 
            'delete' => 'cheque.eliminar',
            'search' => 'cheque.buscar',
            'index'  => 'cheque.index',
            'pdfListar'  => 'cheque.pdfListar',
            'cobrar' => 'cheque.cobrar',
            'anular' => 'cheque.anular',
        );

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscar(Request $request)
    {
        $pagina           = $request->input('page');
        $filas            = $request->input('filas');
        $entidad          = 'Cheque';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',11);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fechavencimiento','>=',$request->input('fechainicio').' 00:00:00');
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fechavencimiento','<=',$request->input('fechafin').' 23:59:59');
        }        
        if($request->input('numero')!=""){
            $resultado = $resultado->where('m2.numero','LIKE','%'.$request->input('numero').'%');
        }    
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('case when paciente.razonsocial is null then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.razonsocial end'),'LIKE','%'.strtoupper($request->input('proveedor')).'%');
        }    
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        if($request->input('banco')!=""){
            $resultado = $resultado->where('movimiento.banco_id','=',$request->input('banco'));   
        }
        if($request->input('moneda')!=""){
            $resultado = $resultado->where('movimiento.moneda','=',$request->input('moneda'));   
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.razonsocial,\' \',paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'m2.numero as nroref','m2.fecha as fecharef')->orderBy('movimiento.fecha', 'ASC')->orderBY('paciente.razonsocial','asc')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Giro.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Venc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Numero', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Banco', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Moneda', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '4');
        
        $user = Auth::user();
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_cobrar    = $this->tituloCobrar;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_cobrar', 'ruta', 'user'));
        }
        return view($this->folderview.'.list')->with(compact('lista', 'entidad'));
    }

    public function index()
    {
        $entidad          = 'Cheque';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $user = Auth::user();
        $cboBanco = array("" => "Todos...");
        $rs = Banco::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboBanco = $cboBanco + array($value->id => $value->nombre);
        }
        $cboSituacion = array("" => "Todos", "P" => "Pendiente", "C" => "Pagado","A" => "Anulado");
        $cboMoneda = array('' => 'Todos', "S" => "Soles", "D" => "Dolares");
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'user','cboBanco','cboSituacion','cboMoneda'));
    }

    public function create(Request $request)
    {
        $listar              = Libreria::getParam($request->input('listar'), 'NO');
        $entidad             = 'Cheque';
        $cheque = null;
        $formData            = array('cheque.store');
        $cboBanco = array();
        $rs = Banco::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboBanco = $cboBanco + array($value->id => $value->nombre);
        }
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $formData            = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('cheque', 'formData', 'entidad', 'boton', 'listar', 'cboBanco', 'cboMoneda'));
    }

    public function store(Request $request)
    {
        $listar     = Libreria::getParam($request->input('listar'), 'NO');
        $reglas     = array(
                'total'          => 'required',
                );
        $mensajes = array(
            'total.required'         => 'Debe ingresar total',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }       
        
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($request,$user,&$dat){
            $arr=explode(",",$request->input('listProducto'));
            if($request->input('listProducto')!=""){
                for($c=0;$c<count($arr);$c++){
                    $movimiento       = new Movimiento();
                    $movimiento->fecha = $request->input('txtFecha'.$arr[$c]);
                    $movimiento->fechavencimiento = $request->input('txtFechaVencimiento'.$arr[$c]);
                    $person_id = $request->input('person_id');
                    $movimiento->persona_id = $person_id;
                    //$movimiento->numero= $request->input('txtCantidad'.$arr[$c]);
                    $movimiento->numero= $request->input('numeroref');
                    $movimiento->responsable_id=$user->person_id;
                    $movimiento->subtotal=0;
                    $movimiento->igv=0;
                    $movimiento->total=str_replace(",","",$request->input('txtTotal'.$arr[$c])); 
                    $movimiento->moneda=$request->input('moneda');
                    $movimiento->tipomovimiento_id=11;
                    $movimiento->tipodocumento_id=23;
                    $movimiento->comentario='';
                    $movimiento->situacion='P';
                    $movimiento->banco_id=$request->input('banco');
                    if($request->input('movimiento_id')!=""){
                        $movimiento->movimiento_id=$request->input('movimiento_id');
                    }
                    $movimiento->save();
                }
            }
            $dat[0]=array("respuesta"=>"OK");
        });
        return is_null($error) ? json_encode($dat) : $error;
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
        $cheque = Movimiento::find($id);
        $entidad             = 'Cheque';
        $formData            = array('cheque.update', $id);
        $cboBanco = array();
        $rs = Banco::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboBanco = $cboBanco + array($value->id => $value->nombre);
        }
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        return view($this->folderview.'.mant2')->with(compact('cheque', 'formData', 'entidad', 'boton', 'listar', 'cboBanco', 'cboMoneda'));
    }

    public function update(Request $request, $id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $dat=array();
        $error = DB::transaction(function() use($id,$user,$request,&$dat){
            $movimiento = Movimiento::find($id);
            $movimiento->fecha = $request->input('fecha');
            $movimiento->fechavencimiento = $request->input('fechavencimiento');
            $person_id = $request->input('person_id');
            $movimiento->persona_id = $person_id;
            $movimiento->numero= $request->input('numeroref');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->subtotal=0;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->moneda=$request->input('moneda');
            $movimiento->tipomovimiento_id=11;
            $movimiento->tipodocumento_id=23;
            $movimiento->comentario='';
            $movimiento->situacion='P';
            $movimiento->banco_id=$request->input('banco');
            $movimiento->save();
            $dat[0]=array("respuesta"=>"OK");
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    public function destroy($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $movimiento = Movimiento::find($id);
            $movimiento->delete();
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
        $entidad  = 'Cuentabancaria';
        $mensaje = '¿Desea eliminar el cheque? <br><br>';
        $formData = array('route' => array('cuentabancaria.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }

    public function anulacion($id)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $movimiento = Movimiento::find($id);
            $movimiento->situacion='A';
            $movimiento->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function anular($id, $listarLuego)
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
        $entidad  = 'Cheque';
        $mensaje = '¿Desea anular el cheque? <br><br>';
        $formData = array('route' => array('cheque.anulacion', $id), 'method' => 'ANULAR', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Anular';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar','mensaje'));
    }
    
    public function pdf(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->join('detallemovimiento','detallemovimiento.movimiento_id','=','m2.id')
                            ->where('movimiento.tipomovimiento_id','=',11)
                            ->whereNotIn('movimiento.situacion',['A'])
                            ->whereNull('detallemovimiento.deleted_at');
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fechavencimiento','>=',$request->input('fechainicio').' 00:00:00');
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fechavencimiento','<=',$request->input('fechafin').' 23:59:59');
        }        
        if($request->input('numero')!=""){
            $resultado = $resultado->where('m2.numero','LIKE','%'.$request->input('numero').'%');
        }    
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('case when paciente.razonsocial is null then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.razonsocial end'),'LIKE','%'.strtoupper($request->input('proveedor')).'%');
        }    
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        if($request->input('banco')!=""){
            $resultado = $resultado->where('movimiento.banco_id','=',$request->input('banco'));   
        }
        if($request->input('moneda')!=""){
            $resultado = $resultado->where('movimiento.moneda','=',$request->input('moneda'));   
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.razonsocial,\' \',paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'m2.numero as nroref','m2.fecha as fecharef','detallemovimiento.producto')
                ->orderBy('cliente','asc')
                ->orderBy('movimiento.fecha', 'ASC')
                //->orderBY('paciente.razonsocial','asc')
                ->orderBy('movimiento.numero', 'ASC')
                ->get();;
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
                // Set font
                $pdf2->SetFont('helvetica', 'B', 10);
                $pdf2->SetXY(100,3);
                $pdf2->Cell(0, 8, date("d/m/Y H:i"), 0, false, 'R');
        
        });
        $pdf->fechainicio =$request->input('fechainicio');
        $pdf->fechafin =$request->input('fechafin');
        $pdf::SetTitle('REPORTE DE CHEQUES DEL '.date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))));
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',14);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,'REPORTE DE CHEQUES DEL '.date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))),0,0,'C');
        $pdf::Ln(); 
        $c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;$vencido=0;
        foreach ($resultado as $key => $value) {
            if($proveedor!=$value->cliente){
                if($proveedor!=""){
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::SetTextColor(255,0,0);
                    $pdf::Cell(37,5,"CHEQUE VENCIDO S/",0,0,'L');
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
                }
                $c=$c+1;
                $pdf::SetFont('helvetica','B',14);
                $pdf::Cell(279,7,$c.". ".$value->cliente,1,0,'L');    
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::Multicell(14,4,"Nro Cheque",0,'C');
                $pdf::SetXY($x,$y);
                $pdf::Cell(14,8,"",1,0,'C');
                $pdf::Cell(22,8,"Banco",1,0,'C');
                $pdf::Cell(19,8,"Fecha Venc.",1,0,'C');
                $pdf::Cell(19,8,"Fecha Giro",1,0,'C');
                $pdf::Cell(50,8,"Maquinaria",1,0,'C');
                $pdf::Cell(50,8,"Detalle",1,0,'C');
                $pdf::Cell(23,8,"Tipo Doc.",1,0,'C');
                $pdf::Cell(20,8,"Nro Doc.",1,0,'C');
                $pdf::Cell(20,8,"Fecha Doc.",1,0,'C');
                //$pdf::Cell(15,8,"Moneda",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(22,8,"Saldo",1,0,'C');
                $pdf::Ln();
                $proveedor=$value->cliente;
            }
            if($value->moneda=="D"){
                $tipocambio = Tipocambio::where('fecha','=',$value->movimientoref->fecha)->first();
                if(!is_null($tipocambio)){
                    $original=$value->total;
                    $value->total = $value->total*$tipocambio->monto;
                    $value->totalpagado = $value->totalpagado*$tipocambio->monto;
                }else{
                    $value->total = 0;
                    $value->totalpagado = 0;
                    $original = $value->total;
                }
            }else{
                $original = $value->total;
            }
            $pdf::SetFont('helvetica','',8.5);
            $alto=$pdf::getNumLines($value->movimientoref->comentario, 50)*4;
            $alto1=$pdf::getNumLines($value->producto, 50)*4;
            if($alto1>$alto) $alto=$alto1;
            $pdf::Cell(14,$alto,$value->numero,1,0,'C');
            $pdf::Cell(22,$alto,$value->banco->nombre,1,0,'C');
            if($value->situacion!="C"){
                if(strtotime('now')<strtotime($value->fechavencimiento) && strtotime($value->fechavencimiento)>strtotime('+6 days',strtotime('now'))){
                    $color =  "green";
                }elseif(strtotime($value->fechavencimiento)<strtotime('+5 days',strtotime('now')) && strtotime($value->fechavencimiento)>strtotime('now')){
                    $color = "yellow";
                }elseif(strtotime('now')>strtotime($value->fechavencimiento)){
                    //$color = "red";
                    $pdf::SetTextColor(255,0,0);
                    $vencido = $vencido + $value->total;
                }
            }else{
                $color = "";
            }    
            $pdf::Cell(19,$alto,date("d/m/Y",strtotime($value->fechavencimiento)),1,0,'C');
            $pdf::SetTextColor(0,0,0);
            $pdf::Cell(19,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(50,3.5,$value->movimientoref->comentario,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(50,$alto,'',1,0,'L');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(50,3.5,$value->producto,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(50,$alto,'',1,0,'L');
            $pdf::Cell(23,$alto,substr($value->movimientoref->tipodocumento->nombre,0,7),1,0,'L');
            $pdf::Cell(20,$alto,$value->movimientoref->numero,1,0,'L');
            $pdf::Cell(20,$alto,date("d/m/Y",strtotime($value->movimientoref->fecha)),1,0,'C');
            //$pdf::Cell(15,$alto,$value->moneda=='S'?'Soles':'Dolares',1,0,'C');
            $pdf::Cell(20,$alto,($value->moneda=="D"?"$ ":"S/ ").number_format($original,2,'.',','),1,0,'C');
            if($value->situacion=="P"){
                $pdf::Cell(22,$alto,number_format($value->total,2,'.',','),1,0,'C');
            }else{
                $pdf::Cell(22,$alto,number_format(0,2,'.',','),1,0,'C');
            }
            $pdf::Ln();
            $total = $total + number_format($value->total,2,'.','');
            if($value->situacion=='C'){
                $totalpagado = $totalpagado + number_format($value->total,2,'.','');
                $totalpagadop = $totalpagadop + number_format($value->total,2,'.','');
            }
            $totalp = $totalp + number_format($value->total,2,'.','');
        }
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::SetTextColor(255,0,0);
        $pdf::Cell(37,5,"CHEQUE VENCIDO S/",0,0,'L');
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
        $pdf::Output('Letras.pdf');
    }

    public function pdfBanco(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('banco','banco.id','=','movimiento.banco_id')
                            ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',11);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fechavencimiento','>=',$request->input('fechainicio').' 00:00:00');
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fechavencimiento','<=',$request->input('fechafin').' 23:59:59');
        }        
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('case when paciente.razonsocial is null then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.razonsocial end'),'LIKE','%'.strtoupper($request->input('proveedor')).'%');
        }    
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        if($request->input('banco')!=""){
            $resultado = $resultado->where('movimiento.banco_id','=',$request->input('banco'));   
        }
        if($request->input('moneda')!=""){
            $resultado = $resultado->where('movimiento.moneda','=',$request->input('moneda'));   
        }

        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.razonsocial,\' \',paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'m2.numero as nroref','m2.fecha as fecharef','banco.nombre as banco')
                ->orderBy('banco.nombre','asc')
                ->orderBy('movimiento.numero', 'ASC')
                ->orderBy('cliente','asc')
                ->orderBy('movimiento.fecha', 'ASC')
                //->orderBY('paciente.razonsocial','asc')
                ->get();;
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
                // Set font
                $pdf2->SetFont('helvetica', 'B', 10);
                $pdf2->SetXY(100,3);
                $pdf2->Cell(0, 8, date("d/m/Y H:i"), 0, false, 'R');
        
        });
        $pdf->fechainicio =$request->input('fechainicio');
        $pdf->fechafin =$request->input('fechafin');
        $pdf::SetTitle('LISTADO DE CHEQUES DEL '.date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))));
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',14);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,'REPORTE DE CHEQUES DEL '.date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))),0,0,'C');
        $pdf::Ln(); 
        $c=0;$total=0;$totalpagado=0;$banco="";$totalp=0;$totalpagadop=0;$vencido=0;
        foreach ($resultado as $key => $value) {
            if($banco!=$value->banco){
                if($banco!=""){
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::SetTextColor(255,0,0);
                    $pdf::Cell(37,5,"CHEQUE VENCIDO S/",0,0,'L');
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
                }
                $c=$c+1;
                $pdf::SetFont('helvetica','B',14);
                $pdf::Cell(279,7,$c.". ".$value->banco,1,0,'L');    
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $x=$pdf::GetX();
                $y=$pdf::GetY();
                $pdf::Multicell(14,4,"Nro Cheque",0,'C');
                $pdf::SetXY($x,$y);
                $pdf::Cell(14,8,"",1,0,'C');
                $pdf::Cell(19,8,"Fecha Venc.",1,0,'C');
                $pdf::Cell(19,8,"Fecha Giro",1,0,'C');
                $pdf::Cell(72,8,"Proveedor",1,0,'C');
                $pdf::Cell(50,8,"Maquinaria",1,0,'C');
                $pdf::Cell(23,8,"Tipo Doc.",1,0,'C');
                $pdf::Cell(20,8,"Nro Doc.",1,0,'C');
                $pdf::Cell(20,8,"Fecha Doc.",1,0,'C');
                //$pdf::Cell(15,8,"Moneda",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(22,8,"Saldo",1,0,'C');
                $pdf::Ln();
                $banco=$value->banco;
            }
            if($value->moneda=="D"){
                if($value->situacion!="A"){
                    $tipocambio = Tipocambio::where('fecha','=',$value->movimientoref->fecha)->first();
                    if(!is_null($tipocambio)){
                        $original=$value->total;
                        $value->total = $value->total*$tipocambio->monto;
                        $value->totalpagado = $value->totalpagado*$tipocambio->monto;
                    }else{
                        $value->total = 0;
                        $value->totalpagado = 0;
                    }
                }else{
                    $original=0;
                }
            }else{
                if($value->situacion!="A"){
                    $original = $value->total;
                }else{
                    $original = 0;
                }
            }
            $pdf::SetFont('helvetica','',8.5);
            if(!is_null($value->movimientoref)){
                $alto=$pdf::getNumLines($value->movimientoref->comentario, 50)*4;
                $alto1=$pdf::getNumLines($value->cliente, 72)*4;
                if($alto1>$alto) $alto=$alto1;
            }else{
                $alto=8;
            }
            if($value->situacion=="A"){
                $pdf::SetTextColor(255,0,0);
            }else{
                $pdf::SetTextColor(0,0,0);
            }

            $pdf::Cell(14,$alto,$value->numero,1,0,'C');
            if($value->situacion!="C"){
                if(strtotime('now')<strtotime($value->fechavencimiento) && strtotime($value->fechavencimiento)>strtotime('+6 days',strtotime('now'))){
                    $color =  "green";
                }elseif(strtotime($value->fechavencimiento)<strtotime('+5 days',strtotime('now')) && strtotime($value->fechavencimiento)>strtotime('now')){
                    $color = "yellow";
                }elseif(strtotime('now')>strtotime($value->fechavencimiento)){
                    //$color = "red";
                    $pdf::SetTextColor(255,0,0);
                    $vencido = $vencido + $value->total;
                }
            }else{
                $color = "";
            }    
            $pdf::Cell(19,$alto,date("d/m/Y",strtotime($value->fechavencimiento)),1,0,'C');
            $pdf::SetTextColor(0,0,0);
            $pdf::Cell(19,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(72,3.5,$value->cliente,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(72,$alto,'',1,0,'L');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            if(!is_null($value->movimientoref)){
                $pdf::Multicell(50,3.5,$value->movimientoref->comentario,0,'L');
            }else{
                $pdf::Multicell(50,3.5,"",0,'L');
            }
            $pdf::SetXY($x,$y);
            $pdf::Cell(50,$alto,'',1,0,'L');
            if(!is_null($value->movimientoref)){
                $pdf::Cell(23,$alto,substr($value->movimientoref->tipodocumento->nombre,0,7),1,0,'L');
                $pdf::Cell(20,$alto,$value->movimientoref->numero,1,0,'L');
                $pdf::Cell(20,$alto,date("d/m/Y",strtotime($value->movimientoref->fecha)),1,0,'C');
            }else{
                $pdf::Cell(23,$alto,"",1,0,'L');
                $pdf::Cell(20,$alto,"",1,0,'L');
                $pdf::Cell(20,$alto,date("d/m/Y",strtotime($value->created_at)),1,0,'C');
            }
            //$pdf::Cell(15,$alto,$value->moneda=='S'?'Soles':'Dolares',1,0,'C');
            $pdf::Cell(20,$alto,($value->moneda=="D"?"$ ":"S/ ").number_format($original,2,'.',','),1,0,'C');
            if($value->situacion=="P"){
                $pdf::Cell(22,$alto,number_format($value->total,2,'.',','),1,0,'C');
            }else{
                $pdf::Cell(22,$alto,number_format(0,2,'.',','),1,0,'C');
            }
            $pdf::Ln();
            $total = $total + number_format($value->total,2,'.','');
            if($value->situacion=='C'){
                $totalpagado = $totalpagado + number_format($value->total,2,'.','');
                $totalpagadop = $totalpagadop + number_format($value->total,2,'.','');
            }
            $totalp = $totalp + number_format($value->total,2,'.','');
        }
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::SetTextColor(255,0,0);
        $pdf::Cell(37,5,"CHEQUE VENCIDO S/",0,0,'L');
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
        $pdf::Output('Letras.pdf');
    }

   	public function pdfListar(Request $request){
        $entidad          = 'Cuentabancaria';
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('conceptopago','conceptopago.id','=','movimiento.conceptopago_id')
                            ->where('conceptopago.nombre','like','%'.$request->input('concepto').'%')
                            ->where('movimiento.tipomovimiento_id','=',12)
                            ->whereNotIn('movimiento.situacion',['A'])
                            ->where('movimiento.Cuentabanco_id','=',$request->input('cuenta_id'));
        if($request->input('fechainicial')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicial'));
        }
        if($request->input('fechafinal')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafinal'));
        }        
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        if($request->input('formapago')!=""){
            $resultado = $resultado->where('movimiento.numeroficha','like',$request->input('formapago'));
        }
        if($request->input('tipodocumento_id')!=""){
            if($request->input('tipodocumento_id')=="0"){
                $resultado = $resultado->whereIn('movimiento.conceptopago_id',[102,103,104,105]);   
            }else{
                $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento_id'));   
            }
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('case when paciente.bussinesname is null then concat(paciente.apellidopaterno," ",paciente.apellidomaterno," ",paciente.nombres) else paciente.bussinesname end'),'LIKE','%'.strtoupper($request->input('persona')).'%');
        } 

        $resultado        = $resultado->select('movimiento.*')->orderBy('movimiento.fecha', 'ASC')->orderBy('movimiento.voucher', 'ASC')->orderBy('movimiento.numeroficha');
        $lista            = $resultado->get();
        if (count($lista) > 0) {            
            $pdf = new TCPDF();
            $pdf::SetTitle('Cuenta Bancaria');
            $pdf::AddPage('L');
            $pdf::SetFont('helvetica','B',11);
            $fechainicial=date("d/m/Y",strtotime($request->input('fechainicial')));
            $fechafinal=date("d/m/Y",strtotime($request->input('fechafinal')));
            $pdf::Cell(0,10,utf8_decode("REPORTE DE CUENTA BANCARIA DEL ".$fechainicial." AL ".$fechafinal),0,0,'C');
            $pdf::Ln();
            $pdf::SetFont('helvetica','B',8);
            //TIPO|NRO|PERSONA|CONCEPTO|IMPORTE|CONDICION(EGRESADO Y COBRADO SOLO EN CHEQUES)|FECHA|FORMA DE PAGO|
            $pdf::Cell(20,6,utf8_decode("FORMA PAGO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("NRO OPE"),1,0,'C');
            $pdf::Cell(10,6,utf8_decode("TIPO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("NRO"),1,0,'C');
            $pdf::Cell(70,6,utf8_decode("PERSONA"),1,0,'C');
            $pdf::Cell(70,6,utf8_decode("CONCEPTO"),1,0,'C');
            $pdf::Cell(15,6,utf8_decode("IMPORTE"),1,0,'C');
            $pdf::Cell(20,6,utf8_decode("CONDICION"),1,0,'C');
            $pdf::Cell(22,6,utf8_decode("FECHA COBRO"),1,0,'C');
            //$pdf::Cell(22,6,utf8_decode("FORMA PAGO"),1,0,'C');
            $pdf::Ln();
            $formapago='';$total=0;$totalg=0;
            foreach ($lista as $key => $value){
                if($formapago!=$value->fecha){
                    if($formapago!=""){
                        $pdf::SetFont('helvetica','B',7);
                        $pdf::Cell(200,5,utf8_decode('TOTAL'),1,0,'R');
                        $pdf::Cell(15,5,number_format($total,2,'.',''),1,0,'C');
                        $pdf::Ln();    
                    }
                    $pdf::SetFont('helvetica','B',7);
                    $pdf::Cell(257,5,date("d/m/Y",strtotime($value->fecha)),1,0,'L');
                    $pdf::Ln();
                    $totalg=$totalg+$total;
                    $total=0;
                    $formapago=$value->fecha;
                }
                $pdf::SetFont('helvetica','',7);
                $pdf::Cell(20,5,$value->numeroficha,1,0,'L');
                $pdf::Cell(15,5,utf8_decode($value->dni),1,0,'L');
                $pdf::Cell(10,5,utf8_decode($value->formapago),1,0,'L');
                $pdf::Cell(15,5,utf8_decode($value->voucher),1,0,'C');
                $persona = ($value->persona->apellidopaterno.' '.$value->persona->apellidomaterno.' '.$value->persona->nombres.' '.$value->persona->bussinesname);
                if(strlen($persona)>25){
                    $x=$pdf::GetX();
                    $y=$pdf::GetY();
                    $pdf::Multicell(70,2,$persona,0,'L');
                    $pdf::SetXY($x,$y);
                    $pdf::Cell(70,5,'',1,0,'L');
                }else{
                    $pdf::Cell(70,5,$persona,1,0,'L');
                }
                $pdf::Cell(70,5,($value->conceptopago->nombre),1,0,'L');
                $pdf::Cell(15,5,number_format($value->total,2,'.',''),1,0,'C');
                $pdf::Cell(20,5,utf8_decode($value->situacion=='P'?'PENDIENTE':'COBRADO'),1,0,'C');
                if($value->fechaentrega!="")
                    $pdf::Cell(22,5,date("d/m/Y",strtotime($value->fechaentrega)),1,0,'L');
                else
                    $pdf::Cell(22,5,'',1,0,'L');
                $total = $total + number_format($value->total,2,'.','');
                //$pdf::Cell(22,5,$value->numeroficha,1,0,'L');
                $pdf::Ln();
            }
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(200,5,utf8_decode('TOTAL'),1,0,'R');
            $pdf::Cell(15,5,number_format($total,2,'.',''),1,0,'C');
            $pdf::Ln(); 
            $totalg=$totalg+$total;
            $pdf::Ln(); 
            $pdf::SetFont('helvetica','B',7);
            $pdf::Cell(200,5,utf8_decode('TOTAL GENERAL'),1,0,'R');
            $pdf::Cell(15,5,number_format($totalg,2,'.',''),1,0,'C');
            $pdf::Ln(); 

            $pdf::Output('ListaVenta.pdf');
        }
    }


    public function cobrar($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'SI');
        $cheque = Movimiento::find($id);
        $entidad             = 'Cheque';
        $formData            = array('cheque.pagar', $id);
        $formData            = array('route' => $formData, 'method' => 'PAGAR', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Pagar';
        return view($this->folderview.'.cobrar')->with(compact('cheque', 'formData', 'entidad', 'boton', 'listar'));
    }

    public function pagar($id,Request $request)
    {
        //$id = $request->input('id');
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $user = Auth::user();
        $error = DB::transaction(function() use($id,$user,$request){
            $movimiento = Movimiento::find($id);
            $movimiento->fechaentrega = $request->input('fecha');
            $movimiento->situacion='C';
            //$movimiento->usuarioentrega_id=$user->person_id;
            $movimiento->save();

            $venta = Movimiento::find($movimiento->movimiento_id);
            $lista=$venta->listapago.",".$request->input('fecha')."@".$movimiento->total."@CHEQUE|";
            $venta->totalpagado = $venta->totalpagado + $movimiento->total;
            $venta->listapago=$lista;
            
            $venta->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function personautocompletar($searching)
    {
        $resultado        = Movimiento::join('person','movimiento.persona_id','=','person.id')->where('movimiento.tipomovimiento_id','=',1)
                            ->where(function($sql) use($searching){
                                $sql->where(DB::raw('CONCAT(movimiento.numero," ",apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orWhere(DB::raw('CONCAT(movimiento.numero," ",ruc," ",razonsocial)'), 'LIKE', '%'.strtoupper($searching).'%');
                            })
                            ->whereNull('movimiento.deleted_at')->where('movimiento.situacion','<>','A')->orderBy('apellidopaterno', 'ASC');
        $list      = $resultado->select('person.*','movimiento.numero',DB::raw('movimiento.id as movimiento_id'))->get();
        $data = array();
        foreach ($list as $key => $value) {
            $name = '';
            if ($value->razonsocial != null) {
                $name = $value->numero." / ".$value->razonsocial;
            }else{
                $name = $value->numero." / ".$value->apellidopaterno." ".$value->apellidomaterno." ".$value->nombres;
            }
            $data[] = array(
                            'label' => trim($name),
                            'person_id'    => $value->id,
                            'id' => $value->movimiento_id,
                            'value' => trim($name),
                            'ruc'   => $value->ruc,
                        );
        }
        return json_encode($data);
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