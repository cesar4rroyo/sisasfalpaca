@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<table id="example1" class="table table-bordered table-striped table-condensed table-hover">

	<thead>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<?php
		$contador = $inicio + 1;
		?>
		@foreach ($lista as $key => $value)
            <?php 
            if($value->situacion=='A'){
                $title='Anulado';
                $color='background:#f73232d6';
            }else{
                $title='';
                $color='';
            }
            if($value->situacion=='A'){
                $situacion='Anulado';
            }elseif($value->situacion=='P'){
                $situacion='Pendiente';
            }elseif($value->situacion=='D'){
                $situacion='Autodetraccion';
            }elseif($value->situacion=='C'){
                $situacion='Cancelado';
            }
            ?>
		<tr title="{{ $title }}" style="{{ $color }};">
			<td>{{ $contador }}</td>
			<td>{{ $value->tipo }}</td>
            <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
            <td>{{ date("H:i:s",strtotime($value->created_at)) }}</td>
            <td>{{ $value->tipodocumento->nombre }}</td>
            <td>{{ $value->numero }}</td>
            <td>{{ $value->cliente }}</td>
			<td>{{ number_format($value->total,2,'.','') }}</td>
			<td>{{ $value->incluye }}</td>
			<td>{{ $value->incluye=='S'?$value->detraccion:'-' }}</td>
			<td>{{ $situacion }}</td>
            <td>{{ $value->responsable2 }}</td>
            <td align="center">{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
            <!--td>{!! Form::button('<div class="glyphicon glyphicon-print"></div> Imprimir', array('onclick' => 'imprimirVenta('. $value->id.')', 'class' => 'btn btn-xs btn-info')) !!}</td-->
            @if($value->situacion!='A')
			    <td align="center">{!! Form::button('<div class="glyphicon glyphicon-usd"></div> Pagar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \'Pagar\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
			@else
			    <td align="center"> - </td>
			@endif
            @if($value->situacion!='A')
                <td align="center">{!! Form::button('<div class="glyphicon glyphicon-minus"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
            @else
                <td align="center"> - </td>
            @endif
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			@foreach($cabecera as $key => $value)
				<th @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
			@endforeach
		</tr>
	</tfoot>
</table>
{!! $paginacion or '' !!}
@endif