<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($producto, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-6 col-md-6 col-sm-6">
			{!! Form::select('tipo', $cboTipo, null, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('categoria_id', 'Categoria:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-6 col-md-6 col-sm-6">
			{!! Form::select('categoria_id', $cboCategoria, null, array('class' => 'form-control input-xs', 'id' => 'categoria_id')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('unidad_id', 'Unidad:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-6 col-md-6 col-sm-6">
			{!! Form::select('unidad_id', $cboUnidad, null, array('class' => 'form-control input-xs', 'id' => 'unidad_id')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('preciocompra', 'P. Compra:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('preciocompra', null, array('class' => 'form-control input-xs', 'id' => 'preciocompra', 'onblur' => 'calcularPrecio();')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('precioventa', 'P. Venta:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('precioventa', null, array('class' => 'form-control input-xs', 'id' => 'precioventa')) !!}
		</div>
	</div>
    <div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardar(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('420');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="preciocompra"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="precioventa"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
});
</script>