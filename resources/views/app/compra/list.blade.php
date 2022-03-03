@if(count($lista) == 0)
<h3 class="text-warning">No se encontraron resultados.</h3>
@else
{!! $paginacion or '' !!}
<div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped table-condensed table-hover">
    
    	<thead>
    		<tr>
    			@foreach($cabecera as $key => $value)
    				<th class='text-center' @if((int)$value['numero'] > 1) colspan="{{ $value['numero'] }}" @endif>{!! $value['valor'] !!}</th>
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
                <td>{{ $value->almacen->nombre }}</td>
                <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
                <td>{{ date("d/m/Y",strtotime($value->fechavencimiento)) }}</td>
                <td>{{ $value->formapago=='A'?'Contado':'Credito' }}</td>
                <td>{{ $value->tipodocumento->nombre }}</td>
                <td>{{ $value->numero }}</td>
                <td>{{ $value->cliente }}</td>
                <td>{{ $value->moneda=='S'?'Soles':'Dolares' }}</td>
    			<td align='center'>{{ number_format($value->total,2,'.','') }}</td>
    			<td align='center'>{{ number_format($value->totalpagado,2,'.','') }}</td>
    			<td>{{ $value->comentario }}</td>
                <td>{{ $value->responsable2 }}</td>
                <td>{!! Form::button('<div class="glyphicon glyphicon-eye-open"></div> Ver', array('onclick' => 'modal (\''.URL::route($ruta["show"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_ver.'\', this);', 'class' => 'btn btn-xs btn-info')) !!}</td>
    			@if($value->situacion!='A')
    			    <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
                    <td>{!! Form::button('<div class="glyphicon glyphicon-minus"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
                @else
                    <td align="center"> - </td>
                    <td align="center"> - </td>
                @endif
    		</tr>
    		<?php
    		$contador = $contador + 1;
    		?>
    		@endforeach
    	</tbody>
    </table>
</div>
<div style="position: absolute; right: 200px; top: 80px; color: red; font-weight: bold;">Soles : {{ number_format($totals,2,'.',',') }}</div>
<div style="position: absolute; right: 40px; top: 80px; color: green; font-weight: bold;">Dolares : {{ number_format($totald,2,'.',',') }}</div>
@endif