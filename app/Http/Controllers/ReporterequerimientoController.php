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
use App\Tipocambio;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Excel;
use Elibyy\TCPDF\Facades\TCPDF;

class ReporterequerimientoController extends Controller
{
    protected $folderview      = 'app.reporterequerimiento';
    protected $tituloAdmin     = 'Reporte Requerimiento';
    protected $tituloRegistrar = 'Registrar compra';
    protected $tituloModificar = 'Modificar compra';
    protected $tituloEliminar  = 'Eliminar compra';
    protected $tituloVer       = 'Ver Compra';
    protected $rutas           = array('create' => 'cuentasporpagar.create', 
            'show'   => 'reporterequerimiento.show', 
            'search' => 'reporterequerimiento.buscar',
            'index'  => 'reporterequerimiento.index',
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
                                ->leftjoin('maquinaria','maquinaria.id','=','movimiento.maquinaria_id')
                                ->leftjoin('obra','obra.id','=','movimiento.obra_id')
                                ->join('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                                ->leftjoin('person as proveedor','proveedor.id','=','m2.persona_id')
                                ->leftjoin('movimiento as m3', function ($join) {
                                        $join->on('m3.movimiento_id','=','m2.id')->where('m3.tipomovimiento_id','=',1)->whereNotIn('m3.situacion',['A'])->whereNull('m3.deleted_at');
                                    })
                                ->leftjoin('detallemovimiento',function ($join) {
                                        $join->on('detallemovimiento.movimiento_id','=',DB::raw('case when m2.id>0 then m2.id else movimiento.id end'))->whereNull('detallemovimiento.deleted_at');
                                })
                                ->leftjoin('producto','detallemovimiento.producto_id','=','producto.id')
                                ->where('movimiento.tipomovimiento_id','=',8)
                                ->whereNotIn('movimiento.situacion',['A'])
                                ->whereNotIn('m2.situacion',['A'])
                                ->where('movimiento.situacion','=','C');
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafin'));
        }
        if($request->input('producto')!=""){
            $resultado = $resultado->where('producto.nombre','like','%'.$request->input('producto').'%');
        }
        if($request->input('obra')!=""){
            $resultado = $resultado->where('obra.nombre','like','%'.$request->input('obra').'%');
        }
        if($request->input('maquinaria')!=""){
            $resultado = $resultado->where(function ($sql) use($request){
                                        $sql->where('maquinaria.nombre','like','%'.$request->input('maquinaria').'%')
                                            ->orWhere('maquinaria.marca','like','%'.$request->input('maquinaria').'%')
                                            ->orWhere('maquinaria.modelo','like','%'.$request->input('maquinaria').'%');
                                    });
        }
        if($request->input('placa')!=""){
            $resultado = $resultado->where('maquinaria.placa','like','%'.$request->input('placa').'%');
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(proveedor.ruc,\' \',proveedor.razonsocial,\' \',proveedor.apellidopaterno,\' \',proveedor.apellidomaterno,\' \',proveedor.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
        }
        $lista            = $resultado->select('movimiento.*','m2.id as id2',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('concat(proveedor.razonsocial,\' \',proveedor.apellidopaterno,\' \',proveedor.apellidomaterno,\' \',proveedor.nombres) as proveedor2'),DB::raw('responsable.nombres as responsable2'),'m2.numero as nroorden','m3.numero as nrocompra','producto.nombre as producto','detallemovimiento.cantidad','detallemovimiento.preciocompra','m2.moneda as monedaorden')->orderBy('fecha', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro. Req.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Personal', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Obra', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Maquinaria', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Producto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro. Orden', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro. Compra', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Proveedor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cant.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'P. Unit.', 'numero' => '1');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver       = $this->tituloVer;
        $ruta             = $this->rutas;
        $totals = 0;$totald = 0;
        foreach($lista as $k=>$v){
            if($v->situacion!="A"){
                if($v->monedaorden!='D'){
                    if($v->tipodocumento_id!=20){
                        $totals = $totals + $v->cantidad*$v->preciocompra;
                    }else{
                        $totals = $totals - ($v->cantidad*$v->preciocompra);
                    }
                }else{
                    $totald = $totald + $v->cantidad*$v->preciocompra;
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
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',1)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }
        $cboSituacion = array('' => 'Todos', 'P' => 'Pendiente', 'C' => 'Pagado');
        $cboModo = array('' => 'Todos','>'=>'Mayor','<'=>'Menor');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoDocumento', 'cboSituacion','cboModo'));
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
                                ->whereNull('detallemovimiento.deleted_at')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fechavencimiento','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fechavencimiento','<=',$request->input('fechafin'));
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
        $resultado = $resultado->select('movimiento.*',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.producto')->orderBy('cliente','asc')->orderBy('fecha', 'ASC')->get();
        
        Excel::create('ExcelCuentasPorPagar', function($excel) use($resultado,$request) {
 
            $excel->sheet('Cuentas', function($sheet) use($resultado,$request) {
 
                $array = array();
                $cabecera = array();
                $cabecera[] = "Fecha";
                $cabecera[] = "Fecha Venc.";
                $cabecera[] = "Forma Pago";
                $cabecera[] = "Tipo Doc.";
                $cabecera[] = "Nro";
                $cabecera[] = "Proveedor";
                $cabecera[] = "Moneda";
                $cabecera[] = "Total";
                $cabecera[] = "Total Pagado";
                $cabecera[] = "Detalle Compra";
                $cabecera[] = "Comentario";
                $cabecera[] = "Detraccion";
                $cabecera[] = "Usuario";
                $sheet->row(1,$cabecera);
                $c=2;$d=3;$band=true;
                $subtotal=0;
                $igv=0;
                $total=0;
                $totalpagado=0;
                foreach ($resultado as $key => $value){
                    $detalle = array();
                    $detalle[] = date('d/m/Y',strtotime($value->fecha));
                    $detalle[] = date('d/m/Y',strtotime($value->fechavencimiento));
                    $detalle[] = $value->formapago=='A'?'Contado':'Credito';
                    $detalle[] = $value->tipodocumento->nombre;
                    $detalle[] = $value->numero;
                    $detalle[] = $value->cliente;
                    $detalle[] = $value->moneda=='S'?'Soles':'Dolares';
                    $detalle[] = number_format($value->total,2,'.','');
                    $detalle[] = number_format($value->totalpagado,2,'.','');
                    $total=$total+number_format($value->total,2,'.','');
                    $totalpagado=$totalpagado+number_format($value->totalpagado,2,'.','');
                    $detalle[] = $value->producto;
                    $detalle[] = $value->comentario;
                    $detalle[] = $value->incluye;
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
                $cabecera[] = number_format($totalpagado,2,'.','');
                $sheet->row($c,$cabecera);
            });
        })->export('xls');
    }
    
    public function pdf(Request $request){
        setlocale(LC_TIME, 'spanish');
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->leftjoin('maquinaria','maquinaria.id','=','movimiento.maquinaria_id')
                                ->leftjoin('obra','obra.id','=','movimiento.obra_id')
                                ->join('movimiento as m2','m2.movimiento_id','=','movimiento.id')
                                ->leftjoin('person as proveedor','proveedor.id','=','m2.persona_id')
                                ->leftjoin('movimiento as m3', function ($join) {
                                        $join->on('m3.movimiento_id','=','m2.id')->where('m3.tipomovimiento_id','=',1)->whereNull('m3.deleted_at');
                                    })
                                ->leftjoin('detallemovimiento',function ($join) {
                                        $join->on('detallemovimiento.movimiento_id','=',DB::raw('case when m2.id>0 then m2.id else movimiento.id end'))->whereNull('detallemovimiento.deleted_at');
                                })
                                ->leftjoin('producto','detallemovimiento.producto_id','=','producto.id')
                                ->where('movimiento.tipomovimiento_id','=',8)
                                ->whereNotIn('movimiento.situacion',['A'])
                                ->whereNotIn('m2.situacion',['A'])
                                ->where('movimiento.situacion','=','C');
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('movimiento.fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafin'));
        }
        if($request->input('producto')!=""){
            $resultado = $resultado->where('detallemovimiento.producto','like','%'.$request->input('producto').'%');
        }
        if($request->input('obra')!=""){
            $resultado = $resultado->where('obra.nombre','like','%'.$request->input('obra').'%');
        }
        if($request->input('maquinaria')!=""){
            $resultado = $resultado->where(function ($sql) use($request){
                                        $sql->where('maquinaria.nombre','like','%'.$request->input('maquinaria').'%')
                                            ->orWhere('maquinaria.marca','like','%'.$request->input('maquinaria').'%')
                                            ->orWhere('maquinaria.modelo','like','%'.$request->input('maquinaria').'%');
                                    });
        }
        if($request->input('placa')!=""){
            $resultado = $resultado->where('maquinaria.placa','like','%'.$request->input('placa').'%');
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(proveedor.ruc,\' \',proveedor.razonsocial,\' \',proveedor.apellidopaterno,\' \',proveedor.apellidomaterno,\' \',proveedor.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
        }
        $resultado    = $resultado->select('movimiento.*','m2.id as id2',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('concat(proveedor.razonsocial,\' \',proveedor.apellidopaterno,\' \',proveedor.apellidomaterno,\' \',proveedor.nombres) as proveedor2'),DB::raw('responsable.nombres as responsable2'),'m2.numero as nroorden','m3.numero as nrocompra','producto.nombre as producto','detallemovimiento.cantidad','detallemovimiento.unidad','detallemovimiento.preciocompra')->orderBy('fecha', 'ASC')->get();
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
            // Set font
            $pdf2->Image("http://asfalpaca.com.pe/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 20);
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
    
        $pdf::SetTitle('Reporte Requerimiento');
        $c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;$vencido=0;$obra='0';$d=1;
        foreach ($resultado as $key => $value) {
            if($obra!=$value->maquinaria_id && $value->maquinaria_id>0){
                $c=0;
                $pdf::AddPage('P');
                $pdf::SetY(30);
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',14);
                $pdf::Cell(0,10,$value->maquinaria->nombre.' / '.$value->maquinaria->placa.' / '.$value->maquinaria->marca.' / '.$value->maquinaria->modelo,0,0,'C');
                $pdf::Ln(); 
                
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(5,8,"#",1,0,'C');
                $pdf::Cell(17,8,"FECHA",1,0,'C');
                $pdf::Cell(30,8,"PERSONAL",1,0,'C');
                $pdf::Cell(40,8,"DESCRIPCION",1,0,'C');
                $pdf::Cell(15,8,"CANT.",1,0,'C');
                $pdf::Cell(15,8,"UNI.",1,0,'C');
                $pdf::Cell(18,8,"P. UNIT.",1,0,'C');
                $pdf::Cell(18,8,"P. TOTAL",1,0,'C');
                $pdf::Cell(35,8,"PROVEEDOR",1,0,'C');
                $pdf::Ln();
                $obra=$value->maquinaria_id;
            }elseif(($value->maquinaria_id+0)==0){
                $pdf::AddPage('P');
                $pdf::SetY(30);
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(5,8,"#",1,0,'C');
                $pdf::Cell(17,8,"FECHA",1,0,'C');
                $pdf::Cell(30,8,"PERSONAL",1,0,'C');
                $pdf::Cell(40,8,"DESCRIPCION",1,0,'C');
                $pdf::Cell(15,8,"CANT.",1,0,'C');
                $pdf::Cell(15,8,"UNI.",1,0,'C');
                $pdf::Cell(18,8,"P. UNIT.",1,0,'C');
                $pdf::Cell(18,8,"P. TOTAL",1,0,'C');
                $pdf::Cell(35,8,"PROVEEDOR",1,0,'C');
                $pdf::Ln();
                $obra=$value->maquinaria_id;
            }
            $c=$c+1;
            $pdf::SetFont('helvetica','',8.5);
            $alto=$pdf::getNumLines($value->proveedor2, 35)*4;
            $alto1=$pdf::getNumLines($value->producto, 40)*4;
            $alto2=$pdf::getNumLines($value->cliente, 30)*4;
            if($alto1>$alto) $alto=$alto1;
            if($alto2>$alto) $alto=$alto2;
            $pdf::SetFont('helvetica','',8.5);
            $pdf::Cell(5,$alto,$c,1,0,'C');
            $pdf::Cell(17,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(30,3.5,$value->cliente,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(30,$alto,'',1,0,'L');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(40,3.5,$value->producto,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(40,$alto,'',1,0,'L');
            $pdf::Cell(15,$alto,$value->cantidad,1,0,'C');
            $pdf::Cell(15,$alto,$value->unidad,1,0,'C');
            $pdf::Cell(18,$alto,number_format($value->preciocompra,2,'.',','),1,0,'C');
            $pdf::Cell(18,$alto,number_format($value->preciocompra*$value->cantidad,2,'.',','),1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(35,3.5,$value->proveedor2,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(35,$alto,'',1,0,'L');
            $pdf::Ln();
            $total = $total + number_format($value->preciocompra*$value->cantidad,2,'.','');
        }
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::SetTextColor(0,0,0);
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::Cell(140,8,"TOTAL S/",0,0,'R');
        $pdf::Cell(18,8,number_format($total,2,'.',','),1,0,'C');
        $pdf::Output('Cuentasporpagar.pdf');
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
    
    public function pdfCorte(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fechavencimiento','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fechavencimiento','<=',$request->input('fechafin'));
        }
        if($request->input('proveedor')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.ruc,\' \',person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
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
        $resultado = $resultado->select('movimiento.*',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.producto')->orderBy('cliente','asc')->orderBy('fecha', 'ASC')->get();
        
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
                // Set font
                $pdf2->SetFont('helvetica', 'B', 10);
                $pdf2->SetXY(100,3);
                $pdf2->Cell(0, 8, date("d/m/Y H:i"), 0, false, 'R');
        
        });
        $pdf->fechainicio =$request->input('fechainicio');
        $pdf->fechafin =$request->input('fechafin');

        $pdf::SetTitle('Cuentas por Pagar');
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',10);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,"CUENTAS POR PAGAR",0,0,'C');
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
                }
                $c=$c+1;
                $pdf::SetFont('helvetica','B',14);
                $pdf::Cell(279,7,$c.". ".$value->cliente,1,0,'L');    
                $pdf::Ln();
                $pdf::SetFont('helvetica','B',9);
                $pdf::Cell(18,8,"Fecha",1,0,'C');
                $pdf::Cell(19,8,"Fecha Venc.",1,0,'C');
                $pdf::Cell(17,8,"Forma P.",1,0,'C');
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
            $pdf::Cell(18,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            if(strtotime($value->fechavencimiento)< strtotime('now')){
                $pdf::SetTextColor(255,0,0);
                $vencido = $vencido + $value->total - $value->totalpagado;
            }   
            $pdf::Cell(19,$alto,date("d/m/Y",strtotime($value->fechavencimiento)),1,0,'C');
            $pdf::SetTextColor(0,0,0);
            $pdf::Cell(17,$alto,$value->formapago=='A'?'Contado':'Credito',1,0,'L');
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

    public function pdfCorte2(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fechavencimiento','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fechavencimiento','<=',$request->input('fechafin'));
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
        $pdf::Cell(0,10,"FACTURAS POR PAGAR VENCIDAS",0,0,'C');
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
    
    public function pdfPago(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->where('tipomovimiento_id','=',1)
                                ->whereNull('detallemovimiento.deleted_at')
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
            $resultado = $resultado->where(DB::raw('concat(person.ruc,\' \',person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('proveedor')).'%');
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
        $resultado = $resultado->select('movimiento.*',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.producto')->orderBy('cliente','asc')->orderBy('fecha', 'ASC')->get();
        
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
        $pdf::SetFont('helvetica','B',10);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,"CUENTAS POR PAGAR",0,0,'C');
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
                $pdf::Cell(23,8,"Tipo Doc.",1,0,'C');
                $pdf::Cell(20,8,"Nro",1,0,'C');
                $pdf::Cell(40,8,"Maquinaria",1,0,'C');
                $pdf::Cell(70,8,"Detalle",1,0,'C');
                //$pdf::Cell(15,8,"Moneda",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(20,8,"Pago a Cta.",1,0,'C');
                $pdf::Cell(40,8,"Forma de Pago",1,0,'C');
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
                $pdf::Cell(23,8,"Tipo Doc.",1,0,'C');
                $pdf::Cell(20,8,"Nro",1,0,'C');
                $pdf::Cell(40,8,"Maquinaria",1,0,'C');
                $pdf::Cell(70,8,"Detalle",1,0,'C');
                //$pdf::Cell(15,8,"Moneda",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(20,8,"Pago a Cta.",1,0,'C');
                $pdf::Cell(40,8,"Forma de Pago",1,0,'C');
                $pdf::Cell(22,8,"Saldo",1,0,'C');
                $pdf::Ln();
            }
            $pdf::SetFont('helvetica','',8.5);
            $pdf::Cell(5,$alto,$z,1,0,'C');
            $pdf::Cell(17,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $pdf::Cell(23,$alto,substr($value->tipodocumento->nombre,0,7),1,0,'L');
            $pdf::Cell(20,$alto,$value->numero,1,0,'L');
            $pdf::SetTextColor(0,0,0);
            //$pdf::Cell(15,$alto,$value->formapago=='A'?'Contado':'Credito',1,0,'L');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(40,3.5,$value->comentario,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(40,$alto,'',1,0,'L');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(70,3.5,$value->producto,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(70,$alto,'',1,0,'L');
            //$pdf::Cell(15,$alto,$value->moneda=='S'?'Soles':'Dolares',1,0,'C');
            $pdf::Cell(20,$alto,number_format($value->total,2,'.',','),1,0,'C');
            $pdf::Cell(20,$alto,number_format($value->totalpagado,2,'.',','),1,0,'C');
            $listapago = "";
            if($value->listapago!=""){
                $lp = explode("|",$value->listapago);
                for($z=0;$z<count($lp);$z++){
                    $ld = explode("@",$lp[$z]);
                    $listapago.=date("d/m/Y",strtotime($ld[0]))." - ".$ld[1]." ";
                }
            }
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(40,$alto,$listapago,0,'C');
            $pdf::SetXY($x,$y);
            $pdf::Cell(40,$alto,'',1,0,'L');
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