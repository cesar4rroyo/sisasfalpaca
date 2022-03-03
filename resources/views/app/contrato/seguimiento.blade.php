<style>
.tr_hover{
	color:red;
}
.form-group{
    margin-bottom: 8px !important;
}
</style>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($movimiento, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listVenta', null, array('id' => 'listVenta')) !!}
    {!! Form::hidden('listAvance', null, array('id' => 'listAvance')) !!}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', $movimiento->fecha, array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('numero', $movimiento->numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('contrato', 'Contrato:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('contrato', $movimiento->movimientoref->numero, array('class' => 'form-control input-xs', 'id' => 'contrato', 'readonly' => 'true')) !!}
                </div>
        	</div>
            <div class="form-group">
        		{!! Form::label('persona', 'Cliente:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-9 col-md-9 col-sm-9">
                {!! Form::text('persona', $movimiento->persona->razonsocial, array('class' => 'form-control input-xs', 'id' => 'persona', 'readonly' => 'true')) !!}
        		</div>
        	</div>
            <div class="form-group">
                {!! Form::label('incluye', 'Incluye IGV:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    <input type="hidden" name="incluye" id="incluye" value="N">
                    <input type="checkbox" name="chkIncluye" id="chkIncluye" onclick="incluye(this.checked);">
                </div>
                {!! Form::label('obra', 'Obra:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::textarea('comentario', $movimiento->comentario, array('class' => 'form-control input-xs', 'id' => 'comentario', 'rows' => '5')) !!}
                </div>
                <div class="col-lg-5 col-md-5 col-sm-5 text-right">
                    {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listAvance\').val(carroAvance);$(\'#listVenta\').val(carroDoc);guardarPago(\''.$entidad.'\', this);')) !!}
                    {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
                </div>
            </div>
         </div>
         <div class="col-lg-8 col-md-8 col-sm-8">
             <div class="box box-info">
                <div class="box-header">
                    <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Facturacion <button type="button" class="btn btn-info btn-xs" onclick="agregarFacturacion();" title="Agregar Detalle"><i class="fa fa-plus"></i></button></h2>
                </div>
                <div class="box-body">
                    <table class="table table-condensed table-border" id="tbFacturacion">
                        <thead>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Factura</th>
                            <th class="text-center">Cant</th>
                            <th class="text-center">Descripcion</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Subtotal</th>
                            <th class="text-center">IGV</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Detraccion</th>
                            <th class="text-center">Moneda</th>
                        </thead>
                        <tbody>
                            <?php
                            if(count($ventas)>0){
                                foreach ($ventas as $key => $value){
                                    echo "<tr>";
                                    echo "<td>$value->tipo</td>";
                                    echo "<td>".date("d/m/Y",strtotime($value->fecha))."</td>";
                                    echo "<td>$value->numero</td>";
                                    echo "<td>$value->cantidad</td>";
                                    echo "<td>$value->producto</td>";
                                    echo "<td>$value->unidad</td>";
                                    echo "<td>".number_format($value->precioventa/1.18,2,".","")."</td>";
                                    echo "<td>".number_format($value->precioventa/1.18*$value->cantidad,2,'.','')."</td>";
                                    echo "<td>".number_format($value->precioventa*$value->cantidad - $value->precioventa*$value->cantidad/1.18,2,'.','')."</td>";
                                    echo "<td>".number_format($value->precioventa*$value->cantidad,2,'.','')."</td>";
                                    if($value->incluye=='S'){
                                        echo "<td align='right'>$value->detraccion (".number_format($value->detraccion*100/($value->total==0?1:$value->total),2,'.','')."%)</td>";
                                    }else{
                                        echo "<td align='right'> - </td>";
                                    }
                                    echo "<td>".($value->moneda=='D'?('Dolares <br />'.$value->tipocambio):'Soles')."</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
         </div>     
     </div>
     <div class="row">
        <div class="col-lg-5 col-md-5 col-sm-5">
            <div class="box box-danger">
                <div class="box-header">
                    <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Detalle </h2>
                </div>
                <div class="box-body">
                    <table class="table table-condensed table-border" id="tbDetalle">
                        <thead>
                            <th class="text-center">Cant.</th>
                            <th class="text-center">Producto/Servicio</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Subtotal</th>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-right" colspan="4" >Total</th>
                                <th class="text-center" align="center">{!! Form::text('total', null, array('class' => 'form-control input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width:60px;')) !!}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
             </div>
         </div>
         <div class="col-lg-4 col-md-4 col-sm-4">
             <div class="box box-success">
                <div class="box-header">
                    <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Abonos <button style='display:none;' type="button" class="btn btn-info btn-xs" onclick="agregarAbono();" title="Agregar Abono"><i class="fa fa-plus"></i></button></h2>
                </div>
                <div class="box-body">
                    <table class="table table-condensed table-border" id="tbAbono">
                        <thead>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Banco</th>
                            <th class="text-center">Forma Pago</th>
                            <th class="text-center">Importe</th>
                        </thead>
                        <tbody>
                            <?php
                            $pagado = 0;
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
    					    </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <th class="text-center" colspan='3'>Total</th>
                            <th class="text-right" align="right"><input type="text" data='numero' class='form-control input-xs' style='width: 60px;' name="txtTotalA" id="txtTotalA" value="<?=$pagado?>" readonly=""></th>
                        </tfoot>
                    </table>
                </div>
            </div>
         </div>   
         <div class="col-lg-3 col-md-3 col-sm-3">
             <div class="box box-warning">
                <div class="box-header">
                    <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Avance <button type="button" class="btn btn-info btn-xs" onclick="agregarAvance();" title="Agregar Avance"><i class="fa fa-plus"></i></button></h2>
                </div>
                <div class="box-body">
                    <table class="table table-condensed table-border" id="tbAvance">
                        <thead>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">M3</th>
                            <th class="text-center">M2</th>
                        </thead>
                        <tbody>
                            <?php
                            $script="";
                            if($movimiento->listapago!=""){
                                $lista = explode("@",$movimiento->listapago);
                                for($x=0;$x<count($lista);$x++){
                                    $dat=explode("|",$lista[$x]);
                                    $z=rand();
                                    echo "<tr id='trV".$z."'>".
                                        "<td><input type='text' id='txtProductoV".$z."' class='form-control input-xs' name='txtProductoV".$z."' value='".(isset($dat[3])?$dat[3]:'')."' /></td>".
                                        "<td><input type='date' id='txtFechaV".$z."' class='form-control input-xs' name='txtFechaV".$z."' value='".$dat[0]."' style='width:120px;' /></td>".
                                        "<td><input type='text' id='txtM3V".$z."' onkeyup='calcularTotalM3();' class='form-control input-xs' value='".$dat[1]."' style='width: 60px;' name='txtM3V".$z."' size='10' data='numero' /></td>".
                                        "<td><input type='text' id='txtM2V".$z."' onkeyup='calcularTotalM2();' class='form-control input-xs' value='".$dat[2]."' style='width: 60px;' name='txtM2V".$z."'  data='numero' /></td>".
                                        "<td><a href='#' onclick=\"quitarAvance('".$z."')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>".
                                        "</tr>";
                                    $script.="carroAvance.push(".$z.");";
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <th colspan='2'>Total</th>
                            <th class="text-right" align="right"><input type="text" data='numero' class='form-control input-xs' style='width: 60px;' readonly="" name="txtTotalM3" id="txtTotalM3"></th>
                            <th class="text-right" align="right"><input type="text" data='numero' class='form-control input-xs' style='width: 60px;' readonly="" name="txtTotalM2" id="txtTotalM2"></th>
                        </tfoot>
                    </table>
                </div>
            </div>
         </div>   
     </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('1700');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });

}); 

var contador=0;
function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono un cliente \n";    
    } 
    for(c=0; c < carroDoc.length; c++){
        if($("#txtFechaF"+carroDoc[c]).val()==""){
            msg += " *No se ingreso una fecha \n";  
            band = false;
        }
        if($("#txtNumeroF"+carroDoc[c]).val()==""){
            msg += " *No se ingreso un numero de documento de venta \n";  
            band = false;
        }
        if($("#txtCantF"+carroDoc[c]).val()==""){
            msg += " *No se ingreso una cantidad en venta \n";  
            band = false;
        }
        if($("#txtDescripcionF"+carroDoc[c]).val()==""){
            msg += " *No se ingreso una en venta \n";  
            band = false;
        }
        if($("#txtUnidadF"+carroDoc[c]).val()==""){
            msg += " *No se ingreso una venta \n";  
            band = false;
        }
        if($("#txtPrecioF"+carroDoc[c]).val()==""){
            msg += " *No se ingreso una venta \n";  
            band = false;
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

var valorinicial="";
var carro = new Array();
var copia = new Array();
var idant = 0;

function calcularTotal(){
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#total").val(total2);
}

function calcularTotalItem(id){
    var cant=parseFloat($("#txtCantidad"+id).val());
    var pv=parseFloat($("#txtPrecio"+id).val());
    var total=Math.round((pv*cant) * 100) / 100;
    $("#txtTotal"+id).val(total);
    calcularTotal();
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

function generarNumero(){
    $.ajax({
        type: "POST",
        url: "contrato/generarNumero",
        data: "tipodocumento=10&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numero"]').val(a);
        }
    });
}

function incluye(check){
    if(check){
        $("#incluye").val("S");
    }else{
        $("#incluye").val("N");
    }
}

function agregarDetalle(id){
    $.ajax({
        type: "POST",
        url: "contrato/agregarDetalle",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            for(d=0;d < datos.length; d++){
                $("#tbDetalle").append("<tr id='tr"+datos[d].idproducto+"'><td><input type='hidden' id='txtIdProducto"+datos[d].idproducto+"' name='txtIdProducto"+datos[d].idproducto+"' value='"+datos[d].idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[d].idproducto+"' name='txtCantidad"+datos[d].idproducto+"' value='"+datos[d].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].idproducto+")}\" onblur=\"calcularTotalItem("+datos[d].idproducto+")\" readonly='s' /></td>"+
                "<td align='left' id='tdDescripcion"+datos[d].idproducto+"'>"+datos[d].producto+"</td>"+
                "<td align='center'>"+datos[d].unidad+"</td>"+
                "<td><input type='hidden' id='txtPrecioCompra"+datos[d].idproducto+"' name='txtPrecioCompra"+datos[d].idproducto+"' value='"+datos[d].preciocompra+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[d].idproducto+"' style='width: 60px;' name='txtPrecio"+datos[d].idproducto+"' value='"+datos[d].precioventa+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].idproducto+")}\" onblur=\"calcularTotalItem("+datos[d].idproducto+")\" readonly='' /></td>"+
                "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[d].idproducto+"' style='width: 60px;' id='txtTotal"+datos[d].idproducto+"' value='"+datos[d].subtotal+"' /></td></tr>");
                carro.push(datos[d].idproducto);
                calcularTotalItem(datos[d].idproducto);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 4 });

            } 
        }
    });
}

var carroDoc = new Array();
function agregarFacturacion(){
    var x1 = Math.round(Math.random()*10000);
    carroDoc.push(x1);
    $("#tbFacturacion").append("<tr id='trF"+x1+"'>"+
        "<td><select id='cboTipoF"+x1+"' class='form-control input-xs' name='cboTipoF"+x1+"'><option value='CON EL ESTADO'>CON EL ESTADO</option><option value='SUBCONTRATA CON EL ESTADO'>SUBCONTRATA CON EL ESTADO</option><option value='PRIVADO'>PRIVADO</option></select></td>"+
        "<td><input type='date' id='txtFechaF"+x1+"' class='form-control input-xs' name='txtFechaF"+x1+"' value='' style='width:120px;' /></td>"+
        "<td><input type='text' id='txtNumeroF"+x1+"' class='form-control input-xs' name='txtNumeroF"+x1+"'  /></td>"+
        "<td><input type='text' id='txtCantF"+x1+"' class='form-control input-xs' name='txtCantF"+x1+"' onkeyup='calcularTotalF("+x1+");' onblur='calcularTotalF("+x1+");' data='numero' size='10'/></td>"+
        "<td><textarea id='txtDescripcionF"+x1+"' class='form-control input-xs' name='txtDescripcionF"+x1+"'></textarea></td>"+
        "<td><input type='text' id='txtUnidadF"+x1+"' class='form-control input-xs' name='txtUnidadF"+x1+"' size='10' /></td>"+
        "<td><input type='text' id='txtPrecioF"+x1+"' class='form-control input-xs' name='txtPrecioF"+x1+"' size='10' data='numero' onkeyup='calcularTotalF("+x1+");' onblur='calcularTotalF("+x1+");' /></td>"+
        "<td><input type='text' id='txtSubtotalF"+x1+"' class='form-control input-xs' name='txtSubtotalF"+x1+"' size='10' data='numero' readonly='' /></td>"+
        "<td><input type='text' id='txtIgvF"+x1+"' class='form-control input-xs' name='txtIgvF"+x1+"' size='10' data='numero' readonly='' /></td>"+
        "<td><input type='text' id='txtTotalF"+x1+"' class='form-control input-xs' name='txtTotalF"+x1+"' size='10' data='numero' readonly='' /></td>"+
        "<td><select id='txtDetraccionF"+x1+"' class='form-control input-xs' name='txtDetraccionF"+x1+"' onchange=\"if(this.value=='S'){$('.detraccion').css('display','');}else{$('.detraccion').css('display','none');}\"><option value='N'>NO</option><option value='S'>SI</option></select><p class='detraccion' style='display:none;'>%<input type='text' id='txtMontoDetraccionF"+x1+"' name='txtMontoDetraccionF"+x1+"' data='numero' class='form-control input-xs' /></p></td>"+
        "<td><select id='txtMonedaF"+x1+"' class='form-control input-xs' name='txtMonedaF"+x1+"' onchange=\"if(this.value=='D'){$('.moneda').css('display','');}else{$('.moneda').css('display','none');}\"><option value='S'>Soles</option><option value='D'>Dolares</option></select><p class='moneda' style='display:none;'>TC:<input type='text' id='txtTipoCambioF"+x1+"' name='txtTipoCambioF"+x1+"' data='numero' class='form-control input-xs' /></p></td>"+
        "<td><a href='#' onclick=\"quitarFacturacion('"+x1+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>"+
        "</tr>");
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 4 });
}

