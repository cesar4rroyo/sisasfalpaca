<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Tipodocumento;
use App\Tipomovimiento;
use App\Movimiento;
use App\Pago;
use App\Concepto;
use App\Producto;
use App\Detallemovimiento;
use App\Stockproducto;
use App\Tipocambio;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Excel;
use Elibyy\TCPDF\Facades\TCPDF;

class ReporteventaController extends Controller
{
    protected $folderview      = 'app.reporteventa';
    protected $tituloAdmin     = 'Reporte Venta';
    protected $tituloRegistrar = 'Registrar compra';
    protected $tituloModificar = 'Modificar compra';
    protected $tituloEliminar  = 'Eliminar compra';
    protected $tituloVer       = 'Ver Compra';
    protected $rutas           = array('create' => 'reporteventa.create', 
            'show'   => 'reporteventa.show', 
            'search' => 'reporteventa.buscar',
            'index'  => 'reporteventa.index',
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
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                                ->where('tipomovimiento_id','=',2)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.ruc,\' \',person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('cliente')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if(trim($request->input('situacion'))!=''){
            if(trim($request->input('situacion'))=='P'){
                $resultado = $resultado->where('movimiento.total','>',DB::raw('case when movimiento.totalpagado is null then 0 else movimiento.totalpagado end'));
            }else{
                $resultado = $resultado->where('movimiento.total','=','movimiento.totalpagado');
            }
        }
        if($request->input('producto')!=""){
            $resultado = $resultado->where(DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end'),'like','%'.$request->input('producto').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa')->orderBy('fecha', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Doc.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cliente', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Producto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'P. Venta', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Subtotal', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver       = $this->tituloVer;
        $ruta             = $this->rutas;
        $totals = 0;$totald = 0;
        if (count($lista) > 0) {
            $clsLibreria     = new Libreria();
            $paramPaginacion = $clsLibreria->generarPaginacion($lista, $pagina, $filas, $entidad);
            $paginacion      = $paramPaginacion['cadenapaginacion'];
            $inicio          = $paramPaginacion['inicio'];
            $fin             = $paramPaginacion['fin'];
            $paginaactual    = $paramPaginacion['nuevapagina'];
            $lista           = $resultado->paginate($filas);
            $request->replace(array('page' => $paginaactual));
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta', 'titulo_ver' ,'totals', 'totald'));
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
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',2)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }
        $cboSituacion = array('' => 'Todos', 'P' => 'Pendiente', 'C' => 'Pagado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoDocumento', 'cboSituacion'));
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

    public function excel(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                                ->where('tipomovimiento_id','=',2)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.ruc,\' \',person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('cliente')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if(trim($request->input('situacion'))!=''){
            if(trim($request->input('situacion'))=='P'){
                $resultado = $resultado->where('movimiento.total','>',DB::raw('case when movimiento.totalpagado is null then 0 else movimiento.totalpagado end'));
            }else{
                $resultado = $resultado->where('movimiento.total','=','movimiento.totalpagado');
            }
        }
        if($request->input('producto')!=""){
            $resultado = $resultado->where(DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end'),'like','%'.$request->input('producto').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa')->orderBy('fecha', 'ASC')->get();
        
        Excel::create('ExcelVenta', function($excel) use($lista,$request) {
 
            $excel->sheet('Venta', function($sheet) use($lista,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Nro";
                $cabecera[] = "Cliente";
                $cabecera[] = "Cant.";
                $cabecera[] = "Producto";
                $cabecera[] = "P. Venta";
                $cabecera[] = "Subtotal";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $subtotal=0;
                $igv=0;
                $total=0;
                $totalpagado=0;
                foreach ($lista as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = $value->tipodocumento->nombre;
                    $detalle[] = $value->numero;
                    $detalle[] = $value->cliente;
                    $detalle[] = number_format($value->cantidad,2,'.','');
                    $detalle[] = $value->producto;
                    $detalle[] = number_format($value->precioventa,2,'.','');
                    $detalle[] = number_format($value->precioventa*$value->cantidad,2,'.','');
                    $total=$total+number_format($value->precioventa*$value->cantidad,2,'.','');
                    $totalpagado=$totalpagado+number_format($value->totalpagado,2,'.','');
                    $detalle[] = $value->responsable2;
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
                $cabecera[] = "Total";
                $cabecera[] = number_format($total,2,'.','');
                $sheet->row($c,$cabecera);
            });
        })->export('xls');
    }
    
    public function pdf(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                                ->where('tipomovimiento_id','=',2)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.ruc,\' \',person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('cliente')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if(trim($request->input('situacion'))!=''){
            if(trim($request->input('situacion'))=='P'){
                $resultado = $resultado->where('movimiento.total','>',DB::raw('case when movimiento.totalpagado is null then 0 else movimiento.totalpagado end'));
            }else{
                $resultado = $resultado->where('movimiento.total','=','movimiento.totalpagado');
            }
        }
        if($request->input('producto')!=""){
            $resultado = $resultado->where(DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end'),'like','%'.$request->input('producto').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' / \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa')->orderBy('fecha', 'ASC')->get();
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
                // Set font
                $pdf2->SetFont('helvetica', 'B', 10);
                $pdf2->SetXY(100,3);
                $pdf2->Cell(0, 8, date("d/m/Y H:i"), 0, false, 'R');
        
        });
        $pdf->fechainicio =$request->input('fechainicio');
        $pdf->fechafin =$request->input('fechafin');

        $pdf::SetTitle('Reporte Venta');
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',12);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,"REPORTE DE VENTA",0,0,'C');
        $pdf::Ln(); 
        
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(18,8,"Fecha",1,0,'C');
        $pdf::Cell(23,8,"Tipo Doc.",1,0,'C');
        $pdf::Cell(25,8,"Nro",1,0,'C');
        $pdf::Cell(80,8,"Cliente",1,0,'C');
        $pdf::Cell(20,8,"Cant.",1,0,'C');
        $pdf::Cell(80,8,"Producto",1,0,'C');
        $pdf::Cell(20,8,"P. Venta",1,0,'C');
        $pdf::Cell(20,8,"Subtotal",1,0,'C');
        $pdf::Ln();
        
        $c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;$vencido=0;
        foreach ($lista as $key => $value) {
            $proveedor=$value->cliente;
            $pdf::SetFont('helvetica','',8.5);
            $comentario=$value->producto;
            $alto=$pdf::getNumLines($comentario, 57)*4;
            $alto1=$pdf::getNumLines($value->cliente, 80)*4;
            if($alto1>$alto) $alto=$alto1;
            $pdf::Cell(18,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $pdf::Cell(23,$alto,substr($value->tipodocumento->nombre,0,11),1,0,'L');
            $pdf::Cell(25,$alto,$value->numero,1,0,'L');
            $pdf::SetTextColor(0,0,0);
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(80,3.5,$value->cliente,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(80,$alto,'',1,0,'L');
            $pdf::Cell(20,$alto,number_format($value->cantidad,2,'.',','),1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(80,3.5,$value->producto,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(80,$alto,'',1,0,'L');
            $pdf::Cell(20,$alto,number_format($value->precioventa,2,'.',','),1,0,'C');
            $pdf::Cell(20,$alto,number_format($value->precioventa*$value->cantidad,2,'.',','),1,0,'C');
            $pdf::Ln();
            $total = $total + number_format($value->precioventa*$value->cantidad,2,'.','');
        }
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::SetTextColor(255,0,0);
        $pdf::Cell(202,5,"",0,0,'L');
        $pdf::SetFont('helvetica','B',12);
        $pdf::SetTextColor(0,0,0);
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::Cell(64,5,"TOTAL S/",0,0,'R');
        $pdf::Cell(20,5,number_format($total,2,'.',','),1,0,'C');
        $pdf::Ln();
        $pdf::Output('ReporteVentas.pdf');
    }

    public function pdf2(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fechavencimiento','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where(function($sql) use($request){
                            $sql->where('fechavencimiento','<=',$request->input('fechafin'))
                                ->orWhere('fecha','<=',$request->input('fechafin'));
                            });
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if(trim($request->input('situacion'))!=''){
            if(trim($request->input('situacion'))=='P'){
                $resultado = $resultado->where('movimiento.total','>',DB::raw('case when movimiento.totalpagado is null then 0 else movimiento.totalpagado end'));
            }else{
                $resultado = $resultado->where('movimiento.total','=','movimiento.totalpagado');
            }
        }
        if($request->input('modo')!="" && $request->input('monto')!=""){
            $resultado = $resultado->where(DB::raw('case when movimiento.moneda=\'S\' then movimiento.total else (case when (select count(*) from tipocambio where fecha=movimiento.fecha and deleted_at is null)=0 then 0 else (select monto from tipocambio where fecha=movimiento.fecha and deleted_at is null) end )*movimiento.total end'),$request->input('modo').'=',$request->input('monto'));
        }
        $resultado = $resultado->select('movimiento.*',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('cliente','asc')->orderBy('fecha', 'ASC')->get();
        
        
        $pdf = new TCPDF();
        $pdf::SetTitle('Cuentas por Pagar');
        $pdf::AddPage('P');
        $pdf::SetFont('helvetica','B',12);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);
        $pdf::Cell(0,10,"FACTURAS POR PAGAR ",0,0,'C');
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
                    $pdf::Cell(20,5,number_format($totalp - $totalpagadop,2,'.',','),1,0,'C');
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
        $pdf::Cell(20,5,number_format($totalp - $totalpagadop,2,'.',','),1,0,'C');
        $pdf::Ln();
        $totalp=0;$totalpagadop=0;
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(160,5,"TOTAL GENERAL S/",0,0,'R');
        //$pdf::Cell(20,5,number_format($total,2,'.',','),1,0,'C');
        //$pdf::Cell(20,5,number_format($totalpagado,2,'.',','),1,0,'C');
        $pdf::Cell(20,5,number_format($total - $totalpagado,2,'.',','),1,0,'C');
        $pdf::Ln();
        $pdf::Output('Cuentasporpagar.pdf');
    }
    
    public function pdfDetraccion(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->leftjoin('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                                ->where('movimiento.tipomovimiento_id','=',2)
                                ->where('movimiento.incluye','like','S')
                                ->whereNotIn('movimiento.situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafin'));
        }
        if($request->input('cliente')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.ruc,\' \',person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('cliente')).'%');
        }
        if($request->input('tipodocumento')!=""){
            $resultado = $resultado->where('movimiento.tipodocumento_id','=',$request->input('tipodocumento'));
        }
        if(trim($request->input('situacion'))!=''){
            if(trim($request->input('situacion'))=='P'){
                $resultado = $resultado->where('movimiento.entregado','not like','S');
            }else{
                $resultado = $resultado->where('movimiento.entregado','like','S');
            }
        }
        $resultado = $resultado->select('movimiento.*','person.ruc',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'),'m2.comentario as comentario2')
                        ->orderBy('movimiento.nrooperacion','desc')
                        ->orderBy('cliente','asc')
                        ->orderBy('fecha', 'ASC')
                        ->get();
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
                // Set font
                $pdf2->SetFont('helvetica', 'B', 10);
                $pdf2->SetXY(100,3);
                $pdf2->Cell(0, 8, date("d/m/Y H:i"), 0, false, 'R');
        
        });
        $pdf->fechainicio =$request->input('fechainicio');
        $pdf->fechafin =$request->input('fechafin');

        $pdf::SetTitle('Detracciones');
        $pdf::AddPage('P');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(0,10,"DETRACCIONES",0,0,'C');
        //$pdf::Ln(); 
        $c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;$vencido=0;$auto=true;$totalg=0;$totalg2=0;
        foreach ($resultado as $key => $value) {
            if($value->nrooperacion!="Detraccion" && $auto){
                $pdf::SetTextColor(0,0,0);
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(41,5,"TOTAL S/",0,0,'R');
                $pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
                $pdf::SetFont('helvetica','B',12);
                $pdf::SetFont('helvetica','B',8.5);
                $pdf::Cell(20,5,"",0,0,'L');
                $pdf::Cell(20,5,number_format($totalpagadop,2,'.',','),1,0,'C');
                $pdf::Ln();
                $totalg=$totalg+$totalp;
                $totalg2=$totalg2+$totalpagadop;
                $totalp=0;$totalpagadop=0;$vencido=0;
                $pdf::Ln();
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',12);
                $pdf::Cell(0,10,"AUTODETRACCIONES",0,0,'C');
                //$pdf::Ln();
                $auto=false;
            }
            
            if($proveedor!=$value->cliente){
                if($proveedor!="" && $totalp>0){
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::SetTextColor(0,0,0);
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::Cell(41,5,"TOTAL S/",0,0,'R');
                    $pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
                    $pdf::SetFont('helvetica','B',12);
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::Cell(20,5,"",0,0,'L');
                    $pdf::Cell(20,5,number_format($totalpagadop,2,'.',','),1,0,'C');
                    $pdf::Ln();
                    $totalg=$totalg+$totalp;
                    $totalg2=$totalg2+$totalpagadop;
                    $totalp=0;$totalpagadop=0;$vencido=0;
                }
                $pdf::Ln();
                if($pdf::GetY()>250){
                    $pdf::AddPage('P');
                }
                $c=$c+1;
                $pdf::SetFont('helvetica','B',12);
                $pdf::Cell(185,7,$c.". ".$value->ruc." ".$value->cliente,1,0,'L');    
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(18,8,"Fecha",1,0,'C');
                $pdf::Cell(23,8,"Nro",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(20,8,"Situacion",1,0,'C');
                $pdf::Cell(20,8,"Saldo",1,0,'C');
                $pdf::Ln();
                $proveedor=$value->cliente;
            }
            $pdf::SetFont('helvetica','',8.5);
            $alto=6;
            $pdf::Cell(18,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $pdf::Cell(23,$alto,$value->numero,1,0,'L');
            $pdf::Cell(20,$alto,number_format($value->detraccion,2,'.',','),1,0,'C');
            if($value->entregado!='S'){
                $pdf::SetTextColor(255,0,0);
            }else{
                $pdf::SetTextColor(0,0,0);
            }
            $pdf::Cell(20,$alto,$value->entregado=='S'?'Cancelado':'Pendiente',1,0,'C');
            $pdf::SetTextColor(0,0,0);
            if($value->entregado!='S'){
                $pdf::Cell(20,$alto,number_format($value->detraccion,2,'.',''),1,0,'C');
            }else{
                $pdf::Cell(20,$alto,'0.00',1,0,'C');
            }
            $pdf::Ln();
            $totalp = $totalp + number_format($value->detraccion,2,'.','');
            if($value->entregado!='S'){
                $totalpagadop = $totalpagadop + number_format($value->detraccion,2,'.','');
                $total = $total + number_format($value->detraccion,2,'.','');
            }
        }
        $totalg=$totalg+$totalp;
        $totalg2=$totalg2+$totalpagadop;
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::SetTextColor(0,0,0);
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::Cell(41,5,"TOTAL S/",0,0,'R');
        $pdf::Cell(20,5,number_format($totalp,2,'.',','),1,0,'C');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(20,5,"",0,0,'L');
        $pdf::Cell(20,5,number_format($totalpagadop,2,'.',','),1,0,'C');
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        if($totalg>0){
            $totalp=0;$totalpagadop=0;
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(41,5,"TOTAL GENERAL S/",0,0,'R');
            $pdf::SetFont('helvetica','B',12);
            $pdf::Cell(20,5,number_format($totalg,2,'.',','),1,0,'C');
            $pdf::SetFont('helvetica','B',9);
            $pdf::Cell(20,5,"",0,0,'L');
            $pdf::Cell(20,5,number_format($totalg2,2,'.',','),1,0,'C');
            $pdf::Ln();
        }
        $pdf::Output('Cuentasporpagar.pdf');
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