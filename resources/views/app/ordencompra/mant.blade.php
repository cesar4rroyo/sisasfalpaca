<?php
use App\Movimiento;
if(!is_null($movimiento)){
    $persona_id = $movimiento->persona_id;
    if ($movimiento->persona->razonsocial != null) {
        $persona = $movimiento->persona->razonsocial;
    }else{
        $persona = $movimiento->persona->apellidopaterno.' '.$movimiento->persona->apellidomaterno.' '.$movimiento->persona->nombres;
    }
}else{
    $persona_id = 0;
    $persona = "";
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
{!! Form::model($movimiento, $formData) !!}	
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
    {!! Form::hidden('listProducto', null, array('id' => 'listProducto')) !!}
    {!! Form::hidden('listRequerimiento', null, array('id' => 'listRequerimiento')) !!}
    {!! Form::hidden('listaMaquinaria', null, array('id' => 'listaMaquinaria')) !!}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-2 col-md-2 col-sm-2">
        			{!! Form::text('numero', $numero, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
        		</div>
        		{!! Form::label('requerimiento', 'Req.:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('requerimiento', '', array('class' => 'form-control input-xs', 'id' => 'requerimiento')) !!}
        			{!! Form::hidden('movimiento_id', '', array('class' => 'form-control input-xs', 'id' => 'movimiento_id')) !!}
        		</div>
        		<div class="col-lg-3 col-md-3 col-sm-3">
        		    <table class="table table-condensed table-border" id="tbDetalle1">
                        <thead>
                            <th class="text-center">Numero</th>
                            <th class="text-center"></th>
                        </thead>
                        <tbody>
                            <?php
                            if(!is_null($movimiento)){
                                $mov = Movimiento::where('movimiento_id','=',$movimiento->id)
                                                ->where('tipomovimiento_id','=',8)
                                                ->get();
                                foreach($mov as $k=>$v){
                                    echo "<tr id='tr1".$v->id."'>";
                                    echo "<td>$v->numero</td>";
                                    echo "<td><a href='#' onclick=\"quitarItem1('".$v->id."')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('persona', 'Proveedor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-9 col-md-9 col-sm-9">
                {!! Form::hidden('persona_id', $persona_id, array('id' => 'persona_id')) !!}
                {!! Form::hidden('ruc', '', array('id' => 'ruc')) !!}
        		{!! Form::text('persona', $persona, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Proveedor')) !!}
        		</div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('persona.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Historia\', this);', 'title' => 'Nueva Persona')) !!}
        		</div>
        	</div>
        	<div class="form-group">
        		{!! Form::label('obra_id', 'Obra:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
        			{!! Form::select('obra_id', $cboObra,null, array('class' => 'form-control input-xs', 'id' => 'obra_id')) !!}
        		</div>
                {!! Form::label('maquinaria_id', 'Maquinaria:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-4 col-md-4 col-sm-4">
        			{!! Form::select('maquinaria_id', $cboMaquinaria, null, array('class' => 'form-control input-xs', 'id' => 'maquinaria_id','onchange'=>'agregarMaquinaria(this.value);')) !!}
        		</div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <table class="table table-condensed table-border" id="tbDetalleMaquinaria">
                        <thead>
                            <th class="text-center">Maquinaria</th>
                            <th class="text-center"></th>
                        </thead>
                        <tbody>
                            <?php
                            if(!is_null($movimiento)){
                                /*$mov = Movimiento::where('movimiento_id','=',$movimiento->id)
                                                ->where('tipomovimiento_id','=',8)
                                                ->get();
                                foreach($mov as $k=>$v){
                                    echo "<tr id='tr1".$v->id."'>";
                                    echo "<td>$v->numero</td>";
                                    echo "<td><a href='#' onclick=\"quitarItem1('".$v->id."')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td>";
                                    echo "</tr>";
                                }*/
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
        	</div><br />
        	<div class="form-group">
        		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listProducto\').val(carro);$(\'#listRequerimiento\').val(carro1);$(\'#listaMaquinaria\').val(carro2);guardarPago(\''.$entidad.'\', this);')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6" >
             <div class="form-group">
        		{!! Form::label('tipo', 'Tipo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('tipo', $cboTipo,null, array('class' => 'form-control input-xs', 'id' => 'tipo')) !!}
        		</div>
        		{!! Form::label('formapago', 'Forma Pago:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-4 col-md-4 col-sm-4">
                    {!! Form::text('formapago', null, array('class' => 'form-control input-xs', 'id' => 'formapago')) !!}
                </div>
        	</div>
            <div class="form-group">
                {!! Form::label('comentario', 'Comentario:', array('class' => 'col-lg-3 col-md-3 col-sm-3 control-label')) !!}
                <div class="col-lg-5 col-md-5 col-sm-5">
                    {!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'rows' => '3', 'cols' => '50')) !!}
                </div>
                {!! Form::label('moneda', 'Moneda:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('moneda',$cboMoneda, null, array('class' => 'form-control input-xs', 'id' => 'moneda')) !!}
                </div>
            </div>
            <div class="form-group" style="display: none;">
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
            <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Detalle <button class="btn btn-info btn-xs" onclick="agregarItem();" style="display: none;"><i class="fa fa-plus"></i></button></h2>
        </div>
        <div class="box-body">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Uni.</th>
                    <th class="text-center">P. Unit.</th>
                    <th class="text-center">Total</th>
                    <th class="text-center"></th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right" colspan="4">Valor Venta</th>
                        <th class="text-center" align="center">{!! Form::text('subtotal', null, array('class' => 'input-xs', 'id' => 'subtotal', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="4">IGV</th>
                        <th class="text-center" align="center">{!! Form::text('igv', null, array('class' => 'input-xs', 'id' => 'igv', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="4">Total</th>
                        <th class="text-center" align="center">{!! Form::text('total', null, array('class' => 'input-xs', 'id' => 'total', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
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

    var personas2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'ordencompra/personautocompletar/%QUERY',
			filter: function (personas2) {
				return $.map(personas2, function (movie) {
					return {
						value: movie.value,
						id: movie.id,
                        ruc: movie.ruc,
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
	
	var contrato = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'ordencompra/requerimientoautocompletar/%QUERY',
            filter: function (contrato2) {
                return $.map(contrato2, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        maquinaria_id: movie.maquinaria_id,
                        obra_id: movie.obra_id
                    };
                });
            }
        }
    });
    contrato.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="requerimiento"]').typeahead(null,{
        displayKey: 'value',
        source: contrato.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="requerimiento"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="obra_id"]').val(datum.obra_id);
        //$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="maquinaria_id"]').val(datum.maquinaria_id);
        agregarMaquinaria(datum.maquinaria_id);
        agregarItem1(datum.id,datum.value);
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
    
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="codigobarra"]').on( 'keydown', function () {
        var e = window.event; 
        var keyc = e.keyCode || e.which;
        if(this.value.length>1 && keyc == 13){
            buscarProductoBarra(this.value);
            this.value='';
        }
    });

}); 

var carro1 = new Array();
function agregarItem1(idreq,numero){
    var band=true;
    for(c=0; c < carro1.length; c++){
        if(carro1[c]==idreq){
            band = false;
        }
    }
    if(band){
        $("#tbDetalle1").append("<tr id='tr1"+idreq+"'><td>"+numero+"</td>"+
            "<td><a href='#' onclick=\"quitarItem1('"+idreq+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
        carro1.push(idreq);
        agregarDetalle(idreq);
    }
}
function quitarItem1(id){
    $("#tr1"+id).remove();
    for(c=0; c < carro1.length; c++){
        if(carro1[c] == id) {
            carro1.splice(c,1);
        }
    }
}


var carro2 = new Array();
function agregarMaquinaria(idmaquinaria){
    var band=true;
    for(c=0; c < carro2.length; c++){
        if(carro2[c]==idmaquinaria){
            band = false;
        }
    }
    if(band){
        $.ajax({
            type: "POST",
            url: "ordencompra/buscarmaquinaria",
            data: "maquinaria_id="+idmaquinaria+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
            success: function(a) {
                datos=JSON.parse(a);
                $("#tbDetalleMaquinaria").append("<tr id='trMaquinaria"+idmaquinaria+"'><td>"+datos[0].nombre+"</td>"+
                    "<td><a href='#' onclick=\"quitarMaquinaria('"+idmaquinaria+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro2.push(idmaquinaria);
            }
        });
    }
}
function quitarMaquinaria(id){
    $("#trMaquinaria"+id).remove();
    for(c=0; c < carro2.length; c++){
        if(carro2[c] == id) {
            carro2.splice(c,1);
        }
    }
}

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
    if($("#persona_id").val()==""){
        band = false;
        msg += " *No se selecciono un proveedor \n";    
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
function seleccionarProducto(idproducto,codigobarra,descripcion,preciocompra,precioventa,stock){
    var band=true;
    for(c=0; c < carro.length; c++){
        if(carro[c]==idproducto){
            band=false;
        }      
    }
    if(band){
        $("#tbDetalle").append("<tr id='tr"+idproducto+"'><td><input type='hidden' id='txtIdProducto"+idproducto+"' name='txtIdProducto"+idproducto+"' value='"+idproducto+"' /><input type='text' data='numero' style='width: 60px;' class='form-control input-xs' id='txtCantidad"+idproducto+"' name='txtCantidad"+idproducto+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='left'>"+codigobarra+"</td>"+
            "<td align='left'>"+descripcion+"</td>"+
            "<td align='center'><input type='hidden' id='txtPrecioVenta"+idproducto+"' name='txtPrecioVenta"+idproducto+"' value='"+precioventa+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+idproducto+"' style='width: 80px;' name='txtPrecio"+idproducto+"' value='"+preciocompra+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idproducto+"' style='width: 80px;' id='txtTotal"+idproducto+"' value='"+preciocompra+"' /></td>"+
            "<td><a href='#' onclick=\"quitarProducto('"+idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
        carro.push(idproducto);
        $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 5 });
        calcularTotal();
    }else{
        $('#txtCantidad'+idproducto).focus();
    }
}

function agregarItem(){
    var idproducto = Math.round(Math.random()*100000);
    $("#tbDetalle").append("<tr id='tr"+idproducto+"'><td><input type='hidden' id='txtIdProducto"+idproducto+"' name='txtIdProducto"+idproducto+"' value='"+idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+idproducto+"' name='txtCantidad"+idproducto+"' value='1' size='3' /></td>"+
            "<td align='left'><textarea rows='2' cols='50' id='txtProducto"+idproducto+"' name='txtProducto"+idproducto+"' class='form-control input-xs'></textarea></td>"+
            "<td align='center'><input type='text' id='txtUnidad"+idproducto+"' name='txtUnidad"+idproducto+"' class='form-control input-xs' style='width: 80px;' /></td>"+
            "<td align='center'><input type='hidden' id='txtPrecioVenta"+idproducto+"' name='txtPrecioVenta"+idproducto+"' value='0' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+idproducto+"' style='width: 60px;' name='txtPrecio"+idproducto+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idproducto+"' style='width: 60px;' id='txtTotal"+idproducto+"' value='0' /></td>"+
            "<td><a href='#' onclick=\"quitarProducto('"+idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    carro.push(idproducto);
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 5 });
    eval("var producto"+idproducto+" = new Bloodhound({"+
		"datumTokenizer: function (d) {"+
			"return Bloodhound.tokenizers.whitespace(d.value);"+
		"},"+
		"queryTokenizer: Bloodhound.tokenizers.whitespace,"+
		"remote: {"+
			"url: 'ordencompra/productoautocompletar/%QUERY',"+
			"filter: function (producto"+idproducto+") {"+
				"return $.map(producto"+idproducto+", function (movie) {"+
					"return {"+
						"value: movie.value,"+
						"id: movie.id,"+
					"};"+
				"});"+
			"}"+
		"}"+
	"});"+
	"producto"+idproducto+".initialize();"+
	"$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id=\"txtProducto"+idproducto+"\"]').typeahead(null,{"+
		"displayKey: 'value',"+
		"source: producto"+idproducto+".ttAdapter()"+
	"}).on('typeahead:selected', function (object, datum) {"+
        "$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id=\"txtProducto"+idproducto+"\"]').val(datum.value);"+
	"});");
	
    calcularTotal();
}

function calcularTotal(){
    var total2=0;
    for(c=0; c < carro.length; c++){
        var tot=parseFloat($("#txtTotal"+carro[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    var subtotal2 = Math.round((total2/1.18)*100)/100;
    var igv2 = Math.round((total2 - subtotal2)*100)/100;
    $("#total").val(total2);
    $("#subtotal").val(subtotal2);
    $("#igv").val(igv2);
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


function agregarDetalle(id){
    $.ajax({
        type: "POST",
        url: "ordencompra/agregarDetalle",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            for(d=0;d < datos.length; d++){
                $("#tbDetalle").append("<tr id='tr"+datos[d].id+"'><td><input hidden='' id='txtIdDetalle"+datos[d].id+"' name='txtIdDetalle"+datos[d].id+"' value='"+datos[d].id+"' /><input type='hidden' id='txtIdProducto"+datos[d].id+"' name='txtIdProducto"+datos[d].id+"' value='"+datos[d].idproducto+"' /><input type='text' data='numero' style='width: 60px;' class='form-control input-xs' id='txtCantidad"+datos[d].id+"' name='txtCantidad"+datos[d].id+"' value='"+datos[d].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].id+")}\" onblur=\"calcularTotalItem("+datos[d].id+")\" /></td>"+
                "<td align='left' id='tdDescripcion"+datos[d].id+"'>"+datos[d].producto+"</td>"+
                "<td align='center'><input type='text' id='txtUnidad"+datos[d].id+"' name='txtUnidad"+datos[d].id+"' value='"+datos[d].unidad+"'  class='form-control input-xs' style='width: 80px;'/></td>"+
                "<td align='center'><input type='hidden' id='txtPrecioCompra"+datos[d].id+"' name='txtPrecioCompra"+datos[d].id+"' value='"+datos[d].precioventa+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[d].id+"' style='width: 80px;' name='txtPrecio"+datos[d].id+"' value='"+datos[d].preciocompra+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].id+")}\" onblur=\"calcularTotalItem("+datos[d].id+")\" /></td>"+
                "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[d].id+"' style='width: 80px;' id='txtTotal"+datos[d].id+"' value='"+datos[d].subtotal+"' /></td>"+
                "<td><a href='#' onclick=\"quitarProducto('"+datos[d].id+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro.push(datos[d].id);
                calcularTotalItem(datos[d].id);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 5 });

            } 
        }
    });
}

<?php
if(!is_null($movimiento)){
    echo "agregarDetalle(".$movimiento->id.");";
    $mov = Movimiento::where('movimiento_id','=',$movimiento->id)
                    ->where('tipomovimiento_id','=',8)
                    ->get();
    foreach($mov as $k=>$v){
        echo "carro1.push(".$v->id.");";
    }
}
?>

</script>