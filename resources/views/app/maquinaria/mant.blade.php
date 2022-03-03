<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($maquinaria, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	<div class="form-group">
		{!! Form::label('tipomaquinaria_id', 'Tipo Maquinaria:', array('class' => 'col-lg-2 col-md-2 col-sm-4 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::select('tipomaquinaria_id', $cboTipomaquinaria, null, array('class' => 'form-control input-xs', 'id' => 'tipomaquinaria_id')) !!}
		</div>
		{!! Form::label('nombre', 'Nombre:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('nombre', null, array('class' => 'form-control input-xs', 'id' => 'nombre', 'placeholder' => 'Ingrese nombre')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('color', 'Color:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('color', null, array('class' => 'form-control input-xs', 'id' => 'color', 'placeholder' => 'Ingrese color')) !!}
		</div>
		{!! Form::label('carroceria', 'Carroceria:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('carroceria', null, array('class' => 'form-control input-xs', 'id' => 'carroceria', 'placeholder' => 'Ingrese carroceria')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('marca', 'Marca:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('marca', null, array('class' => 'form-control input-xs', 'id' => 'marca', 'placeholder' => 'Ingrese marca')) !!}
		</div>
		{!! Form::label('modelo', 'Modelo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('modelo', null, array('class' => 'form-control input-xs', 'id' => 'modelo', 'placeholder' => 'Ingrese modelo')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('placa', 'Placa:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('placa', null, array('class' => 'form-control input-xs', 'id' => 'placa', 'placeholder' => 'Ingrese placa')) !!}
		</div>
		{!! Form::label('anio', 'Año:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('anio', null, array('class' => 'form-control input-xs', 'id' => 'anio', 'placeholder' => 'Ingrese año')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('serie', 'Serie:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('serie', null, array('class' => 'form-control input-xs', 'id' => 'serie', 'placeholder' => 'Ingrese serie')) !!}
		</div>
		{!! Form::label('largo', 'Largo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('largo', null, array('class' => 'form-control input-xs', 'id' => 'largo', 'placeholder' => 'Ingrese largo')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('ancho', 'Ancho:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('ancho', null, array('class' => 'form-control input-xs', 'id' => 'ancho', 'placeholder' => 'Ingrese ancho')) !!}
		</div>
		{!! Form::label('alto', 'Alto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('alto', null, array('class' => 'form-control input-xs', 'id' => 'alto', 'placeholder' => 'Ingrese alto')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('pesobruto', 'Peso Bruto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('pesobruto', null, array('class' => 'form-control input-xs', 'id' => 'pesobruto', 'placeholder' => 'Ingrese peso bruto')) !!}
		</div>
		{!! Form::label('pesoneto', 'Peso Neto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('pesoneto', null, array('class' => 'form-control input-xs', 'id' => 'pesoneto', 'placeholder' => 'Ingrese peso neto')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('motor', 'Motor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('motor', null, array('class' => 'form-control input-xs', 'id' => 'motor', 'placeholder' => 'Ingrese motor')) !!}
		</div>
		{!! Form::label('seriemotor', 'Serie Motor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('seriemotor', null, array('class' => 'form-control input-xs', 'id' => 'seriemotor', 'placeholder' => 'Ingrese serie motor')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('potencia', 'Potencia Motor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('potencia', null, array('class' => 'form-control input-xs', 'id' => 'potencia', 'placeholder' => 'Ingrese potencia')) !!}
		</div>
		{!! Form::label('capacidad', 'Capacidad:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::text('capacidad', null, array('class' => 'form-control input-xs', 'id' => 'capacidad', 'placeholder' => 'Ingrese capacidad')) !!}
		</div>
	</div>
	<div class="form-group">
	    {!! Form::label('archivo', 'Archivo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::file('archivo', null, array('class' => 'form-control input-xs', 'id' => 'archivo')) !!}
		</div>
	</div>
    <div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => 'guardarPago(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('800');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="anio"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 0 });
}); 
var contador=0;
function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    if(band && contador==0){
        contador=1;
    	var idformulario = IDFORMMANTENIMIENTO + entidad;
    	var data         = submitForm(idformulario);
    	var respuesta    = '';
    	var btn = $(idboton);
    	btn.button('loading');
    	data.done(function(msg) {
    		respuesta = msg;
    	}).fail(function(xhr, textStatus, errorThrown) {
    		respuesta = 'ERROR';
            contador=0;
    	}).always(function() {
    		btn.button('reset');
            contador=0;
    		if(respuesta === 'ERROR'){
    		}else{
    		  //alert(respuesta);
                var dat = JSON.parse(respuesta);
                if(dat[0]!==undefined){
                    resp=dat[0].respuesta;    
                }else{
                    resp='VALIDACION';
                }
    			if (resp === 'OK') {
    			    enviarArchivo(dat[0].maquinaria_id);
    				cerrarModal();
                    buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
                    //window.open('/juanpablo/ticket/pdfComprobante3?ticket_id='+dat[0].ticket_id,'_blank')
    			} else if(resp === 'ERROR') {
    				alert(dat[0].msg);
    			} else {
    				mostrarErrores(respuesta, idformulario, entidad);
    			}
    		}
    	});
    }else{
        alert("Corregir los sgtes errores: \n"+msg);
    }
}
function enviarArchivo(idcompra){
    //var form = $('#formMantenimientoCompra')[0];
    //var formulario = new FormData(form);
    var data = new FormData();
    jQuery.each($('input[type=file]')[0].files, function(i, file) {
        data.append('file-'+i, file);
    });
    data.append("id",idcompra);
    $.ajax({
		url: '{{ url("/maquinaria/archivos") }}',
		headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}' },
		type: 'POST',
		enctype: 'multipart/form-data',
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		timeout: 600000
    });
}

</script>