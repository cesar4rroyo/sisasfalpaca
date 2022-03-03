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
        			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
        		</div>
        		{!! Form::label('numeroref', 'Nro Ref:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label numeroref', 'style' => 'display:none;')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3 numeroref" style="display:none;">
        		    {!! Form::hidden('movimientoref_id', '0', array('id' => 'movimientoref_id')) !!}
        			{!! Form::text('numeroref', '', array('class' => 'form-control input-xs', 'id' => 'numeroref')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('persona', 'Cliente:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-9 col-md-9 col-sm-9">
                {!! Form::hidden('persona_id', 0, array('id' => 'persona_id')) !!}
                {!! Form::hidden('dni', '', array('id' => 'dni')) !!}
        		{!! Form::text('persona', 'VARIOS', array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Cliente')) !!}
        		</div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('persona.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Person\', this);', 'title' => 'Nueva Persona')) !!}
        		</div>
        	</div>
        	<div class="form-group">
                {!! Form::label('detraccion', 'Detraccion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('detraccion',$cboDetraccion, 'S', array('class' => 'form-control input-xs', 'id' => 'detraccion', 'onchange'=>'mostrarDetraccion(this.value)')) !!}
                </div>
                {!! Form::label('montodetraccion', '% :', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label detraccion')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2 detraccion">
                    {!! Form::text('porcentaje',0, array('class' => 'form-control input-xs ', 'id' => 'porcentaje', 'onkeyup' => 'calcularDetraccion();')) !!}
                </div>
                <div class="col-lg-2 col-md-2 col-sm-2 detraccion">
                    {!! Form::text('montodetraccion',0, array('class' => 'form-control input-xs', 'id' => 'montodetraccion', 'readonly' => 'true')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('tipo',$cboTipo, null, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
                </div>
                {!! Form::label('moneda', 'Moneda:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('moneda',$cboMoneda,null, array('class' => 'form-control input-xs ', 'id' => 'moneda', 'onchange' => 'validarMoneda(this.value);')) !!}
                </div>
                {!! Form::label('tipocambio', 'TC:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label moneda', 'style'=>'display:none')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2 moneda" style='display:none'>
                    {!! Form::text('tipocambio',0, array('class' => 'form-control input-xs ', 'id' => 'tipocambio')) !!}
                </div>
            </div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {!! Form::label('codigo', 'Cod. Barra:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('codigobarra', null, array('class' => 'form-control input-xs', 'id' => 'codigobarra')) !!}
        		</div>
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
                    <th class="text-center">Producto</th>
                    <th class="text-center">Unidad</th>
                    <th class="text-center">Precio</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right" colspan="4" style="color: green;font-size:30px;">Subtotal:</th>
                        <th class="text-center" align="center" style="color: green;font-size:30px;" align='center'>{!! Form::text('subtotal', null, array('class' => 'form-control input-xs', 'id' => 'subtotal', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 150px;font-size:30px;color:green;')) !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="4" style="color: green;font-size:30px;">IGV:</th>
                        <th class="text-center" align="center" style="color: green;font-size:30px;" align='center'>{!! Form::text('igv', null, array('class' => 'form-control input-xs', 'id' => 'igv', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 150px;font-size:30px;color:green;')) !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="4" style="color: green;font-size:30px;">Total:</th>
                        <th class="text-center" align="center" style="color: green;font-size:30px;" align='center'>{!! Form::text('total', null, array('class' => 'form-control input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 150px;font-size:30px;color:green;')) !!}</th>
                    </tr>
                    <tr style='display:none;'>
                        <th class="text-right" colspan="4" style="color: red;font-size:30px;">Dinero</th>
                        <th class="text-center"  align='center'>{!! Form::text('dinero', null, array('class' => 'form-control input-xs', 'id' => 'dinero', 'size' => 3, 'style' => 'width: 100px;font-size:30px;color:red;', 'onkeyup' => 'calcularVuelto();')) !!}</th>
                    </tr>
                    <tr style='display:none;'>
                        <th class="text-right" colspan="4" style="color: blue;font-size:30px;">Vuelto</th>
                        <th class="text-center"  align='center'>
                            {!! Form::text('vuelto', null, array('class' => 'form-control input-xs', 'id' => 'vuelto', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 100px;font-size:30px;color:blue;')) !!}
                            <input type="hidden" name="acuenta" id="acuenta" value="N">
                            <input type="checkbox" onclick="aCuenta(this.checked);" style="display:none;" />
                            <label style="display:none;">A Cuenta</label>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right">Tipo Doc.:</th>
                        <th><div class="col-lg-7 col-md-7 col-sm-7">
                            {!! Form::select('tipodocumento',$cboTipoDocumento, null, array('class' => 'form-control input-xs', 'id' => 'tipodocumento', 'onchange' => 'generarNumero();')) !!}
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="4"></th>
                        <th><div class="form-group">
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
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="subtotal"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="igv"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="dinero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="vuelto"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });

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
	
	var doc1 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'venta/numeroautocompletar/%QUERY',
			filter: function (doc1) {
				return $.map(doc1, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
					};
				});
			}
		}
	});
	doc1.initialize();
	$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numeroref"]').typeahead(null,{
		displayKey: 'value',
		source: doc1.ttAdapter()
	}).on('typeahead:selected', function (object, datum) {
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numeroref"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimientoref_id"]').val(datum.id);
	});

    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="codigobarra"]').focus();

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
    
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="codigobarra"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>1 && keyc == 13){
            buscarProductoBarra(this.value);
            this.value='';
        }
    });

}); 

function guardarHistoria (entidad, idboton) {
	var idformulario = IDFORMMANTENIMIENTO + entidad;
	var data         = submitForm(idformulario);
	var respuesta    = '';
	var btn = $(idboton);
	btn.button('loading');
	data.done(function(msg) {
		respuesta = msg;
	}).fail(function(xhr, textStatus, errorThrown) {
		respuesta = 'ERROR';
	}).always(function() {
		btn.button('reset');
		if(respuesta === 'ERROR'){
		}else{
		  //alert(respuesta);
            var dat = JSON.parse(respuesta);
			if (dat[0]!==undefined && (dat[0].respuesta=== 'OK')) {
				cerrarModal();
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="historia_id"]').val(dat[0].id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="numero_historia"]').val(dat[0].historia);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="person_id"]').val(dat[0].person_id);
                $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="tipopaciente"]').val(dat[0].tipopaciente);
                alert('Historia Generada');
                window.open("historia/pdfhistoria?id="+dat[0].id,"_blank");
			} else {
				mostrarErrores(respuesta, idformulario, entidad);
			}
		}
	});
}

var contador=0;
function guardarPago (entidad, idboton) {
    var band=true;
    var msg="";
    if($("#person_id").val()==""){
        band = false;
        msg += " *No se selecciono un cliente \n";    
    }
    if(parseFloat($("#total").val())>700 && $("#tipodocumento").val()=="3"){//BOLETA
        if($("#dni").val().trim().length!=8){
            band = false;
            msg += " *El cliente debe tener DNI correcto \n";
        }
    }   
    if($("#tipodocumento").val()=="4"){//FACTURA
        var ruc = $("#ruc").val();
        ruc = ruc.replace("_"," ");
        console.log(ruc);
        if(ruc.trim().length<11){
            band = false;
            msg += " *Debe registrar un correcto RUC \n";   
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

function buscarProductoBarra(barra){
    $.ajax({
        type: "POST",
        url: "venta/buscarproductobarra",
        data: "codigobarra="+barra+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            seleccionarProducto(datos[0].idproducto,datos[0].codigobarra,datos[0].producto,datos[0].preciocompra,datos[0].precioventa,datos[0].stock);
	    }
    });
}


var valorinicial="";
function buscarProducto(valor){
    $.ajax({
        type: "POST",
        url: "venta/buscarproducto",
        data: "descripcion="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="descripcion"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            $("#divBusqueda").html("<table class='table table-bordered table-condensed table-hover' border='1' id='tablaProducto'><thead><tr><th class='text-center'>COD. BARRA</th><th class='text-center'>PRODUCTO</th><th class='text-center'>STOCK</th><th class='text-center'>P. UNIT.</th></tr></thead></table>");
            var pag=parseInt($("#pag").val());
            var d=0;
            for(c=0; c < datos.length; c++){
                var a="<tr id='"+datos[c].idproducto+"' onclick=\"seleccionarProducto('"+datos[c].idproducto+"','"+datos[c].codigobarra+"','"+datos[c].producto+"','"+datos[c].preciocompra+"','"+datos[c].precioventa+"','"+datos[c].stock+"')\"><td align='center'>"+datos[c].codigobarra+"</td><td>"+datos[c].producto+"</td><td align='right'>"+datos[c].stock+"</td><td align='right'>"+datos[c].precioventa+"</td></tr>";
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
function seleccionarProducto(idproducto,codigobarra,descripcion,preciocompra,precioventa,stock){
    var band=true;
    for(c=0; c < carro.length; c++){
        if(carro[c]==idproducto){
            band=false;
        }      
    }
    if(band){
        $("#tbDetalle").append("<tr id='tr"+idproducto+"'><td><input type='hidden' id='txtIdProducto"+idproducto+"' name='txtIdProducto"+idproducto+"' value='"+idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+idproducto+"' name='txtCantidad"+idproducto+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            //"<td align='left'>"+codigobarra+"</td>"+
            "<td align='left' id='tdDescripcion"+idproducto+"'>"+descripcion+"</td>"+
            "<td><input type='text' id='txtUnidad"+idproducto+"' class='form-control input-xs' name='txtUnidad"+idproducto+"' size='10' /></td>"+
            "<td><input type='hidden' id='txtPrecioCompra"+idproducto+"' name='txtPrecioCompra"+idproducto+"' value='"+preciocompra+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+idproducto+"' style='width: 80px;' name='txtPrecio"+idproducto+"' value='"+precioventa+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idproducto+"' style='width: 60px;' id='txtTotal"+idproducto+"' value='"+precioventa+"' /></td>"+
            "<td><a href='#' onclick=\"quitarProducto('"+idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
        carro.push(idproducto);
        if(idant>0){
            $("#tdDescripcion"+idant).css('font-size','');
            $("#tdDescripcion"+idant).css('color','');
            $("#tdDescripcion"+idant).css('font-weight','');
        }
        //$("#tdDescripcion"+idproducto).css('font-size','30px');
        //$("#tdDescripcion"+idproducto).css('color','blue');
        //$("#tdDescripcion"+idproducto).css('font-weight','bold');
        idant=idproducto;
        $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 4 });
        calcularTotal();
    }else{
        if(idant>0){
            $("#tdDescripcion"+idant).css('font-size','');
            $("#tdDescripcion"+idant).css('color','');
            $("#tdDescripcion"+idant).css('font-weight','');
        }
        //$("#tdDescripcion"+idproducto).css('font-size','30px');
        //$("#tdDescripcion"+idproducto).css('color','blue');
        //$("#tdDescripcion"+idproducto).css('font-weight','bold');
        idant=idproducto;
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
    var igv = Math.round(total2*0.18*100)/100;
    var total3 = Math.round((igv+total2)*100)/100;
    $("#subtotal").val(total2);
    $("#igv").val(igv);
    $("#total").val(total3);
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
        url: "venta/generarNumero",
        data: "tipodocumento="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="numero"]').val(a);
            if($(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()=="26" || $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="tipodocumento"]').val()=="27"){
                $(".numeroref").css('display','');
            }else{
                $(".numeroref").css('display','none');
            }
        }
    });
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
            } 
            $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="coa"]').attr("readonly","true");
            $(".datofactura").css("display","none");
        }
    });
}

function calcularVuelto(){
    var tot=parseFloat($("#total").val());
    var din=parseFloat($("#dinero").val());
    var vue=Math.round((din - tot) * 100) / 100;
    $("#vuelto").val(vue);
}

function aCuenta(check){
    if(check){
        $("#acuenta").val("S");
    }else{
        $("#acuenta").val("N");
    }
}

function mostrarDetraccion(det){
    if(det=='S'){
        $(".detraccion").css('display','');
    }else{
        $(".detraccion").css('display','none');
    }
}

function calcularDetraccion(){
    var por = parseFloat($("#porcentaje").val());
    var det = Math.round(parseFloat($("#total").val())*por)/100;
    $("#montodetraccion").val(det);
}

function validarMoneda(vmoneda){
    if(vmoneda=="D"){
        $(".moneda").css('display','');
    }else{
        $(".moneda").css('display','none');
    }
}
<?php
if(!is_null($movimiento)){
    echo "agregarDetalle(".$movimiento->id.");";
}else{
    echo "generarNumero()";
}
?>

</script>