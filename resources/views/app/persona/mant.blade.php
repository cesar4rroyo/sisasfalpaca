<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($persona, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('roles', null, array('id' => 'roles')) !!}
    <div class="form-group">
		{!! Form::label('dni', 'DNI:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('dni', null, array('class' => 'form-control input-xs', 'id' => 'dni')) !!}
		</div>
		<div class="col-lg-1 col-md-1 col-sm-1">
		    <button class="btn btn-info btn-xs" title="Consultar DNI" onclick="consultaDNI();"><i class="fa fa-spinner fa-lg"></i></button>
		</div>
		{!! Form::label('ruc', 'RUC:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
		<div class="col-lg-3 col-md-3 col-sm-3">
			{!! Form::text('ruc', null, array('class' => 'form-control input-xs', 'id' => 'ruc')) !!}
		</div>
		<div class="col-lg-1 col-md-1 col-sm-1">
		    <button class="btn btn-info btn-xs" title="Consultar RUC" onclick="consultaRUC();"><i class="fa fa-spinner fa-lg"></i></button>
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('apellidopaterno', 'Apellido Paterno:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('apellidopaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidopaterno', 'placeholder' => 'Ingrese apellido paterno')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('apellidomaterno', 'Apellido Materno:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('apellidomaterno', null, array('class' => 'form-control input-xs', 'id' => 'apellidomaterno', 'placeholder' => 'Ingrese apellido materno')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('nombres', 'Nombres:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('nombres', null, array('class' => 'form-control input-xs', 'id' => 'nombres', 'placeholder' => 'Ingrese nombres')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('razonsocial', 'Razon Social:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('razonsocial', null, array('class' => 'form-control input-xs', 'id' => 'razonsocial', 'placeholder' => 'Razon Social')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('direccion', 'Direccion:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('direccion', null, array('class' => 'form-control input-xs', 'id' => 'direccion', 'placeholder' => 'Ingrese direccion')) !!}
		</div>
	</div>
    <div class="form-group">
		{!! Form::label('telefono', 'Telefono:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('telefono', null, array('class' => 'form-control input-xs', 'id' => 'telefono', 'placeholder' => 'Ingrese telefono')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('email', 'Correo:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::text('email', null, array('class' => 'form-control input-xs', 'id' => 'email', 'placeholder' => 'Ingrese correo')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('cuenta', 'Cta. Bancaria:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			{!! Form::textarea('cuenta', null, array('class' => 'form-control input-xs', 'id' => 'cuenta', 'rows' => '5')) !!}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('rolpersona', 'Roles:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
		<div class="col-lg-9 col-md-9 col-sm-9">
			<?php foreach($cboRol as $k=>$value){ 
				if(!is_null($cboRp) && count($cboRp)>0){
					if(isset($cboRp[$k]) && !is_null($cboRp[$k])){
						$check = "checked";
					}else{
						$check = "";
					}
				}else{
					$check = "";
				}
			?>
				<input type="checkbox" {{ $check }} onclick='agregarRol(this.checked,{{ $k }})'/>{{ $value }} <br />
			<?php } ?>
		</div>
	</div>
    <div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$("#roles").val(carroRol);guardar(\''.$entidad.'\', this)')) !!}
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('550');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'M', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').inputmask("99999999");
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').inputmask("99999999999");
}); 
var carroRol = new Array();
function agregarRol(check,id){
	if(check){
		carroRol.push(id);
	}else{
		for(c=0; c < carroRol.length; c++){
	        if(carroRol[c] == id) {
	            carroRol.splice(c,1);
	        }
	    }
	}
}
function consultaRUC(){
    var ruc = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val();
    $.ajax({
        type: 'GET',
        url: "../SunatPHP/demo.php",
        data: "ruc="+ruc+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        beforeSend(){
        	alert("Consultando...");
        },
        success: function (data, textStatus, jqXHR) {
        	alert("Datos Recibidos");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razonsocial"]').val(data.RazonSocial);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(data.Direccion);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razonsocial"]').focus();
        }
    });
}
function consultaDNI(){
    var ruc = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').val();
    $.ajax({
        type: 'GET',
        url: "../SunatPHP/dni.php",
        data: "dni="+ruc+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        beforeSend(){
        	alert("Consultando...");
        },
        success: function (data, textStatus, jqXHR) {
        	alert("Datos Recibidos");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="apellidopaterno"]').val(data.apepat);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="apellidomaterno"]').val(data.apemat);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="nombres"]').val(data.nombres);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="dni"]').focus();
        }
    });
}
function consultaRUC(){
    var ruc = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val();
    $.ajax({
        type: 'GET',
        url: "https://comprobante-e.com/facturacion/buscaCliente/BuscaClienteRuc.php",
        data: "fe=N&token=qusEj_w7aHEpX&ruc="+ruc,
        beforeSend(){
        	alert("Consultando...");
        },
        success: function (data, textStatus, jqXHR) {
        	data = JSON.parse(data);
        	alert("Datos Recibidos");
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razonsocial"]').val(data.RazonSocial);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="direccion"]').val(data.Direccion);
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="razonsocial"]').focus();
        }
    });
}
<?php
foreach ($cboRp as $key => $value) {
	echo "agregarRol(true,".$key.");";
}
?>
</script>