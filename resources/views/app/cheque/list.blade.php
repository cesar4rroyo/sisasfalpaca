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
    			<td>{{ $value->nroref }}</td>
    			<td>{{ $value->cliente }}</td>
                <td>{{ date("d/m/Y",strtotime($value->fecharef)) }}</td>
                <td>{{ date("d/m/Y",strtotime($value->fecha)) }}</td>
                <?php
                if($value->situacion!="C"){
                    if(strtotime('now')<strtotime($value->fechavencimiento) && strtotime($value->fechavencimiento)>strtotime('+6 days',strtotime('now'))){
                        $color =  "green";
                    }elseif(strtotime($value->fechavencimiento)<strtotime('+5 days',strtotime('now')) && strtotime($value->fechavencimiento)>strtotime('now')){
                        $color = "yellow";
                    }elseif(strtotime('now')>strtotime($value->fechavencimiento)){
                        $color = "red";
                    }
                }else{
                    $color = "";
                }    
                ?>
                <td style="background:{{ $color }}">{{ date("d/m/Y",strtotime($value->fechavencimiento)) }}</td>
                <td>{{ $value->numero }}</td>
                <td>{{ $value->banco->nombre }}</td>
                <td>{{ $value->moneda=='S'?'Soles':'Dolares' }}</td>
    			<td align='center'>{{ number_format($value->total,2,'.','') }}</td>
    			<td>{{ $value->situacion!='C'?'-':date("d/m/Y",strtotime($value->fechaentrega)) }}</td>
    			<td>{{ $value->situacion=='P'?'Pendiente':($value->situacion=='A'?'Anulado':'Cobrado') }}</td>
                <td>{{ $value->responsable2 }}</td>
    			@if($value->situacion!='A' && $value->situacion!='C')
    			    <td>{!! Form::button('<div class="glyphicon glyphicon-pencil"></div> Editar', array('onclick' => 'modal (\''.URL::route($ruta["edit"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_modificar.'\', this);', 'class' => 'btn btn-xs btn-warning')) !!}</td>
    			    <td>{!! Form::button('<div class="glyphicon glyphicon-usd"></div> Pagar', array('onclick' => 'modal (\''.URL::route($ruta["cobrar"], array($value->id, 'listar'=>'SI')).'\', \''.$titulo_cobrar.'\', this);', 'class' => 'btn btn-xs btn-success')) !!}</td>
                    <td>{!! Form::button('<div class="glyphicon glyphicon-minus"></div> Eliminar', array('onclick' => 'modal (\''.URL::route($ruta["delete"], array($value->id, 'SI')).'\', \''.$titulo_eliminar.'\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
                @else
                    <td align="center"> - </td>
                    <td align="center"> - </td>
                    <td align="center"> - </td>
                @endif
                <td>{!! Form::button('<div class="glyphicon glyphicon-trash"></div> Anular', array('onclick' => 'modal (\''.URL::route($ruta["anular"], array($value->id, 'SI')).'\', \'Anular\', this);', 'class' => 'btn btn-xs btn-danger')) !!}</td>
    		</tr>
    		<?php
    		$contador = $contador + 1;
    		?>
    		@endforeach
    	</tbody>
    </table>
</div>
@endif