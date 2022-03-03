<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', ['as' =>'auth/login', 'uses' => 'Auth\AuthController@postLogin']);
Route::get('auth/logout', ['as' => 'auth/logout', 'uses' => 'Auth\AuthController@getLogout']);
 
// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', ['as' => 'auth/register', 'uses' => 'Auth\AuthController@postRegister']);

Route::get('/', function(){
    return redirect('/dashboard');
});

Route::get('/vistamedico', function(){
    return View::make('app.producto.vistamedico');
});


Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', function(){
        return View::make('layouts.app');
    });

    Route::post('categoriaopcionmenu/buscar', 'CategoriaopcionmenuController@buscar')->name('categoriaopcionmenu.buscar');
    Route::get('categoriaopcionmenu/eliminar/{id}/{listarluego}', 'CategoriaopcionmenuController@eliminar')->name('categoriaopcionmenu.eliminar');
    Route::resource('categoriaopcionmenu', 'CategoriaopcionmenuController', array('except' => array('show')));

    Route::post('opcionmenu/buscar', 'OpcionmenuController@buscar')->name('opcionmenu.buscar');
    Route::get('opcionmenu/eliminar/{id}/{listarluego}', 'OpcionmenuController@eliminar')->name('opcionmenu.eliminar');
    Route::resource('opcionmenu', 'OpcionmenuController', array('except' => array('show')));

    Route::post('tipousuario/buscar', 'TipousuarioController@buscar')->name('tipousuario.buscar');
    Route::get('tipousuario/obtenerpermisos/{listar}/{id}', 'TipousuarioController@obtenerpermisos')->name('tipousuario.obtenerpermisos');
    Route::post('tipousuario/guardarpermisos/{id}', 'TipousuarioController@guardarpermisos')->name('tipousuario.guardarpermisos');
    Route::get('tipousuario/eliminar/{id}/{listarluego}', 'TipousuarioController@eliminar')->name('tipousuario.eliminar');
    Route::resource('tipousuario', 'TipousuarioController', array('except' => array('show')));

    Route::post('usuario/buscar', 'UsuarioController@buscar')->name('usuario.buscar');
    Route::get('usuario/eliminar/{id}/{listarluego}', 'UsuarioController@eliminar')->name('usuario.eliminar');
    Route::resource('usuario', 'UsuarioController', array('except' => array('show')));

    /* CATEGORIA */
    Route::post('categoria/buscar', 'CategoriaController@buscar')->name('categoria.buscar');
    Route::get('categoria/eliminar/{id}/{listarluego}', 'CategoriaController@eliminar')->name('categoria.eliminar');
    Route::resource('categoria', 'CategoriaController', array('except' => array('show')));
    
    /* UNIDAD */
    Route::post('unidad/buscar', 'UnidadController@buscar')->name('unidad.buscar');
    Route::get('unidad/eliminar/{id}/{listarluego}', 'UnidadController@eliminar')->name('unidad.eliminar');
    Route::resource('unidad', 'UnidadController', array('except' => array('show')));
    
    /* MARCA */
    Route::post('marca/buscar', 'MarcaController@buscar')->name('marca.buscar');
    Route::get('marca/eliminar/{id}/{listarluego}', 'MarcaController@eliminar')->name('marca.eliminar');
    Route::resource('marca', 'MarcaController', array('except' => array('show')));
    
    /* TIPO MAQUINARIA */
    Route::post('tipomaquinaria/buscar', 'TipomaquinariaController@buscar')->name('tipomaquinaria.buscar');
    Route::get('tipomaquinaria/eliminar/{id}/{listarluego}', 'TipomaquinariaController@eliminar')->name('tipomaquinaria.eliminar');
    Route::resource('tipomaquinaria', 'TipomaquinariaController', array('except' => array('show')));
    
    /* MAQUINARIA */
    Route::post('maquinaria/buscar', 'MaquinariaController@buscar')->name('maquinaria.buscar');
    Route::get('maquinaria/eliminar/{id}/{listarluego}', 'MaquinariaController@eliminar')->name('maquinaria.eliminar');
    Route::resource('maquinaria', 'MaquinariaController', array('except' => array('show')));
    Route::post('maquinaria/archivos','MaquinariaController@archivos')->name('maquinaria.archivos');
    Route::get('maquinaria/pdf', 'MaquinariaController@pdf')->name('maquinaria.pdf');
    
    /* OBRA */
    Route::post('obra/buscar', 'ObraController@buscar')->name('obra.buscar');
    Route::get('obra/eliminar/{id}/{listarluego}', 'ObraController@eliminar')->name('obra.eliminar');
    Route::resource('obra', 'ObraController', array('except' => array('show')));
    
    /* TIPO CAMBIO */
    Route::post('tipocambio/buscar', 'TipocambioController@buscar')->name('tipocambio.buscar');
    Route::get('tipocambio/eliminar/{id}/{listarluego}', 'TipocambioController@eliminar')->name('tipocambio.eliminar');
    Route::resource('tipocambio', 'TipocambioController', array('except' => array('show')));

    /* TIPO DOCUMENTO */
    Route::post('tipodocumento/buscar', 'TipodocumentoController@buscar')->name('tipodocumento.buscar');
    Route::get('tipodocumento/eliminar/{id}/{listarluego}', 'TipodocumentoController@eliminar')->name('tipodocumento.eliminar');
    Route::resource('tipodocumento', 'TipodocumentoController', array('except' => array('show')));


    /* PERSONA */
    Route::post('persona/buscar', 'PersonaController@buscar')->name('persona.buscar');
    Route::get('persona/eliminar/{id}/{listarluego}', 'PersonaController@eliminar')->name('persona.eliminar');
    Route::resource('persona', 'PersonaController', array('except' => array('show')));

    /* CONCEPTOPAGO */
    Route::post('concepto/buscar', 'ConceptoController@buscar')->name('concepto.buscar');
    Route::get('concepto/eliminar/{id}/{listarluego}', 'ConceptoController@eliminar')->name('concepto.eliminar');
    Route::resource('concepto', 'ConceptoController', array('except' => array('show')));

    /* CAJA */
    Route::post('caja/buscar', 'CajaController@buscar')->name('caja.buscar');
    Route::post('caja/buscarcontrol', 'CajaController@buscarControl')->name('caja.buscarcontrol');
    Route::get('caja/eliminar/{id}/{listarluego}', 'CajaController@eliminar')->name('caja.eliminar');
    Route::get('caja/edit2', 'CajaController@edit2')->name('caja.edit2');
    Route::post('caja/editarapertura', 'CajaController@editarapertura')->name('caja.editarapertura');
    Route::resource('caja', 'CajaController', array('except' => array('show')));
    Route::get('caja/apertura', 'CajaController@apertura')->name('caja.apertura');
    Route::post('caja/aperturar', 'CajaController@aperturar')->name('caja.aperturar');
    Route::get('caja/cierre', 'CajaController@cierre')->name('caja.cierre');
    Route::post('caja/cerrar', 'CajaController@cerrar')->name('caja.cerrar');
    Route::post('caja/generarConcepto', 'CajaController@generarConcepto')->name('caja.generarconcepto');
    Route::post('caja/generarNumero', 'CajaController@generarNumero')->name('caja.generarnumero');
    Route::get('caja/personautocompletar/{searching}', 'CajaController@personautocompletar')->name('caja.personautocompletar');
    Route::get('caja/pdfCierre', 'CajaController@pdfCierre')->name('caja.pdfCierre');
    Route::get('caja/pdfDetalleCierre', 'CajaController@pdfDetalleCierre')->name('caja.pdfDetalleCierre');
    Route::get('caja/pdfDetalleCierreF', 'CajaController@pdfDetalleCierreF')->name('caja.pdfDetalleCierreF');

    /* VENTA */
    Route::post('venta/buscar', 'VentaController@buscar')->name('venta.buscar');
    Route::get('venta/eliminar/{id}/{listarluego}', 'VentaController@eliminar')->name('venta.eliminar');
    Route::resource('venta', 'VentaController', array('except' => array('show')));
    Route::get('venta/show2/{listarluego}/{id}', 'VentaController@show2')->name('venta.show2');
    Route::post('venta/buscarproducto', 'VentaController@buscarproducto')->name('venta.buscarproducto');
    Route::post('venta/eliminarPago', 'VentaController@eliminarPago')->name('venta.eliminarPago');
    Route::post('venta/buscarproductobarra', 'VentaController@buscarproductobarra')->name('venta.buscarproductobarra');
    Route::post('venta/generarNumero', 'VentaController@generarNumero')->name('venta.generarNumero');
    Route::get('venta/personautocompletar/{searching}', 'VentaController@personautocompletar')->name('venta.personautocompletar');
    Route::get('venta/numeroautocompletar/{searching}', 'VentaController@numeroautocompletar')->name('venta.numeroautocompletar');
    Route::post('venta/imprimirVenta', 'VentaController@imprimirVenta')->name('venta.imprimirVenta');
    Route::get('venta/pdf2', 'VentaController@pdf2')->name('venta.pdf2');
    Route::get('venta/excel', 'VentaController@excel')->name('venta.excel');

    /* PRODUCTO */
    Route::post('producto/buscar', 'ProductoController@buscar')->name('producto.buscar');
    Route::post('producto/buscar2', 'ProductoController@buscar2')->name('producto.buscar2');
    Route::get('producto/create2', 'ProductoController@create2')->name('producto.create2');
    Route::get('producto/edit2/{id}', 'ProductoController@edit2')->name('producto.edit2');
    Route::get('producto/eliminar/{id}/{listarluego}', 'ProductoController@eliminar')->name('producto.eliminar');
    Route::post('producto/buscarproducto', 'ProductoController@buscarproducto')->name('producto.buscarproducto');
    Route::resource('producto', 'ProductoController', array('except' => array('show')));
    Route::get('producto/index2', 'ProductoController@index2')->name('producto.index2');

     /* COMPRA */
    Route::post('compras/buscar', 'CompraController@buscar')->name('compras.buscar');
    Route::get('compras/eliminar/{id}/{listarluego}', 'CompraController@eliminar')->name('compras.eliminar');
    Route::get('compras/buscarproducto', array('as' => 'compras.buscarproducto', 'uses' => 'CompraController@buscarproducto'));
    Route::get('compras/personautocompletar/{searching}', 'CompraController@personautocompletar')->name('compras.personautocompletar');
    Route::get('compra/excel', 'CompraController@excel')->name('compra.excel');
    Route::resource('compras', 'CompraController');
    Route::get('compra/ordenautocompletar/{searching}', 'CompraController@ordenautocompletar')->name('compra.ordenautocompletar');
    Route::post('compras/archivos','CompraController@archivos')->name('compras.archivos');
    Route::get('compra/pdf', 'CompraController@pdf')->name('compra.pdf');
    Route::get('compra/pdfResumen', 'CompraController@pdfResumen')->name('compra.pdfResumen');
    Route::post('compra/agregarDetalle', 'CompraController@agregarDetalle')->name('compra.agregarDetalle');
    Route::get('storage/{id}/{archivo}', function ($id,$archivo) {
         
         //verificamos si el archivo existe y lo retornamos
         /* if (\Storage::exists($archivo)){
            return response()->download( "/app/".$id."/".$archivo);
         }
         //si no se encuentra lanzamos un error 404.
         abort(404);*/
         $url=\Storage::get($request->input('id').'/'.$nombre)->url();
         $public_path = public_path();
         //$url = $public_path.'/../storage/app/'.$id.'/'.$archivo;
         return response()->download($url);
    });
    
    /* OTRAS COMPRA */
    Route::post('otrascompra/buscar', 'OtrascompraController@buscar')->name('otrascompra.buscar');
    Route::get('otrascompra/eliminar/{id}/{listarluego}', 'OtrascompraController@eliminar')->name('otrascompra.eliminar');
    Route::get('otrascompra/cobrar/{id}/{listarluego}', 'OtrascompraController@cobrar')->name('otrascompra.cobrar');
    Route::post('otrascompra/pagar/{id}', 'OtrascompraController@pagar')->name('otrascompra.pagar');
    Route::get('otrascompra/buscarproducto', array('as' => 'otrascompra.buscarproducto', 'uses' => 'OtrascompraController@buscarproducto'));
    Route::get('otrascompra/personautocompletar/{searching}', 'OtrascompraController@personautocompletar')->name('otrascompra.personautocompletar');
    Route::get('otrascompra/pdf', 'OtrascompraController@pdf')->name('otrascompra.pdf');
    Route::resource('otrascompra', 'OtrascompraController');
    
    /* LETRA*/
    Route::post('letra/buscar', 'LetraController@buscar')->name('letra.buscar');
    Route::get('letra/eliminar/{id}/{listarluego}', 'LetraController@eliminar')->name('letra.eliminar');
    Route::get('letra/cobrar/{id}/{listarluego}', 'LetraController@cobrar')->name('letra.cobrar');
    Route::post('letra/pagar/{id}', 'LetraController@pagar')->name('letra.pagar');
    Route::get('letra/buscarproducto', array('as' => 'letra.buscarproducto', 'uses' => 'LetraController@buscarproducto'));
    Route::get('letra/personautocompletar/{searching}', 'LetraController@personautocompletar')->name('letra.personautocompletar');
    Route::get('letra/pdf', 'LetraController@pdf')->name('letra.pdf');
    Route::resource('letra', 'LetraController');
    
    /* CHEQUE*/
    Route::post('cheque/buscar', 'ChequeController@buscar')->name('cheque.buscar');
    Route::get('cheque/eliminar/{id}/{listarluego}', 'ChequeController@eliminar')->name('cheque.eliminar');
    Route::get('cheque/cobrar/{id}/{listarluego}', 'ChequeController@cobrar')->name('cheque.cobrar');
    Route::post('cheque/pagar/{id}', 'ChequeController@pagar')->name('cheque.pagar');
    Route::get('cheque/anular/{id}/{listarluego}', 'ChequeController@anular')->name('cheque.anular');
    Route::post('cheque/anulacion/{id}', 'ChequeController@anulacion')->name('cheque.anulacion');
    Route::get('cheque/buscarproducto', array('as' => 'cheque.buscarproducto', 'uses' => 'ChequeController@buscarproducto'));
    Route::get('cheque/personautocompletar/{searching}', 'ChequeController@personautocompletar')->name('cheque.personautocompletar');
    Route::get('cheque/pdf', 'ChequeController@pdf')->name('cheque.pdf');
    Route::get('cheque/pdfBanco', 'ChequeController@pdfBanco')->name('cheque.pdfBanco');
    Route::resource('cheque', 'ChequeController');

    /* REQUERIMIENTO */
    Route::post('requerimiento/buscar', 'RequerimientoController@buscar')->name('requerimiento.buscar');
    Route::get('requerimiento/eliminar/{id}/{listarluego}', 'RequerimientoController@eliminar')->name('requerimiento.eliminar');
    Route::post('requerimiento/buscarproducto', array('as' => 'requerimiento.buscarproducto', 'uses' => 'RequerimientoController@buscarproducto'));
    Route::get('requerimiento/personautocompletar/{searching}', 'RequerimientoController@personautocompletar')->name('requerimiento.personautocompletar');
    Route::get('requerimiento/pdf2', 'RequerimientoController@pdf2')->name('requerimiento.pdf2');
    Route::post('requerimiento/agregardetalle', 'RequerimientoController@agregardetalle')->name('requerimiento.agregardetalle');
    Route::resource('requerimiento', 'RequerimientoController');
    
    /* ORDEN DE COMPRA */
    Route::post('ordencompra/buscar', 'OrdencompraController@buscar')->name('ordencompra.buscar');
    Route::get('ordencompra/eliminar/{id}/{listarluego}', 'OrdencompraController@eliminar')->name('ordencompra.eliminar');
    Route::get('ordencompra/buscarproducto', array('as' => 'ordencompra.buscarproducto', 'uses' => 'OrdencompraController@buscarproducto'));
    Route::get('ordencompra/personautocompletar/{searching}', 'OrdencompraController@personautocompletar')->name('ordencompra.personautocompletar');
    Route::get('ordencompra/requerimientoautocompletar/{searching}', 'OrdencompraController@requerimientoautocompletar')->name('ordencompra.requerimientoautocompletar');
    Route::get('ordencompra/productoautocompletar/{searching}', 'OrdencompraController@productoautocompletar')->name('ordencompra.productoautocompletar');
    Route::get('ordencompra/confirmar/{id}/{listarluego}', 'OrdencompraController@confirmar')->name('ordencompra.confirmar');
    Route::post('ordencompra/confirm/{id}', 'OrdencompraController@confirm')->name('ordencompra.confirm');
    Route::post('ordencompra/agregarDetalle', 'OrdencompraController@agregarDetalle')->name('ordencompra.agregarDetalle');
    Route::get('ordencompra/pdf', 'OrdencompraController@pdf')->name('ordencompra.pdf');
    Route::get('ordencompra/pdfReporte', 'OrdencompraController@pdfReporte')->name('ordencompra.pdfReporte');
    Route::post('ordencompra/buscarmaquinaria', 'OrdencompraController@buscarmaquinaria')->name('ordencompra.buscarmaquinaria');
    Route::resource('ordencompra', 'OrdencompraController');

    /* MOVIMIENTO ALMACEN */
    Route::post('movimientoalmacen/buscar', 'MovimientoalmacenController@buscar')->name('movimientoalmacen.buscar');
    Route::get('movimientoalmacen/eliminar/{id}/{listarluego}', 'MovimientoalmacenController@eliminar')->name('movimientoalmacen.eliminar');
    Route::post('movimientoalmacen/buscarproducto', 'MovimientoalmacenController@buscarproducto')->name('movimientoalmacen.buscarproducto');
    Route::post('movimientoalmacen/generarNumero', 'MovimientoalmacenController@generarNumero')->name('movimientoalmacen.generarNumero');
    Route::get('movimientoalmacen/personautocompletar/{searching}', 'MovimientoalmacenController@personautocompletar')->name('movimientoalmacen.personautocompletar');
    Route::resource('movimientoalmacen', 'MovimientoalmacenController');

    /* COTIZACION */
    Route::post('cotizacion/buscar', 'CotizacionController@buscar')->name('cotizacion.buscar');
    Route::get('cotizacion/eliminar/{id}/{listarluego}', 'CotizacionController@eliminar')->name('cotizacion.eliminar');
    Route::get('cotizacion/confirmar/{id}/{listarluego}', 'CotizacionController@confirmar')->name('cotizacion.confirmar');
    Route::post('cotizacion/confirm/{id}', 'CotizacionController@confirm')->name('cotizacion.confirm');
    Route::get('cotizacion/rechazar/{id}/{listarluego}', 'CotizacionController@rechazar')->name('cotizacion.rechazar');
    Route::post('cotizacion/rechaza/{id}', 'CotizacionController@rechaza')->name('cotizacion.rechaza');
    Route::resource('cotizacion', 'CotizacionController', array('except' => array('show')));
    Route::post('cotizacion/buscarproducto', 'CotizacionController@buscarproducto')->name('cotizacion.buscarproducto');
    Route::post('cotizacion/generarNumero', 'CotizacionController@generarNumero')->name('cotizacion.generarNumero');
    Route::get('cotizacion/personautocompletar/{searching}', 'CotizacionController@personautocompletar')->name('cotizacion.personautocompletar');
    Route::get('cotizacion/pdf', 'CotizacionController@pdf')->name('cotizacion.pdf');
    Route::get('cotizacion/Reporte', 'CotizacionController@Reporte')->name('cotizacion.Reporte');
    Route::get('cotizacion/Excel', 'CotizacionController@Excel')->name('cotizacion.Excel');

    /* CONTRATO */
    Route::post('contrato/buscar', 'ContratoController@buscar')->name('contrato.buscar');
    Route::get('contrato/eliminar/{id}/{listarluego}', 'contrato@eliminar')->name('contrato.eliminar');
    Route::resource('contrato', 'ContratoController', array('except' => array('show')));
    Route::post('contrato/agregarDetalle', 'ContratoController@agregarDetalle')->name('contrato.agregarDetalle');
    Route::post('contrato/generarNumero', 'ContratoController@generarNumero')->name('contrato.generarNumero');
    Route::get('contrato/cotizacionautocompletar/{searching}', 'ContratoController@cotizacionautocompletar')->name('contrato.cotizacionautocompletar');
    Route::get('contrato/word', 'ContratoController@word')->name('contrato.word');    
    Route::get('contrato/excel', 'ContratoController@excel')->name('contrato.excel');    

    /*PERSON*/
    Route::post('person/search', 'PersonController@search')->name('person.search');
    Route::get('person/employeesautocompleting/{searching}', 'PersonController@employeesautocompleting')->name('person.employeesautocompleting');
    Route::get('person/providersautocompleting/{searching}', 'PersonController@providersautocompleting')->name('person.providersautocompleting');
    Route::get('person/customersautocompleting/{searching}', 'PersonController@customersautocompleting')->name('person.customersautocompleting');
    Route::get('person/doctorautocompleting/{searching}', 'PersonController@doctorautocompleting')->name('person.doctorautocompleting');

    /* REPORTE REQUERIMIENTO */
    Route::post('reporterequerimiento/buscar', 'ReporterequerimientoController@buscar')->name('reporterequerimiento.buscar');
    Route::resource('reporterequerimiento', 'ReporterequerimientoController', array('except' => array('show')));
    Route::get('reporterequerimiento/excel', 'ReporterequerimientoController@excel')->name('reporterequerimiento.excel');
    Route::get('reporterequerimiento/pdf', 'ReporterequerimientoController@pdf')->name('reporterequerimiento.pdf');
    

    /* CUENTAS POR PAGAR */
    Route::post('cuentasporpagar/buscar', 'CuentasporpagarController@buscar')->name('cuentasporpagar.buscar');
    Route::get('cuentasporpagar/eliminar/{id}/{listarluego}', 'CuentasporpagarController@eliminar')->name('cuentasporpagar.eliminar');
    Route::resource('cuentasporpagar', 'CuentasporpagarController', array('except' => array('show')));
    Route::get('cuentasporpagar/personautocompletar/{searching}', 'CuentasporpagarController@personautocompletar')->name('cuentasporpagar.personautocompletar');
    Route::get('cuentasporpagar/excel', 'CuentasporpagarController@excel')->name('cuentasporpagar.excel');
    Route::get('cuentasporpagar/pdf', 'CuentasporpagarController@pdf')->name('cuentasporpagar.pdf');
    Route::get('cuentasporpagar/pdfCorte', 'CuentasporpagarController@pdfCorte')->name('cuentasporpagar.pdfCorte');
    Route::get('cuentasporpagar/pdf2', 'CuentasporpagarController@pdf2')->name('cuentasporpagar.pdf2');
    Route::get('cuentasporpagar/pdfCorte2', 'CuentasporpagarController@pdfCorte2')->name('cuentasporpagar.pdfCorte2');
    Route::get('cuentasporpagar/pdfPago', 'CuentasporpagarController@pdfPago')->name('cuentasporpagar.pdfPago');

    /* CUENTAS POR COBRAR */
    Route::post('cuentasporcobrar/buscar', 'CuentasporcobrarController@buscar')->name('cuentasporcobrar.buscar');
    Route::resource('cuentasporcobrar', 'CuentasporcobrarController', array('except' => array('show')));
    Route::get('cuentasporcobrar/excel', 'CuentasporcobrarController@excel')->name('cuentasporcobrar.excel');
    Route::get('cuentasporcobrar/pdf', 'CuentasporcobrarController@pdf')->name('cuentasporcobrar.pdf');
    Route::get('cuentasporcobrar/pdfDetraccion', 'CuentasporcobrarController@pdfDetraccion')->name('cuentasporcobrar.pdfDetraccion');
    Route::get('cuentasporcobrar/pdf2', 'CuentasporcobrarController@pdf2')->name('cuentasporcobrar.pdf2');

    /* REPORTE VENTA */
    Route::post('reporteventa/buscar', 'ReporteventaController@buscar')->name('reporteventa.buscar');
    Route::resource('reporteventa', 'ReporteventaController', array('except' => array('show')));
    Route::get('reporteventa/excel', 'ReporteventaController@excel')->name('reporteventa.excel');
    Route::get('reporteventa/pdf', 'ReporteventaController@pdf')->name('reporteventa.pdf');
    
    /* STOCK */
    Route::post('stockproducto/buscar', 'StockproductoController@buscar')->name('stockproducto.buscar');
    Route::resource('stockproducto', 'StockproductoController', array('except' => array('show')));
    Route::get('stockproducto/excel', 'StockproductoController@excel')->name('stockproducto.excel');
    Route::get('stockproducto/pdf', 'StockproductoController@pdf')->name('stockproducto.pdf');
    Route::get('stockproducto/kardex', 'StockproductoController@kardex')->name('stockproducto.kardex');
    Route::get('stockproducto/reporteKardex', 'StockproductoController@reporteKardex')->name('stockproducto.reporteKardex');
    Route::get('stockproducto/productoautocompletar/{searching}', 'StockproductoController@productoautocompletar')->name('stockproducto.personautocompletar');
    
    /* CONCEPTOPAGO */
    Route::post('conceptopago/buscar', 'ConceptopagoController@buscar')->name('conceptopago.buscar');
    Route::get('conceptopago/eliminar/{id}/{listarluego}', 'ConceptopagoController@eliminar')->name('conceptopago.eliminar');
    Route::resource('conceptopago', 'ConceptopagoController', array('except' => array('show')));

    /* CAJA */
    Route::post('caja/buscar', 'CajaController@buscar')->name('caja.buscar');
    Route::post('caja/buscarcontrol', 'CajaController@buscarControl')->name('caja.buscarcontrol');
    Route::get('caja/eliminar/{id}/{listarluego}', 'CajaController@eliminar')->name('caja.eliminar');
    Route::resource('caja', 'CajaController', array('except' => array('show')));
    Route::get('caja/apertura', 'CajaController@apertura')->name('caja.apertura');
    Route::post('caja/aperturar', 'CajaController@aperturar')->name('caja.aperturar');
    Route::get('caja/cierre', 'CajaController@cierre')->name('caja.cierre');
    Route::post('caja/cerrar', 'CajaController@cerrar')->name('caja.cerrar');
    Route::post('caja/generarConcepto', 'CajaController@generarConcepto')->name('caja.generarconcepto');
    Route::post('caja/generarNumero', 'CajaController@generarNumero')->name('caja.generarnumero');
     Route::get('caja/personautocompletar/{searching}', 'CajaController@personautocompletar')->name('caja.personautocompletar');
    Route::get('caja/acept/{id}/{listarluego}', 'CajaController@acept')->name('caja.acept');
    Route::post('caja/aceptar/{id}', 'CajaController@aceptar')->name('caja.aceptar');
    Route::get('caja/reject/{id}/{listarluego}', 'CajaController@reject')->name('caja.reject');
    Route::post('caja/rechazar/{id}', 'CajaController@rechazar')->name('caja.rechazar');
    Route::get('caja/pdfCierre', 'CajaController@pdfCierre')->name('caja.pdfCierre');
    Route::get('caja/pdfDetalleCierre', 'CajaController@pdfDetalleCierre')->name('caja.pdfDetalleCierre');
    Route::get('caja/pdfDetalleCierreF', 'CajaController@pdfDetalleCierreF')->name('caja.pdfDetalleCierreF');
    Route::get('caja/pdfRecibo', 'CajaController@pdfRecibo')->name('caja.pdfRecibo');
    Route::post('caja/venta', 'CajaController@venta')->name('caja.venta');
    Route::post('caja/buscarCompras', 'CajaController@buscarCompras')->name('caja.buscarCompras');
    
    /* ALMACEN */
    Route::post('almacen/buscar', 'AlmacenController@buscar')->name('almacen.buscar');
    Route::get('almacen/eliminar/{id}/{listarluego}', 'AlmacenController@eliminar')->name('almacen.eliminar');
    Route::resource('almacen', 'AlmacenController', array('except' => array('show')));
    
    /* MOVIMIENTO CAJA*/
    Route::post('movimientocaja/buscar', 'MovimientocajaController@buscar')->name('movimientocaja.buscar');
    Route::get('movimientocaja/pdflistar', 'MovimientocajaController@pdfListar')->name('movimientocaja.pdfListar');
    Route::get('movimientocaja/detalle/{id}/{listarluego}', 'MovimientocajaController@detalle')->name('movimientocaja.detalle');
    Route::resource('movimientocaja', 'MovimientocajaController', array('except' => array('show')));
    Route::get('movimientocaja/excel', 'MovimientocajaController@excel')->name('movimientocaja.excel');

    /* BANCO */
    Route::post('banco/buscar', 'BancoController@buscar')->name('banco.buscar');
    Route::get('banco/eliminar/{id}/{listarluego}', 'BancoController@eliminar')->name('banco.eliminar');
    Route::resource('banco', 'BancoController', array('except' => array('show')));

    /* CUENTA BANCO */
    Route::post('cuenta/buscar', 'CuentaController@buscar')->name('cuenta.buscar');
    Route::get('cuenta/eliminar/{id}/{listarluego}', 'CuentaController@eliminar')->name('cuenta.eliminar');
    Route::resource('cuenta', 'CuentaController', array('except' => array('show')));


    /* CUENTA BANCARIA */
    Route::post('cuentabancaria/buscar', 'CuentabancariaController@buscar')->name('cuentabancaria.buscar');
    Route::get('cuentabancaria/eliminar/{id}/{listarluego}', 'CuentabancariaController@eliminar')->name('cuentabancaria.eliminar');
    Route::resource('cuentabancaria', 'CuentabancariaController', array('except' => array('show')));
    Route::get('cuentabancaria/pdfRecibo', 'CuentabancariaController@pdfRecibo')->name('cuentabancaria.pdfRecibo');
    Route::get('cuentabancaria/cobrar/{id}/{listarluego}', 'CuentabancariaController@cobrar')->name('cuentabancaria.cobrar');
    Route::post('cuentabancaria/pagar/{id}', 'CuentabancariaController@pagar')->name('cuentabancaria.pagar');
    Route::post('cuentabancaria/generarConcepto', 'CuentabancariaController@generarConcepto')->name('cuentabancaria.generarConcepto');
    Route::get('cuentabancaria/pdfListar', 'CuentabancariaController@pdfListar')->name('cuentabancaria.pdfListar');
    Route::post('cuentabancaria/cuentasporpagar', 'CuentabancariaController@cuentasporpagar')->name('cuentabancaria.cuentasporpagar');


      /* MIGRAR EXCEL*/
    Route::get('importHistoria', 'ExcelController@importHistoria');
    Route::get('downloadExcel/{type}', 'ExcelController@downloadExcel');
    Route::post('importMatricula', 'ExcelController@importMatricula');
    Route::post('importProfesor', 'ExcelController@importProfesor');
    Route::post('importApellidoExcel', 'ExcelController@importApellidoExcel');
    Route::post('importProducto', 'ExcelController@importProducto');
    
    Route::get('/empresa', function(){
        return View::make('dashboard.empresa.admin');
    });
    Route::get('/egresado', function(){
        return View::make('dashboard.egresado.admin');
    });
    Route::get('/publicacion', function(){
        return View::make('dashboard.publicacion.admin');
    });
});

Route::get('provincia/cboprovincia/{id?}', array('as' => 'provincia.cboprovincia', 'uses' => 'ProvinciaController@cboprovincia'));
Route::get('distrito/cbodistrito/{id?}', array('as' => 'distrito.cbodistrito', 'uses' => 'DistritoController@cbodistrito'));
