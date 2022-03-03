<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Tipomaquinaria;
use App\Maquinaria;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Storage;

class MaquinariaController extends Controller
{
    protected $folderview      = 'app.maquinaria';
    protected $tituloAdmin     = 'Maquinaria';
    protected $tituloRegistrar = 'Registrar maquinaria';
    protected $tituloModificar = 'Modificar maquinaria';
    protected $tituloEliminar  = 'Eliminar maquinaria';
    protected $rutas           = array('create' => 'maquinaria.create', 
            'edit'   => 'maquinaria.edit', 
            'delete' => 'maquinaria.eliminar',
            'search' => 'maquinaria.buscar',
            'index'  => 'maquinaria.index',
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
        $entidad          = 'Maquinaria';
        $nombre             = Libreria::getParam($request->input('nombre'));
        $placa             = Libreria::getParam($request->input('placa'));
        $resultado        = Maquinaria::join('tipomaquinaria','tipomaquinaria.id','=','maquinaria.tipomaquinaria_id')
                            ->where('maquinaria.nombre', 'LIKE', '%'.strtoupper($nombre).'%')
                            ->where('maquinaria.placa', 'LIKE', '%'.strtoupper($placa).'%')->orderBy('maquinaria.nombre', 'ASC');
        if($request->input('tipomaquinaria')!=""){
            $resultado = $resultado->where('tipomaquinaria_id','=',$request->input('tipomaquinaria'));
        }
        $lista            = $resultado->select('maquinaria.*','tipomaquinaria.nombre as tipomaquinaria2')->get();
        $cabecera         = array();
        $cabecera[]       = array('valor' => '#', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Tipo Maq.', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Nombre', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Marca', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Modelo', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Placa', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Año', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Serie', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Ancho', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Motor', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Potencia', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Peso', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Capacidad', 'numero' => '1');
        $cabecera[]       = array('valor' => 'Operaciones', 'numero' => '3');
        
        $titulo_modificar = $this->tituloModificar;
        $titulo_eliminar  = $this->tituloEliminar;
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
            return view($this->folderview.'.list')->with(compact('lista', 'paginacion', 'inicio', 'fin', 'entidad', 'cabecera', 'titulo_modificar', 'titulo_eliminar', 'ruta'));
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
        $entidad          = 'Maquinaria';
        $title            = $this->tituloAdmin;
        $titulo_registrar = $this->tituloRegistrar;
        $ruta             = $this->rutas;
        $cboTipomaquinaria = array('' => 'Todos');
        $tipomaquinaria = Tipomaquinaria::orderBy('nombre','asc')->get();
        foreach($tipomaquinaria as $k=>$v){
            $cboTipomaquinaria = $cboTipomaquinaria + array($v->id => $v->nombre);
        }
        return view($this->folderview.'.admin')->with(compact('entidad', 'title', 'titulo_registrar', 'ruta', 'cboTipomaquinaria'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $entidad  = 'Maquinaria';
        $maquinaria = null;
        $formData = array('maquinaria.store');
        $cboTipomaquinaria = array();
        $tipomaquinaria = Tipomaquinaria::orderBy('nombre','asc')->get();
        foreach($tipomaquinaria as $k=>$v){
            $cboTipomaquinaria = $cboTipomaquinaria + array($v->id => $v->nombre);
        }
        $formData = array('route' => $formData, 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Registrar'; 
        return view($this->folderview.'.mant')->with(compact('maquinaria', 'formData', 'entidad', 'boton', 'listar','cboTipomaquinaria'));
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
        $reglas     = array('nombre' => 'required|max:500');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        }
        $dat=array();
        $error = DB::transaction(function() use($request,&$dat){
            $maquinaria = new Maquinaria();
            $maquinaria->nombre = strtoupper($request->input('nombre'));
            $maquinaria->tipomaquinaria_id = $request->input('tipomaquinaria_id');
            $maquinaria->color = strtoupper($request->input('color'));
            $maquinaria->carroceria = strtoupper($request->input('carroceria'));
            $maquinaria->marca = strtoupper($request->input('marca'));
            $maquinaria->modelo = strtoupper($request->input('modelo'));
            $maquinaria->anio = strtoupper($request->input('anio'));
            $maquinaria->placa = strtoupper($request->input('placa'));
            $maquinaria->serie = strtoupper($request->input('serie'));
            $maquinaria->largo = strtoupper($request->input('largo'));
            $maquinaria->alto = strtoupper($request->input('alto'));
            $maquinaria->ancho = strtoupper($request->input('ancho'));
            $maquinaria->pesobruto = strtoupper($request->input('pesobruto'));
            $maquinaria->pesoneto = strtoupper($request->input('pesoneto'));
            $maquinaria->motor = strtoupper($request->input('motor'));
            $maquinaria->seriemotor = strtoupper($request->input('seriemotor'));
            $maquinaria->potencia = strtoupper($request->input('potencia'));
            $maquinaria->capacidad = strtoupper($request->input('capacidad'));
            $maquinaria->save();
            $dat[0]=array("respuesta"=>"OK","maquinaria_id"=>$maquinaria->id);
        });
        return is_null($error) ? json_encode($dat) : $error;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $existe = Libreria::verificarExistencia($id, 'maquinaria');
        if ($existe !== true) {
            return $existe;
        }
        $listar   = Libreria::getParam($request->input('listar'), 'NO');
        $maquinaria = Maquinaria::find($id);
        $entidad  = 'Maquinaria';
        $cboTipomaquinaria = array();
        $tipomaquinaria = Tipomaquinaria::orderBy('nombre','asc')->get();
        foreach($tipomaquinaria as $k=>$v){
            $cboTipomaquinaria = $cboTipomaquinaria + array($v->id => $v->nombre);
        }
        $formData = array('maquinaria.update', $id);
        $formData = array('route' => $formData, 'method' => 'PUT', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Modificar';
        return view($this->folderview.'.mant')->with(compact('maquinaria', 'formData', 'entidad', 'boton', 'listar', 'cboTipomaquinaria'));
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
        $existe = Libreria::verificarExistencia($id, 'maquinaria');
        if ($existe !== true) {
            return $existe;
        }
        $reglas     = array('nombre' => 'required|max:500');
        $mensajes = array(
            'nombre.required'         => 'Debe ingresar un nombre'
            );
        $validacion = Validator::make($request->all(), $reglas, $mensajes);
        if ($validacion->fails()) {
            return $validacion->messages()->toJson();
        } 
        $dat=array();
        $error = DB::transaction(function() use($request, $id,&$dat){
            $maquinaria = Maquinaria::find($id);
            $maquinaria->nombre = strtoupper($request->input('nombre'));
            $maquinaria->tipomaquinaria_id = $request->input('tipomaquinaria_id');
            $maquinaria->color = strtoupper($request->input('color'));
            $maquinaria->carroceria = strtoupper($request->input('carroceria'));
            $maquinaria->marca = strtoupper($request->input('marca'));
            $maquinaria->modelo = strtoupper($request->input('modelo'));
            $maquinaria->anio = strtoupper($request->input('anio'));
            $maquinaria->placa = strtoupper($request->input('placa'));
            $maquinaria->serie = strtoupper($request->input('serie'));
            $maquinaria->largo = strtoupper($request->input('largo'));
            $maquinaria->alto = strtoupper($request->input('alto'));
            $maquinaria->ancho = strtoupper($request->input('ancho'));
            $maquinaria->pesobruto = strtoupper($request->input('pesobruto'));
            $maquinaria->pesoneto = strtoupper($request->input('pesoneto'));
            $maquinaria->motor = strtoupper($request->input('motor'));
            $maquinaria->seriemotor = strtoupper($request->input('seriemotor'));
            $maquinaria->potencia = strtoupper($request->input('potencia'));
            $maquinaria->capacidad = strtoupper($request->input('capacidad'));
            $maquinaria->save();
            $dat[0]=array("respuesta"=>"OK","maquinaria_id"=>$maquinaria->id);
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
        $existe = Libreria::verificarExistencia($id, 'maquinaria');
        if ($existe !== true) {
            return $existe;
        }
        $error = DB::transaction(function() use($id){
            $maquinaria= Maquinaria::find($id);
            $maquinaria->delete();
        });
        return is_null($error) ? "OK" : $error;
    }

    public function eliminar($id, $listarLuego)
    {
        $existe = Libreria::verificarExistencia($id, 'maquinaria');
        if ($existe !== true) {
            return $existe;
        }
        $listar = "NO";
        if (!is_null(Libreria::obtenerParametro($listarLuego))) {
            $listar = $listarLuego;
        }
        $modelo   = Maquinaria::find($id);
        $entidad  = 'Maquinaria';
        $formData = array('route' => array('maquinaria.destroy', $id), 'method' => 'DELETE', 'class' => 'form-horizontal', 'id' => 'formMantenimiento'.$entidad, 'autocomplete' => 'off');
        $boton    = 'Eliminar';
        return view('app.confirmarEliminar')->with(compact('modelo', 'formData', 'entidad', 'boton', 'listar'));
    }
    
    public function archivos(Request $request){
        //obtenemos el campo file definido en el formulario
        $file = $request->file('file-0');
 
        //obtenemos el nombre del archivo
        $nombre = $file->getClientOriginalName();
 
        //indicamos que queremos guardar un nuevo archivo en el disco local
        //$path = public_path('avatar/'.$filename);
        $carpeta = '/M'.$request->input('id');
        if (!file_exists($carpeta)) {
            \Storage::makeDirectory($carpeta);
        }
        
        $path = public_path('image/'.$request->input('id').'-'.$nombre);
        
        $file->move('image', $request->input('id').'-'.$nombre);
        
        /*\Storage::disk('local')->put($nombre,  \File::get($file));
        \Storage::move($nombre, 'M'.$request->input('id').'/'.$nombre);
        //print_r(\Storage::disk('local')->url('M'.$request->input('id').'/'.$nombre));
        echo asset('storage/M'.$request->input('id').'/'.$nombre);*/
        $maquinaria = Maquinaria::find($request->input('id'));
        $maquinaria->archivo = $nombre;
        $maquinaria->save();
        //$file->move('compra',$nombre);
       return "archivo guardado";
    }
    
    public function pdf(Request $request){
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function($pdf2) {
            $pdf2->Image("http://asfalpaca.com.pe//asfalpaca/dist/img/logo.jpg", 10, 7, 190, 20);
        });
        /*$pdf::setFooterCallback(function($pdf2) {
            $pdf2->Image("http://martinampuero.com/asfalpaca/dist/img/pie.png", 10, 267, 190, 23);
        });*/
        $maquinaria = Maquinaria::find($request->input('id'));
        $pdf::SetTitle('MAQUINARIA '.$maquinaria->nombre);
        $pdf::SetMargins(10, 25, 10);
        $pdf::SetFooterMargin(50);
        $pdf::SetAutoPageBreak(true, 30);
        $pdf::AddPage();
        $pdf::Image("http://asfalpaca.com.pe/asfalpaca/image/".$maquinaria->id."-".$maquinaria->archivo, 55, 130, 100, 80);
        $pdf::SetFont('helvetica','B',14);
        $pdf::Cell(0,5,utf8_decode(''),0,0,'R');
        /*$pdf::Ln();
        $pdf::Cell(0,30,utf8_decode(''),0,0,'R');
        $pdf::Ln();*/
        //$pdf::Image("http://martinampuero.com/asfalpaca/dist/img/logo.jpg", 10, 7, 190, 30);
        //$pdf::Cell(0,5,utf8_decode(""),0,0,'C');
        $pdf::Ln(); 
        $pdf::Cell(0,5,utf8_decode("FICHA TECNICA"),0,0,'C');
        $pdf::Ln(); 
        $pdf::Ln(); 
        $pdf::SetFont('helvetica','B',10);
        $pdf::SetFillColor(194,194,194);
        $pdf::Cell(85,5,utf8_decode("EQUIPO"),1,0,'C',true);
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(85,5,utf8_decode("DIMENSIONES"),1,0,'C',true);
        $pdf::Ln(); 
        $pdf::Cell(30,5,utf8_decode("MARCA:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->marca),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("LARGO:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->largo),1,0,'L');
        $pdf::Ln(); 
        $pdf::Cell(30,5,utf8_decode("MODELO:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->modelo),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("ANCHO:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->ancho),1,0,'L');
        $pdf::Ln(); 
        $pdf::Cell(30,5,utf8_decode("PLACA:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->placa),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("ALTO:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->alto),1,0,'L');
        $pdf::Ln(); 
        $pdf::Cell(30,5,utf8_decode("SERIE:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->serie),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("PESO NETO:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->pesoneto),1,0,'L');
        $pdf::Ln(); 
        $pdf::Cell(30,5,("AÑO:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->anio),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("PESO BRUTO:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->pesobruto),1,0,'L');
        $pdf::Ln(); 

        $pdf::Ln(); 
        $pdf::Cell(85,5,utf8_decode("MOTOR"),1,0,'C',true);
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(85,5,utf8_decode("OTROS DATOS"),1,0,'C',true);
        $pdf::Ln();
        $pdf::Cell(30,5,utf8_decode("MOTOR:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->motor),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("CATEGORIA:"),1,0,'L',true);
        $pdf::Cell(55,5,($maquinaria->tipomaquinaria->nombre),1,0,'L');
        $pdf::Ln(); 
        $pdf::Cell(30,5,utf8_decode("SERIE:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->seriemotor),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("COLOR:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->color),1,0,'L');
        $pdf::Ln(); 
        $pdf::Cell(30,5,utf8_decode("POTENCIA:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->potencia),1,0,'L');
        $pdf::Cell(20,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("CARROCERIA:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->carroceria),1,0,'L');
        $pdf::Ln(); 
        $pdf::Cell(105,5,utf8_decode(''),0,0,'R');
        $pdf::Cell(30,5,utf8_decode("CAPACIDAD:"),1,0,'L',true);
        $pdf::Cell(55,5,utf8_decode($maquinaria->capacidad),1,0,'L');
        $pdf::Ln();         
        $pdf::Output('Maquinaria.pdf');
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