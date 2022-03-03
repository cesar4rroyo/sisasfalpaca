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
			<td>{{ $value->tipo }}</td>
            <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
            <td>{{ $value->numero }}</td>
            <td>{{ $value->cliente }}</td>
			<td>{{ number_format($value->total,2,'.','') }}</td>
            <td>{{ $value->responsable2 }}</td>
            <td>{{ $value->situacion=='P'?'PENDIENTE':($value->situacion=='C'?'CONFIRMADO':($value->situacion=='R'?'DESISTIDO':'ANULADO')) }}</td>
            <td>{!! Form::button('<div class="glyphicon glyphicon-print"></div> Imprimir', array('onclick' => 'pdf('. $value->id.')', 'class' => 'btn btn-xs btn-info')) !!}</td>
            @if($value->situacion=='P')
            	<td>{!! Form::button('<div class="glyphicon glyphicon-check"></div> Confirmar', array('onclick' => 'modal (\''.URL::route($ruta["confirmar"], array($value->id, 'listar'=>'SI')).'\', \'Confirmar\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
            	<td>{!! Form::button('<div class="glyphicon glyphicon-remove"></div> Desistir', array('onclick' => 'modal (\''.URL::route($ruta["rechazar"], array($value->id, 'listar'=>'SI')).'\', \'Desistir\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
            @endif
			<!--td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td-->
            @if($value->situacion!='A' && $value->situacion!="C")
                <td>{!! Form::button('<div class="glyphicon glyphicon-minus"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
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