<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
		{{-- <small>Descripción</small> --}}
	</h1>
	{{--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Tables</a></li>
		<li class="active">Data tables</li>
	</ol>
	--}}
</section>

<!-- Main content -->
<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header">
					<div class="row">
						<div class="col-xs-12">
							{!! Form::open(['route' => $ruta["search"], 'method' => 'POST' ,'onsubmit' => 'return false;', 'class' => 'form-inline', 'role' => 'form', 'autocomplete' => 'off', 'id' => 'formBusqueda'.$entidad]) !!}
							{!! Form::hidden('page', 1, array('id' => 'page')) !!}
							{!! Form::hidden('accion', 'listar', array('id' => 'accion')) !!}
							<div class="form-group">
								{!! Form::label('almacen', 'Almacen:') !!}
								{!! Form::select('almacen', $cboAlmacen, '', array('class' => 'form-control input-xs', 'id' => 'almacen')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechainicio', 'Fecha inicio:') !!}
								{!! Form::date('fechainicio', date('Y-m-').'01', array('class' => 'form-control input-xs', 'id' => 'fechainicio')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafin', 'Fecha fin:') !!}
								{!! Form::date('fechafin', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechafin')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('proveedor', 'Proveedor:') !!}
								{!! Form::text('proveedor', '', array('class' => 'form-control input-xs', 'id' => 'proveedor')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('tipodocumento', 'Tipo Doc.:') !!}
								{!! Form::select('tipodocumento', $cboTipoDocumento, '', array('class' => 'form-control input-xs', 'id' => 'tipodocumento')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion, '', array('class' => 'form-control input-xs', 'id' => 'situacion', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('modo', 'Monto:') !!}
								{!! Form::select('modo', $cboModo, '', array('class' => 'form-control input-xs', 'id' => 'modo', 'onchange' => 'comboMonto(this.value)')) !!}
								{!! Form::text('monto', '', array('class' => 'form-control input-xs monto', 'id' => 'monto','style'=>'display:none;width:50px;')) !!}
							</div>
							
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 10, 40, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-info btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-success btn-xs', 'id' => 'btnExcel', 'onclick' => 'excel(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Pdf Detallado', array('class' => 'btn btn-info btn-xs', 'id' => 'btnPdf', 'onclick' => 'pdf(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Pdf Corte', array('class' => 'btn btn-info btn-xs', 'id' => 'btnPdf', 'onclick' => 'pdfCorte(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Pdf Resumen', array('class' => 'btn btn-info btn-xs', 'id' => 'btnPdf2', 'onclick' => 'pdf2(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Pdf Resumen Corte', array('class' => 'btn btn-info btn-xs', 'id' => 'btnPdf2', 'onclick' => 'pdfCorte2(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Pdf Pagos', array('class' => 'btn btn-info btn-xs', 'id' => 'btnPdf5', 'onclick' => 'pdfPago(\''.$entidad.'\')')) !!}
							{!! Form::close() !!}
						</div>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body" id="listado{{ $entidad }}">
				</div>
				<!-- /.box-body -->
			</div>
			<!-- /.box -->
		</div>
		<!-- /.col -->
	</div>
	<!-- /.row -->
</section>
<!-- /.content -->	
<script>
	$(document).ready(function () {
		buscar('{{ $entidad }}');
		init(IDFORMBUSQUEDA+'{{ $entidad }}', 'B', '{{ $entidad }}');
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="proveedor"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
        $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="numero"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
		$(':input[id="monto"]').inputmask('decimal', { radixPoint: ".", autoGroup: true, groupSeparator: "", groupSize: 3, digits: 2 });
	});
	function excel(entidad){
        window.open("cuentasporpagar/excel?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&almacen="+$("#almacen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function pdf(entidad){
        window.open("cuentasporpagar/pdf?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&almacen="+$("#almacen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function pdfCorte(entidad){
        window.open("cuentasporpagar/pdfCorte?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&almacen="+$("#almacen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function pdf2(entidad){
        window.open("cuentasporpagar/pdf2?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&almacen="+$("#almacen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function pdfCorte2(entidad){
        window.open("cuentasporpagar/pdfCorte2?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&almacen="+$("#almacen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function pdfPago(entidad){
        window.open("cuentasporpagar/pdfPago?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&almacen="+$("#almacen").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function comboMonto(cbo){
	    if(cbo!=""){
	        $(".monto").css("display","");
	    }else{
	        $(".monto").css("display","none");
	    }
	}
</script>