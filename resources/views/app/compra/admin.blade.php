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
								{!! Form::date('fechainicio', date('Y-m-d'), array('class' => 'form-control input-xs', 'id' => 'fechainicio')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('fechafin', 'Fecha fin:') !!}
								{!! Form::date('fechafin', '', array('class' => 'form-control input-xs', 'id' => 'fechafin')) !!}
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
								{!! Form::label('numero', 'Nro:') !!}
								{!! Form::text('numero', '', array('class' => 'form-control input-xs', 'id' => 'numero')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('situacion', 'Situacion:') !!}
								{!! Form::select('situacion', $cboSituacion, '', array('class' => 'form-control input-xs', 'id' => 'situacion')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-plus"></i> Nuevo', array('class' => 'btn btn-info btn-xs', 'id' => 'btnNuevo', 'onclick' => 'modal (\''.URL::route($ruta["create"], array('listar'=>'SI')).'\', \''.$titulo_registrar.'\', this);')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Pdf', array('class' => 'btn btn-info btn-xs', 'id' => 'btnPdf5', 'onclick' => 'pdf(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Resumen Pdf', array('class' => 'btn btn-warning btn-xs', 'id' => 'btnPdf', 'onclick' => 'pdfResumen(\''.$entidad.'\')')) !!}
							{!! Form::button('<i class="glyphicon glyphicon-file"></i> Excel', array('class' => 'btn btn-danger btn-xs','style'=>'display:','id' => 'btnPdf', 'onclick' => 'excel(\''.$entidad.'\')')) !!}
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
	});
	function pdf(entidad){
        window.open("compra/pdf?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function excel(entidad){
        window.open("compra/excel?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
	function pdfResumen(entidad){
        window.open("compra/pdfResumen?fechainicio="+$('#fechainicio').val()+"&fechafin="+$("#fechafin").val()+"&proveedor="+$("#proveedor").val()+"&tipodocumento="+$("#tipodocumento").val()+"&situacion="+$("#situacion").val()+"&modo="+$("#modo").val()+"&monto="+$("#monto").val()+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),"_blank");
	}
</script>