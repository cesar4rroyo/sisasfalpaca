<?php
$montodetraccion = $venta->detraccion;
$porcentajedetraccion = round($venta->detraccion*100/$venta->total,2);
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($venta, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
	{!! Form::hidden('listPago', null, array('id' => 'listPago')) !!}
	{!! Form::select('cboBanco',$cboBanco, null, array('class' => 'form-control input-xs', 'id' => 'cboBanco', 'style' => 'display:none')) !!}
    <div class="row">
    	<div class="col-lg-12 col-md-12 col-sm-12">
    		<div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
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
        		{!! Form::label('persona', 'Cliente:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-9 col-md-9 col-sm-9">
                {!! Form::hidden('persona_id', 0, array('id' => 'persona_id')) !!}
                {!! Form::hidden('dni', '', array('id' => 'dni')) !!}
        		{!! Form::text('persona', $venta->persona->razonsocial.' / '.$venta->persona->apellidopaterno.' '.$venta->persona->apellidomaterno.' '.$venta->persona->nombres, array('class' => 'form-control input-xs', 'id' => 'persona', 'readonly' => 'true')) !!}
        		</div>
        	</div>
        	<div class="form-group">
                {!! Form::label('cboDetraccion', 'Detraccion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('cboDetraccion',$cboDetraccion, trim($venta->incluye), array('class' => 'form-control input-xs', 'id' => 'cboDetraccion', 'onchange'=>'mostrarDetraccion(this.value)')) !!}
                </div>
                {!! Form::label('montodetraccion', '% :', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label detraccion')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2 detraccion">
                    {!! Form::text('porcentaje',$porcentajedetraccion, array('class' => 'form-control input-xs ', 'id' => 'porcentaje', 'onkeyup' => 'calcularDetraccion();', 'readonly' => 'true')) !!}
                </div>
                <div class="col-lg-2 col-md-2 col-sm-2 detraccion">
                    {!! Form::text('montodetraccion',$montodetraccion, array('class' => 'form-control input-xs', 'id' => 'montodetraccion', 'readonly' => 'true')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::text('tipo', $venta->tipo, array('class' => 'form-control input-xs', 'id' => 'tipo', 'readonly' => 'true')) !!}
                </div>
            </div>
    	</div>
     </div>
	<div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Detalle Pago <button type="button" class="btn btn-info btn-xs" onclick="agregarPago();" title="Agregar Pago"><i class="fa fa-plus"></i></button></h2>
        </div>
        <div class="box-body">
            <table class="table table-condensed table-border" id="tbPago">
                <thead>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Banco</th>
                    <th class="text-center">Forma Pago</th>
                    <th class="text-center">Importe</th>
                </thead>
                <tbody>
                <?php
                $pagado=0;
                ?>
                @foreach($pagos as $key => $value)
                    <?php
                    $pagado = $pagado + $value->monto;
                    ?>
					<tr>
                        <td class="text-center">{!! $value->fecha !!}</td>
                        <td class="text-left">{!! $value->banco->nombre !!}</td>
                        <td class="text-center">{!! $value->formapago !!}</td>
						<td class="text-center">{!! number_format($value->monto,2,'.','') !!}</td>
						<td><a href='#' onclick="eliminarPago('<?=$value->id?>','<?=$venta->id?>')"><i class='fa fa-minus-circle' title='Eliminar' width='20px' height='20px'></i></td>
					</tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right" colspan="3">Pagado</th>
                        <th class="text-center" align="center">
                            <input type='hidden' id='pagado2' name='pagado2' value='<?=$pagado?>' />
                            <input type='text' class='form-control input-xs' style='width: 80px;' readonly='' id='pagado' name='pagado' value='<?=number_format($pagado,2,'.','')?>' />
                        </th>
                    </tr>
                    <tr style="<?php if($venta->incluye!='S') echo "display:none;";?>">
                        <th class="text-right" colspan="3">
                            <select id='nrooperacion' name='nrooperacion'><option value='Detraccion' <?php if($venta->nrooperacion!="Autodetraccion") echo "selected";?>>Detraccion</option><option value='Autodetraccion' <?php if($venta->nrooperacion=="Autodetraccion") echo "selected";?>>Autodetraccion</option></select> Pagada:<select id='entregado' name='entregado' class='input-xs' style='width: 60px;' onchange="pagoDetraccion(this.value);"><option value='N' <?php if($venta->entregado=='N') echo "selected";?>>No</option><option value='S' <?php if($venta->entregado=='S') echo "selected";?>>Si</option></select></th>
                        <th class="text-center" align="center"><input type='text' class='form-control input-xs' style='width: 80px;' readonly='' id='detraccion' name='detraccion' value='<?=number_format(($venta->entregado=='S'?$venta->detraccion:0),2,'.','')?>' /></th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="3">Total</th>
                        <th class="text-center" align="center"><input type='text' class='form-control input-xs' style='width: 80px;' readonly='' id='total' name='total' value='<?=number_format($venta->total,2,'.','')?>' /></th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="3">Saldo</th>
                        <th class="text-center" align="center"><input type='text' class='form-control input-xs' style='width: 80px;' readonly='' id='saldo' name='saldo' value='<?=number_format($venta->total - $pagado,2,'.','')?>' /></th>
                    </tr>
                </tfoot>
            </table>
        </div>
     </div>
    <br>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
		    {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listPago\').val(carroPago);guardarPago(\''.$entidad.'\', this);')) !!}	
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('800');
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="pagado"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="saldo"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	$(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="detraccion"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
}); 


function mostrarDetraccion(det){
    if(det=='S'){
        $(".detraccion").css('display','');
    }else{
        $(".detraccion").css('display','none');
    }
}


var carroPago = new Array();
function agregarPago(){
    var x1 = Math.round(Math.random()*1000000);
    carroPago.push(x1);
    $("#tbPago").append("<tr id='trP"+x1+"'>"+
        "<td><input type='date' id='txtFechaP"+x1+"' class='form-control input-xs' name='txtFechaP"+x1+"' value='' style='width:120px;' /></td>"+
        "<td><select id='cboBancoP"+x1+"' class='form-control input-xs' name='cboBancoP"+x1+"'>"+$("#cboBanco").html()+"</select></td>"+
        "<td><input type='text' id='txtFormaP"+x1+"' class='form-control input-xs' name='txtFormaP"+x1+"' value='' /></td>"+
        "<td><input type='text' id='txtTotalP"+x1+"' onkeyup='calcularTotalPago();' class='form-control input-xs' style='width: 80px;' name='txtTotalP"+x1+"' size='10' data='numero' /></td>"+
        "<td><a href='#' onclick=\"quitarPago('"+x1+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>"+
        "</tr>");
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    calcularTotalPago();
}

function quitarPago(id){
    $("#trP"+id).remove();
    for(c=0; c < carroPago.length; c++){
        if(carroPago[c] == id) {
            carroPago.splice(c,1);
        }
    }
    calcularTotalPago();
}

function calcularTotalPago(){
    var pago=0;
    for(c=0; c < carroPago.length; c++){
        if($("#txtTotalP"+carroPago[c]).val()!=""){
            pago = Math.round((pago + parseFloat($("#txtTotalP"+carroPago[c]).val()))*100)/100;
        }
    }   
    pago = pago + parseFloat($("#pagado2").val());
    $("#pagado").val(pago);
    /*if($("#nrooperacion").val()=="Detraccion"){
        var detraccion = parseFloat($("#detraccion").val());
    }else{*/
        var detraccion = 0;
    //}
    var total = parseFloat($("#total").val());
    var saldo = Math.round((total - pago - detraccion)*100)/100;
    $("#saldo").val(saldo);
}


var contador=0;
function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono un cliente \n";    
    }
    if(parseFloat($("#saldo").val())<0){
        band = false;
        msg += " *El saldo no puede ser negativo \n";
    }
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
    				cerrarModal();
                    buscarCompaginado('', 'Accion realizada correctamente', entidad, 'OK');
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

function pagoDetraccion(chc){
    if(chc=='S'){
        $('#entregado').val('S');
        $("#detraccion").val($("#montodetraccion").val());
    }else{
        $('#entregado').val('N');
        $("#detraccion").val("0");
    }
    calcularTotalPago();
}

function eliminarPago(id,idventa){
    if(confirm('Desea eliminar el pago?')){
        $.ajax({
            type: "POST",
            url: "venta/eliminarPago",
            data: "id="+id+"&idventa="+idventa+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                alert("Eliminado");
                console.log(a);
                cerrarModal();
    	    }
        });
    }
}

<?php
echo "mostrarDetraccion('".$venta->incluye."');";
?>
</script>