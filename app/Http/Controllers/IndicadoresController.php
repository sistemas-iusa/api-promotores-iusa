<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\OpportunitiesINEGI;

use App\RouteList;

use App\Forms;

use App\Order;

use App\OrderDetail;

use App\OpportunitiesINEGIPRO;

use App\RouteListPRO;

use App\FormsPRO;

use App\OrderPRO;

use App\OrderDetailPRO;

use App\Entidad;

use App\Municipio;

use App\User;

class IndicadoresController extends Controller
{
    //
    public function getReporte(Request $request)
    {
        $opportunitiesTotal = OpportunitiesINEGI::where('id','!=', null)->count();
        $opportunitiesVisitadas = OpportunitiesINEGI::where('bandera_encuesta', '1')->count();
        $opportunitiesAsignadasRuta = OpportunitiesINEGI::where('id_ruta','!=', null)->count();
        $opportunitiesPendientesAsignadasRuta = OpportunitiesINEGI::where('id_ruta', null)->count();
        $opportunitiesPendientesVisitar = OpportunitiesINEGI::where('id_ruta','!=', null)->where('bandera_encuesta', null)->where('bandera_cancelada', null)->count();
        
        $oportunidades_nuevas = OpportunitiesINEGI::where('bandera_nuevo', 1)->count();
        
        $opportunitiesCanceladas = OpportunitiesINEGI::where('bandera_cancelada', '1')->count();
        $opportunitiesCanceladasMotivo1 = OpportunitiesINEGI::where('bandera_cancelada', '1')->where('motivo_cancelacion', 'El negocio ya no existe')->count();
        $opportunitiesCanceladasMotivo2 = OpportunitiesINEGI::where('bandera_cancelada', '1')->where('motivo_cancelacion', 'Reubicación de local')->count();
        $opportunitiesCanceladasMotivo3 = OpportunitiesINEGI::where('bandera_cancelada', '1')->where('motivo_cancelacion','!=', 'El negocio ya no existe')->where('motivo_cancelacion','!=', 'Reubicación de local')->count();
        
        //Porcentaje oportunidades
        if($opportunitiesAsignadasRuta != 0 ){
        $porcentajeOportunidadesProspectos =  number_format(($opportunitiesVisitadas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
        $porcentajeOportunidadesCanceladas =  number_format(($opportunitiesCanceladas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
        $porcentajeOportunidadesPendientesVisitar =  number_format(($opportunitiesPendientesVisitar/$opportunitiesAsignadasRuta)*100, 0, '.', '');
        $porcentajeOportunidades_nuevas =  number_format(($oportunidades_nuevas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
        }else{
            $porcentajeOportunidadesProspectos =  0;
            $porcentajeOportunidadesCanceladas =  0;
            $porcentajeOportunidadesPendientesVisitar = 0;
            $porcentajeOportunidades_nuevas = 0;
        }
        // tasa de convercion 
        $oportunidades_ya_visitadas = $opportunitiesVisitadas + $opportunitiesCanceladas;
        if($oportunidades_ya_visitadas != 0 ){
            $porcentajeProspectosTasaConvercion = number_format(($opportunitiesVisitadas/$oportunidades_ya_visitadas)*100, 0, '.', '');
            $porcentajeCanceladasTasaConvercion =  number_format(($opportunitiesCanceladas/$oportunidades_ya_visitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosTasaConvercion = 0;
            $porcentajeCanceladasTasaConvercion = 0;
        }
        //porcentajes opportunitiesCanceladas
        if($opportunitiesCanceladas != 0){
        $porcentajeOpportunitiesCanceladasMotivo1 =  number_format(($opportunitiesCanceladasMotivo1/$opportunitiesCanceladas)*100, 0, '.', '');
        $porcentajeOpportunitiesCanceladasMotivo2 =  number_format(($opportunitiesCanceladasMotivo2/$opportunitiesCanceladas)*100, 0, '.', '');
        $porcentajeOpportunitiesCanceladasMotivo3 =  number_format(($opportunitiesCanceladasMotivo3/$opportunitiesCanceladas)*100, 0, '.', '');
        }else{
            $porcentajeOpportunitiesCanceladasMotivo1 =  0 ;
            $porcentajeOpportunitiesCanceladasMotivo2 =  0 ;
            $porcentajeOpportunitiesCanceladasMotivo3 = 0;
        }
        
        $rutasTotal = RouteList::where('id','!=', null)->count();
        $rutasAsignadas = RouteList::where('id_promotor','!=', null)->count();
        $rutasPendientes = RouteList::where('id_promotor', null)->count();
        $rutasSinIniciar = RouteList::where('estatus', 'Sin iniciar')->count();
        $rutasCurso = RouteList::where('estatus', 'En proceso')->count();
        $rutasPausa = RouteList::where('estatus', 'En pausa')->count();
        $rutasFinalizadas = RouteList::where('estatus', 'Terminado')->count();
        $rutas10 = RouteList::where('orden_ruta', '10')->count();
        $rutasMenos10 = RouteList::where('orden_ruta','!=','10')->count();
        $encuestasContenstadas = Forms::where('id','!=', null)->count();

        $prospectosVisitasRecurrentes = Forms::where('pregunta3', 'SI')->count();
        $prospectosNOVisitasRecurrentes = Forms::where('pregunta3', 'NO')->count();
        //porcentajes prospectosVisitasRecurrentes
        if($opportunitiesVisitadas != 0){
        $porcentajeProspectosVisitasRecurrentes =  number_format(($prospectosVisitasRecurrentes/$opportunitiesVisitadas)*100, 0, '.', '');
        $porcentajeProspectosNOVisitasRecurrentes =  number_format(($prospectosNOVisitasRecurrentes/$opportunitiesVisitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosVisitasRecurrentes = 0;
            $porcentajeProspectosNOVisitasRecurrentes = 0;
        }
        $prospectosProductoIUSA = Forms::where('pregunta1', 'SI')->count();
        $prospectosNOProductoIUSA = Forms::where('pregunta1', 'NO')->count();
        //porcentajes prospectosProductoIUSA
        if($opportunitiesVisitadas != 0){
        $porcentajeProspectosProductoIUSA =  number_format(($prospectosProductoIUSA/$opportunitiesVisitadas)*100, 0, '.', '');
        $porcentajeProspectosNOProductoIUSA =  number_format(($prospectosNOProductoIUSA/$opportunitiesVisitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosProductoIUSA = 0;
            $porcentajeProspectosNOProductoIUSA = 0;
        }
        $PedidoSugeridoTotal = Order::where('id','!=', null)->count();
        $PedidoSugeridoSinEnviar = Order::where('estatus', 'PENDIENTE')->count();
        $PedidoSugeridoEnviado = Order::where('estatus', 'ENVIADO')->count();
        $PedidoSugeridoTerminado = Order::where('estatus', 'TERMINADO')->count();
        //porcentajes Grafica Pedido Sugerido
        if($PedidoSugeridoTotal != 0){
        $porcentajePedidoSugeridoSinEnviar = number_format(($PedidoSugeridoSinEnviar/$PedidoSugeridoTotal)*100, 0, '.', '');
        $porcentajePedidoSugeridoEnviado = number_format(($PedidoSugeridoEnviado/$PedidoSugeridoTotal)*100, 0, '.', '');
        $porcentajePedidoSugeridoTerminado = number_format(($PedidoSugeridoTerminado/$PedidoSugeridoTotal)*100, 0, '.', '');
        }else{
            $porcentajePedidoSugeridoSinEnviar = 0;
            $porcentajePedidoSugeridoEnviado = 0;
            $porcentajePedidoSugeridoTerminado = 0;
        }
        //obtener ventas por familia, 
        $detallePedidoSugerido = OrderDetail::where('id','!=', null)->get();
        $arrayCobre = array('id'=>'COBRE Y SUS ALEACIONES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayElectricos = array('id'=>'ELECTRICOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayEsIndustriales = array('id'=>'ESPECIALIDADES INDUSTRIALES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayExCatalogo = array('id'=>'EXHIBIDOR Y CATALOGOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayHerramientas = array('id'=>'HERRAMIENTAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayLineaBlanca = array('id'=>'LINEA BLANCA','u_pedidas'=>0,'lista_material'=>[]);
        $arrayAgua = array('id'=>'MANEJO DE AGUA Y GAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayMedical = array('id'=>'MEDICAL CENTER','u_pedidas'=>0,'lista_material'=>[]);
        $resumenVentasFamilia = array($arrayCobre,$arrayElectricos,$arrayEsIndustriales,$arrayExCatalogo,$arrayHerramientas,$arrayLineaBlanca,$arrayAgua,$arrayMedical);
        $total_unidades = 0;
        foreach ($detallePedidoSugerido as $material_pedido) {
            foreach ($resumenVentasFamilia as $k => $re_pedido_sugeridos) {                
                if($material_pedido->division_comercial == $re_pedido_sugeridos['id']){
                    $re_pedido_sugeridos['u_pedidas'] = $re_pedido_sugeridos['u_pedidas'] + $material_pedido->unidades_confirmadas; 
                    //$re_pedido_sugeridos['lista_material'] = $material_pedido;
                    $info_pedido_padre= Order::find($material_pedido->orden_compra_id);
                    $material_pedido->folio_pedido = $info_pedido_padre->folio;
                    $material_pedido->nombre_prospecto = $info_pedido_padre->nombreUsuario;
                    array_push($re_pedido_sugeridos['lista_material'], $material_pedido);
                    $resumenVentasFamilia[$k] = $re_pedido_sugeridos;
                }
            }
            $total_unidades = $total_unidades + $material_pedido->unidades_confirmadas;
        }
        //obtener familia mas vendida
        $mayor_ventas_familia=0;
        $mayor_nombre_familia = '';
        foreach ($resumenVentasFamilia as $L => $re_pedido_sugeridos2) {
            if ($re_pedido_sugeridos2['u_pedidas']>$mayor_ventas_familia){
            $mayor_ventas_familia = $re_pedido_sugeridos2['u_pedidas'];
            $mayor_nombre_familia = $re_pedido_sugeridos2['id'];
            }
            //porcentaje 
            if($total_unidades != 0){
            $porcentaje = ($re_pedido_sugeridos2['u_pedidas']/$total_unidades)*100;
            $porcentaje_format = number_format($porcentaje, 0, '.', '');
            $re_pedido_sugeridos2['porcentaje'] = $porcentaje_format;
            }else{
                $re_pedido_sugeridos2['porcentaje'] = 0;
            }
            $resumenVentasFamilia[$L] = $re_pedido_sugeridos2;
        }
        
        //proceso para obtener los prospectos con pedidos ya generados 
        $opportunitiesVisitadasObject = OpportunitiesINEGI::where('bandera_encuesta', '1')->get();
        $PedidoSugeridoTotalObject = Order::where('id','!=', null)->get();
        $ProspectosPedidoSugerido = 0;
        $ProspectoSinPedidoSugerido = 0;
        foreach ($opportunitiesVisitadasObject as $comparador) {
            $bandera = 0;
            foreach ($PedidoSugeridoTotalObject as $comparador1) {
                if($comparador->id == $comparador1->idUsuario){
                    $bandera = 1;
                }
            }
            if($bandera == 1){
                $ProspectosPedidoSugerido++;  
            }else{
                $ProspectoSinPedidoSugerido++;
            }
        }
        //porcentajes ProspectosPedidoSugerido
        if($opportunitiesVisitadas != 0){
        $porcentajeProspectosPedidoSugerido =  number_format(($ProspectosPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
        $porcentajeProspectoSinPedidoSugerido =  number_format(($ProspectoSinPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosPedidoSugerido = 0;
            $porcentajeProspectoSinPedidoSugerido = 0;
        }
        $datos = [
            'opportunitiesTotal' => $opportunitiesTotal,
            'opportunitiesVisitadas' => $opportunitiesVisitadas,
            'opportunitiesCanceladas' => $opportunitiesCanceladas,
            'opportunitiesCanceladasMotivo1' => $opportunitiesCanceladasMotivo1,
            'opportunitiesCanceladasMotivo2' => $opportunitiesCanceladasMotivo2,
            'opportunitiesCanceladasMotivo3' => $opportunitiesCanceladasMotivo3,
            'porcentajeOpportunitiesCanceladasMotivo1' => $porcentajeOpportunitiesCanceladasMotivo1,
            'porcentajeOpportunitiesCanceladasMotivo2' => $porcentajeOpportunitiesCanceladasMotivo2,
            'porcentajeOpportunitiesCanceladasMotivo3' => $porcentajeOpportunitiesCanceladasMotivo3,
            'opportunitiesAsignadasRuta' => $opportunitiesAsignadasRuta,
            'opportunitiesPendientesAsignadasRuta' => $opportunitiesPendientesAsignadasRuta,
            'opportunitiesPendientesVisitar' => $opportunitiesPendientesVisitar,
            'rutasTotal' => $rutasTotal,
            'rutasAsignadas' => $rutasAsignadas,
            'rutasPendientes' => $rutasPendientes,
            'rutasSinIniciar' => $rutasSinIniciar,
            'rutasCurso' => $rutasCurso,
            'rutasPausa' => $rutasPausa,
            'rutasFinalizadas' => $rutasFinalizadas,
            'rutas10' => $rutas10,
            'rutasMenos10' => $rutasMenos10,
            'encuestasContenstadas' => $encuestasContenstadas,
            'prospectosVisitasRecurrentes' => $prospectosVisitasRecurrentes,
            'prospectosNOVisitasRecurrentes' => $prospectosNOVisitasRecurrentes,
            'porcentajeProspectosVisitasRecurrentes' => $porcentajeProspectosVisitasRecurrentes,
            'porcentajeProspectosNOVisitasRecurrentes' => $porcentajeProspectosNOVisitasRecurrentes,
            'prospectosProductoIUSA' => $prospectosProductoIUSA,
            'prospectosNOProductoIUSA' => $prospectosNOProductoIUSA,
            'porcentajeProspectosProductoIUSA' => $porcentajeProspectosProductoIUSA,
            'porcentajeProspectosNOProductoIUSA' => $porcentajeProspectosNOProductoIUSA,
            'PedidoSugeridoTotal' => $PedidoSugeridoTotal,
            'PedidoSugeridoSinEnviar' => $PedidoSugeridoSinEnviar,
            'PedidoSugeridoEnviado' => $PedidoSugeridoEnviado,
            'PedidoSugeridoTerminado' => $PedidoSugeridoTerminado,
            'porcentajePedidoSugeridoSinEnviar' => $porcentajePedidoSugeridoSinEnviar,
            'porcentajePedidoSugeridoEnviado' => $porcentajePedidoSugeridoEnviado,
            'porcentajePedidoSugeridoTerminado' => $porcentajePedidoSugeridoTerminado,
            'ProspectosPedidoSugerido' => $ProspectosPedidoSugerido,
            'ProspectoSinPedidoSugerido' => $ProspectoSinPedidoSugerido,
            'porcentajeProspectosPedidoSugerido' => $porcentajeProspectosPedidoSugerido,
            'porcentajeProspectoSinPedidoSugerido' => $porcentajeProspectoSinPedidoSugerido,
            'resumenVentasFamilia' => $resumenVentasFamilia,
            'mayor_nombre_familia' => $mayor_nombre_familia,
            'mayor_ventas_familia' => $mayor_ventas_familia,
            'porcentajeOportunidadesProspectos' =>  $porcentajeOportunidadesProspectos,
            'porcentajeOportunidadesCanceladas' => $porcentajeOportunidadesCanceladas,
            'porcentajeOportunidadesPendientesVisitar' => $porcentajeOportunidadesPendientesVisitar,
            'porcentajeProspectosTasaConvercion' => $porcentajeProspectosTasaConvercion,
            'porcentajeCanceladasTasaConvercion' => $porcentajeCanceladasTasaConvercion,
            'oportunidades_nuevas' => $oportunidades_nuevas,
            'porcentajeOportunidades_nuevas' => $porcentajeOportunidades_nuevas
        ]; 
        return response()->json(
            $datos
        );
    }

    public function getReporteMunicipio(Request $request)
    {
        //nombre de entidad y municipio 
        $entidad = Entidad::where('clave',$request->id_entidad)->get();
        $municipio = Municipio::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->get();
        $entidad = $entidad->first();
        $municipio = $municipio->first();
        $nombre_entidad = $entidad->nombre;
        $nombre_municipio = $municipio->nombre;

        $rutasTotal = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id','!=', null)->count();

        $opportunitiesTotal = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id','!=', null)->count();
        $opportunitiesVisitadas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('bandera_encuesta', '1')->count();
        $opportunitiesAsignadasRuta = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_ruta','!=', null)->count();
        $opportunitiesPendientesAsignadasRuta = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_ruta', null)->count();
        $opportunitiesPendientesVisitar = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_ruta','!=', null)->where('bandera_encuesta', null)->where('bandera_cancelada', null)->count();
        $opportunitiesCanceladas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('bandera_cancelada', '1')->count();
        $opportunitiesCanceladasMotivo1 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'El negocio ya no existe')->count();
        $opportunitiesCanceladasMotivo2 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'Reubicación de local')->count();
        $opportunitiesCanceladasMotivo3 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('bandera_cancelada', '1')->where('motivo_cancelacion','!=', 'El negocio ya no existe')->where('motivo_cancelacion','!=', 'Reubicación de local')->count();
        $oportunidades_nuevas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('bandera_nuevo', 1)->count();
        //Porcentaje oportunidades
        //Porcentajes opportunitiesCanceladas
        if($opportunitiesVisitadas != 0){
            $porcentajeOportunidadesProspectos =  number_format(($opportunitiesVisitadas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesCanceladas =  number_format(($opportunitiesCanceladas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesPendientesVisitar =  number_format(($opportunitiesPendientesVisitar/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidades_nuevas =  number_format(($oportunidades_nuevas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
        }else{
            $porcentajeOportunidadesProspectos =  0;
            $porcentajeOportunidadesCanceladas =  0;
            $porcentajeOportunidadesPendientesVisitar = 0;    
            $porcentajeOportunidades_nuevas = 0;       
        }  
        
        
        if($opportunitiesCanceladas != 0){
            $porcentajeOpportunitiesCanceladasMotivo1 =  number_format(($opportunitiesCanceladasMotivo1/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo2 =  number_format(($opportunitiesCanceladasMotivo2/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo3 =  number_format(($opportunitiesCanceladasMotivo3/$opportunitiesCanceladas)*100, 0, '.', '');
        }else{
            $porcentajeOpportunitiesCanceladasMotivo1 = 0;
            $porcentajeOpportunitiesCanceladasMotivo2 = 0;
            $porcentajeOpportunitiesCanceladasMotivo3 = 0; 
        }

        // tasa de convercion 
        $oportunidades_ya_visitadas = $opportunitiesVisitadas + $opportunitiesCanceladas;
        if($oportunidades_ya_visitadas != 0 ){
            $porcentajeProspectosTasaConvercion = number_format(($opportunitiesVisitadas/$oportunidades_ya_visitadas)*100, 0, '.', '');
            $porcentajeCanceladasTasaConvercion =  number_format(($opportunitiesCanceladas/$oportunidades_ya_visitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosTasaConvercion = 0;
            $porcentajeCanceladasTasaConvercion = 0;
        }

        $rutasTotal = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id','!=', null)->count();
        $rutasAsignadas = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor','!=', null)->count();
        $rutasPendientes = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', null)->count();
        $rutasSinIniciar = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('estatus', 'Sin iniciar')->count();
        $rutasCurso = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('estatus', 'En proceso')->count();
        $rutasPausa = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('estatus', 'En pausa')->count();
        $rutasFinalizadas = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('estatus', 'Terminado')->count();
        $rutas10 = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('orden_ruta', '10')->count();
        $rutasMenos10 = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('orden_ruta','!=','10')->count();
        //encuestas
        $oportunidades_lista = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id','!=', null)->get();
        $encuestasItems = [];
        $contadorEncuestasItems = 0;
        foreach ($oportunidades_lista as $I => $oportunidades_lista_check) {
            $buscador_encuesta = Forms::where('id_oportunidad',$oportunidades_lista_check->id)->get();
            if(count($buscador_encuesta) == 0){
                //return 'cero';
            }else{
                $encuestasItems[$contadorEncuestasItems] = $buscador_encuesta;
                $contadorEncuestasItems++;
            }           
        }

        $encuestasContenstadas = count($encuestasItems);
        $prospectosVisitasRecurrentes = 0;
        $prospectosNOVisitasRecurrentes = 0;
        $prospectosProductoIUSA = 0;
        $prospectosNOProductoIUSA = 0;

        if($encuestasContenstadas != 0){
            foreach ($encuestasItems as $J => $encuestasItems_check) {
                $encuestasItems_check1 = $encuestasItems_check[0];
                if($encuestasItems_check1['pregunta3'] == 'SI'){
                   $prospectosVisitasRecurrentes++; 
                }else{
                    $prospectosNOVisitasRecurrentes++;
                }

                if($encuestasItems_check1['pregunta1'] == 'SI'){
                    $prospectosProductoIUSA++; 
                 }else{
                     $prospectosNOProductoIUSA++;
                 }
            }//foreach end
             //porcentajes prospectosVisitasRecurrentes
            $porcentajeProspectosVisitasRecurrentes =  number_format(($prospectosVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOVisitasRecurrentes =  number_format(($prospectosNOVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosProductoIUSA =  number_format(($prospectosProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOProductoIUSA =  number_format(($prospectosNOProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosVisitasRecurrentes = 0;
            $porcentajeProspectosNOVisitasRecurrentes = 0;
            $porcentajeProspectosProductoIUSA = 0;
            $porcentajeProspectosNOProductoIUSA = 0;
        }
        //Proceso de Ordenes de compra
        $ordenesItems = [];
        $contadorOedenesItems = 0;
        foreach ($oportunidades_lista as $K => $oportunidades_ordenes_check) {
            $buscador_ordenes = Order::where('idUsuario',$oportunidades_ordenes_check->id)->get();
            if(count($buscador_ordenes) == 0){
                //return 'cero';
            }else{
                foreach ($buscador_ordenes as $L => $buscador_ordenes_check) {
                    $ordenesItems[$contadorOedenesItems] = $buscador_ordenes_check;
                    $contadorOedenesItems++;
                }
            }           
        }

        $PedidoSugeridoTotal = count($ordenesItems);
        $PedidoSugeridoSinEnviar = 0;
        $PedidoSugeridoEnviado = 0;
        $PedidoSugeridoTerminado = 0;
        if($PedidoSugeridoTotal != 0){
            foreach ($ordenesItems as $M => $ordenesItems_check) {
                if($ordenesItems_check['estatus'] == 'PENDIENTE'){
                   $PedidoSugeridoSinEnviar++; 
                }else if($ordenesItems_check['estatus'] == 'ENVIADO'){
                    $PedidoSugeridoEnviado++;
                }else if($ordenesItems_check['estatus'] == 'TERMINADO'){
                    $PedidoSugeridoTerminado++;
                }
            }//foreach end
            //porcentajes Grafica Pedido Sugerido
            $porcentajePedidoSugeridoSinEnviar = number_format(($PedidoSugeridoSinEnviar/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoEnviado = number_format(($PedidoSugeridoEnviado/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoTerminado = number_format(($PedidoSugeridoTerminado/$PedidoSugeridoTotal)*100, 0, '.', '');
        }else{
            $porcentajePedidoSugeridoSinEnviar = 0;
            $porcentajePedidoSugeridoEnviado = 0;
            $porcentajePedidoSugeridoTerminado = 0;
        }
        //obtener ventas por familia, 
        $detallePedidoSugerido = [];
        $count_detallePedidoSugerido = 0;
        foreach ($ordenesItems as $N => $ordenesItemsDetail_check) {
            $buscar_order_detail = OrderDetail::where('orden_compra_id', $ordenesItemsDetail_check['id'])->get();
            if(count($buscar_order_detail) == 0){
                //return 'cero';
            }else{
                foreach ($buscar_order_detail as $O => $buscar_order_detail_check) {
                    $detallePedidoSugerido[$count_detallePedidoSugerido] = $buscar_order_detail_check;
                    $count_detallePedidoSugerido++;
                }
            }
        }
   
        $arrayCobre = array('id'=>'COBRE Y SUS ALEACIONES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayElectricos = array('id'=>'ELECTRICOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayEsIndustriales = array('id'=>'ESPECIALIDADES INDUSTRIALES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayExCatalogo = array('id'=>'EXHIBIDOR Y CATALOGOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayHerramientas = array('id'=>'HERRAMIENTAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayLineaBlanca = array('id'=>'LINEA BLANCA','u_pedidas'=>0,'lista_material'=>[]);
        $arrayAgua = array('id'=>'MANEJO DE AGUA Y GAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayMedical = array('id'=>'MEDICAL CENTER','u_pedidas'=>0,'lista_material'=>[]);
        $resumenVentasFamilia = array($arrayCobre,$arrayElectricos,$arrayEsIndustriales,$arrayExCatalogo,$arrayHerramientas,$arrayLineaBlanca,$arrayAgua,$arrayMedical);
        $total_unidades = 0;
        foreach ($detallePedidoSugerido as $material_pedido) {
            foreach ($resumenVentasFamilia as $k => $re_pedido_sugeridos) {                
                if($material_pedido->division_comercial == $re_pedido_sugeridos['id']){
                    $re_pedido_sugeridos['u_pedidas'] = $re_pedido_sugeridos['u_pedidas'] + $material_pedido->unidades_confirmadas; 
                    $info_pedido_padre= Order::find($material_pedido->orden_compra_id);
                    $material_pedido->folio_pedido = $info_pedido_padre->folio;
                    $material_pedido->nombre_prospecto = $info_pedido_padre->nombreUsuario;
                    array_push($re_pedido_sugeridos['lista_material'], $material_pedido);
                    $resumenVentasFamilia[$k] = $re_pedido_sugeridos;
                }
            }
            $total_unidades = $total_unidades + $material_pedido->unidades_confirmadas;
        }
        //obtener familia mas vendida
        $mayor_ventas_familia=0;
        $mayor_nombre_familia = 'Sin Venta';
        foreach ($resumenVentasFamilia as $P => $re_pedido_sugeridos2) {
            if ($re_pedido_sugeridos2['u_pedidas']>$mayor_ventas_familia){
            $mayor_ventas_familia = $re_pedido_sugeridos2['u_pedidas'];
            $mayor_nombre_familia = $re_pedido_sugeridos2['id'];
            }
            //porcentaje 
            if($total_unidades != 0){
                $porcentaje = ($re_pedido_sugeridos2['u_pedidas']/$total_unidades)*100;
                $porcentaje_format = number_format($porcentaje, 0, '.', '');
            }else{
                $porcentaje_format = 0;
            }
            
            $re_pedido_sugeridos2['porcentaje'] = $porcentaje_format;
            $resumenVentasFamilia[$P] = $re_pedido_sugeridos2;
        }

        //proceso para obtener los prospectos con pedidos ya generados 
        $opportunitiesVisitadasObject = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('bandera_encuesta', '1')->get();
        $ProspectosPedidoSugerido = 0;
        $ProspectoSinPedidoSugerido = 0;
        foreach ($opportunitiesVisitadasObject as $comparador) {
            $bandera = 0;
            foreach ($ordenesItems as $comparador1) {
                if($comparador->id == $comparador1['idUsuario']){
                    $bandera = 1;
                }
            }
            if($bandera == 1){
                $ProspectosPedidoSugerido++;  
            }else{
                $ProspectoSinPedidoSugerido++;
            }
        }
        if($opportunitiesVisitadas != 0){
            //porcentajes ProspectosPedidoSugerido
            $porcentajeProspectosPedidoSugerido =  number_format(($ProspectosPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
            $porcentajeProspectoSinPedidoSugerido =  number_format(($ProspectoSinPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosPedidoSugerido = 0;
            $porcentajeProspectoSinPedidoSugerido = 0;
        }
        

        $datos = [
            'opportunitiesTotal' => $opportunitiesTotal,
            'opportunitiesVisitadas' => $opportunitiesVisitadas,
            'opportunitiesCanceladas' => $opportunitiesCanceladas,
            'opportunitiesCanceladasMotivo1' => $opportunitiesCanceladasMotivo1,
            'opportunitiesCanceladasMotivo2' => $opportunitiesCanceladasMotivo2,
            'opportunitiesCanceladasMotivo3' => $opportunitiesCanceladasMotivo3,
            'porcentajeOpportunitiesCanceladasMotivo1' => $porcentajeOpportunitiesCanceladasMotivo1,
            'porcentajeOpportunitiesCanceladasMotivo2' => $porcentajeOpportunitiesCanceladasMotivo2,
            'porcentajeOpportunitiesCanceladasMotivo3' => $porcentajeOpportunitiesCanceladasMotivo3,
            'opportunitiesAsignadasRuta' => $opportunitiesAsignadasRuta,
            'opportunitiesPendientesAsignadasRuta' => $opportunitiesPendientesAsignadasRuta,
            'opportunitiesPendientesVisitar' => $opportunitiesPendientesVisitar,
            'rutasTotal' => $rutasTotal,
            'rutasAsignadas' => $rutasAsignadas,
            'rutasPendientes' => $rutasPendientes,
            'rutasSinIniciar' => $rutasSinIniciar,
            'rutasCurso' => $rutasCurso,
            'rutasPausa' => $rutasPausa,
            'rutasFinalizadas' => $rutasFinalizadas,
            'rutas10' => $rutas10,
            'rutasMenos10' => $rutasMenos10,
            'encuestasContenstadas' => $encuestasContenstadas,
            'prospectosVisitasRecurrentes' => $prospectosVisitasRecurrentes,
            'prospectosNOVisitasRecurrentes' => $prospectosNOVisitasRecurrentes,
            'porcentajeProspectosVisitasRecurrentes' => $porcentajeProspectosVisitasRecurrentes,
            'porcentajeProspectosNOVisitasRecurrentes' => $porcentajeProspectosNOVisitasRecurrentes,
            'prospectosProductoIUSA' => $prospectosProductoIUSA,
            'prospectosNOProductoIUSA' => $prospectosNOProductoIUSA,
            'porcentajeProspectosProductoIUSA' => $porcentajeProspectosProductoIUSA,
            'porcentajeProspectosNOProductoIUSA' => $porcentajeProspectosNOProductoIUSA,
            'PedidoSugeridoTotal' => $PedidoSugeridoTotal,
            'PedidoSugeridoSinEnviar' => $PedidoSugeridoSinEnviar,
            'PedidoSugeridoEnviado' => $PedidoSugeridoEnviado,
            'PedidoSugeridoTerminado' => $PedidoSugeridoTerminado,
            'porcentajePedidoSugeridoSinEnviar' => $porcentajePedidoSugeridoSinEnviar,
            'porcentajePedidoSugeridoEnviado' => $porcentajePedidoSugeridoEnviado,
            'porcentajePedidoSugeridoTerminado' => $porcentajePedidoSugeridoTerminado,
            'ProspectosPedidoSugerido' => $ProspectosPedidoSugerido,
            'ProspectoSinPedidoSugerido' => $ProspectoSinPedidoSugerido,
            'porcentajeProspectosPedidoSugerido' => $porcentajeProspectosPedidoSugerido,
            'porcentajeProspectoSinPedidoSugerido' => $porcentajeProspectoSinPedidoSugerido,
            'resumenVentasFamilia' => $resumenVentasFamilia,
            'mayor_nombre_familia' => $mayor_nombre_familia,
            'mayor_ventas_familia' => $mayor_ventas_familia,
            'porcentajeOportunidadesProspectos' =>  $porcentajeOportunidadesProspectos,
            'porcentajeOportunidadesCanceladas' => $porcentajeOportunidadesCanceladas,
            'porcentajeOportunidadesPendientesVisitar' => $porcentajeOportunidadesPendientesVisitar,
            'nombre_entidad' => $nombre_entidad,
            'nombre_municipio' => $nombre_municipio,
            'porcentajeProspectosTasaConvercion' => $porcentajeProspectosTasaConvercion,
            'porcentajeCanceladasTasaConvercion' => $porcentajeCanceladasTasaConvercion,
            'oportunidades_nuevas' => $oportunidades_nuevas,
            'porcentajeOportunidades_nuevas' => $porcentajeOportunidades_nuevas
        ]; 

        return response()->json(
            $datos
        );
    }

    public function getRutasSinAsignar(Request $request)
    {
        $rutasPendientesList = RouteList::where('id_promotor', null)->orderBy('entidad', 'ASC')->orderBy('id_municipio', 'ASC')->orderBy('numero_ruta', 'ASC')->get();
        return response()->json(
            $rutasPendientesList 
        );
    }

    public function getReportePromotor(Request $request)
    {
        //find promotor 
        $promotor_object= User::find($request->id_promotor);
        $email_promotor = $promotor_object->email;
        $nombre_promotor= $promotor_object->name;
        $opportunitiesTotal = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->count();
        $opportunitiesVisitadas = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('bandera_encuesta', '1')->count();
        $opportunitiesAsignadasRuta = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('id_ruta','!=', null)->count();
        $opportunitiesPendientesAsignadasRuta = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('id_ruta', null)->count();
        $opportunitiesPendientesVisitar = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('id_ruta','!=', null)->where('bandera_encuesta',null)->where('bandera_cancelada',null)->count();
        $opportunitiesCanceladas = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->count();
        $opportunitiesCanceladasMotivo1 = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'El negocio ya no existe')->count();
        $opportunitiesCanceladasMotivo2 = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'Reubicación de local')->count();
        $opportunitiesCanceladasMotivo3 = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->where('motivo_cancelacion','!=', 'El negocio ya no existe')->where('motivo_cancelacion','!=', 'Reubicación de local')->count();
        //Porcentaje oportunidades
        //Porcentajes opportunitiesCanceladas
        if($opportunitiesVisitadas != 0){
            $porcentajeOportunidadesProspectos =  number_format(($opportunitiesVisitadas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesCanceladas =  number_format(($opportunitiesCanceladas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesPendientesVisitar =  number_format(($opportunitiesPendientesVisitar/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo1 =  number_format(($opportunitiesCanceladasMotivo1/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo2 =  number_format(($opportunitiesCanceladasMotivo2/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo3 =  number_format(($opportunitiesCanceladasMotivo3/$opportunitiesCanceladas)*100, 0, '.', '');
        }else{
            $porcentajeOportunidadesProspectos =  0;
            $porcentajeOportunidadesCanceladas =  0;
            $porcentajeOportunidadesPendientesVisitar = 0;
            $porcentajeOpportunitiesCanceladasMotivo1 = 0;
            $porcentajeOpportunitiesCanceladasMotivo2 = 0;
            $porcentajeOpportunitiesCanceladasMotivo3 = 0;
        }
        //Rutas
        $rutasTotal = RouteList::where('id_promotor', $request->id_promotor)->where('id','!=', null)->count();
        $rutasAsignadas = RouteList::where('id_promotor', $request->id_promotor)->where('id_promotor','!=', null)->count();
        $rutasPendientes = RouteList::where('id_promotor', $request->id_promotor)->where('id_promotor', null)->count();
        $rutasSinIniciar = RouteList::where('id_promotor', $request->id_promotor)->where('estatus', 'Sin iniciar')->count();
        $rutasCurso = RouteList::where('id_promotor', $request->id_promotor)->where('estatus', 'En proceso')->count();
        $rutasPausa = RouteList::where('id_promotor', $request->id_promotor)->where('estatus', 'En pausa')->count();
        $rutasFinalizadas = RouteList::where('id_promotor', $request->id_promotor)->where('estatus', 'Terminado')->count();
        $rutas10 = RouteList::where('id_promotor', $request->id_promotor)->where('orden_ruta', '10')->count();
        $rutasMenos10 = RouteList::where('id_promotor', $request->id_promotor)->where('orden_ruta','!=','10')->count();
        //encuestas
        $oportunidades_lista = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->get();
        $encuestasItems = [];
        $contadorEncuestasItems = 0;
        foreach ($oportunidades_lista as $I => $oportunidades_lista_check) {
            $buscador_encuesta = Forms::where('id_oportunidad',$oportunidades_lista_check->id)->get();
            if(count($buscador_encuesta) == 0){
                //return 'cero';
            }else{
                $encuestasItems[$contadorEncuestasItems] = $buscador_encuesta;
                $contadorEncuestasItems++;
            }           
        }

        $encuestasContenstadas = count($encuestasItems);
        $prospectosVisitasRecurrentes = 0;
        $prospectosNOVisitasRecurrentes = 0;
        $prospectosProductoIUSA = 0;
        $prospectosNOProductoIUSA = 0;

        if($encuestasContenstadas != 0){
            foreach ($encuestasItems as $J => $encuestasItems_check) {
                $encuestasItems_check1 = $encuestasItems_check[0];
                if($encuestasItems_check1['pregunta3'] == 'SI'){
                   $prospectosVisitasRecurrentes++; 
                }else{
                    $prospectosNOVisitasRecurrentes++;
                }

                if($encuestasItems_check1['pregunta1'] == 'SI'){
                    $prospectosProductoIUSA++; 
                 }else{
                     $prospectosNOProductoIUSA++;
                 }
            }//foreach end
             //porcentajes prospectosVisitasRecurrentes
            $porcentajeProspectosVisitasRecurrentes =  number_format(($prospectosVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOVisitasRecurrentes =  number_format(($prospectosNOVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosProductoIUSA =  number_format(($prospectosProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOProductoIUSA =  number_format(($prospectosNOProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosVisitasRecurrentes = 0;
            $porcentajeProspectosNOVisitasRecurrentes = 0;
            $porcentajeProspectosProductoIUSA = 0;
            $porcentajeProspectosNOProductoIUSA = 0;
        }
        //Proceso de Ordenes de compra
        $ordenesItems = [];
        $contadorOedenesItems = 0;
        foreach ($oportunidades_lista as $K => $oportunidades_ordenes_check) {
            $buscador_ordenes = Order::where('idUsuario',$oportunidades_ordenes_check->id)->get();
            if(count($buscador_ordenes) == 0){
                //return 'cero';
            }else{
                foreach ($buscador_ordenes as $L => $buscador_ordenes_check) {
                    $ordenesItems[$contadorOedenesItems] = $buscador_ordenes_check;
                    $contadorOedenesItems++;
                }
            }           
        }

        $PedidoSugeridoTotal = count($ordenesItems);
        $PedidoSugeridoSinEnviar = 0;
        $PedidoSugeridoEnviado = 0;
        $PedidoSugeridoTerminado = 0;
        if($PedidoSugeridoTotal != 0){
            foreach ($ordenesItems as $M => $ordenesItems_check) {
                if($ordenesItems_check['estatus'] == 'PENDIENTE'){
                   $PedidoSugeridoSinEnviar++; 
                }else if($ordenesItems_check['estatus'] == 'ENVIADO'){
                    $PedidoSugeridoEnviado++;
                }else if($ordenesItems_check['estatus'] == 'TERMINADO'){
                    $PedidoSugeridoTerminado++;
                }
            }//foreach end
            //porcentajes Grafica Pedido Sugerido
            $porcentajePedidoSugeridoSinEnviar = number_format(($PedidoSugeridoSinEnviar/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoEnviado = number_format(($PedidoSugeridoEnviado/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoTerminado = number_format(($PedidoSugeridoTerminado/$PedidoSugeridoTotal)*100, 0, '.', '');
        }else{
            $porcentajePedidoSugeridoSinEnviar = 0;
            $porcentajePedidoSugeridoEnviado = 0;
            $porcentajePedidoSugeridoTerminado = 0;
        }
        //obtener ventas por familia, 
        $detallePedidoSugerido = [];
        $count_detallePedidoSugerido = 0;
        foreach ($ordenesItems as $N => $ordenesItemsDetail_check) {
            $buscar_order_detail = OrderDetail::where('orden_compra_id', $ordenesItemsDetail_check['id'])->get();
            if(count($buscar_order_detail) == 0){
                //return 'cero';
            }else{
                foreach ($buscar_order_detail as $O => $buscar_order_detail_check) {
                    $detallePedidoSugerido[$count_detallePedidoSugerido] = $buscar_order_detail_check;
                    $count_detallePedidoSugerido++;
                }
            }
        }
   
        $arrayCobre = array('id'=>'COBRE Y SUS ALEACIONES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayElectricos = array('id'=>'ELECTRICOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayEsIndustriales = array('id'=>'ESPECIALIDADES INDUSTRIALES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayExCatalogo = array('id'=>'EXHIBIDOR Y CATALOGOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayHerramientas = array('id'=>'HERRAMIENTAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayLineaBlanca = array('id'=>'LINEA BLANCA','u_pedidas'=>0,'lista_material'=>[]);
        $arrayAgua = array('id'=>'MANEJO DE AGUA Y GAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayMedical = array('id'=>'MEDICAL CENTER','u_pedidas'=>0,'lista_material'=>[]);
        $resumenVentasFamilia = array($arrayCobre,$arrayElectricos,$arrayEsIndustriales,$arrayExCatalogo,$arrayHerramientas,$arrayLineaBlanca,$arrayAgua,$arrayMedical);
        $total_unidades = 0;
        foreach ($detallePedidoSugerido as $material_pedido) {
            foreach ($resumenVentasFamilia as $k => $re_pedido_sugeridos) {                
                if($material_pedido->division_comercial == $re_pedido_sugeridos['id']){
                    $re_pedido_sugeridos['u_pedidas'] = $re_pedido_sugeridos['u_pedidas'] + $material_pedido->unidades_confirmadas; 
                    $info_pedido_padre= Order::find($material_pedido->orden_compra_id);
                    $material_pedido->folio_pedido = $info_pedido_padre->folio;
                    $material_pedido->nombre_prospecto = $info_pedido_padre->nombreUsuario;
                    array_push($re_pedido_sugeridos['lista_material'], $material_pedido);
                    $resumenVentasFamilia[$k] = $re_pedido_sugeridos;
                }
            }
            $total_unidades = $total_unidades + $material_pedido->unidades_confirmadas;
        }
        //obtener familia mas vendida
        $mayor_ventas_familia=0;
        $mayor_nombre_familia = 'Sin Venta';
        foreach ($resumenVentasFamilia as $P => $re_pedido_sugeridos2) {
            if ($re_pedido_sugeridos2['u_pedidas']>$mayor_ventas_familia){
            $mayor_ventas_familia = $re_pedido_sugeridos2['u_pedidas'];
            $mayor_nombre_familia = $re_pedido_sugeridos2['id'];
            }
            //porcentaje 
            if($total_unidades != 0){
                $porcentaje = ($re_pedido_sugeridos2['u_pedidas']/$total_unidades)*100;
                $porcentaje_format = number_format($porcentaje, 0, '.', '');
            }else{
                $porcentaje_format = 0;
            }
            
            $re_pedido_sugeridos2['porcentaje'] = $porcentaje_format;
            $resumenVentasFamilia[$P] = $re_pedido_sugeridos2;
        }

        //proceso para obtener los prospectos con pedidos ya generados 
        $opportunitiesVisitadasObject = OpportunitiesINEGI::where('id_promotor', $request->id_promotor)->where('bandera_encuesta', '1')->get();
        $ProspectosPedidoSugerido = 0;
        $ProspectoSinPedidoSugerido = 0;
        foreach ($opportunitiesVisitadasObject as $comparador) {
            $bandera = 0;
            foreach ($ordenesItems as $comparador1) {
                if($comparador->id == $comparador1['idUsuario']){
                    $bandera = 1;
                }
            }
            if($bandera == 1){
                $ProspectosPedidoSugerido++;  
            }else{
                $ProspectoSinPedidoSugerido++;
            }
        }
        if($opportunitiesVisitadas != 0){
            //porcentajes ProspectosPedidoSugerido
            $porcentajeProspectosPedidoSugerido =  number_format(($ProspectosPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
            $porcentajeProspectoSinPedidoSugerido =  number_format(($ProspectoSinPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosPedidoSugerido = 0;
            $porcentajeProspectoSinPedidoSugerido = 0;
        }
        

        $datos = [
            'opportunitiesTotal' => $opportunitiesTotal,
            'opportunitiesVisitadas' => $opportunitiesVisitadas,
            'opportunitiesCanceladas' => $opportunitiesCanceladas,
            'opportunitiesCanceladasMotivo1' => $opportunitiesCanceladasMotivo1,
            'opportunitiesCanceladasMotivo2' => $opportunitiesCanceladasMotivo2,
            'opportunitiesCanceladasMotivo3' => $opportunitiesCanceladasMotivo3,
            'porcentajeOpportunitiesCanceladasMotivo1' => $porcentajeOpportunitiesCanceladasMotivo1,
            'porcentajeOpportunitiesCanceladasMotivo2' => $porcentajeOpportunitiesCanceladasMotivo2,
            'porcentajeOpportunitiesCanceladasMotivo3' => $porcentajeOpportunitiesCanceladasMotivo3,
            'opportunitiesAsignadasRuta' => $opportunitiesAsignadasRuta,
            'opportunitiesPendientesAsignadasRuta' => $opportunitiesPendientesAsignadasRuta,
            'opportunitiesPendientesVisitar' => $opportunitiesPendientesVisitar,
            'rutasTotal' => $rutasTotal,
            'rutasAsignadas' => $rutasAsignadas,
            'rutasPendientes' => $rutasPendientes,
            'rutasSinIniciar' => $rutasSinIniciar,
            'rutasCurso' => $rutasCurso,
            'rutasPausa' => $rutasPausa,
            'rutasFinalizadas' => $rutasFinalizadas,
            'rutas10' => $rutas10,
            'rutasMenos10' => $rutasMenos10,
            'encuestasContenstadas' => $encuestasContenstadas,
            'prospectosVisitasRecurrentes' => $prospectosVisitasRecurrentes,
            'prospectosNOVisitasRecurrentes' => $prospectosNOVisitasRecurrentes,
            'porcentajeProspectosVisitasRecurrentes' => $porcentajeProspectosVisitasRecurrentes,
            'porcentajeProspectosNOVisitasRecurrentes' => $porcentajeProspectosNOVisitasRecurrentes,
            'prospectosProductoIUSA' => $prospectosProductoIUSA,
            'prospectosNOProductoIUSA' => $prospectosNOProductoIUSA,
            'porcentajeProspectosProductoIUSA' => $porcentajeProspectosProductoIUSA,
            'porcentajeProspectosNOProductoIUSA' => $porcentajeProspectosNOProductoIUSA,
            'PedidoSugeridoTotal' => $PedidoSugeridoTotal,
            'PedidoSugeridoSinEnviar' => $PedidoSugeridoSinEnviar,
            'PedidoSugeridoEnviado' => $PedidoSugeridoEnviado,
            'PedidoSugeridoTerminado' => $PedidoSugeridoTerminado,
            'porcentajePedidoSugeridoSinEnviar' => $porcentajePedidoSugeridoSinEnviar,
            'porcentajePedidoSugeridoEnviado' => $porcentajePedidoSugeridoEnviado,
            'porcentajePedidoSugeridoTerminado' => $porcentajePedidoSugeridoTerminado,
            'ProspectosPedidoSugerido' => $ProspectosPedidoSugerido,
            'ProspectoSinPedidoSugerido' => $ProspectoSinPedidoSugerido,
            'porcentajeProspectosPedidoSugerido' => $porcentajeProspectosPedidoSugerido,
            'porcentajeProspectoSinPedidoSugerido' => $porcentajeProspectoSinPedidoSugerido,
            'resumenVentasFamilia' => $resumenVentasFamilia,
            'mayor_nombre_familia' => $mayor_nombre_familia,
            'mayor_ventas_familia' => $mayor_ventas_familia,
            'porcentajeOportunidadesProspectos' =>  $porcentajeOportunidadesProspectos,
            'porcentajeOportunidadesCanceladas' => $porcentajeOportunidadesCanceladas,
            'porcentajeOportunidadesPendientesVisitar' => $porcentajeOportunidadesPendientesVisitar,
            'email_promotor' => $email_promotor,
            'nombre_promotor' => $nombre_promotor
        ]; 

        return response()->json(
            $datos
        );
    }//end getReportePromotor
    

    public function getReportePromotorMunicipio(Request $request)
    {
        //find promotor 
        $promotor_object= User::find($request->id_promotor);
        $email_promotor = $promotor_object->email;
        $nombre_promotor= $promotor_object->name;
        //nombre de entidad y municipio 
        $entidad = Entidad::where('clave',$request->id_entidad)->get();
        $municipio = Municipio::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->get();
        $entidad = $entidad->first();
        $municipio = $municipio->first();
        $nombre_entidad = $entidad->nombre;
        $nombre_municipio = $municipio->nombre;

        $opportunitiesTotal = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->count();
        $opportunitiesVisitadas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('bandera_encuesta', '1')->count();
        $opportunitiesAsignadasRuta = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('id_ruta','!=', null)->count();
        $opportunitiesPendientesAsignadasRuta = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('id_ruta', null)->count();
        $opportunitiesPendientesVisitar = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('id_ruta','!=', null)->where('bandera_encuesta',null)->where('bandera_cancelada',null)->count();
        $opportunitiesCanceladas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->count();
        $opportunitiesCanceladasMotivo1 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'El negocio ya no existe')->count();
        $opportunitiesCanceladasMotivo2 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'Reubicación de local')->count();
        $opportunitiesCanceladasMotivo3 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('bandera_cancelada', '1')->where('motivo_cancelacion','!=', 'El negocio ya no existe')->where('motivo_cancelacion','!=', 'Reubicación de local')->count();
        //Porcentaje oportunidades
        //Porcentajes opportunitiesCanceladas
        if($opportunitiesVisitadas != 0){
            $porcentajeOportunidadesProspectos =  number_format(($opportunitiesVisitadas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesCanceladas =  number_format(($opportunitiesCanceladas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesPendientesVisitar =  number_format(($opportunitiesPendientesVisitar/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo1 =  number_format(($opportunitiesCanceladasMotivo1/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo2 =  number_format(($opportunitiesCanceladasMotivo2/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo3 =  number_format(($opportunitiesCanceladasMotivo3/$opportunitiesCanceladas)*100, 0, '.', '');
        }else{
            $porcentajeOportunidadesProspectos =  0;
            $porcentajeOportunidadesCanceladas =  0;
            $porcentajeOportunidadesPendientesVisitar = 0;
            $porcentajeOpportunitiesCanceladasMotivo1 = 0;
            $porcentajeOpportunitiesCanceladasMotivo2 = 0;
            $porcentajeOpportunitiesCanceladasMotivo3 = 0;
        }
        //Rutas
        $rutasTotal = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('id','!=', null)->count();
        $rutasAsignadas = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('id_promotor','!=', null)->count();
        $rutasPendientes = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('id_promotor', null)->count();
        $rutasSinIniciar = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('estatus', 'Sin iniciar')->count();
        $rutasCurso = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('estatus', 'En proceso')->count();
        $rutasPausa = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('estatus', 'En pausa')->count();
        $rutasFinalizadas = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('estatus', 'Terminado')->count();
        $rutas10 = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('orden_ruta', '10')->count();
        $rutasMenos10 = RouteList::where('id_entidad',$request->id_entidad)->where('id_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('orden_ruta','!=','10')->count();
        //encuestas
        $oportunidades_lista = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->get();
        $encuestasItems = [];
        $contadorEncuestasItems = 0;
        foreach ($oportunidades_lista as $I => $oportunidades_lista_check) {
            $buscador_encuesta = Forms::where('id_oportunidad',$oportunidades_lista_check->id)->get();
            if(count($buscador_encuesta) == 0){
                //return 'cero';
            }else{
                $encuestasItems[$contadorEncuestasItems] = $buscador_encuesta;
                $contadorEncuestasItems++;
            }           
        }

        $encuestasContenstadas = count($encuestasItems);
        $prospectosVisitasRecurrentes = 0;
        $prospectosNOVisitasRecurrentes = 0;
        $prospectosProductoIUSA = 0;
        $prospectosNOProductoIUSA = 0;

        if($encuestasContenstadas != 0){
            foreach ($encuestasItems as $J => $encuestasItems_check) {
                $encuestasItems_check1 = $encuestasItems_check[0];
                if($encuestasItems_check1['pregunta3'] == 'SI'){
                   $prospectosVisitasRecurrentes++; 
                }else{
                    $prospectosNOVisitasRecurrentes++;
                }

                if($encuestasItems_check1['pregunta1'] == 'SI'){
                    $prospectosProductoIUSA++; 
                 }else{
                     $prospectosNOProductoIUSA++;
                 }
            }//foreach end
             //porcentajes prospectosVisitasRecurrentes
            $porcentajeProspectosVisitasRecurrentes =  number_format(($prospectosVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOVisitasRecurrentes =  number_format(($prospectosNOVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosProductoIUSA =  number_format(($prospectosProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOProductoIUSA =  number_format(($prospectosNOProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosVisitasRecurrentes = 0;
            $porcentajeProspectosNOVisitasRecurrentes = 0;
            $porcentajeProspectosProductoIUSA = 0;
            $porcentajeProspectosNOProductoIUSA = 0;
        }
        //Proceso de Ordenes de compra
        $ordenesItems = [];
        $contadorOedenesItems = 0;
        foreach ($oportunidades_lista as $K => $oportunidades_ordenes_check) {
            $buscador_ordenes = Order::where('idUsuario',$oportunidades_ordenes_check->id)->get();
            if(count($buscador_ordenes) == 0){
                //return 'cero';
            }else{
                foreach ($buscador_ordenes as $L => $buscador_ordenes_check) {
                    $ordenesItems[$contadorOedenesItems] = $buscador_ordenes_check;
                    $contadorOedenesItems++;
                }
            }           
        }

        $PedidoSugeridoTotal = count($ordenesItems);
        $PedidoSugeridoSinEnviar = 0;
        $PedidoSugeridoEnviado = 0;
        $PedidoSugeridoTerminado = 0;
        if($PedidoSugeridoTotal != 0){
            foreach ($ordenesItems as $M => $ordenesItems_check) {
                if($ordenesItems_check['estatus'] == 'PENDIENTE'){
                   $PedidoSugeridoSinEnviar++; 
                }else if($ordenesItems_check['estatus'] == 'ENVIADO'){
                    $PedidoSugeridoEnviado++;
                }else if($ordenesItems_check['estatus'] == 'TERMINADO'){
                    $PedidoSugeridoTerminado++;
                }
            }//foreach end
            //porcentajes Grafica Pedido Sugerido
            $porcentajePedidoSugeridoSinEnviar = number_format(($PedidoSugeridoSinEnviar/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoEnviado = number_format(($PedidoSugeridoEnviado/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoTerminado = number_format(($PedidoSugeridoTerminado/$PedidoSugeridoTotal)*100, 0, '.', '');
        }else{
            $porcentajePedidoSugeridoSinEnviar = 0;
            $porcentajePedidoSugeridoEnviado = 0;
            $porcentajePedidoSugeridoTerminado = 0;
        }
        //obtener ventas por familia, 
        $detallePedidoSugerido = [];
        $count_detallePedidoSugerido = 0;
        foreach ($ordenesItems as $N => $ordenesItemsDetail_check) {
            $buscar_order_detail = OrderDetail::where('orden_compra_id', $ordenesItemsDetail_check['id'])->get();
            if(count($buscar_order_detail) == 0){
                //return 'cero';
            }else{
                foreach ($buscar_order_detail as $O => $buscar_order_detail_check) {
                    $detallePedidoSugerido[$count_detallePedidoSugerido] = $buscar_order_detail_check;
                    $count_detallePedidoSugerido++;
                }
            }
        }
   
        $arrayCobre = array('id'=>'COBRE Y SUS ALEACIONES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayElectricos = array('id'=>'ELECTRICOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayEsIndustriales = array('id'=>'ESPECIALIDADES INDUSTRIALES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayExCatalogo = array('id'=>'EXHIBIDOR Y CATALOGOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayHerramientas = array('id'=>'HERRAMIENTAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayLineaBlanca = array('id'=>'LINEA BLANCA','u_pedidas'=>0,'lista_material'=>[]);
        $arrayAgua = array('id'=>'MANEJO DE AGUA Y GAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayMedical = array('id'=>'MEDICAL CENTER','u_pedidas'=>0,'lista_material'=>[]);
        $resumenVentasFamilia = array($arrayCobre,$arrayElectricos,$arrayEsIndustriales,$arrayExCatalogo,$arrayHerramientas,$arrayLineaBlanca,$arrayAgua,$arrayMedical);
        $total_unidades = 0;
        foreach ($detallePedidoSugerido as $material_pedido) {
            foreach ($resumenVentasFamilia as $k => $re_pedido_sugeridos) {                
                if($material_pedido->division_comercial == $re_pedido_sugeridos['id']){
                    $re_pedido_sugeridos['u_pedidas'] = $re_pedido_sugeridos['u_pedidas'] + $material_pedido->unidades_confirmadas; 
                    $info_pedido_padre= Order::find($material_pedido->orden_compra_id);
                    $material_pedido->folio_pedido = $info_pedido_padre->folio;
                    $material_pedido->nombre_prospecto = $info_pedido_padre->nombreUsuario;
                    array_push($re_pedido_sugeridos['lista_material'], $material_pedido);
                    $resumenVentasFamilia[$k] = $re_pedido_sugeridos;
                }
            }
            $total_unidades = $total_unidades + $material_pedido->unidades_confirmadas;
        }
        //obtener familia mas vendida
        $mayor_ventas_familia=0;
        $mayor_nombre_familia = 'Sin Venta';
        foreach ($resumenVentasFamilia as $P => $re_pedido_sugeridos2) {
            if ($re_pedido_sugeridos2['u_pedidas']>$mayor_ventas_familia){
            $mayor_ventas_familia = $re_pedido_sugeridos2['u_pedidas'];
            $mayor_nombre_familia = $re_pedido_sugeridos2['id'];
            }
            //porcentaje 
            if($total_unidades != 0){
                $porcentaje = ($re_pedido_sugeridos2['u_pedidas']/$total_unidades)*100;
                $porcentaje_format = number_format($porcentaje, 0, '.', '');
            }else{
                $porcentaje_format = 0;
            }
            
            $re_pedido_sugeridos2['porcentaje'] = $porcentaje_format;
            $resumenVentasFamilia[$P] = $re_pedido_sugeridos2;
        }

        //proceso para obtener los prospectos con pedidos ya generados 
        $opportunitiesVisitadasObject = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->where('id_promotor', $request->id_promotor)->where('bandera_encuesta', '1')->get();
        $ProspectosPedidoSugerido = 0;
        $ProspectoSinPedidoSugerido = 0;
        foreach ($opportunitiesVisitadasObject as $comparador) {
            $bandera = 0;
            foreach ($ordenesItems as $comparador1) {
                if($comparador->id == $comparador1['idUsuario']){
                    $bandera = 1;
                }
            }
            if($bandera == 1){
                $ProspectosPedidoSugerido++;  
            }else{
                $ProspectoSinPedidoSugerido++;
            }
        }
        if($opportunitiesVisitadas != 0){
            //porcentajes ProspectosPedidoSugerido
            $porcentajeProspectosPedidoSugerido =  number_format(($ProspectosPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
            $porcentajeProspectoSinPedidoSugerido =  number_format(($ProspectoSinPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosPedidoSugerido = 0;
            $porcentajeProspectoSinPedidoSugerido = 0;
        }
        

        $datos = [
            'opportunitiesTotal' => $opportunitiesTotal,
            'opportunitiesVisitadas' => $opportunitiesVisitadas,
            'opportunitiesCanceladas' => $opportunitiesCanceladas,
            'opportunitiesCanceladasMotivo1' => $opportunitiesCanceladasMotivo1,
            'opportunitiesCanceladasMotivo2' => $opportunitiesCanceladasMotivo2,
            'opportunitiesCanceladasMotivo3' => $opportunitiesCanceladasMotivo3,
            'porcentajeOpportunitiesCanceladasMotivo1' => $porcentajeOpportunitiesCanceladasMotivo1,
            'porcentajeOpportunitiesCanceladasMotivo2' => $porcentajeOpportunitiesCanceladasMotivo2,
            'porcentajeOpportunitiesCanceladasMotivo3' => $porcentajeOpportunitiesCanceladasMotivo3,
            'opportunitiesAsignadasRuta' => $opportunitiesAsignadasRuta,
            'opportunitiesPendientesAsignadasRuta' => $opportunitiesPendientesAsignadasRuta,
            'opportunitiesPendientesVisitar' => $opportunitiesPendientesVisitar,
            'rutasTotal' => $rutasTotal,
            'rutasAsignadas' => $rutasAsignadas,
            'rutasPendientes' => $rutasPendientes,
            'rutasSinIniciar' => $rutasSinIniciar,
            'rutasCurso' => $rutasCurso,
            'rutasPausa' => $rutasPausa,
            'rutasFinalizadas' => $rutasFinalizadas,
            'rutas10' => $rutas10,
            'rutasMenos10' => $rutasMenos10,
            'encuestasContenstadas' => $encuestasContenstadas,
            'prospectosVisitasRecurrentes' => $prospectosVisitasRecurrentes,
            'prospectosNOVisitasRecurrentes' => $prospectosNOVisitasRecurrentes,
            'porcentajeProspectosVisitasRecurrentes' => $porcentajeProspectosVisitasRecurrentes,
            'porcentajeProspectosNOVisitasRecurrentes' => $porcentajeProspectosNOVisitasRecurrentes,
            'prospectosProductoIUSA' => $prospectosProductoIUSA,
            'prospectosNOProductoIUSA' => $prospectosNOProductoIUSA,
            'porcentajeProspectosProductoIUSA' => $porcentajeProspectosProductoIUSA,
            'porcentajeProspectosNOProductoIUSA' => $porcentajeProspectosNOProductoIUSA,
            'PedidoSugeridoTotal' => $PedidoSugeridoTotal,
            'PedidoSugeridoSinEnviar' => $PedidoSugeridoSinEnviar,
            'PedidoSugeridoEnviado' => $PedidoSugeridoEnviado,
            'PedidoSugeridoTerminado' => $PedidoSugeridoTerminado,
            'porcentajePedidoSugeridoSinEnviar' => $porcentajePedidoSugeridoSinEnviar,
            'porcentajePedidoSugeridoEnviado' => $porcentajePedidoSugeridoEnviado,
            'porcentajePedidoSugeridoTerminado' => $porcentajePedidoSugeridoTerminado,
            'ProspectosPedidoSugerido' => $ProspectosPedidoSugerido,
            'ProspectoSinPedidoSugerido' => $ProspectoSinPedidoSugerido,
            'porcentajeProspectosPedidoSugerido' => $porcentajeProspectosPedidoSugerido,
            'porcentajeProspectoSinPedidoSugerido' => $porcentajeProspectoSinPedidoSugerido,
            'resumenVentasFamilia' => $resumenVentasFamilia,
            'mayor_nombre_familia' => $mayor_nombre_familia,
            'mayor_ventas_familia' => $mayor_ventas_familia,
            'porcentajeOportunidadesProspectos' =>  $porcentajeOportunidadesProspectos,
            'porcentajeOportunidadesCanceladas' => $porcentajeOportunidadesCanceladas,
            'porcentajeOportunidadesPendientesVisitar' => $porcentajeOportunidadesPendientesVisitar,
            'email_promotor' => $email_promotor,
            'nombre_promotor' => $nombre_promotor,
            'nombre_entidad' => $nombre_entidad,
            'nombre_municipio' => $nombre_municipio
        ]; 

        return response()->json(
            $datos
        );
    }//end getReportePromotorMunicipio


    public function getReporteEstado(Request $request)
    {
        //nombre de entidad y municipio 
        $entidad = Entidad::where('clave',$request->id_entidad)->get();
        //$municipio = Municipio::where('clave_entidad',$request->id_entidad)->where('clave_municipio',$request->id_municipio)->get();
        $entidad = $entidad->first();
        //$municipio = $municipio->first();
        $nombre_entidad = $entidad->nombre;
        //$nombre_municipio = $municipio->nombre;

        $rutasTotal = RouteList::where('id_entidad',$request->id_entidad)->where('id','!=', null)->count();

        $opportunitiesTotal = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('id','!=', null)->count();
        $opportunitiesVisitadas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('bandera_encuesta', '1')->count();
        $opportunitiesAsignadasRuta = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('id_ruta','!=', null)->count();
        $opportunitiesPendientesAsignadasRuta = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('id_ruta', null)->count();
        $opportunitiesPendientesVisitar = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('id_ruta','!=', null)->where('bandera_encuesta', null)->where('bandera_cancelada', null)->count();
        $opportunitiesCanceladas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('bandera_cancelada', '1')->count();
        $opportunitiesCanceladasMotivo1 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'El negocio ya no existe')->count();
        $opportunitiesCanceladasMotivo2 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('bandera_cancelada', '1')->where('motivo_cancelacion', 'Reubicación de local')->count();
        $opportunitiesCanceladasMotivo3 = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('bandera_cancelada', '1')->where('motivo_cancelacion','!=', 'El negocio ya no existe')->where('motivo_cancelacion','!=', 'Reubicación de local')->count();
        $oportunidades_nuevas = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('bandera_nuevo', 1)->count();
        //Porcentaje oportunidades
        //Porcentajes opportunitiesCanceladas
        if($opportunitiesVisitadas != 0){
            $porcentajeOportunidadesProspectos =  number_format(($opportunitiesVisitadas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesCanceladas =  number_format(($opportunitiesCanceladas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidadesPendientesVisitar =  number_format(($opportunitiesPendientesVisitar/$opportunitiesAsignadasRuta)*100, 0, '.', '');
            $porcentajeOportunidades_nuevas =  number_format(($oportunidades_nuevas/$opportunitiesAsignadasRuta)*100, 0, '.', '');
        }else{
            $porcentajeOportunidadesProspectos =  0;
            $porcentajeOportunidadesCanceladas =  0;
            $porcentajeOportunidadesPendientesVisitar = 0;    
            $porcentajeOportunidades_nuevas = 0;       
        }  
        
        
        if($opportunitiesCanceladas != 0){
            $porcentajeOpportunitiesCanceladasMotivo1 =  number_format(($opportunitiesCanceladasMotivo1/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo2 =  number_format(($opportunitiesCanceladasMotivo2/$opportunitiesCanceladas)*100, 0, '.', '');
            $porcentajeOpportunitiesCanceladasMotivo3 =  number_format(($opportunitiesCanceladasMotivo3/$opportunitiesCanceladas)*100, 0, '.', '');
        }else{
            $porcentajeOpportunitiesCanceladasMotivo1 = 0;
            $porcentajeOpportunitiesCanceladasMotivo2 = 0;
            $porcentajeOpportunitiesCanceladasMotivo3 = 0; 
        }

        // tasa de convercion 
        $oportunidades_ya_visitadas = $opportunitiesVisitadas + $opportunitiesCanceladas;
        if($oportunidades_ya_visitadas != 0 ){
            $porcentajeProspectosTasaConvercion = number_format(($opportunitiesVisitadas/$oportunidades_ya_visitadas)*100, 0, '.', '');
            $porcentajeCanceladasTasaConvercion =  number_format(($opportunitiesCanceladas/$oportunidades_ya_visitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosTasaConvercion = 0;
            $porcentajeCanceladasTasaConvercion = 0;
        }

        $rutasTotal = RouteList::where('id_entidad',$request->id_entidad)->where('id','!=', null)->count();
        $rutasAsignadas = RouteList::where('id_entidad',$request->id_entidad)->where('id_promotor','!=', null)->count();
        $rutasPendientes = RouteList::where('id_entidad',$request->id_entidad)->where('id_promotor', null)->count();
        $rutasSinIniciar = RouteList::where('id_entidad',$request->id_entidad)->where('estatus', 'Sin iniciar')->count();
        $rutasCurso = RouteList::where('id_entidad',$request->id_entidad)->where('estatus', 'En proceso')->count();
        $rutasPausa = RouteList::where('id_entidad',$request->id_entidad)->where('estatus', 'En pausa')->count();
        $rutasFinalizadas = RouteList::where('id_entidad',$request->id_entidad)->where('estatus', 'Terminado')->count();
        $rutas10 = RouteList::where('id_entidad',$request->id_entidad)->where('orden_ruta', '10')->count();
        $rutasMenos10 = RouteList::where('id_entidad',$request->id_entidad)->where('orden_ruta','!=','10')->count();
        //encuestas
        $oportunidades_lista = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('id','!=', null)->get();
        $encuestasItems = [];
        $contadorEncuestasItems = 0;
        foreach ($oportunidades_lista as $I => $oportunidades_lista_check) {
            $buscador_encuesta = Forms::where('id_oportunidad',$oportunidades_lista_check->id)->get();
            if(count($buscador_encuesta) == 0){
                //return 'cero';
            }else{
                $encuestasItems[$contadorEncuestasItems] = $buscador_encuesta;
                $contadorEncuestasItems++;
            }           
        }

        $encuestasContenstadas = count($encuestasItems);
        $prospectosVisitasRecurrentes = 0;
        $prospectosNOVisitasRecurrentes = 0;
        $prospectosProductoIUSA = 0;
        $prospectosNOProductoIUSA = 0;

        if($encuestasContenstadas != 0){
            foreach ($encuestasItems as $J => $encuestasItems_check) {
                $encuestasItems_check1 = $encuestasItems_check[0];
                if($encuestasItems_check1['pregunta3'] == 'SI'){
                   $prospectosVisitasRecurrentes++; 
                }else{
                    $prospectosNOVisitasRecurrentes++;
                }

                if($encuestasItems_check1['pregunta1'] == 'SI'){
                    $prospectosProductoIUSA++; 
                 }else{
                     $prospectosNOProductoIUSA++;
                 }
            }//foreach end
             //porcentajes prospectosVisitasRecurrentes
            $porcentajeProspectosVisitasRecurrentes =  number_format(($prospectosVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOVisitasRecurrentes =  number_format(($prospectosNOVisitasRecurrentes/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosProductoIUSA =  number_format(($prospectosProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
            $porcentajeProspectosNOProductoIUSA =  number_format(($prospectosNOProductoIUSA/$encuestasContenstadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosVisitasRecurrentes = 0;
            $porcentajeProspectosNOVisitasRecurrentes = 0;
            $porcentajeProspectosProductoIUSA = 0;
            $porcentajeProspectosNOProductoIUSA = 0;
        }
        //Proceso de Ordenes de compra
        $ordenesItems = [];
        $contadorOedenesItems = 0;
        foreach ($oportunidades_lista as $K => $oportunidades_ordenes_check) {
            $buscador_ordenes = Order::where('idUsuario',$oportunidades_ordenes_check->id)->get();
            if(count($buscador_ordenes) == 0){
                //return 'cero';
            }else{
                foreach ($buscador_ordenes as $L => $buscador_ordenes_check) {
                    $ordenesItems[$contadorOedenesItems] = $buscador_ordenes_check;
                    $contadorOedenesItems++;
                }
            }           
        }

        $PedidoSugeridoTotal = count($ordenesItems);
        $PedidoSugeridoSinEnviar = 0;
        $PedidoSugeridoEnviado = 0;
        $PedidoSugeridoTerminado = 0;
        if($PedidoSugeridoTotal != 0){
            foreach ($ordenesItems as $M => $ordenesItems_check) {
                if($ordenesItems_check['estatus'] == 'PENDIENTE'){
                   $PedidoSugeridoSinEnviar++; 
                }else if($ordenesItems_check['estatus'] == 'ENVIADO'){
                    $PedidoSugeridoEnviado++;
                }else if($ordenesItems_check['estatus'] == 'TERMINADO'){
                    $PedidoSugeridoTerminado++;
                }
            }//foreach end
            //porcentajes Grafica Pedido Sugerido
            $porcentajePedidoSugeridoSinEnviar = number_format(($PedidoSugeridoSinEnviar/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoEnviado = number_format(($PedidoSugeridoEnviado/$PedidoSugeridoTotal)*100, 0, '.', '');
            $porcentajePedidoSugeridoTerminado = number_format(($PedidoSugeridoTerminado/$PedidoSugeridoTotal)*100, 0, '.', '');
        }else{
            $porcentajePedidoSugeridoSinEnviar = 0;
            $porcentajePedidoSugeridoEnviado = 0;
            $porcentajePedidoSugeridoTerminado = 0;
        }
        //obtener ventas por familia, 
        $detallePedidoSugerido = [];
        $count_detallePedidoSugerido = 0;
        foreach ($ordenesItems as $N => $ordenesItemsDetail_check) {
            $buscar_order_detail = OrderDetail::where('orden_compra_id', $ordenesItemsDetail_check['id'])->get();
            if(count($buscar_order_detail) == 0){
                //return 'cero';
            }else{
                foreach ($buscar_order_detail as $O => $buscar_order_detail_check) {
                    $detallePedidoSugerido[$count_detallePedidoSugerido] = $buscar_order_detail_check;
                    $count_detallePedidoSugerido++;
                }
            }
        }
   
        $arrayCobre = array('id'=>'COBRE Y SUS ALEACIONES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayElectricos = array('id'=>'ELECTRICOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayEsIndustriales = array('id'=>'ESPECIALIDADES INDUSTRIALES','u_pedidas'=>0,'lista_material'=>[]);
        $arrayExCatalogo = array('id'=>'EXHIBIDOR Y CATALOGOS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayHerramientas = array('id'=>'HERRAMIENTAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayLineaBlanca = array('id'=>'LINEA BLANCA','u_pedidas'=>0,'lista_material'=>[]);
        $arrayAgua = array('id'=>'MANEJO DE AGUA Y GAS','u_pedidas'=>0,'lista_material'=>[]);
        $arrayMedical = array('id'=>'MEDICAL CENTER','u_pedidas'=>0,'lista_material'=>[]);
        $resumenVentasFamilia = array($arrayCobre,$arrayElectricos,$arrayEsIndustriales,$arrayExCatalogo,$arrayHerramientas,$arrayLineaBlanca,$arrayAgua,$arrayMedical);
        $total_unidades = 0;
        foreach ($detallePedidoSugerido as $material_pedido) {
            foreach ($resumenVentasFamilia as $k => $re_pedido_sugeridos) {                
                if($material_pedido->division_comercial == $re_pedido_sugeridos['id']){
                    $re_pedido_sugeridos['u_pedidas'] = $re_pedido_sugeridos['u_pedidas'] + $material_pedido->unidades_confirmadas; 
                    $info_pedido_padre= Order::find($material_pedido->orden_compra_id);
                    $material_pedido->folio_pedido = $info_pedido_padre->folio;
                    $material_pedido->nombre_prospecto = $info_pedido_padre->nombreUsuario;
                    array_push($re_pedido_sugeridos['lista_material'], $material_pedido);
                    $resumenVentasFamilia[$k] = $re_pedido_sugeridos;
                }
            }
            $total_unidades = $total_unidades + $material_pedido->unidades_confirmadas;
        }
        //obtener familia mas vendida
        $mayor_ventas_familia=0;
        $mayor_nombre_familia = 'Sin Venta';
        foreach ($resumenVentasFamilia as $P => $re_pedido_sugeridos2) {
            if ($re_pedido_sugeridos2['u_pedidas']>$mayor_ventas_familia){
            $mayor_ventas_familia = $re_pedido_sugeridos2['u_pedidas'];
            $mayor_nombre_familia = $re_pedido_sugeridos2['id'];
            }
            //porcentaje 
            if($total_unidades != 0){
                $porcentaje = ($re_pedido_sugeridos2['u_pedidas']/$total_unidades)*100;
                $porcentaje_format = number_format($porcentaje, 0, '.', '');
            }else{
                $porcentaje_format = 0;
            }
            
            $re_pedido_sugeridos2['porcentaje'] = $porcentaje_format;
            $resumenVentasFamilia[$P] = $re_pedido_sugeridos2;
        }

        //proceso para obtener los prospectos con pedidos ya generados 
        $opportunitiesVisitadasObject = OpportunitiesINEGI::where('clave_entidad',$request->id_entidad)->where('bandera_encuesta', '1')->get();
        $ProspectosPedidoSugerido = 0;
        $ProspectoSinPedidoSugerido = 0;
        foreach ($opportunitiesVisitadasObject as $comparador) {
            $bandera = 0;
            foreach ($ordenesItems as $comparador1) {
                if($comparador->id == $comparador1['idUsuario']){
                    $bandera = 1;
                }
            }
            if($bandera == 1){
                $ProspectosPedidoSugerido++;  
            }else{
                $ProspectoSinPedidoSugerido++;
            }
        }
        if($opportunitiesVisitadas != 0){
            //porcentajes ProspectosPedidoSugerido
            $porcentajeProspectosPedidoSugerido =  number_format(($ProspectosPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
            $porcentajeProspectoSinPedidoSugerido =  number_format(($ProspectoSinPedidoSugerido/$opportunitiesVisitadas)*100, 0, '.', '');
        }else{
            $porcentajeProspectosPedidoSugerido = 0;
            $porcentajeProspectoSinPedidoSugerido = 0;
        }
        

        $datos = [
            'opportunitiesTotal' => $opportunitiesTotal,
            'opportunitiesVisitadas' => $opportunitiesVisitadas,
            'opportunitiesCanceladas' => $opportunitiesCanceladas,
            'opportunitiesCanceladasMotivo1' => $opportunitiesCanceladasMotivo1,
            'opportunitiesCanceladasMotivo2' => $opportunitiesCanceladasMotivo2,
            'opportunitiesCanceladasMotivo3' => $opportunitiesCanceladasMotivo3,
            'porcentajeOpportunitiesCanceladasMotivo1' => $porcentajeOpportunitiesCanceladasMotivo1,
            'porcentajeOpportunitiesCanceladasMotivo2' => $porcentajeOpportunitiesCanceladasMotivo2,
            'porcentajeOpportunitiesCanceladasMotivo3' => $porcentajeOpportunitiesCanceladasMotivo3,
            'opportunitiesAsignadasRuta' => $opportunitiesAsignadasRuta,
            'opportunitiesPendientesAsignadasRuta' => $opportunitiesPendientesAsignadasRuta,
            'opportunitiesPendientesVisitar' => $opportunitiesPendientesVisitar,
            'rutasTotal' => $rutasTotal,
            'rutasAsignadas' => $rutasAsignadas,
            'rutasPendientes' => $rutasPendientes,
            'rutasSinIniciar' => $rutasSinIniciar,
            'rutasCurso' => $rutasCurso,
            'rutasPausa' => $rutasPausa,
            'rutasFinalizadas' => $rutasFinalizadas,
            'rutas10' => $rutas10,
            'rutasMenos10' => $rutasMenos10,
            'encuestasContenstadas' => $encuestasContenstadas,
            'prospectosVisitasRecurrentes' => $prospectosVisitasRecurrentes,
            'prospectosNOVisitasRecurrentes' => $prospectosNOVisitasRecurrentes,
            'porcentajeProspectosVisitasRecurrentes' => $porcentajeProspectosVisitasRecurrentes,
            'porcentajeProspectosNOVisitasRecurrentes' => $porcentajeProspectosNOVisitasRecurrentes,
            'prospectosProductoIUSA' => $prospectosProductoIUSA,
            'prospectosNOProductoIUSA' => $prospectosNOProductoIUSA,
            'porcentajeProspectosProductoIUSA' => $porcentajeProspectosProductoIUSA,
            'porcentajeProspectosNOProductoIUSA' => $porcentajeProspectosNOProductoIUSA,
            'PedidoSugeridoTotal' => $PedidoSugeridoTotal,
            'PedidoSugeridoSinEnviar' => $PedidoSugeridoSinEnviar,
            'PedidoSugeridoEnviado' => $PedidoSugeridoEnviado,
            'PedidoSugeridoTerminado' => $PedidoSugeridoTerminado,
            'porcentajePedidoSugeridoSinEnviar' => $porcentajePedidoSugeridoSinEnviar,
            'porcentajePedidoSugeridoEnviado' => $porcentajePedidoSugeridoEnviado,
            'porcentajePedidoSugeridoTerminado' => $porcentajePedidoSugeridoTerminado,
            'ProspectosPedidoSugerido' => $ProspectosPedidoSugerido,
            'ProspectoSinPedidoSugerido' => $ProspectoSinPedidoSugerido,
            'porcentajeProspectosPedidoSugerido' => $porcentajeProspectosPedidoSugerido,
            'porcentajeProspectoSinPedidoSugerido' => $porcentajeProspectoSinPedidoSugerido,
            'resumenVentasFamilia' => $resumenVentasFamilia,
            'mayor_nombre_familia' => $mayor_nombre_familia,
            'mayor_ventas_familia' => $mayor_ventas_familia,
            'porcentajeOportunidadesProspectos' =>  $porcentajeOportunidadesProspectos,
            'porcentajeOportunidadesCanceladas' => $porcentajeOportunidadesCanceladas,
            'porcentajeOportunidadesPendientesVisitar' => $porcentajeOportunidadesPendientesVisitar,
            'nombre_entidad' => $nombre_entidad,
            'porcentajeProspectosTasaConvercion' => $porcentajeProspectosTasaConvercion,
            'porcentajeCanceladasTasaConvercion' => $porcentajeCanceladasTasaConvercion,
            'oportunidades_nuevas' => $oportunidades_nuevas,
            'porcentajeOportunidades_nuevas' => $porcentajeOportunidades_nuevas
        ]; 

        return response()->json(
            $datos
        );
    }//end reporte Estado
}
