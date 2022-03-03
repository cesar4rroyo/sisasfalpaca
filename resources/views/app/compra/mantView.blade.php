<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($venta, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    <div class="row">
    	<div class="col-lg-12 col-md-12 col-sm-12">
    		<div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::date('fecha', $venta->fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('tipodocumento', 'Tipo Doc.:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('tipodocumento',$cboTipoDocumento, $venta->tipodocumento_id, array('class' => 'form-control input-xs', 'id' => 'tipodocumento', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('numero', $venta->numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('persona', 'Proveedor:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-6 col-md-6 col-sm-6">
                {!! Form::hidden('persona_id', 0, array('id' => 'persona_id')) !!}
                {!! Form::hidden('dni', '', array('id' => 'dni')) !!}
        		{!! Form::text('persona', $persona, array('class' => 'form-control input-xs', 'id' => 'persona', 'readonly' => 'true' )) !!}
        		</div>
                {!! Form::label('fechavencimiento', 'Fecha Venc.:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::date('fechavencimiento', $venta->fechavencimiento, array('class' => 'form-control input-xs', 'id' => 'fechavencimiento', 'readonly' => 'true')) !!}
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
                    <th class="text-center">Precio</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                @foreach($detalles as $key => $value)
					<tr>
                        <td class="text-center">{!! number_format($value->cantidad,2,'.','') !!}</td>
                        <td class="text-left">{!! $value->producto !!}</td>
						<td class="text-center">{!! number_format($value->preciocompra,2,'.','') !!}</td>
						<td class="text-center">{!! number_format($value->preciocompra*$value->cantidad,2,'.','') !!}</td>
					</tr>
                @endforeach
                </tbody>
                <tfoot>
                    <th class="text-right" colspan="3">Total</th>
                    <th class="text-center" align="center">{!! number_format($venta->total,2,'.','') !!}</th>
                </tfoot>
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