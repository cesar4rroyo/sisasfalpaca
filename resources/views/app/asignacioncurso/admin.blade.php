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
								{!! Form::label('profesor', 'Profesor:') !!}
								{!! Form::select('profesor', $cboProfesor, '', array('class' => 'form-control input-xs', 'id' => 'profesor', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('especialidad', 'Especialidad:') !!}
								{!! Form::select('especialidad', $cboEspecialidad, '', array('class' => 'form-control input-xs', 'id' => 'especialidad', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
                            <div class="form-group">
								{!! Form::label('ciclo', 'Ciclo:') !!}
								{!! Form::select('ciclo', $cboCiclo, '', array('class' => 'form-control input-xs', 'id' => 'ciclo', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							<div class="form-group">
								{!! Form::label('filas', 'Filas a mostrar:')!!}
								{!! Form::selectRange('filas', 20, 40, 20, array('class' => 'form-control input-xs', 'onchange' => 'buscar(\''.$entidad.'\')')) !!}
							</div>
							{!! Form::button('<i class="glyphicon glyphicon-search"></i> Buscar', array('class' => 'btn btn-success btn-xs', 'id' => 'btnBuscar', 'onclick' => 'buscar(\''.$entidad.'\')')) !!}
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
		$(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="nombre"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
        $(IDFORMBUSQUEDA + '{{ $entidad }} :input[id="codigo"]').keyup(function (e) {
			var key = window.event ? e.keyCode : e.which;
			if (key == '13') {
				buscar('{{ $entidad }}');
			}
		});
	});
    function asignarCurso(check,id,idcurso,idprofesor,idseccion){
        $.ajax({
	        type: "POST",
	        url: "asignacioncurso/asignar",
	        data: "check="+check+"&id="+id+"&curso_id="+idcurso+"&profesor_id="+idprofesor+"&seccion_id="+idseccion+"&_token="+$(IDFORMBUSQUEDA + '{!! $entidad !!} :input[name="_token"]').val(),
	        success: function(a) {
	            if(a!='OK'){
	            	alert('Error guardando asignacion');
	            }
	        },
            error: function (request, status, error) {
                alert("No se guardo la asignacion, intente de nuevo");
            }
    	});
    }
</script>