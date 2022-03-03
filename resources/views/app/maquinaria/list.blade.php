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
		@if($value->situacion=="A")
		  <tr style="background-color:rgba(0,255,0,0.5)" title="Periodo Actual">
        @else
            <tr>
        @endif
			<td>{{ $contador }}</td>
			<td>{{ $value->tipomaquinaria2 }}</td>
			<td>{{ $value->nombre }}</td>
			<td>{{ $value->marca }}</td>
			<td>{{ $value->modelo }}</td>
			<td>{{ $value->placa }}</td>
			<td>{{ $value->anio }}</td>
			<td>{{ $value->serie }}</td>
			<td>{{ $value->ancho }}</td>
			<td>{{ $value->motor }}</td>
			<td>{{ $value->potencia }}</td>
			<td>{{ $value->peso }}</td>
			<td>{{ $value->capacidad }}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
            <?php /*<td>{!! Form::button('<div class="glyphicon glyphicon-print"></div> Ficha Tecnica', array('onclick' => 'pdf('. $value->id.')', 'class' => 'btn btn-xs btn-info')) !!}</td> */?>
            @if($value->archivo!="")
            	<td>{!! Form::button('<div class="glyphicon glyphicon-print"></div> Ficha Tecnica', array('onclick' => 'window.open("image/'.$value->id.'-'.$value->archivo.'","_blank")', 'class' => 'btn btn-xs btn-info')) !!}</td>
            @else
            	<td> - </td>
            @endif
            <td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
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