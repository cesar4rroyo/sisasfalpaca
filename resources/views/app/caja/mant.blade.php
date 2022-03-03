<?php
if(is_null($caja)){
    $persona = "";
    $person_id=null;
    $referencia=null;
    $total=0;
    $modo=null;
    $tipocambio=0;
    $tipodocumento=null;
    $concepto=null;
}else{
    if (($caja->persona->razonsocial != null && trim($caja->persona->razonsocial)!="") || trim($caja->persona->apellidopaterno." ".$caja->persona->apellidomaterno." ".$caja->persona->nombres)=="") {
        $persona = $caja->persona->razonsocial;
    }else{
        $persona = trim($caja->persona->apellidopaterno." ".$caja->persona->apellidomaterno." ".$caja->persona->nombres);
    }    
    $person_id=$caja->persona_id;
    $referencia=$caja->entregado;
    $total=$caja->total;
    $modo=$caja->tipo;
    $tipocambio=$caja->detraccion;
    $tipodocumento=$caja->tipodocumento_id;
    $concepto=$caja->concepto_id;
}
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($caja, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('lista', '', array('id' => 'lista')) !!}
	<div class="form-group">
		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
		</div>
		{!! Form::label('numero', 'Nro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('tipodocumento', 'Tipo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('tipodocumento', $cboTipoDoc, $tipodocumento, array('class' => 'form-control input-xs', 'id' => 'tipodocumento', 'onchange' => 'generarConcepto(this.value);')) !!}
		</div>
		{!! Form::label('concepto', 'Concepto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
		<div class="col-lg-4 col-md-4 col-sm-4">
			{!! Form::select('concepto', $cboConcepto, $concepto, array('class' => 'form-control input-xs', 'id' => 'concepto', 'onchange' => 'validarConcepto();')) !!}
		</div>
	</div>
    <div class="form-group" id="divPersona">
		{!! Form::label('persona', 'Persona:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
        {!! Form::hidden('person_id', $person_id, array('id' => 'person_id')) !!}
			{!! Form::text('persona', $persona, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Persona')) !!}
		</div>
    </div>
    <div class="form-group">
		{!! Form::label('referencia', 'Referencia:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
		    {!! Form::text('referencia', $referencia, array('class' => 'form-control input-xs', 'id' => 'referencia', 'placeholder' => 'Ingrese Referencia')) !!}
		</div>
    </div>
    <div class='form-group divDocumento' style='display:none;'>
	    {!! Form::label('documento', 'Documentos:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
	    <div class="col-lg-9 col-md-9 col-sm-9">
	        <table id='tbDocumentos' class='table table-bordered table-striped table-condensed table-hover'>
	            <thead>
	                <tr>
	                    <th>Nro.</th>
	                    <th>Saldo</th>
	                    <th>Pago</th>
	                </tr>
	            </thead>
	            <tbody id='tbody'>
	            </tbody>
	        </table>
	    </div>
	</div>
    <div class="form-group">
		{!! Form::label('total', 'Total:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::text('total', $total, array('class' => 'form-control input-xs', 'id' => 'total')) !!}
		</div>
		{!! Form::label('moneda', 'Moneda:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2">
			{!! Form::select('moneda', $cboMoneda,null, array('class' => 'form-control input-xs', 'id' => 'moneda', 'onchange' => 'validarMoneda(this.value);')) !!}
		</div>
		{!! Form::label('tipocambio', 'Tipo Cambio:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label tipocambio', 'style' => 'display:none')) !!}
		<div class="col-lg-2 col-md-2 col-sm-2 tipocambio" style="display:none;">
			{!! Form::text('tipocambio', $tipocambio, array('class' => 'form-control input-xs', 'id' => 'tipocambio')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-5 col-md-5 col-sm-5">
			{!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'cols' => 10 , 'rows','5')) !!}
		</div>
		{!! Form::label('modo', 'Modo:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::select('modo', $cboModo,$modo, array('class' => 'form-control input-xs', 'id' => 'modo')) !!}
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
	configurarAnchoModal('750');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="tipocambio"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 3 });
	var personas = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
        limit: 10,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'caja/personautocompletar/%QUERY',
			filter: function (personas) {
				return $.map(personas, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
					};
				});
			}
		}
	});
	personas.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').typeahead(null,{
		displayKey: 'value',
		source: personas.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="busqueda"]').val(datum.value);
        buscarDocumentos(datum.id);
	});   
    
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').focus();
}); 

function validarMoneda(val){
    if(val=="S"){
        $(".tipocambio").css('display','none');
    }else{
        $(".tipocambio").css('display','');
    }
}

function generarConcepto(valor){
    $.ajax({
        type: "POST",
        url: "caja/generarConcepto",
        data: "tipodocumento_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="concepto"]').html(a);
            generarNumero(valor);
            //transferencia($("#concepto").val());
            if(valor=='7'){
                $(".divDocumento").css('display','');
            }else{
                $(".divDocumento").css('display','none');
            }
        }
    });
}

function generarNumero(valor){
    $.ajax({
        type: "POST",
        url: "caja/generarNumero",
        data: "tipodocumento_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numero"]').val(a);
        }
    });    
}

function buscarDocumentos(valor){
    $.ajax({
        type: "POST",
        url: "caja/buscarCompras",
        data: "persona_id="+valor+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $("#tbody").html(a);
            $('.pago').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 3 });
        }
    });    
}

</script>