function quitarFacturacion(id){
    $("#trF"+id).remove();
    for(c=0; c < carroDoc.length; c++){
        if(carroDoc[c] == id) {
            carroDoc.splice(c,1);
        }
    }
}

function calcularTotalF(id){
    var cant = $("#txtCantF"+id).val();
    var prec = $("#txtPrecioF"+id).val();
    var sub = Math.round((cant*prec)*100)/100;
    var igv = Math.round((cant*prec*0.18)*100)/100;
    var tot = Math.round((cant*prec*1.18)*100)/100;
    $("#txtSubtotalF"+id).val(sub);
    $("#txtIgvF"+id).val(igv);
    $("#txtTotalF"+id).val(tot);
}

var carroAbono = new Array();
function agregarAbono(){
    var x1 = Math.round(Math.random()*1000000);
    carroAbono.push(x1);
    $("#tbAbono").append("<tr id='trA"+x1+"'>"+
        "<td><input type='date' id='txtFechaA"+x1+"' class='form-control input-xs' name='txtFechaA"+x1+"' value='' style='width:120px;' /></td>"+
        "<td><input type='text' id='txtTotalA"+x1+"' onkeyup='calcularTotalAbono();' class='form-control input-xs' style='width: 60px;' name='txtTotalA"+x1+"' size='10' data='numero' /></td>"+
        "<td><input type='text' id='txtNumeroA"+x1+"' class='form-control input-xs' name='txtNumeroA"+x1+"'  /></td>"+
        "<td><a href='#' onclick=\"quitarAbono('"+x1+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>"+
        "</tr>");
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    calcularTotalAbono();
}

