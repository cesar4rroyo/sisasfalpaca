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
                <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
                <td>{{ $value->tipodocumento->nombre }}</td>
                <td>{{ $value->numero }}</td>
                <td>{{ $value->cliente }}</td>
                <td>{{ number_format($value->cantidad,2,'.',',') }}</td>
                <td>{{ $value->producto }}</td>
    			<td>{{ number_format($value->precioventa,2,'.',',') }}</td>
    			<td>{{ number_format($value->precioventa*$value->cantidad,2,'.',',') }}</td>
                <td>{{ $value->responsable2 }}</td>
    		</tr>
    		<?php
    		$contador = $contador + 1;
    		?>
    		@endforeach
    	</tbody>
    </table>
</div>
@endif