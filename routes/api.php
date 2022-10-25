<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
return $request->user();
});*/

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('getInfo', 'AuthController@me');
    Route::post('createUser', 'PromotorController@create');
});

Route::group(['middleware' => 'api'], function ($router) {
    Route::post('getOpportunities', 'OpportunityController@getOpportunities');
    Route::post('getProspects', 'OpportunityController@getProspects');
    Route::post('getOpportunity', 'OpportunityController@getOpportunity');
    Route::post('getDistributors', 'DistributorController@getDistributors');
    Route::get('getDealers', 'DistributorController@getDealers');
    Route::get('getSellers', 'DistributorController@getSellers');
    Route::post('getMaterial', 'OrderController@getMaterial');
    Route::post('setOrder', 'OrderController@setOrder');

    Route::get('getEsporadicos', 'OrderEsporadicoController@getEsporadicos');
    Route::post('getMaterialEsporadico', 'OrderEsporadicoController@getMaterial');
    Route::post('setOrderEsporadico', 'OrderEsporadicoController@setOrderEsporadico');

    Route::post('getCustomers', 'OrderCustomerController@getCustomers');
    Route::post('InfoCustomer', 'OrderCustomerController@InfoCustomer');
    Route::post('setOrderCustomer', 'OrderCustomerController@setOrderCustomer');

    Route::post('getOrders', 'OrderManagementController@getOrders');
    Route::post('getOrder', 'OrderManagementController@getOrder');
    Route::post('getDealerEmail', 'OrderManagementController@getDealerEmail');
    Route::post('sendOrder', 'OrderManagementController@sendOrder');

    Route::post('getRouteList', 'RouteController@getRouteList');
    Route::post('getRouteMap', 'RouteController@getRouteMap');
    Route::post('startRoute', 'RouteController@startRoute');
    Route::post('getRouteInfo', 'RouteController@getRouteInfo');
    Route::post('pauseRoute', 'RouteController@pauseRoute');
    Route::post('obtenerDistribuidores', 'FormController@obtenerDistribuidores');
    Route::post('saveForm', 'FormController@saveForm');
    //Route::post('getInfoOpportunity', 'OpportunityController@getInfoOpportunity');
    Route::post('deleteOportunity', 'OpportunityController@deleteOportunity');
    Route::post('getOpportunitiesProspecto', 'OpportunityController@getOpportunitiesProspecto');
    Route::post('getEvents', 'DailyController@getEvents');
    Route::post('crearNuevaCita', 'DailyController@crearNuevaCita');
    Route::post('borrarCita', 'DailyController@borrarCita');
    Route::post('agendaActivarCheckIn', 'DailyController@agendaActivarCheckIn');
    Route::post('agendaActivarCheckOut', 'DailyController@agendaActivarCheckOut');
    Route::post('getFormDetail', 'FormController@getFormDetail');

    Route::post('saveCamera', 'GalleryController@saveCamera');
    Route::post('getPicktures', 'GalleryController@getPicktures');
    Route::post('sendEvidencia', 'OrderManagementController@sendEvidencia');
    Route::post('newOpportunities', 'OpportunityController@newOpportunities');

    Route::post('getEntidades', 'AdminMapController@getEntidades');
    Route::post('getMunicipio', 'AdminMapController@getMunicipio');

    Route::post('getReporteEstadosMunicipios', 'AdminMapController@getReporteEstadosMunicipios');

    Route::post('getOpportunitiesInegi', 'AdminMapController@getOpportunitiesInegi');

    Route::post('getOpportunitiesInegiEstado', 'AdminMapController@getOpportunitiesInegiEstado');

    Route::post('getProcesoOpportunitiesInegi', 'AdminMapController@getProcesoOpportunitiesInegi');
    Route::post('getRouteMapPrueba', 'AdminMapController@getRouteMapPrueba');
    Route::post('saveRuta', 'AdminMapController@saveRuta');

    Route::post('getPromotores', 'PromotorController@getPromotores');
    Route::post('getRutasDisponibles', 'RouteController@getRutasDisponibles');
    Route::post('asignarRutaPromotor', 'RouteController@asignarRutaPromotor');
    Route::post('quitarRutaPromotor', 'RouteController@quitarRutaPromotor');

    Route::post('getPromotoresAll', 'PromotorController@getPromotoresAll');
    Route::post('updateUser', 'PromotorController@updateUser');
    Route::post('updatePassword', 'PromotorController@updatePassword');
    Route::post('altaPromotor', 'PromotorController@altaPromotor');
    Route::post('bajaPromotor', 'PromotorController@bajaPromotor');
    Route::post('deletePromotor', 'PromotorController@deletePromotor');

    Route::post('startInegiApi', 'ProcesoInegiController@startInegiApi');
    Route::post('guardarNuevoInegiApi', 'ProcesoInegiController@guardarNuevoInegiApi');
    Route::post('guardarNuevoInegiApiLista', 'ProcesoInegiController@guardarNuevoInegiApiLista');

    Route::post('getReporte', 'IndicadoresController@getReporte');
    Route::post('saveFormPromotor', 'FormController@saveFormPromotor');

    Route::post('getUserInfo', 'PromotorController@getUserInfo');
    Route::post('updateImageUser', 'PromotorController@updateImageUser');
    Route::post('getReporteEstado', 'IndicadoresController@getReporteEstado');
    Route::post('getReporteMunicipio', 'IndicadoresController@getReporteMunicipio');
    Route::post('getRutasSinAsignar', 'IndicadoresController@getRutasSinAsignar');
    Route::post('getReportePromotor', 'IndicadoresController@getReportePromotor');
    Route::post('getReportePromotorMunicipio', 'IndicadoresController@getReportePromotorMunicipio');
    Route::post('upadteProspecto', 'OpportunityController@upadteProspecto');

    Route::post('deleteDistributor', 'DistributorController@deleteDistributor');
    Route::post('addDistributor', 'DistributorController@addDistributor');
    Route::post('getDistributorsAll', 'DistributorController@getDistributorsAll');

    Route::post('getPedidoHistory', 'HistorialOrderController@getPedidoHistory');
    Route::post('getRecordatoriosHistory', 'HistorialOrderController@getRecordatoriosHistory');
    Route::post('getInfoPedido', 'HistorialOrderController@getInfoPedido');

    Route::post('getMaterialSustitutos', 'OrderEsporadicoController@getMaterialSustitutos');
});
