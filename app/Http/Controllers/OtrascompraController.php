<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Tipodocumento;
use App\Tipomovimiento;
use App\Movimiento;
use App\Concepto;
use App\Banco;
use App\Producto;
use App\Tipocambio;
use App\Detallemovimiento;
use App\Stockproducto;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;

class OtrascompraController extends Controller
{
    protected $folderview      = 'app.otrascompra';
    protected $tituloAdmin     = 'Leasing Compra';
    protected $tituloRegistrar = 'Registrar Leasing compra';
    protected $tituloModificar = 'Modificar Leasing compra';
    protected $tituloEliminar  = 'Eliminar Leasing compra';
    protected $tituloVer       = 'Ver Compra';
    protected $tituloCobrar = 'Pagar Leasing';
    protected $rutas           = array('create' => 'otrascompra.create', 
            'edit'   => 'otrascompra.edit',
            'show'   => 'otrascompra.show', 
            'delete' => 'otrascompra.eliminar',
            'search' => 'otrascompra.buscar',
            'index'  => 'otrascompra.index',
            'cobrar' => 'otrascompra.cobrar',
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
        $entidad          = 'Otrascompra';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->join('banco','banco.id','=','movimiento.banco_id')
                                ->where('tipomovimiento_id','=',9)
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('banco')!=""){
            $resultado = $resultado->where('banco.nombre','like','%'.trim($request->input('banco')).'%');
        }
        if($request->input('situacion')!=""){
            $resultado = $resultado->where('movimiento.situacion','like',trim($request->input('situacion')));
        }
        if($request->input('comentario')!=""){
            $resultado = $resultado->where('movimiento.comentario','like','%'.trim($request->input('comentario')).'%');
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('persona')).'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('movimiento.comentario', 'ASC')->orderBy('fecha', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Banco', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Moneda', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Total', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Comentario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha Pago', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Situacion', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Usuario', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '4');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
        $titulo_ver       = $this->tituloVer;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'titulo_cobrar', 'ruta', 'titulo_ver'));
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
        $entidad          = 'Otrascompra';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboBanco = array('' => 'Todos');
        $banco = Banco::orderBy('nombre','asc')->get();
        foreach($banco as $k=>$v){
            $cboBanco = $cboBanco + array($v->id => $v->nombre);
        }
        $cboSituacion = array('' => 'Todos', 'P' => 'Pendiente', 'C' => 'Cobrado');
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboBanco', 'cboSituacion'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Otrascompra';
        $movimiento = null;
        $cboBanco = array();
        $banco = Banco::orderBy('nombre','asc')->get();
        foreach($banco as $k=>$v){
            $cboBanco = $cboBanco + array($v->id => $v->nombre);
        }   
        $detalle = null;     
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $formData = array('otrascompra.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'detalle', 'cboBanco', 'cboMoneda'));
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
        $error = DB::transaction(function() use($request,$user,&$dat){
            $arr=explode(",",$request->input('listProducto'));
            if($request->input('listProducto')!=""){
                for($c=0;$c<count($arr);$c++){
                    $movimiento       = new Movimiento();
                    $movimiento->fecha = $request->input('txtFecha'.$arr[$c]);
                    $person_id = $request->input('persona_id');
                    $movimiento->persona_id = $person_id;
                    $movimiento->numero= $request->input('txtCantidad'.$arr[$c]);
                    $movimiento->responsable_id=$user->person_id;
                    $movimiento->subtotal=str_replace(",","",$request->input('totalpagado')); ;
                    $movimiento->igv=0;
                    $movimiento->total=str_replace(",","",$request->input('txtTotal'.$arr[$c])); 
                    $movimiento->moneda=$request->input('moneda');
                    $movimiento->tipomovimiento_id=9;
                    $movimiento->tipodocumento_id=19;
                    $movimiento->comentario=$request->input('comentario');
                    $movimiento->situacion='P';
                    $movimiento->banco_id=$request->input('banco_id');
                    $movimiento->movimiento_id=$request->input('movimiento_id');
                    $movimiento->save();
                }
            }
            $dat[0]=array("respuesta"=>"OK","venta_id"=>$movimiento->id);
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
        $entidad             = 'Otrascompra';
        $cboTipoDocumento        = Tipodocumento::lists('nombre', 'id')->all();
        $formData            = array('venta.update', $id);
        $formData            = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Modificar';
        $cboBanco = array();
        $rs = Banco::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboBanco = $cboBanco + array($value->id => $value->nombre);
        }
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $persona = $venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres.' '.$venta->persona->razonsocial;
        //$numerocuotas = count($cuentas);
        return view($this->folderview.'.mantView')->with(compact('venta', 'formData', 'entidad', 'boton', 'listar','cboTipoDocumento','persona','cboBanco','cboMoneda'));
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
        $tipodocumento = Tipodocumento::where('tipomovimiento_id','=',9)->orderBy('nombre','asc')->get();
        foreach($tipodocumento as $k=>$v){
            $cboTipoDocumento = $cboTipoDocumento + array($v->id => $v->nombre);
        }        
        $cboBanco = array();
        $rs = Banco::orderBy('nombre','ASC')->get();
        foreach ($rs as $key => $value) {
            $cboBanco = $cboBanco + array($value->id => $value->nombre);
        }
        $cboMoneda = array('S'=>'Soles','D'=>'Dolares');
        $entidad  = 'Otrascompra';
        $formData = array('otrascompra.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant2')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'cboBanco', 'cboTipoDocumento', 'cboMoneda'));
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
        
        $error = DB::transaction(function() use($request, $id, $user, &$dat){
            $movimiento = Movimiento::find($id);
            $movimiento->fecha = $request->input('fecha');
            $person_id = $request->input('person_id');
            $movimiento->persona_id = $person_id;
            $movimiento->numero= $request->input('cantidad');
            $movimiento->responsable_id=$user->person_id;
            $movimiento->igv=0;
            $movimiento->total=str_replace(",","",$request->input('total')); 
            $movimiento->moneda=$request->input('moneda');
            $movimiento->tipomovimiento_id=9;
            $movimiento->tipodocumento_id=19;
            $movimiento->comentario=$request->input('comentario');
            $movimiento->situacion='P';
            $movimiento->banco_id=$request->input('banco');
            $movimiento->save();
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
        $mensaje = 'Desea eliminar el movimiento '.$modelo->numero.' ? <br><br>';
        $entidad  = 'Otrascompra';
        $formData = array('route' => array('otrascompra.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar', 'mensaje'));
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

    public function cobrar($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'movimiento');
        if ($existe !== true) {
            return $existe;
        }
        $listar              = Libreria::getParam($request->input('listar'), 'SI');
        $letra = Movimiento::find($id);
        $entidad             = 'Letra';
        $formData            = array('otrascompra.pagar', $id);
        $formData            = array('route' => $formData, 'method' => 'PAGAR', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton               = 'Pagar';
        return view($this->folderview.'.cobrar')->with(compact('letra', 'formData', 'entidad', 'boton', 'listar'));
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
        });
        return is_null($error) ? "OK" : $error;
    }
    
    public function pdf(Request $request){
        setlocale(LC_TIME, 'spanish');
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('movimiento.tipomovimiento_id','=',9)
                                ->whereNotIn('movimiento.situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where(function($sql) use($request){
                            $sql->where('movimiento.fecha','>=',$request->input('fechainicio'));
                                //->orWhere('movimiento.fecha','<=',$request->input('fechainicio'));
                            });
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('movimiento.fecha','<=',$request->input('fechafin'));
        }
        if($request->input('comentario')!=""){
            $resultado = $resultado->where('movimiento.comentario','like','%'.trim($request->input('comentario')).'%');
        }
        if($request->input('persona')!=""){
            $resultado = $resultado->where(DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres)'),'like','%'.trim($request->input('persona')).'%');
        }
        if(trim($request->input('situacion'))!=''){
            $resultado = $resultado->where('movimiento.situacion','like',$request->input('situacion'));
        }
        $resultado = $resultado->select('movimiento.*',DB::raw('case when person.razonsocial is null or person.razonsocial like "" then concat(person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) else person.razonsocial end as cliente'),DB::raw('responsable.nombres as responsable2'))
                ->orderBy('cliente','asc')
                ->orderBy('movimiento.id','asc')
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

        $pdf::SetTitle('Leasing');
        $pdf::AddPage('L');
        $pdf::SetFont('helvetica','B',14);
        //$pdf::Image(public_path()."/dist/img/logo.jpg", 10, 7, 190, 30);//AL ".date("d/m/Y",strtotime($request->input('fechafin')))
        $pdf::Cell(0,10,'LEASING POR PAGAR DEL '.date("d/m/Y",strtotime($request->input('fechainicio')))." AL ".date("d/m/Y",strtotime($request->input('fechafin'))),0,0,'C');
        $pdf::Ln(); 
        $c=0;$total=0;$totalpagado=0;$proveedor="";$totalp=0;$totalpagadop=0;$vencido=0;
        foreach ($resultado as $key => $value) {
            if($proveedor!=$value->cliente){
                if($proveedor!=""){
                    $pdf::SetFont('helvetica','B',8.5);
                    $pdf::SetTextColor(255,0,0);
                    $pdf::Cell(37,5,"LEASING VENCIDO S/",0,0,'L');
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
                $pdf::Cell(12,8,"Nro.",1,0,'C');
                $pdf::Cell(19,8,"Fecha Venc.",1,0,'C');
                $pdf::Cell(50,8,"Comentario",1,0,'C');
                $pdf::Cell(15,8,"Moneda",1,0,'C');
                $pdf::Cell(20,8,"Importe",1,0,'C');
                $pdf::Cell(22,8,"Saldo",1,0,'C');
                $pdf::Ln();
                $proveedor=$value->cliente;
            }
            /*if($value->moneda=="D"){
                $tipocambio = Tipocambio::where('fecha','=',$value->fecha)->first();
                if(!is_null($tipocambio)){
                    $value->total = $value->total*$tipocambio->monto;
                    $value->totalpagado = $value->totalpagado*$tipocambio->monto;
                }else{
                    $value->total = 0;
                    $value->totalpagado = 0;
                }
            }*/
            $pdf::SetFont('helvetica','',8.5);
            $alto=$pdf::getNumLines($value->comentario, 50)*4;
            $alto1=$pdf::getNumLines($value->producto, 70)*4;
            if($alto1>$alto) $alto=$alto1;
            $z=count(Movimiento::where('movimiento_id','=',$value->movimiento_id)->get());
            
            $pdf::Cell(12,$alto,$value->numero."/$z",1,0,'C');
            if(strtotime($value->fecha)< strtotime('now')){
                $pdf::SetTextColor(255,0,0);
                $vencido = $vencido + $value->total;
            }   
            $pdf::Cell(19,$alto,date("d/m/Y",strtotime($value->fecha)),1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(50,3.5,$value->comentario,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(50,$alto,'',1,0,'L');
            $pdf::Cell(15,$alto,$value->moneda=='S'?'Soles':'Dolares',1,0,'C');
            $pdf::Cell(20,$alto,number_format($value->total,2,'.',','),1,0,'C');
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
        $pdf::Cell(37,5,"LEASING VENCIDO S/",0,0,'L');
        $pdf::SetFont('helvetica','B',12);
        $pdf::Cell(17,5,number_format($vencido,2,'.',','),'TR',0,'R');
        $pdf::SetTextColor(0,0,0);
        $pdf::SetFont('helvetica','B',8.5);
        $pdf::Cell(163,5,"TOTAL ",0,0,'R');
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