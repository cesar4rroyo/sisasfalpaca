<?php
if(!is_null($letra)){
    $fecha = $movimiento->fecha;
    $fecha2 = $movimiento->fechavencimiento;
    $tipodocumento = $movimiento->tipodocumento_id;
    $detraccion = $movimiento->incluye;
    $proveedor_id = $movimiento->persona_id;
    $proveedor = trim($movimiento->persona->razonsocial.' '.$movimiento->persona->apellidopaterno.' '.$movimiento->persona->apellidomaterno.' '.$movimiento->persona->nombres);
    $montodetraccion = $movimiento->detraccion;
    $porcentajedetraccion = round($movimiento->detraccion*100/$movimiento->total,2);
}else{
    $fecha = date("Y-m-d");
    $fecha2 = date("Y-m-d");
    $tipodocumento = null;
    $detraccion = null;
    $proveedor = null;
    $proveedor_id = 0;
    $montodetraccion = 0;
    $porcentajedetraccion = null;
}
?>
<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($letra, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listProducto', null, array('id' => 'listProducto')) !!}
    {!! Form::hidden('listPago', null, array('id' => 'listPago')) !!}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {!! Form::label('numero', 'Nro Doc. Ref.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-10 col-md-10 col-sm-10">
        			{!! Form::text('numero', null, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
        			{!! Form::hidden('movimiento_id', null, array('class' => 'form-control input-xs', 'id' => 'movimiento_id')) !!}
        			{!! Form::hidden('person_id', null, array('class' => 'form-control input-xs', 'id' => 'person_id')) !!}
        		</div>
        	</div>
        	<div class="form-group">
        	    {!! Form::label('banco', 'Banco:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('banco',$cboBanco, null, array('class' => 'form-control input-xs', 'id' => 'banco')) !!}
                </div>
                {!! Form::label('moneda', 'Moneda:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('moneda',$cboMoneda, null, array('class' => 'form-control input-xs', 'id' => 'moneda')) !!}
                </div>
        	</div>
            <div class="form-group">
        		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listProducto\').val(carro);$(\'#listPago\').val(carroDoc);guardarLetra(\''.$entidad.'\', this);')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6" >
                <div class="box">
                    <div class="box-header">
                        <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Detalle <button class="btn btn-info btn-xs" onclick="agregarItem();"><i class="fa fa-plus"></i></button></h2>
                    </div>
                    <div class="box-body">
                        <table class="table table-condensed table-border" id="tbDetalle">
                            <thead>
                                <th class="text-center">Nro.</th>
                                <th class="text-center">Fecha Venc.</th>
                                <th class="text-center">Total</th>
                            </thead>
                            <tbody>
                                <?php
                                if(!is_null($letra)){
                                    $js="";
                                    foreach($detalle as $k => $v){
                                        $idproducto = $v->id;
                                        $igv = $v->preciocompra - round($v->preciocompra/1.18,2); 
                                        echo "<tr id='tr".$idproducto."'><td><input type='hidden' id='txtIdProducto".$idproducto."' name='txtIdProducto".$idproducto."' value='".$idproducto."' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad".$idproducto."' name='txtCantidad".$idproducto."' value='".$v->cantidad."' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem(".$idproducto.")}\" onblur=\"calcularTotalItem(".$idproducto.")\" /></td>";
                                        echo "<td align='left'><textarea rows='2' cols='50' id='txtProducto".$idproducto."' name='txtProducto".$idproducto."' class='form-control input-xs'>".$v->producto."</textarea></td>";
                                        echo "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtSubtotal".$idproducto."' style='width: 60px;' name='txtSubtotal".$idproducto."' value='".round($v->preciocompra/1.18,2)."' onkeydown=\"if(event.keyCode==13){calcularTotalItem(".$idproducto.")}\" onblur=\"calcularTotalItem(".$idproducto.")\" readonly=''/></td>";
                                        echo "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtIGV".$idproducto."' style='width: 60px;' name='txtIGV".$idproducto."' value='".$igv."' onkeydown=\"if(event.keyCode==13){calcularTotalItem(".$idproducto.")}\" onblur=\"calcularTotalItem(".$idproducto.")\" readonly=''/></td>";
                                        echo "<td align='center'><input type='hidden' id='txtPrecioVenta".$idproducto."' name='txtPrecioVenta".$idproducto."' value='0' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio".$idproducto."' style='width: 60px;' name='txtPrecio".$idproducto."' value='".$v->preciocompra."' onkeydown=\"if(event.keyCode==13){calcularTotalItem(".$idproducto.")}\" onblur=\"calcularTotalItem(".$idproducto.")\" /></td>";
                                        echo "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal".$idproducto."' style='width: 60px;' id='txtTotal".$idproducto."' value='".$v->preciocompra*$v->cantidad."' /></td>";
                                        echo "<td><a href='#' onclick=\"quitarProducto('".$idproducto."')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>";
                                        $js.="carro.push(".$idproducto.");";
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-right" colspan="2">Total</th>
                                    <th class="text-center" align="center">{!! Form::text('total', null, array('class' => 'input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                 </div>
            </div>
         </div>     
     </div>
     
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('1300');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="totalpagado"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });

    var personas2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'letra/personautocompletar/%QUERY',
			filter: function (personas2) {
				return $.map(personas2, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        ruc: movie.ruc,
                        person_id: movie.person_id,
					};
				});
			}
		}
	});
	personas2.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').typeahead(null,{
		displayKey: 'value',
		source: personas2.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(datum.person_id);
	});

}); 

var contador=0;
function guardarLetra (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#movimiento_id").val()==""){
        band = false;
        msg += " *No se selecciono un documento ref. \n";    
    }
    for(c=0; c < carro.length; c++){
        if($("#txtFecha"+carro[c]).val()==""){
            band = false;
            msg += " *Fecha de Letra "+$("#txtCantidad"+carro[c]).val()+" incorrecta \n";
        }
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


var carro = new Array();
var carroDoc = new Array();
var copia = new Array();

function agregarItem(){
    var idproducto = Math.round(Math.random()*100000);
    $("#tbDetalle").append("<tr id='tr"+idproducto+"'><td><input type='hidden' id='txtIdProducto"+idproducto+"' name='txtIdProducto"+idproducto+"' value='"+idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+idproducto+"' name='txtCantidad"+idproducto+"' value='"+(carro.length+1)+"' size='3' /></td>"+
            "<td align='left'><input type='date' id='txtFecha"+idproducto+"' name='txtFecha"+idproducto+"' class='form-control input-xs' /></td>"+
            "<td align='center'><input type='text' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idproducto+"' style='width: 60px;' id='txtTotal"+idproducto+"' value='0' onkeyup='calcularTotal();' /></td>"+
            "<td><a href='#' onclick=\"quitarProducto('"+idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    carro.push(idproducto);
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    calcularTotal();
}

function calcularTotal(){
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#total").val(total2);
}

function quitarProducto(id){
    $("#tr"+id).remove();
    for(c=0; c < carro.length; c++){
        if(carro[c] == id) {
            carro.splice(c,1);
        }
    }
    calcularTotal();
}

function agregarDetalle(id){
    $.ajax({
        type: "POST",
        url: "ticket/agregardetalle",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            for(d=0;d < datos.length; d++){
                if(datos[d].idservicio>0){
                    datos[d].id=datos[d].idservicio;
                }else{
                    datos[d].id="00"+Math.round(Math.random()*100);
                }
                //console.log(datos[d].idservicio);
                datos[d].idservicio="01"+Math.round(Math.random()*100)+datos[d].idservicio;
                $("#tbDetalle").append("<tr id='tr"+datos[d].idservicio+"'><td><input type='hidden' id='txtIdTipoServicio"+datos[d].idservicio+"' name='txtIdTipoServicio"+datos[d].idservicio+"' value='"+datos[d].idtiposervicio+"' /><input type='hidden' id='txtIdServicio"+datos[d].idservicio+"' name='txtIdServicio"+datos[d].idservicio+"' value='"+datos[d].id+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[d].idservicio+"' name='txtCantidad"+datos[d].idservicio+"' value='"+datos[d].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotal()}\" onblur=\"calcularTotalItem('"+datos[d].idservicio+"')\" /></td>"+
                    "<td><input type='checkbox' id='chkCopiar"+datos[d].idservicio+"' onclick=\"checkMedico(this.checked,'"+datos[d].idservicio+"')\" /></td>"+
                    "<td><input type='text' class='form-control input-xs' id='txtMedico"+datos[d].idservicio+"' name='txtMedico"+datos[d].idservicio+"' value='"+datos[d].medico+"' /><input type='hidden' id='txtIdMedico"+datos[d].idservicio+"' name='txtIdMedico"+datos[d].idservicio+"' value='"+datos[d].idmedico+"' /></td>"+
                    "<td align='left'>"+datos[d].tiposervicio+"</td><td>"+datos[d].servicio+"</td>"+
                    "<td><input type='hidden' id='txtPrecio2"+datos[d].idservicio+"' name='txtPrecio2"+datos[d].idservicio+"' value='0' /><input type='text' size='5' class='form-control input-xs' style='width: 60px;' data='numero' id='txtPrecio"+datos[d].idservicio+"' name='txtPrecio"+datos[d].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+datos[d].idservicio+"')}\" onblur=\"calcularTotalItem2('"+datos[d].idservicio+"')\" /></td>"+
                    "<td><input type='text' size='5' style='width: 60px;' class='form-control input-xs' data='numero' id='txtDescuento"+datos[d].idservicio+"' name='txtDescuento"+datos[d].idservicio+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem2('"+datos[d].idservicio+"')}\" onblur=\"calcularTotalItem2('"+datos[d].idservicio+"')\" style='width:50%' /></td>"+
                    "<td><input type='hidden' id='txtPrecioHospital2"+datos[d].idservicio+"' name='txtPrecioHospital2"+datos[d].idservicio+"' value='0' /><input type='text' size='5' style='width: 60px;' class='form-control input-xs' data='numero'  id='txtPrecioHospital"+datos[d].idservicio+"' name='txtPrecioHospital"+datos[d].idservicio+"' value='0' onblur=\"calcularTotalItem2("+datos[d].idservicio+")\" /></td>"+
                    "<td><input type='hidden' id='txtPrecioMedico2"+datos[d].idservicio+"' name='txtPrecioMedico2"+datos[d].idservicio+"' value='0' /><input type='text' size='5' class='form-control input-xs' data='numero'  id='txtPrecioMedico"+datos[d].idservicio+"' name='txtPrecioMedico"+datos[d].idservicio+"' value='0' style='width: 60px;' /></td>"+
                    "<td><input type='text' style='width: 60px;' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[d].idservicio+"' id='txtTotal"+datos[d].idservicio+"' value=0' /></td>"+
                    "<td><a href='#' id='Quitar"+datos[d].idservicio+"' onclick=\"quitarServicio('"+datos[d].idservicio+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                if(datos[d].situacionentrega!="A"){
                    carro.push(datos[d].idservicio);
                }else{
                    $("#Quitar"+datos[d].idservicio).css('display','none');
                }
                calcularTotalItem(datos[d].idservicio);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
                eval("var planes"+datos[d].idservicio+" = new Bloodhound({"+
                    "datumTokenizer: function (d) {"+
                        "return Bloodhound.tokenizers.whitespace(d.value);"+
                    "},"+
                    "limit: 10,"+
                    "queryTokenizer: Bloodhound.tokenizers.whitespace,"+
                    "remote: {"+
                        "url: 'medico/medicoautocompletar/%QUERY',"+
                        "filter: function (planes"+datos[d].idservicio+") {"+
                            "return $.map(planes"+datos[d].idservicio+", function (movie) {"+
                                "return {"+
                                    "value: movie.value,"+
                                    "id: movie.id,"+
                                "};"+
                            "});"+
                        "}"+
                    "}"+
                "});"+
                "planes"+datos[d].idservicio+".initialize();"+
                "$('#txtMedico"+datos[d].idservicio+"').typeahead(null,{"+
                    "displayKey: 'value',"+
                    "source: planes"+datos[d].idservicio+".ttAdapter()"+
                "}).on('typeahead:selected', function (object, datum) {"+
                    "$('#txtMedico"+datos[d].idservicio+"').val(datum.value);"+
                    "$('#txtIdMedico"+datos[d].idservicio+"').val(datum.id);"+
                    "copiarMedico('"+datos[d].idservicio+"');"+
                "});");
                $("#txtMedico"+datos[d].idservicio).focus(); 

            } 
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="coa"]').attr("readonly","true");
            $(".datofactura").css("display","none");
        }
    });
}
<?php
if(!is_null($letra)){
    //echo "agregarDetalle(".$movimiento->id.");";
    echo "mostrarDetraccion('".$letra->incluye."');";
    echo $js;echo $js2;
    echo "$(':input[data=\"numero\"]').inputmask('decimal', { radixPoint: \".\", autoGroup: true, groupSeparator: \"\", groupSize: 3, digits: 2 });";
}
?>

</script>