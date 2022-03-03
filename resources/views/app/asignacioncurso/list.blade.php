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
		<tr>
            <td><input type="checkbox" <?php if($value->id>0) echo "checked=''";?> onclick="asignarCurso(this.checked,{{$value->id}},{{$value->curso_id}},{{$value->profesor_id}},{{$value->seccion_id}});" <?php if($value->id>0) echo "checked";?> /></td>
            <td>{{ $value->especialidad2 }}</td>
            <td>{{ $value->grado2 }}</td>
			<td>{{ $value->curso2 }}</td>
            <td>{{ $value->seccion2 }}</td>
            <td>{{ $value->horas }}</td>
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