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
                //print_r($value);
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
    			<td>{{ $value->numero }}</td>
                <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
                <td>{{ $value->cliente }}</td>
                <td>{{ $value->obra_id>0?$value->obra->nombre:'-' }}</td>
                <td>{{ !is_null($value->maquinaria)?($value->maquinaria->nombre.' / '.$value->maquinaria->placa." / ".$value->maquinaria->marca." / ".$value->maquinaria->modelo):'-' }}</td>
                <td>{{ $value->producto }}</td>
                <td>{{ $value->nroorden }}</td>
                <td>{{ $value->nrocompra }}</td>
                <td>{{ $value->proveedor2 }}</td>
                <td align='center'>{{ number_format($value->cantidad,2,'.',',') }}</td>
    			<td align='center'>{{ number_format($value->preciocompra,2,'.',',') }}</td>
    		</tr>
    		<?php
    		$contador = $contador + 1;
    		?>
    		@endforeach
    	</tbody>
    </table>
</div>
<div style="position: absolute; right: 200px; top: 70px; color: red; font-weight: bold;">Soles : {{ number_format($totals,2,'.',',') }}</div>
<div style="position: absolute; right: 40px; top: 70px; color: green; font-weight: bold;">Dolares : {{ number_format($totald,2,'.',',') }}</div>
@endif