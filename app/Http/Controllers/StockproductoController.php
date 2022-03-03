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
use App\Almacen;
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

class StockproductoController extends Controller
{
    protected $folderview      = 'app.stockproducto';
    protected $tituloAdmin     = 'Reporte Stock';
    protected $tituloRegistrar = 'Registrar producto';
    protected $tituloModificar = 'Modificar compra';
    protected $tituloEliminar  = 'Eliminar compra';
    protected $tituloVer       = 'Ver Compra';
    protected $rutas           = array('create' => 'stockproducto.create', 
            'show'   => 'stockproducto.show', 
            'search' => 'stockproducto.buscar',
            'index'  => 'stockproducto.index',
            'kardex' => 'stockproducto.kardex',
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
        $entidad          = 'Stock';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Stockproducto::join('almacen','almacen.id','=','stockproducto.almacen_id')
                                ->join('producto','producto.id','=','stockproducto.producto_id');
        if($request->input('almacen_id')!=""){
            $resultado = $resultado->where('almacen_id','=',$request->input('almacen_id'));
        }
        if($request->input('producto')!=""){
            $resultado = $resultado->where('producto.nombre','like','%'.strtoupper($request->input('producto')).'%');
        }
        $lista            = $resultado->orderBy('producto.nombre', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Almacen', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Producto', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Cantidad', 'numero' => '1');
        
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
        $entidad          = 'Stock';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipoDocumento = array('' => 'Todos');
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',2)->orderBy('nombre','asc')->get();
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
        $cboSituacion = array('' => 'Todos', 'P' => 'Pendiente', 'C' => 'Pagado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipoDocumento', 'cboSituacion','cboAlmacen','cboDestino'));
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

    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Stock';
        $stock = null;
        $formData = array('stockproducto.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('stock', 'formData', 'entidad', 'boton', 'listar'));
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
        $reglas     = array('nombre' => 'required|max:100');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre',
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $error = DB::transaction(function() use($request){
            $stock = new Stockproducto();
            $stock->producto = $request->input('nombre');
            $stock->cantidad = 0;
            $stock->almacen_id = 1;
            $stock->save();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function kardex(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Stock';
        $stock = null;
        $formData = array('stockproducto.store');
        $cboAlmacen = array();
        $almacen = Almacen::get();
        foreach($almacen as $k=>$v){
            $cboAlmacen = $cboAlmacen + array($v->id => $v->nombre);
        }
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Reporte'; 
        return view($this->folderview.'.kardex')->with(compact('stock', 'formData', 'entidad', 'boton', 'listar','cboAlmacen'));
    }

    public function productoautocompletar($searching)
    {
        $resultado        = Stockproducto::join('producto','producto.id','=','stockproducto.producto_id')
                            ->where('producto.nombre','like','%'.$searching.'%')
                            ->orderBy('producto.nombre', 'ASC');
        $list      = $resultado->select('stockproducto.*',DB::Raw('producto.nombre as producto2'))->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => trim($value->producto2),
                            'id'    => $value->id,
                            'value' => trim($value->producto2),
                        );
        }
        return json_encode($data);
    }

    public function reporteKardex(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('detallemovimiento','detallemovimiento.movimiento_id','=','movimiento.id')
                                ->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                                ->where('tipomovimiento_id','=',3)
                                ->whereNull('detallemovimiento.deleted_at')
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('producto')!=""){
            $resultado = $resultado->where(DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end'),'like','%'.$request->input('producto').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('case when detallemovimiento.producto_id>0 then producto.nombre else detallemovimiento.producto end as producto'),DB::raw('responsable.nombres as responsable2'),'detallemovimiento.cantidad','detallemovimiento.precioventa')->orderBy('fecha', 'ASC')->get();
        
        Excel::create('ExcelKardex', function($excel) use($lista,$request) {
 
            $excel->sheet('Kardex', function($sheet) use($lista,$request) {

                $cabecera = array();
                $cabecera[] = "PRODUCTO:";
                $cabecera[] = $request->input('producto');
                $sheet->row(1,$cabecera);
 
                $cabecera = array();
                $cabecera[] = "FECHA";
                $cabecera[] = "TIPO DOC";
                $cabecera[] = "NUMERO";
                $cabecera[] = "OPERADOR / CONDUCTOR";
                $cabecera[] = "MAQUINA / VEHICULO";
                $cabecera[] = "ENTRADA";
                $cabecera[] = "SALIDA";
                $cabecera[] = "SALDO";
                $sheet->row(2,$cabecera);

                $datos = Detallemovimiento::join('movimiento','movimiento.id','=','detallemovimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',3)
                            ->where('producto','like',$request->input('producto'))
                            ->whereNull('movimiento.deleted_at')
                            ->whereNotIn('situacion',['A'])
                            ->where('movimiento.id','>=',8947)
                            ->where('movimiento.fecha','<',$request->input('fechainicio'))
                            ->select(DB::raw('sum(case when movimiento.tipodocumento_id=9 then detallemovimiento.cantidad*(-1) else detallemovimiento.cantidad end) as stock'))
                            ->groupBy('detallemovimiento.producto')
                            ->get();
                if(count($datos)==0){
                    $saldo = 0;
                }else{
                    $saldo = $datos->stock;
                }
                
                $cabecera = array();
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "";
                $cabecera[] = "SALDO INICIAL";
                $cabecera[] = number_format($saldo,2,'.','');

                $sheet->row(3,$cabecera);
                
                $c=4;$d=3;$band=true;
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
                    $detalle[] = ($value->maquinaria_id>0 && !is_null($value->maquinaria))?($value->maquinaria->nombre.' / '.$value->maquinaria->marca.' / '.$value->maquinaria->modelo):'-';
                    if($value->tipodocumento_id=="9"){//SALIDA
                        $detalle[] = "0";
                        $detalle[] = number_format($value->cantidad,2,'.','');
                        $saldo = $saldo - $value->cantidad;
                    }else{
                        $detalle[] = number_format($value->cantidad,2,'.','');
                        $detalle[] = "0";
                        $saldo = $saldo + $value->cantidad;
                    }
                    $detalle[] = number_format($saldo,2,'.','');
                    $sheet->row($c,$detalle);
                    $c=$c+1;
                }
            });
        })->export('xls');
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