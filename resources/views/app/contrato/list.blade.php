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
            ?>
		<tr title="{{ $title }}" style="{{ $color }};">
			<td>{{ $contador }}</td>
			<td>{{ $value->numeroref }}</td>
            <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
            <td>{{ $value->numero }}</td>
            <td>{{ $value->cliente }}</td>
            <td>{{ $value->comentario }}</td>
			<td>{{ number_format($value->total,2,'.','') }}</td>
            <td>{{ $value->responsable2 }}</td>
            <td>{{ $value->situacion=='P'?'PENDIENTE':($value->situacion=='F'?'FINALIZADO':'-') }}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-file"></div> Word', array('onclick' => 'word('. $value->id.')', 'class' => 'btn btn-xs btn-info')) !!}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-th-large"></div> Seguimiento', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
		</tr>
		<?php
		$contador = $contador + 1;
		?>
		@endforeach
	</tbody>
</table>
@endif