<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		{{ $title }}
	</h1>
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
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 1, 30, 10, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
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
	});

    function imprimir(){
        window.open("caja/pdfCierre","_blank");
    }

    function imprimirDetalle(){
        window.open("caja/pdfDetalleCierre","_blank");
    }
    
    function modalCaja (controlador, titulo) {
    	var idContenedor = "divModal" + contadorModal;
    	var divmodal     = "<div id=\"" + idContenedor + "\"></div>";
    	var box          = bootbox.dialog({
    		message: divmodal,
    		className: 'modal' +  contadorModal,
    		title: titulo,
    		closeButton: false
    	});
    	box.prop('id', 'modal'+contadorModal);
    	/*$('#modal'+contadorModal).draggable({
    		handle: ".modal-header"
    	});*/
    	modales[contadorModal] = box;
    	contadorModal          = contadorModal + 1;
    	setTimeout(function(){
    		cargarRuta(controlador+"&saldo="+$( '#saldo').val(), idContenedor);
    	},400);
    }

</script>