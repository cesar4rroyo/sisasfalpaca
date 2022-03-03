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
    {!! Form::hidden('listProducto', null, array('id' => 'listProducto')) !!}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha', 'readonly' => 'true')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
        		</div>
                {!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('tipo', $cboTipo, '', array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
                </div>
        	</div>
            <div class="form-group">
        		{!! Form::label('persona', 'Cliente:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-9 col-md-9 col-sm-9">
                {!! Form::hidden('persona_id', 0, array('id' => 'persona_id')) !!}
                {!! Form::hidden('dni', '', array('id' => 'dni')) !!}
        		{!! Form::text('persona', '', array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Cliente')) !!}
        		</div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('persona.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Person\', this);', 'title' => 'Nueva Persona')) !!}
        		</div>
        	</div>
            <div class="form-group">
                {!! Form::label('incluye', 'Incluye IGV:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    <input type="hidden" name="incluye" id="incluye" value="N">
                    <input type="checkbox" name="chkIncluye" id="chkIncluye" onclick="incluyeIGV(this.checked);">
                </div>
                {!! Form::label('entregado', 'Obra o Proyecto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::textarea('entregado', '', array('class' => 'form-control input-xs', 'id' => 'entregado', 'rows' => '5')) !!}
                </div>
                {!! Form::label('comentario', 'Observaciones:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::textarea('comentario', '', array('class' => 'form-control input-xs', 'id' => 'comentario', 'rows' => '5')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('formapago', 'Forma Pago:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::textarea('formapago', '', array('class' => 'form-control input-xs', 'id' => 'formapago', 'rows' => '5')) !!}
                </div>
                {!! Form::label('gastos', 'Gastos Generales(%):', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('gastos', '0', array('class' => 'form-control input-xs', 'id' => 'gastos')) !!}
                </div>
                {!! Form::label('utilidades', 'Utilidades(%):', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('utilidades', '0', array('class' => 'form-control input-xs', 'id' => 'utilidades')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('cuentas', 'Cuentas Bancarias:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    {!! Form::textarea('cuentas', 'Banco Continental 0011-0287-0100022143
Banco de Crédito del Perú 305-1764536-002
Banco Interbank 700-3000425-197
Banco Scotiabank 000-2325977
Banco Pichincha 110-0003223-99246
Banco Banbif 007000222018
Banco de la Nación 00-231-081739
Cuenta de Detracciones 00-231-059822', array('class' => 'form-control input-xs', 'id' => 'cuentas', 'rows' => '5')) !!}
                </div>
            </div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {!! Form::label('descripcion', 'Producto:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-5 col-md-5 col-sm-5">
        			{!! Form::text('descripcion', null, array('class' => 'form-control input-xs', 'id' => 'descripcion', 'onkeypress' => '')) !!}
        		</div>
            </div>
            <div class="form-group col-lg-12 col-md-12 col-sm-12" id="divBusqueda">
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
                    <tr>
                        <th colspan="5"><div class="form-group">
                                <div class="col-lg-12 col-md-12 col-sm-12 text-right">
                                    {!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listProducto\').val(carro);guardarPago(\''.$entidad.'\', this);')) !!}
                                    {!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
                                </div>
                            </div>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
     </div>
{!! Form::close() !!}
<script type="text/javascript">
var valorbusqueda="";
$(document).ready(function() {
	configurarAnchoModal('1300');
	init(IDFORMMANTENIMIENTO+'{!! $entidad !!}', 'B', '{!! $entidad !!}');
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="utilidades"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="gastos"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    var personas2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'venta/personautocompletar/%QUERY',
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
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').typeahead(null,{
		displayKey: 'value',
		source: personas2.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona_id"]').val(datum.id);
	});

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>1 && keyc == 13){
            buscarProducto(this.value);
            valorbusqueda=this.value;
            this.focus();
            return false;
        }
        if(keyc == 38 || keyc == 40 || keyc == 13) {
            var tabladiv='tablaProducto';
			var child = document.getElementById(tabladiv).rows;
			var indice = -1;
			var i=0;
            $('#tablaProducto tr').each(function(index, elemento) {
                if($(elemento).hasClass("tr_hover")) {
    			    $(elemento).removeClass("par");
    				$(elemento).removeClass("impar");								
    				indice = i;
                }
                if(i % 2==0){
    			    $(elemento).removeClass("tr_hover");
    			    $(elemento).addClass("impar");
                }else{
    				$(elemento).removeClass("tr_hover");								
    				$(elemento).addClass('par');
    			}
    			i++;
    		});		 
			// return
			if(keyc == 13) {        				
			     if(indice != -1){
					var seleccionado = '';			 
					if(child[indice].id) {
					   seleccionado = child[indice].id;
					} else {
					   seleccionado = child[indice].id;
					}		 		
					seleccionarProducto(seleccionado);
				}
			} else {
				// abajo
				if(keyc == 40) {
					if(indice == (child.length - 1)) {
					   indice = 1;
					} else {
					   if(indice==-1) indice=0;
	                   indice=indice+1;
					} 
				// arriba
				} else if(keyc == 38) {
					indice = indice - 1;
					if(indice==0) indice=-1;
					if(indice < 0) {
						indice = (child.length - 1);
					}
				}	 
				child[indice].className = child[indice].className+' tr_hover';
			}
        }
    });

}); 

var contador=0;
function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono un cliente \n";    
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
function buscarProducto(valor){
    $.ajax({
        type: "POST",
        url: "cotizacion/buscarproducto",
        data: "descripcion="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            $("#divBusqueda").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaProducto'><thead><tr><th class='text-center'>PRODUCTO / SERVICIO</th><th class='text-center'>UNIDAD</th><th class='text-center'>P. UNIT.</th></tr></thead></table>");
            var pag=parseInt($("#pag").val());
            var d=0;
            for(c=0; c < datos.length; c++){
                var a="<tr id='"+datos[c].idproducto+"' onclick=\"seleccionarProducto('"+datos[c].idproducto+"','"+datos[c].producto+"','"+datos[c].preciocompra+"','"+datos[c].precioventa+"','"+datos[c].unidad+"')\"><td>"+datos[c].producto+"</td><td align='center'>"+datos[c].unidad+"</td><td align='right'>"+datos[c].precioventa+"</td></tr>";
                $("#tablaProducto").append(a);           
            }
            $('#tablaProducto').DataTable({
                "scrollY":        "250px",
                "scrollCollapse": true,
                "paging":         false
            });
            $('#tablaProducto_filter').css('display','none');
            $("#tablaProducto_info").css("display","none");
	    }
    });
}

var carro = new Array();
var carroDoc = new Array();
var copia = new Array();
var idant = 0;
function seleccionarProducto(idproducto,descripcion,preciocompra,precioventa,unidad){
    var band=true;
    for(c=0; c < carro.length; c++){
        if(carro[c]==idproducto){
            band=false;
        }      
    }
    if(band){
        $("#tbDetalle").append("<tr id='tr"+idproducto+"'><td><input type='hidden' id='txtIdProducto"+idproducto+"' name='txtIdProducto"+idproducto+"' value='"+idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+idproducto+"' name='txtCantidad"+idproducto+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='left' id='tdDescripcion"+idproducto+"'>"+descripcion+"</td>"+
            "<td align='center'>"+unidad+"</td>"+
            "<td><input type='hidden' id='txtPrecioCompra"+idproducto+"' name='txtPrecioCompra"+idproducto+"' value='"+preciocompra+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+idproducto+"' style='width: 60px;' name='txtPrecio"+idproducto+"' value='"+precioventa+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idproducto+"' style='width: 60px;' id='txtTotal"+idproducto+"' value='"+precioventa+"' /></td>"+
            "<td><a href='#' onclick=\"quitarProducto('"+idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
        carro.push(idproducto);
        $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits:4  });
        calcularTotal();
    }else{
        var cant = parseInt($('#txtCantidad'+idproducto).val())+1; 
        $('#txtCantidad'+idproducto).val(cant);
        calcularTotalItem(idproducto);
    }
}

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
        url: "cotizacion/generarNumero",
        data: "tipodocumento=10&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numero"]').val(a);
        }
    });
}

function incluyeIGV(check){
    if(check){
        $("#incluye").val("S");
    }else{
        $("#incluye").val("N");
    }
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
if(!is_null($movimiento)){
    echo "agregarDetalle(".$movimiento->id.");";
}else{
    echo "generarNumero()";
}
?>

</script>