<?php
$montodetraccion = $venta->detraccion;
$porcentajedetraccion = round($venta->detraccion*100/$venta->total,2);
?>
<div id="divMensajeError{!! $entidad !!}"></div>
{!! Form::model($venta, $formData) !!}
	{!! Form::hidden('listar', $listar, array('id' => 'listar')) !!}
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
                {!! Form::label('detraccion', 'Detraccion:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::select('detraccion',$cboDetraccion, $venta->incluye, array('class' => 'form-control input-xs', 'id' => 'detraccion', 'onchange'=>'mostrarDetraccion(this.value)')) !!}
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
                @if($nota!="")
                {!! Form::label('tipo', 'Doc. Ref.:', array('class' => 'col-lg-2 col-md-2 col-sm-2 control-label')) !!}
                <div class="col-lg-2 col-md-2 col-sm-2">
                    {!! Form::text('nota', $nota, array('class' => 'form-control input-xs', 'id' => 'nota', 'readonly' => 'true')) !!}
                </div>
                @endif
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
                @foreach($detalles as $key => $value)
					<tr>
                        <td class="text-center">{!! number_format($value->cantidad,2,'.','') !!}</td>
                        <td class="text-left">{!! $value->producto2 !!}</td>
                        <td class="text-center">{!! $value->unidad !!}</td>
						<td class="text-center">{!! number_format($value->precioventa,2,'.','') !!}</td>
						<td class="text-center">{!! number_format($value->precioventa*$value->cantidad,2,'.','') !!}</td>
					</tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right" colspan="4">Subtotal</th>
                        <th class="text-center" align="center">{!! number_format($venta->subtotal,2,'.','') !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="4">IGV</th>
                        <th class="text-center" align="center">{!! number_format($venta->igv,2,'.','') !!}</th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="4">Total</th>
                        <th class="text-center" align="center">{!! number_format($venta->total,2,'.','') !!}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
     </div>
    <br>
    <div class="box">
        <div class="box-header">
            <h2 class="box-title col-lg-5 col-md-5 col-sm-5">Detalle Pago</h2>
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
					</tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right" colspan="3">Pagado</th>
                        <th class="text-center" align="center"><?=number_format($pagado,2,'.','')?></th>
                    </tr>
                    <tr style="<?php if($venta->incluye!='S') echo "display:none;";?>">
                        <th class="text-right" colspan="3">
                             <?=$venta->nrooperacion?> Pagada:<select id='entregado' name='entregado' class='input-xs' style='width: 60px;' onchange="pagoDetraccion(this.value);"><option value='N' <?php if($venta->entregado=='N') echo "selected";?>>No</option><option value='S' <?php if($venta->entregado=='S') echo "selected";?>>Si</option></select></th>
                        <th class="text-center" align="center"><?=number_format(($venta->entregado=='S'?($venta->nrooperacion=="Autodetraccion"?0:$venta->detraccion):0),2,'.','')?></th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="3">Total</th>
                        <th class="text-center" align="center"><?=number_format($venta->total,2,'.','')?></th>
                    </tr>
                    <tr>
                        <th class="text-right" colspan="3">Saldo</th>
                        <th class="text-center" align="center"><?=number_format($venta->total - $pagado /*- ($venta->entregado=='S'?$venta->detraccion:0)*/,2,'.','')?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
     </div>
    <br>
	<div class="form-group">
		<div class="col-lg-12 col-md-12 col-sm-12 text-right">	
			{!! Form::button('<i class="fa fa-exclamation fa-lg"></i> Cancelar', array('class' => 'btn btn-warning btn-sm', 'id' => 'btnCancelar'.$entidad, 'onclick' => 'cerrarModal();')) !!}
		</div>
	</div>
{!! Form::close() !!}
<script type="text/javascript">
$(document).ready(function() {
	configurarAnchoModal('900');
}); 


function mostrarDetraccion(det){
    if(det=='S'){
        $(".detraccion").css('display','');
    }else{
        $(".detraccion").css('display','none');
    }
}

<?php
echo "mostrarDetraccion('".$venta->incluye."');";
?>
</script>