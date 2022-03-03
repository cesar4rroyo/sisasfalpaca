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
                $title='Eliminado';
                $color='background:#f73232d6';
            }else{
                $title='';
                $color='';
            }
            ?>
		<tr title="{{ $title }}" style="{{ $color }};">
			<td>{{ $contador }}</td>
            <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
            <td>{{ $value->numero }}</td>
            <td>{{ $value->cliente }}</td>
            <td>{{ $value->moneda=='S'?'Soles':'Dolares' }}</td>
            <td>{{ $value->comentario }}</td>
            <td>{{ $value->responsable2 }}</td>
            <td>{{ $value->situacion=='P'?'Pendiente':($value->situacion=='C'?'Confirmado':'Anulado') }}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-print"></div> Imprimir', array('onclick' => 'pdf('. $value->id.')', 'class' => 'btn btn-xs btn-info')) !!}</td>
            @if($value->situacion=='P')
                <td>{!! Form::button('<div class="glyphicon glyphicon-check"></div> Confirmar', array('onclick' => 'modal (\''.URL::route($ruta["confirmar"], array($value->id, 'SI')).'\', \'Confirmar\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
			    <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
			@else
			    <td>-</td>
			    <td align="center"> - </td>
			@endif
            @if($value->situacion!='A')
                <td>{!! Form::button('<div class="glyphicon glyphicon-minus"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
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
@endif