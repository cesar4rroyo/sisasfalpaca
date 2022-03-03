<?php
use App\User;
use App\Person;
use App\Usertype;
use Jenssegers\Date\Date;
use App\Movimiento;
Date::setLocale('es');
$user     = Auth::user();
$person   = Person::find($user->person_id);
$usertype = Usertype::find($user->usertype_id);
$date     = Date::instance($usertype->created_at)->format('l j F Y');
?>
<style>
.enlaces{
    float: left;
    background-image: none;
    padding: 15px 15px;
    cursor: pointer;    color: #000;
    font-family: fontAwesome;
}
</style>
<header class="main-header"><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
    <!-- Logo -->
    <a href="#" class="logo" onclick="window.open('{{ url('/dashboard')}}','_blank')">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>ASFALPACA</b></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>ASFALPACA</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <?php
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',10);
        $resultado = $resultado->where('movimiento.situacion','like','P');
        
        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.razonsocial,\' \',paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'m2.numero as nroref','m2.fecha as fecharef')->orderBy('movimiento.fecha', 'ASC')->orderBY('paciente.razonsocial','asc')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $y=0;$r=0;$detalle="";
        foreach ($lista as $key => $value){
            if($value->situacion!="C"){
                if(strtotime('now')<strtotime($value->fecha)){
                    //$color =  "green";
                }elseif(strtotime($value->fecha)>strtotime('+5 days',strtotime('now')) && strtotime($value->fecha)>strtotime('now')){
                    //$color = "yellow";
                    $y = $y+1;
                }else{
                    $r=$r+1;
                    //$color = "red";
                }
            }else{
                $color = "";
            }
        }
        if($y>0 || $r>0){
            $detalle = "Tiene Letras $y Amarillas y $r Rojas";
        }
        ?>
        @if($detalle!="")
            <button class="btn btn-warning btn-ls" type="button" onclick="cargarRuta('<? echo URL::to('letra'); ?>', 'container');"><i class='fa fa-warning'></i>{{ $detalle }}</button>
        @endif
        <?php
        $resultado        = Movimiento::leftjoin('person as paciente', 'paciente.id', '=', 'movimiento.persona_id')
                            ->join('person as responsable', 'responsable.id', '=', 'movimiento.responsable_id')
                            ->join('movimiento as m2','m2.id','=','movimiento.movimiento_id')
                            ->where('movimiento.tipomovimiento_id','=',11);
        $resultado = $resultado->where('movimiento.situacion','like','P');
        
        $resultado        = $resultado->select('movimiento.*',DB::raw('concat(paciente.razonsocial,\' \',paciente.apellidopaterno,\' \',paciente.apellidomaterno,\' \',paciente.nombres) as cliente'),DB::raw('responsable.nombres as responsable2'),'m2.numero as nroref','m2.fecha as fecharef')->orderBy('movimiento.fecha', 'ASC')->orderBY('paciente.razonsocial','asc')->orderBy('movimiento.numero', 'ASC');
        $lista            = $resultado->get();
        $y=0;$r=0;$detalle="";
        foreach ($lista as $key => $value){
            if($value->situacion!="C"){
                if(strtotime('now')<strtotime($value->fechavencimiento) && strtotime($value->fechavencimiento)>strtotime('+6 days',strtotime('now'))){
                    //$color =  "green";
                }elseif(strtotime($value->fechavencimiento)<strtotime('+5 days',strtotime('now')) && strtotime($value->fechavencimiento)>strtotime('now')){
                    //$color = "yellow";
                    $y = $y+1;
                }else{
                    $r=$r+1;
                    //$color = "red";
                }
            }else{
                $color = "";
            }
        }
        if($y>0 || $r>0){
            $detalle = "Tiene Cheque $y Amarillas y $r Rojas";
        }
        ?>
        @if($detalle!="")
            <button class="btn btn-warning btn-ls" type="button" onclick="cargarRuta('<? echo URL::to('cheque'); ?>', 'container');"><i class='fa fa-danger'></i>{{ $detalle }}</button>
        @endif
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="dist/img/logo2.jpg" class="user-image" alt="User Image">
                        <span class="hidden-xs">{{ $person->nombres.' '.$person->apellidopaterno.' '.$person->apeliidomaterno }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="dist/img/logo2.jpg" class="img-circle" alt="User Image">

                            <p>
                                {{ $person->nombres.' '.$person->apellidopaterno.' '.$person->apeliidomaterno }} - {{ $usertype->name }}
                                <small>Miembro desde {{ $date }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Perfil</a>
                            </div>
                            <div class="pull-right">
                                <a href="{{ url('/auth/logout') }}" class="btn btn-default btn-flat">Cerrar Sesión</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>