function quitarAbono(id){
    $("#trA"+id).remove();
    for(c=0; c < carroAbono.length; c++){
        if(carroAbono[c] == id) {
            carroAbono.splice(c,1);
        }
    }
    calcularTotalAbono();
}

function calcularTotalAbono(){
    var abono=0;
    for(c=0; c < carroAbono.length; c++){
        if($("#txtTotalA"+carroAbono[c]).val()!=""){
            abono = Math.round((abono + parseFloat($("#txtTotalA"+carroAbono[c]).val()))*100)/100;
            console.log(abono);
        }
    }   
    $("#txtTotalA").val(abono);
}

var carroAvance = new Array();
function agregarAvance(){
    var x1 = Math.round(Math.random()*10000);
    carroAvance.push(x1);
    $("#tbAvance").append("<tr id='trV"+x1+"'>"+
        "<td><input type='text' id='txtProductoV"+x1+"' class='form-control input-xs' name='txtProductoV"+x1+"'></td>"+
        "<td><input type='date' id='txtFechaV"+x1+"' class='form-control input-xs' name='txtFechaV"+x1+"' value='' style='width:120px;' /></td>"+
        "<td><input type='text' id='txtM3V"+x1+"' onkeyup='calcularTotalM3();' class='form-control input-xs' style='width: 60px;' name='txtM3V"+x1+"' size='10' data='numero' /></td>"+
        "<td><input type='text' id='txtM2V"+x1+"' onkeyup='calcularTotalM2();' class='form-control input-xs'  style='width: 60px;' name='txtM2V"+x1+"'  data='numero' /></td>"+
        "<td><a href='#' onclick=\"quitarAvance('"+x1+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>"+
        "</tr>");
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
}

function quitarAvance(id){
    $("#trV"+id).remove();
    for(c=0; c < carroAvance.length; c++){
        if(carroAvance[c] == id) {
            carroAvance.splice(c,1);
        }
    }
    calcularTotalM2();
    calcularTotalM3();
}

function calcularTotalM2(){
    var m2=0;
    for(c=0; c < carroAvance.length; c++){
        if($("#txtM2V"+carroAvance[c]).val()!=""){
            m2 = Math.round((m2 + parseFloat($("#txtM2V"+carroAvance[c]).val()))*100)/100;
        }
    }   
    $("#txtTotalM2").val(m2);
}

function calcularTotalM3(){
    var m3=0;
    for(c=0; c < carroAvance.length; c++){
        if($("#txtM3V"+carroAvance[c]).val()!=""){
            m3 = Math.round((m3 + parseFloat($("#txtM3V"+carroAvance[c]).val()))*100)/100;
        }
    }   
    $("#txtTotalM3").val(m3);
}

<?php
if(!is_null($movimiento)){
    echo "agregarDetalle(".$movimiento->id.");";
    echo $script.";calcularTotalM2();calcularTotalM3();";
}else{
    echo "generarNumero()";
}
?>

</script>