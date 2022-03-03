<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($venta, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="row">
    	<div class="col-lg-12 col-md-12 col-sm-12">
    		<div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', $venta->fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('numero', $venta->numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
        		</div>
        		{!! Form::label('moneda', 'Moneda:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('moneda', $venta->moneda=='S'?'Soles':'Dolares', array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
        		</div>
        		
        	</div>
            <div class="form-group">
        		{!! Form::label('persona', 'Proveedor:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-6 col-md-6 col-sm-6">
        		{!! Form::text('persona', $persona, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Pesonal', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                {!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'rows' => '3')) !!}
                </div>
        	</div>
    	</div>
     </div>
	<div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Detalle </h2>
        </div>
        <div class="box-body">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Uni.</th>
                    <th class="text-center">P. Unit.</th>
                    <th class="text-center">Total</th>
                </thead>
                <tbody>
                @foreach($detalles as $key => $value)
					<tr>
                        <td class="text-center">{!! number_format($value->cantidad,2,'.','') !!}</td>
                        <td class="text-left"><textarea class='form-control input-xs' rows='2' cols='50' >{!! ($value->producto_id>0?$value->producto2:$value->producto) !!}</textarea></td>
                        <td class="text-center">{!! $value->unidad !!}</td>
                        <td class="text-right">{!! $value->preciocompra !!}</td>
                        <td class="text-right">{!! number_format($value->preciocompra*$value->cantidad,2,'.','') !!}</td>
					</tr>
                @endforeach
                </tbody>
            </table>
        </div>
     </div>
    <br>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">	
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('900');
}); 
</script>