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
use App\Maquinaria;
use App\Obra;
use App\Detallemovimiento;
use App\Stockproducto;
use App\Person;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;

class RequerimientoController extends Controller
{
    protected $folderview      = 'app.requerimiento';
    protected $tituloAdmin     = 'Requerimiento';
    protected $tituloRegistrar = 'Registrar requerimiento';
    protected $tituloModificar = 'Modificar requerimiento';
    protected $tituloEliminar  = 'Eliminar requerimiento';
    protected $tituloVer       = 'Ver Requerimiento';
    protected $rutas           = array('create' => 'requerimiento.create', 
            'edit'   => 'requerimiento.edit',
            'show'   => 'requerimiento.show', 
            'delete' => 'requerimiento.eliminar',
            'search' => 'requerimiento.buscar',
            'index'  => 'requerimiento.index',
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
        $entidad          = 'Requerimiento';
        $nombre             = Libreria::getParam($request->input('cliente'));
        $resultado        = Movimiento::join('person','person.id','=','movimiento.persona_id')
                                ->join('person as responsable','responsable.id','=','movimiento.responsable_id')
                                ->where('tipomovimiento_id','=',8)
                                ->whereNotIn('situacion',['A']);
        if($request->input('fechainicio')!=""){
            $resultado = $resultado->where('fecha','>=',$request->input('fechainicio'));
        }
        if($request->input('fechafin')!=""){
            $resultado = $resultado->where('fecha','<=',$request->input('fechafin'));
        }
        if($request->input('numero')!=""){
            $resultado = $resultado->where('movimiento.numero','like','%'.$request->input('numero').'%');
        }
        $lista            = $resultado->select('movimiento.*',DB::raw('concat(person.razonsocial,\' \',person.apellidopaterno,\' \',person.apellidomaterno,\' \',person.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'))->orderBy('fecha', 'ASC')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Fecha', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nro', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Persona', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Obra', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Maquinaria', 'numero' => '1');
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
        $entidad          = 'Requerimiento';
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
        $entidad  = 'Requerimiento';
        $movimiento = null;
        $numero = Movimiento::NumeroSigue2(8,14);
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
        $formData = array('requerimiento.store');
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'numero', 'cboMaquinaria', 'cboObra'));
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
            $Venta->subtotal = 0;
            $Venta->igv = 0;
            $Venta->total = 0;
            $Venta->tipomovimiento_id=8;
            $Venta->tipodocumento_id=14;
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='P';
            $Venta->comentario = $request->input('comentario');
            if($request->input('obra_id')!="") $Venta->obra_id = $request->input('obra_id');
            if($request->input('maquinaria_id')!="") $Venta->maquinaria_id = $request->input('maquinaria_id');
            $Venta->responsable_id=$user->person_id;
            $Venta->save();
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                $Detalle = new Detallemovimiento();
                $Detalle->movimiento_id=$Venta->id;
                $Detalle->producto_id=$request->input('txtIdProducto'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=0;
                $Detalle->preciocompra=0;
                //$Detalle->producto = $request->input('txtProducto'.$arr[$c]);
                $Detalle->save();
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
        $persona = $venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres;
        $detalles = Detallemovimiento::where('movimiento_id','=',$venta->id)->leftjoin('producto','producto.id','=','detallemovimiento.producto_id')->select('detallemovimiento.*','producto.nombre as producto2')->get();
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
        $numero = $movimiento->numero;
        $entidad  = 'Requerimiento';
        $formData = array('requerimiento.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('movimiento', 'formData', 'entidad', 'boton', 'listar', 'numero', 'cboObra', 'cboMaquinaria'));
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
            $Venta->subtotal = 0;
            $Venta->igv = 0;
            $Venta->total = 0;
            $Venta->tipomovimiento_id=8;
            $Venta->tipodocumento_id=14;
            $Venta->persona_id = $request->input('persona_id')=="0"?1:$request->input('persona_id');
            $Venta->situacion='P';
            $Venta->comentario = $request->input('comentario');
            if($request->input('obra_id')!="") $Venta->obra_id = $request->input('obra_id');
            if($request->input('maquinaria_id')!="") $Venta->maquinaria_id = $request->input('maquinaria_id');
            $Venta->responsable_id=$user->person_id;
            $Venta->save();
            $arr=explode(",",$request->input('listProducto'));
            for($c=0;$c<count($arr);$c++){
                if(!is_null($request->input('txtIdDetalle'.$arr[$c]))){
                    $Detalle = Detallemovimiento::find($request->input('txtIdDetalle'.$arr[$c]));   
                }else{
                    $Detalle = new Detallemovimiento();
                }
                $Detalle->movimiento_id=$Venta->id;
                $Detalle->producto_id=$request->input('txtIdProducto'.$arr[$c]);
                $Detalle->cantidad=$request->input('txtCantidad'.$arr[$c]);
                $Detalle->precioventa=0;
                $Detalle->preciocompra=0;
                //$Detalle->producto = $request->input('txtProducto'.$arr[$c]);
                $Detalle->save();
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
        $entidad  = 'Requerimiento';
        $formData = array('route' => array('requerimiento.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarAnular')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function buscarproducto(Request $request)
    {
        $descripcion = $request->input("descripcion");
        $resultado = Producto::leftjoin('stockproducto','stockproducto.producto_id','=','producto.id')->where('nombre','like','%'.strtoupper($descripcion).'%')->select('producto.*','stockproducto.cantidad')->where('tipo','like','L')->get();
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
        $resultado = Producto::leftjoin('stockproducto','stockproducto.producto_id','=','producto.id')->where(DB::raw('trim(codigobarra)'),'like',trim($codigobarra))->select('producto.*','stockproducto.cantidad')->where('tipo','like','L')->get();
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
    
    public function agregardetalle(Request $request){
        $list = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                    ->where('movimiento_id','=',$request->input('id'))
                    ->select('detallemovimiento.*','detallemovimiento.producto as producto2','producto.nombre as producto3')
                    ->get();
        $c=0;$data=array();            
        foreach ($list as $key => $value) {
            $data[$c] = array(
                        'producto' => ($value->producto_id>0?$value->producto3:$value->producto2),
                        'cantidad' => $value->cantidad,
                        'idproducto' => $value->producto_id,
                        'id' => $value->id,
                );
            $c++;
        }
        return json_encode($data);
    }
    
    public function personautocompletar($searching)
    {
        $resultado        = Person::join('rolpersona','rolpersona.person_id','=','person.id')->where('rolpersona.rol_id','=',1)
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

    public function pdf2(Request $request){
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
            $pdf2->Image("http://asfalpaca.com.pe//asfalpaca/dist/img/logo.jpg", 10, 7, 190, 15);
        });
        /*$pdf::setFooterCallback(function($pdf2) {
            $pdf2->Image("http://martinampuero.com/asfalpaca/dist/img/pie.png", 10, 267, 190, 23);
        });*/
        $cotizacion = Movimiento::find($request->input('id'));
        $pdf::SetTitle('Requerimiento '.$cotizacion->numero);
        $pdf::SetMargins(10, 25, 10);
        $pdf::SetFooterMargin(50);
        $pdf::SetAutoPageBreak(true, 30);
        $pdf::AddPage();
        $pdf::SetFont('helvetica','B',12);
        /*$pdf::Ln();
        $pdf::Cell(0,30,utf8_decode(''),0,0,'R');
        $pdf::Ln();*/
        //$pdf::Image("http://martinampuero.com/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
        $pdf::Cell(0,5,utf8_decode("REQUERIMIENTO NRO ".$cotizacion->numero),0,0,'C');
        $pdf::Ln(); 
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(15,5,utf8_decode("Fecha:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,utf8_decode(date("d/m/Y",strtotime($cotizacion->fecha))),0,0,'L');
        $pdf::Ln();
        
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(20,5,("Solicitante:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,trim(strtoupper($cotizacion->persona->apellidopaterno." ".$cotizacion->persona->apellidomaterno." ".$cotizacion->persona->nombres)),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(15,5,("Obra:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,trim(strtoupper($cotizacion->obra_id>0?$cotizacion->obra->nombre:'-')),0,0,'L');
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',10);
        $pdf::Cell(20,5,("Maquinaria:"),0,0,'L');
        $pdf::SetFont('helvetica','',10);
        $pdf::Cell(0,5,trim(strtoupper(!is_null($cotizacion->maquinaria)?($cotizacion->maquinaria->nombre.' / '.$cotizacion->maquinaria->marca.' / '.$cotizacion->maquinaria->modelo):'-')),0,0,'L');
        $pdf::Ln();
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(10,5,"Item",1,0,'C');
        $pdf::Cell(120,5,"Descripcion",1,0,'C');
        $pdf::Cell(17,5,"Cant.",1,0,'C');
        $pdf::Ln();$c=0;
        $list = Detallemovimiento::leftjoin('producto','producto.id','=','detallemovimiento.producto_id')
                    ->where('movimiento_id','=',$cotizacion->id)
                    ->select('detallemovimiento.*','producto.nombre as producto2')
                    ->get();
        foreach ($list as $key => $value) {$c=$c+1;
            if($value->producto_id>0){
                $producto=$value->producto2;
            }else{
                $producto=$value->producto;
            }
            $alto=$pdf::getNumLines($producto, 120)*4;
            $pdf::SetFont('helvetica','',9);
            $pdf::Cell(10,$alto,$c,1,0,'C');
            $x=$pdf::GetX();
            $y=$pdf::GetY();
            $pdf::Multicell(120,4,$producto,0,'L');
            $pdf::SetXY($x,$y);
            $pdf::Cell(120,$alto,"",1,0,'L');
            $pdf::Cell(17,$alto,number_format($value->cantidad,2,'.',','),1,0,'C');
            //$pdf::Cell(15,$alto,$value->unidad2,1,0,'C');
            //$pdf::Cell(23,$alto,number_format($value->precioventa*$value->cantidad,2,'.',','),1,0,'R');
            $pdf::Ln();
        }
        $pdf::Ln();
        $pdf::SetFont('helvetica','B',9);
        $pdf::Cell(15,5,"Observ.",0,0,'L');
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
        
        $pdf::Output('Requerimiento.pdf');
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