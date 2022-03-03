<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($stock, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('almacen', 'Almacen:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::select('almacen', $cboAlmacen, null, array('class' => 'form-control input-xs', 'id' => 'almacen')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('producto', 'Producto:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::hidden('producto_id', 0, array('id' => 'producto_id')) !!}
			{!! Form::text('producto2', null, array('class' => 'form-control input-xs', 'id' => 'producto2', 'placeholder' => 'Ingrese producto')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('fechainicial', 'Fecha Inicial:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::date('fechainicial', date("Y-m-d"), array('class' => 'form-control input-xs', 'id' => 'fechainicial')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('fechafinal', 'Fecha Final:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::date('fechafinal', date("Y-m-d"), array('class' => 'form-control input-xs', 'id' => 'fechafinal')) !!}
		</div>
	</div>
    <div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-file fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnFile', 'onclick' => 'kardex(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('500');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	var personas2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'stockproducto/productoautocompletar/%QUERY',
			filter: function (personas2) {
				return $.map(personas2, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
					};
				});
			}
		}
	});
	personas2.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="producto2"]').typeahead(null,{
		displayKey: 'value',
		source: personas2.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="producto2"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="producto_id"]').val(datum.id);
	});
}); 
function kardex(entidad){
	window.open("stockproducto/reporteKardex?fechainicio="+$('#fechainicial').val()+"&fechafin="+$("#fechafinal").val()+"&producto="+$("#producto2").val()+"&almacen="+$("#almacen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
}
</script>