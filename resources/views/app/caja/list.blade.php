@if($conceptopago_id==1)
	{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Apertura', array('class' => 'btn btn-info btn-xs', 'disabled' => 'true', 'id' => 'btnApertura', 'onclick' => 'modalCaja (\''.URL::route($ruta["apertura"], array('listar'=>'SI')).'\', \''.$titulo_apertura.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-usd"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-remove-circle"></i> Cierre', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["cierre"], array('listar'=>'SI')).'\', \''.$titulo_cierre.'\', this);')) !!}
@elseif($conceptopago_id==2)
    {!! Form::button('<i class="glyphicon glyphicon-plus"></i> Apertura', array('class' => 'btn btn-info btn-xs', 'id' => 'btnApertura', 'onclick' => 'modalCaja (\''.URL::route($ruta["apertura"], array('listar'=>'SI')).'\', \''.$titulo_apertura.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-usd"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'disabled' => 'true', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-remove-circle"></i> Cierre', array('class' => 'btn btn-danger btn-xs' , 'disabled' => 'true', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["cierre"], array('listar'=>'SI')).'\', \''.$titulo_cierre.'\', this);')) !!}
@else
    {!! Form::button('<i class="glyphicon glyphicon-plus"></i> Apertura', array('class' => 'btn btn-info btn-xs', 'disabled' => 'true', 'id' => 'btnApertura', 'onclick' => 'modalCaja (\''.URL::route($ruta["apertura"], array('listar'=>'SI')).'\', \''.$titulo_apertura.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-usd"></i> Nuevo', array('class' => 'btn btn-success btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
    {!! Form::button('<i class="glyphicon glyphicon-remove-circle"></i> Cierre', array('class' => 'btn btn-danger btn-xs', 'id' => 'btnCerrar', 'onclick' => 'modalCaja (\''.URL::route($ruta["cierre"], array('listar'=>'SI')).'\', \''.$titulo_cierre.'\', this);')) !!}
@endif
{!! Form::button('<i class="glyphicon glyphicon-print"></i> Imprimir', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnDetalle', 'onclick' => 'imprimir();')) !!}   
<?php 
$saldo = number_format($ingreso - $egreso,2,'.','');
?>
{!! Form::hidden('saldo', $saldo, array('id' => 'saldo')) !!}   
<hr />
@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
<div class="table-responsive">
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">

	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th class="text-center" @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
        <?php
        $color="";
        $color2="";
        $titulo="";
        $color=($value->situacion=='A')?'background:rgba(215,57,37,0.50)':'';
        $titulo=($value->situacion=='A')?'Anulado':'';            
        if($value->concepto->tipo=='I'){
            $color2='color:green;font-weight: bold;';
        }else{
            $color2='color:red;font-weight: bold;';
        }
        ?>
		<tr style="{{ $color }}" title="{{ $titulo }}">
            <td>{{ date('d/m/Y',strtotime($value->fecha)).' '.date('H:i:s',strtotime($value->created_at)) }}</td>
            <td>{{ $value->numero }}</td>
            <td>{{ $value->concepto->nombre }}</td>
            <td>{{ $value->cliente }}<br />{{ $value->entregado }}</td>
            <td>{{ $value->moneda=='S'?'Soles':('Dolares: '.$value->total)}}</td>
            @if(!is_null($value->situacion) && $value->situacion<>'R' && !is_null($value->situacion2) && $value->situacion2<>'R')
                @if($value->concepto_id>0 && !is_null($value->concepto_id) && $value->concepto->tipo=="I")
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total2,2,'.','') }}</td>
                    <td align="center">0.00</td>
                @else
                    <td align="center">0.00</td>
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
                @endif
            @else
                @if($value->concepto->tipo=="I")
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total2,2,'.','') }}</td>
                    <td align="center">0.00</td>
                @else
                    <td align="center">0.00</td>
                    <td align="center" style='{{ $color2 }}'>{{ number_format($value->total,2,'.','') }}</td>
                @endif
            @endif 
            <td>{{ $value->tipo}}</td>
            <td>{{ $value->comentario }}</td>
            <td>{{ $value->responsable }}</td>
            @if($conceptopago_id<>2 && $value->situacion<>'A' && $value->concepto_id<>3 && $value->concepto_id<>1)
                <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-remove"></div>', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_anular.'\', this);', 'class' => 'btn btn-xs btn-danger', 'title' => 'Anular')) !!}</td>
            @elseif($value->concepto_id==1)      
                <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit2"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
            @else                
                <td align="center"> - </td>
            @endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
{!! $paginacion or '' !!}
<table class="table-bordered table-striped table-condensed" align="center">
    <thead>
        <tr>
            <th class="text-center" colspan="2">Resumen de Caja</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>Ingresos :</th>
            <th class="text-right">{{ number_format($ingreso,2,'.','') }}</th>
        </tr>
        <tr>
            <th>Egresos :</th>
            <th class="text-right">{{ number_format($egreso,2,'.','') }}</th>
        </tr>
        <tr>
            <th>Saldo :</th>
            <th class="text-right">{{ number_format($ingreso - $egreso,2,'.','') }}</th>
        </tr>
    </tbody>
</table>
</div>
@endif