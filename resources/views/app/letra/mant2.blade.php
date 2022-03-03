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
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {!! Form::label('numero', 'Nro Doc. Ref.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-10 col-md-10 col-sm-10">
        			{!! Form::text('numero', $letra->movimientoref->numero, array('class' => 'form-control input-xs', 'id' => 'numero', 'readonly' => 'true')) !!}
        			{!! Form::hidden('movimiento_id', $letra->movimiento_id, array('class' => 'form-control input-xs', 'id' => 'movimiento_id')) !!}
        			{!! Form::hidden('person_id', $letra->persona_id, array('class' => 'form-control input-xs', 'id' => 'person_id')) !!}
        		</div>
        	</div>
        	<div class="form-group">
        	    {!! Form::label('banco', 'Banco:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('banco',$cboBanco, $letra->banco_id, array('class' => 'form-control input-xs', 'id' => 'banco')) !!}
                </div>
                {!! Form::label('moneda', 'Moneda:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('moneda',$cboMoneda, $letra->moneda, array('class' => 'form-control input-xs', 'id' => 'moneda')) !!}
                </div>
                {!! Form::label('numeroref', 'Codigo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('numeroref',$letra->comentario, array('class' => 'form-control input-xs', 'id' => 'numeroref')) !!}
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
            <div class="form-group">
                {!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', $letra->fecha, array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
        		{!! Form::label('cantidad', 'Nro:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('cantidad', $letra->numero, array('class' => 'form-control input-xs', 'id' => 'cantidad')) !!}
        		</div>
        	</div>
        	<div class="form-group">
                {!! Form::label('total', 'Total:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('total', $letra->total, array('class' => 'form-control input-xs', 'id' => 'total')) !!}
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
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });

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

</script>