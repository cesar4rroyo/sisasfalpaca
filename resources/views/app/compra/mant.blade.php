<?php
use Illuminate\Support\Facades\Storage;

if(!is_null($movimiento)){
    $fecha = $movimiento->fecha;
    $fecha2 = $movimiento->fechavencimiento;
    $tipodocumento = $movimiento->tipodocumento_id;
    $detraccion = $movimiento->incluye;
    $proveedor_id = $movimiento->persona_id;
    if(!is_null($movimiento->persona)){
        $proveedor = trim($movimiento->persona->razonsocial.' '.$movimiento->persona->apellidopaterno.' '.$movimiento->persona->apellidomaterno.' '.$movimiento->persona->nombres);
    }else{
        $proveedor = "";
    }
    $montodetraccion = $movimiento->detraccion;
    $porcentajedetraccion = round($movimiento->detraccion*100/($movimiento->total>0?$movimiento->total:1),2);
    if($movimiento->movimiento_id>0){
        $movimiento_id = $movimiento->movimiento_id;
        $orden = $movimiento->movimientoref->numero;
    }else{
        $movimiento_id= '';
        $orden = '';
    }
}else{
    $fecha = date("Y-m-d");
    $fecha2 = date("Y-m-d");
    $tipodocumento = null;
    $detraccion = null;
    $proveedor = null;
    $proveedor_id = 0;
    $montodetraccion = 0;
    $porcentajedetraccion = null;
    $movimiento_id= '';
    $orden = '';
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
    {!! Form::hidden('listPago', null, array('id' => 'listPago')) !!}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
        		{!! Form::label('fecha', 'Fecha:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::date('fecha', $fecha, array('class' => 'form-control input-xs', 'id' => 'fecha')) !!}
        		</div>
                {!! Form::label('tipodocumento', 'Tipo Doc.:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::select('tipodocumento',$cboTipoDocumento, $tipodocumento, array('class' => 'form-control input-xs', 'id' => 'tipodocumento')) !!}
        		</div>
                {!! Form::label('numero', 'Nro:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('numero', null, array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
        		</div>
        	</div>
            <div class="form-group">
        		{!! Form::label('persona', 'Proveedor:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-9 col-md-9 col-sm-9">
                {!! Form::hidden('persona_id', $proveedor_id, array('id' => 'persona_id')) !!}
                {!! Form::hidden('ruc', '', array('id' => 'ruc')) !!}
        		{!! Form::text('persona', $proveedor, array('class' => 'form-control input-xs', 'id' => 'persona', 'placeholder' => 'Ingrese Proveedor')) !!}
        		</div>
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::button('<i class="fa fa-file fa-lg"></i>', array('class' => 'btn btn-info btn-xs', 'onclick' => 'modal (\''.URL::route('persona.create', array('listar'=>'SI','modo'=>'popup')).'\', \'Nueva Historia\', this);', 'title' => 'Nueva Persona')) !!}
        		</div>
        	</div>
        	<div class="form-group">
        	    {!! Form::label('archivo', 'Archivo:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::file('archivo', null, array('class' => 'form-control input-xs', 'id' => 'archivo')) !!}
        		</div>
        	</div>
        	<?php if(!is_null($movimiento)){ ?>
        	<div class="form-group">
        	    {!! Form::label('listaarchivos', 'Lista:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        	    <div class="col-lg-10 col-md-10 col-sm-10">
        	        <table  class="table table-bordered table-striped table-condensed table-hover">
        	            <thead>
        	                <tr>
        	                    <th>#</th>
        	                    <th>Archivo</th>
        	                </tr>
        	            </thead>
        	            <tbody>
        	                <?php
        	                $files = \Storage::files('/'.$movimiento->id);
        	                $xx=0;
        	                foreach($files as $k){$xx=$xx+1;
                                //$url = Storage::get($k)->url();
                                //$url = \Storage::publicUrl('/'.$movimiento->id."/".$k);
                                $k = str_replace("/", "-", $k);
        	                    echo "<tr>";
        	                    echo "<td>$xx</td>";
        	                    echo "<td><a href='javascript:void();' onclick='window.open(\"compra/".$k."\",\"_blank\")'>$k</a></td>";
                                //echo "<td><a href='javascript:void();' onclick='window.open(\"".$url."\",\"_blank\")'>$k</a></td>";
        	                    echo "</tr>";
        	                }
        	                ?>
        	            </tbody>
        	        </table>
        	    </div>
        	</div>
        	<?php } ?>
        	<div class="form-group">
        	    {!! Form::label('orden', 'Orden Ref:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
        		<div class="col-lg-3 col-md-3 col-sm-3">
        			{!! Form::text('orden', $orden, array('class' => 'form-control input-xs', 'id' => 'orden')) !!}
        			{!! Form::hidden('movimiento_id', $movimiento_id, array('class' => 'form-control input-xs', 'id' => 'movimiento_id')) !!}
        		</div>
                {!! Form::label('almacen_id', 'Almacen:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('almacen_id',$cboAlmacen, null, array('class' => 'form-control input-xs', 'id' => 'almacen_id')) !!}
                </div>
        	</div>
        	<div class="form-group">
        		<div class="col-lg-12 col-md-12 col-sm-12 text-right">
        			{!! Form::button('<i class="fa fa-check fa-lg"></i> '.$boton, array('class' => 'btn btn-success btn-sm', 'id' => 'btnGuardar', 'onclick' => '$(\'#listProducto\').val(carro);$(\'#listPago\').val(carroDoc);guardarPago(\''.$entidad.'\', this);')) !!}
        			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
        		</div>
        	</div>
         </div>
         <div class="col-lg-6 col-md-6 col-sm-6" >
            <div class="form-group">
                {!! Form::label('fechavencimiento', 'Fecha Venc.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-1 col-md-1 col-sm-1">
                    {!! Form::text('dias',null, array('class' => 'form-control input-xs', 'id' => 'dias', 'onkeyup' => 'calcularFecha(this.value)')) !!}
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::date('fechavencimiento', $fecha2, array('class' => 'form-control input-xs', 'id' => 'fechavencimiento')) !!}
                </div>
                {!! Form::label('formapago', 'Forma Pago:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('formapago',$cboFormaPago, null, array('class' => 'form-control input-xs', 'id' => 'formapago')) !!}
                </div>
                {!! Form::label('moneda', 'Moneda:', array('class' => 'col-lg-1 col-md-1 col-sm-1 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('moneda',$cboMoneda, null, array('class' => 'form-control input-xs', 'id' => 'moneda')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('detraccion', 'Detraccion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::select('detraccion',$cboDetraccion, $detraccion, array('class' => 'form-control input-xs', 'id' => 'detraccion', 'onchange'=>'mostrarDetraccion(this.value)')) !!}
                </div>
                {!! Form::label('comentario', 'Maquinaria:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-5 col-md-5 col-sm-5">
                    {!! Form::textarea('comentario', null, array('class' => 'form-control input-xs', 'id' => 'comentario', 'rows' => '3')) !!}
                </div>
            </div>
            <div class="form-group detraccion" style='display:'>
                {!! Form::label('montodetraccion', '% :', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('porcentaje',$porcentajedetraccion, array('class' => 'form-control input-xs', 'id' => 'porcentaje', 'onkeyup' => 'calcularDetraccion();')) !!}
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    {!! Form::text('montodetraccion',$montodetraccion, array('class' => 'form-control input-xs', 'id' => 'montodetraccion', 'readonly' => 'true')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('pagos', 'Pagos:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-8 col-md-8 col-sm-8">
                    <table id="tbPago" class="table table-condensed table-border">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Forma Pago</th>
                                <th><?php if($current_user->usertype!="7" && $current_user->usertype!="8"){ ?><a href='#' onclick='agregarPago();'><i class='fa fa-plus-circle' title='Agregar' width='20px' height='20px'></i></a><?php }?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $js2="";$readonly="";
                        if($current_user->usertype!="7"){
                            $readonly="readonly=''";
                        }
                        if(!is_null($movimiento) && trim($movimiento->listapago)!=""){
                            $lp = explode("|",$movimiento->listapago);
                            for($x=0;$x<count($lp);$x++){
                                if($lp[$x]!=""){
                                    $idpago=rand(100000,999999);
                                    $ld = explode("@",$lp[$x]);
                                    echo "<tr id='trP".$idpago."'>";
                                    echo "<td><input type='date' $readonly class='form-control input-xs' id='txtFechaP".$idpago."' name='txtFechaP".$idpago."' value='".$ld[0]."' /></td>";
                                    echo "<td align='center'><input type='text' $readonly size='5' class='form-control input-xs' data='numero' id='txtPago".$idpago."' style='width: 60px;' name='txtPago".$idpago."' value='".$ld[1]."' onkeydown=\"if(event.keyCode==13){calcularPago(".$idpago.")}\" onblur=\"calcularPago(".$idpago.")\" /></td>";
                                    echo "<td align='center'><input type='text' $readonly class='form-control input-xs' name='txtForma".$idpago."' id='txtForma".$idpago."' value='".$ld[2]."' /></td>";
                                    if($current_user->usertype!="7"){
                                        echo "<td><a href='#' onclick=\"quitarPago('".$idpago."')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>";
                                    }
                                    echo "</tr>";
                                    $js2.="carroDoc.push(".$idpago.");";
                                }
                            }
                        }
                        ?>                            
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Pagado:</th>
                                <th>{!! Form::text('totalpagado', null, array('class' => 'input-xs', 'id' => 'totalpagado', 'size' => 3, 'style' => 'width: 60px;', 'readonly' => 'true')) !!}</th>
                            </tr>
                        </tfoot>
                    </table>
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
            <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Detalle <button class="btn btn-info btn-xs" onclick="agregarItem();"><i class="fa fa-plus"></i></button></h2>
        </div>
        <div class="box-body">
            <table class="table table-condensed table-border" id="tbDetalle">
                <thead>
                    <th class="text-center">Cant.</th>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Valo Venta</th>
                    <th class="text-center">IGV</th>
                    <th class="text-center">P. Unit.</th>
                    <th class="text-center">Subtotal</th>
                </thead>
                <tbody>
                    <?php
                    if(!is_null($movimiento)){
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
                        <th class="text-right" colspan="5">Valor Venta</th>
                        <th class="text-center" align="center">{!! Form::text('subtotal', null, array('class' => 'input-xs', 'id' => 'subtotal', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="5">IGV</th>
                        <th class="text-center" align="center">{!! Form::text('igv', null, array('class' => 'input-xs', 'id' => 'igv', 'size' => 3, 'readonly' => 'true', 'style' => 'width: 60px;')) !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="5">Total</th>
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
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="total"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="subtotal"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="igv"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="totalpagado"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="montodetraccion"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    $(IDFORMMANTENIMIENTO + '{{ $entidad }} :input[id="porcentaje"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });

    var personas2 = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'compras/personautocompletar/%QUERY',
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
		$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="ruc"]').val(datum.ruc);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona_id"]').val(datum.id);
	});
	
	var contrato = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'compra/ordenautocompletar/%QUERY',
            filter: function (contrato2) {
                return $.map(contrato2, function (movie) {
                    return {
                        value: movie.value,
                        id: movie.id,
                        persona: movie.persona,
                        persona_id: movie.persona_id,
                        maquinaria: movie.maquinaria,
                        moneda: movie.moneda
                    };
                });
            }
        }
    });
    contrato.initialize();
    $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="orden"]').typeahead(null,{
        displayKey: 'value',
        source: contrato.ttAdapter()
    }).on('typeahead:selected', function (object, datum) {
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="orden"]').val(datum.value);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="comentario"]').val(datum.maquinaria);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="movimiento_id"]').val(datum.id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona"]').val(datum.persona);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="persona_id"]').val(datum.persona_id);
        $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[id="moneda"]').val(datum.moneda);
        agregarDetalle(datum.id);
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
    if($("#totalpagado").val()=="") $("#totalpagado").val(0);
    if($("#persona_id").val()==""){
        band = false;
        msg += " *No se selecciono un proveedor \n";    
    }
    var total = Math.round((parseFloat($("#total").val()))*100)/100;
    var totalpagado = Math.round((parseFloat($("#totalpagado").val()))*100)/100;
    if(totalpagado>total){
        band = false;
        msg += " *Total pagado no debe superar al total del comprobante \n";
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
    			    enviarArchivo(dat[0].compra_id);
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
		url: '{{ url("/compras/archivos") }}',
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
        var sub = Math.round((preciocompra/1.18)*100)/100;
        var igv = Math.round((preciocompra - igv)*100)/100;
        $("#tbDetalle").append("<tr id='tr"+idproducto+"'><td><input type='hidden' id='txtIdProducto"+idproducto+"' name='txtIdProducto"+idproducto+"' value='"+idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+idproducto+"' name='txtCantidad"+idproducto+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='left'>"+codigobarra+"</td>"+
            "<td align='left'>"+descripcion+"</td>"+
            "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtSubtotal"+idproducto+"' style='width: 60px;' name='txtSubtotal"+idproducto+"' value='"+sub+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" readonly='' /></td>"+
            "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtIGV"+idproducto+"' style='width: 60px;' name='txtIGV"+idproducto+"' value='"+igv+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" readonly='' /></td>"+
            "<td align='center'><input type='hidden' id='txtPrecioVenta"+idproducto+"' name='txtPrecioVenta"+idproducto+"' value='"+precioventa+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+idproducto+"' style='width: 60px;' name='txtPrecio"+idproducto+"' value='"+preciocompra+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idproducto+"' style='width: 60px;' id='txtTotal"+idproducto+"' value='"+preciocompra+"' /></td>"+
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
    $("#tbDetalle").append("<tr id='tr"+idproducto+"'><td><input type='hidden' id='txtIdProducto"+idproducto+"' name='txtIdProducto"+idproducto+"' value='"+idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+idproducto+"' name='txtCantidad"+idproducto+"' value='1' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='left'><textarea rows='2' cols='50' id='txtProducto"+idproducto+"' name='txtProducto"+idproducto+"' class='form-control input-xs'></textarea></td>"+
            "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtSubtotal"+idproducto+"' style='width: 60px;' name='txtSubtotal"+idproducto+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" readonly='' /></td>"+
            "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtIGV"+idproducto+"' style='width: 60px;' name='txtIGV"+idproducto+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" readonly='' /></td>"+
            "<td align='center'><input type='hidden' id='txtPrecioVenta"+idproducto+"' name='txtPrecioVenta"+idproducto+"' value='0' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+idproducto+"' style='width: 60px;' name='txtPrecio"+idproducto+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+idproducto+")}\" onblur=\"calcularTotalItem("+idproducto+")\" /></td>"+
            "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+idproducto+"' style='width: 60px;' id='txtTotal"+idproducto+"' value='0' /></td>"+
            "<td><a href='#' onclick=\"quitarProducto('"+idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    carro.push(idproducto);
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 5 });
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
    var subtotal = Math.round((total/1.18)*100)/100;
    var igv = Math.round((total - subtotal)*100)/100;
    $("#txtTotal"+id).val(total);
    $("#txtSubtotal"+id).val(subtotal);
    $("#txtIGV"+id).val(igv);
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
        url: "compra/agregarDetalle",
        data: "id="+id+"&_token="+$(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="_token"]').val(),
        success: function(a) {
            datos=JSON.parse(a);
            for(d=0;d < datos.length; d++){
                $("#tbDetalle").append("<tr id='tr"+datos[d].idproducto+"'><td><input type='hidden' id='txtIdProducto"+datos[d].idproducto+"' name='txtIdProducto"+datos[d].idproducto+"' value='"+datos[d].idproducto+"' /><input type='text' data='numero' style='width: 40px;' class='form-control input-xs' id='txtCantidad"+datos[d].idproducto+"' name='txtCantidad"+datos[d].idproducto+"' value='"+datos[d].cantidad+"' size='3' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].idproducto+")}\" onblur=\"calcularTotalItem("+datos[d].idproducto+")\" /></td>"+
                "<td align='left' id='tdDescripcion"+datos[d].idproducto+"'><textarea rows='2' cols='50' id='txtProducto"+datos[d].idproducto+"' name='txtProducto"+datos[d].idproducto+"' class='form-control input-xs'>"+datos[d].producto+"</textarea></td>"+
                "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtSubtotal"+datos[d].idproducto+"' style='width: 60px;' name='txtSubtotal"+datos[d].idproducto+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].idproducto+")}\" onblur=\"calcularTotalItem("+datos[d].idproducto+")\" readonly='' /></td>"+
                "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtIGV"+datos[d].idproducto+"' style='width: 60px;' name='txtIGV"+datos[d].idproducto+"' value='0' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].idproducto+")}\" onblur=\"calcularTotalItem("+datos[d].idproducto+")\" readonly='' /></td>"+
                "<td align='center'><input type='hidden' id='txtPrecioVenta"+datos[d].idproducto+"' name='txtPrecioVenta"+datos[d].idproducto+"' value='"+datos[d].preciocompra+"' /><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPrecio"+datos[d].idproducto+"' style='width: 60px;' name='txtPrecio"+datos[d].idproducto+"' value='"+datos[d].preciocompra+"' onkeydown=\"if(event.keyCode==13){calcularTotalItem("+datos[d].idproducto+")}\" onblur=\"calcularTotalItem("+datos[d].idproducto+")\" /></td>"+
                "<td align='center'><input type='text' readonly='' data='numero' class='form-control input-xs' size='5' name='txtTotal"+datos[d].idproducto+"' style='width: 60px;' id='txtTotal"+datos[d].idproducto+"' value='"+datos[d].subtotal+"' /></td>"+
                "<td><a href='#' onclick=\"quitarProducto('"+datos[d].idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
                carro.push(datos[d].idproducto);
                calcularTotalItem(datos[d].idproducto);
                $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 5 });

            } 
        }
    });
}

function agregarPago(){
    var idproducto = Math.round(Math.random()*100000);
    $("#tbPago").append("<tr id='trP"+idproducto+"'><td><input type='date' class='form-control input-xs' id='txtFechaP"+idproducto+"' name='txtFechaP"+idproducto+"' /></td>"+
            "<td align='center'><input type='text' size='5' class='form-control input-xs' data='numero' id='txtPago"+idproducto+"' style='width: 60px;' name='txtPago"+idproducto+"' value='0' onkeydown=\"if(event.keyCode==13){calcularPago("+idproducto+")}\" onblur=\"calcularPago("+idproducto+")\" /></td>"+
            "<td align='center'><input type='text' class='form-control input-xs' name='txtForma"+idproducto+"' id='txtForma"+idproducto+"' /></td>"+
            "<td><a href='#' onclick=\"quitarPago('"+idproducto+"')\"><i class='fa fa-minus-circle' title='Quitar' width='20px' height='20px'></i></td></tr>");
    carroDoc.push(idproducto);
    $(':input[data="numero"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
    calcularPago();
}

function quitarPago(id){
    $("#trP"+id).remove();
    for(c=0; c < carroDoc.length; c++){
        if(carroDoc[c] == id) {
            carroDoc.splice(c,1);
        }
    }
    calcularPago();
}

function calcularPago(){
    var total2=0;
    for(c=0; c < carroDoc.length; c++){
        var tot=parseFloat($("#txtPago"+carroDoc[c]).val());
        total2=Math.round((total2+tot) * 100) / 100;        
    }
    $("#totalpagado").val(total2);
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

function calcularFecha(valor){
    var aFecha1 = $(IDFORMMANTENIMIENTO + '{!! $entidad !!} :input[name="fecha"]').val();
    aFecha1 = aFecha1.split('-');
    var fecha1 = new Date();
    fecha1.setFullYear(aFecha1[0],parseInt(aFecha1[1])-1,aFecha1[2]);
    fecha1.setDate(fecha1.getDate() + parseInt(valor));
    if((fecha1.getUTCMonth()+1)<10){
        var mes = "0"+(fecha1.getUTCMonth()+1);
    }else{
        var mes = (fecha1.getUTCMonth()+1);
    }
    if(fecha1.getUTCDate()<10){
        var dia = "0"+fecha1.getUTCDate();
    }else{
        var dia = fecha1.getUTCDate();
    }
    $("#fechavencimiento").val(fecha1.getUTCFullYear()+"-"+mes+"-"+dia);
}
<?php
if(!is_null($movimiento)){
    //echo "agregarDetalle(".$movimiento->id.");";
    echo "mostrarDetraccion('".$movimiento->incluye."');";
    echo $js;echo $js2;
    echo "$(':input[data=\"numero\"]').inputmask('decimal', { radixPoint: \".\", autoGroup: true, groupSeparator: \"\", groupSize: 3, digits: 2 });";
}
?>

</script>