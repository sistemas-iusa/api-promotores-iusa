<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use DB;

class HistorialOrderController extends Controller
{

  public function getPedidoHistory(Request $request) 
    {
      //return $request;
      $usuario_vendedor = $request->vendedor;
      $puesto= $request->puesto;
      $prueba = DB::connection('Express_DES')->select("select * from vw_Sim_Ord_Stat where KUNNR like '$request->idCliente' and VBELN not like '0000000000' order by IDORD desc");
      $prueba = collect($prueba);
       $contador_pedidos = count($prueba);

       if ($contador_pedidos == 0) {
        $lista_pedidos =[];
      }else{
      
       for ($j=0; $j < $contador_pedidos ; $j++) {
            $rec_ped = $prueba[$j];
      
            $fecha = $rec_ped->DFACTU;
            $consec = $rec_ped->IDORD;
            $orden = $rec_ped->PURCHC;
            $pedido = $rec_ped->VBELN;
            $entrega = $rec_ped->ENTRE;
            $factura = $rec_ped->FACTU;
            $estatus = $rec_ped->ORDST;
      
            $motivo1 = $rec_ped->MCREDI;
            $motivo2 = $rec_ped->MCOMER;
            $motivo3 = $rec_ped->MGPROD;
      
            $accion = $rec_ped->DFACTU;
            $embarcado1 = $rec_ped->FEEMBARQUECEDI;
            $embarcado2 = $rec_ped->FEEMBARQUE;
            $recibido = $rec_ped->FEACUSE;

            if($estatus == 1){
              $estatus = "Facturado";
            }else if($estatus == 2){
              $estatus = "Bloqueado por Credito";
            }else if($estatus == 3){
              $estatus = "Bloqueado por Comercial";
            }else if($estatus == 5){
              $estatus = "Rechazado por Credito";
            }else if($estatus == 6){
              $estatus = "Rechazado por Comercial";
            }else if($estatus == 7){
              $estatus = "En tratamiento VE";
            }else if($estatus == 8){
              $estatus = "Sin entrega";
            }else if($estatus == 9){
              $estatus = "Sin factura";
            }else if($estatus == 10){
              $estatus = "facturada";
            }else if($estatus == 12){
              $estatus = "Bloqueado por Gerente Planeación";
            }else if($estatus == 14){
              $estatus = "Rechazado por Gerente Planeación";
            }
      
      $lista_pedidos[$j] =[
            'fecha' => $fecha,
            'consec' =>  $consec, 
             'orden' =>  $orden,
             'pedido' =>  $pedido, 
             'entrega' =>  $entrega, 
             'factura' =>  $factura, 
              'estatus' => $estatus, 
             'motivo1' =>  $motivo1,
             'motivo2' => $motivo2,
             'motivo3'=> $motivo3,
             'accion' => $accion,
             'embarcado1' => $embarcado1,
             'embarcado2' => $embarcado2,
             'recibido' => $recibido
             ];
       }
      
       } 
       return response()->json(
        $lista_pedidos
        );
    }

    public function getRecordatoriosHistory(Request $request) 
    {
      $usuario_vendedor = $request->vendedor;
      $puesto= $request->puesto;

      function obj2array($obj) {
        $out = array();
        foreach ($obj as $key => $val) {
          switch(true) {
              case is_object($val):
               $out[$key] = obj2array($val);
               break;
            case is_array($val):
               $out[$key] = obj2array($val);
               break;
            default:
              $out[$key] = $val;
          }
        }
        return $out;
      }//fin funcion obj2arra
      //DETERMINAR EL CANAL DEL VENDEDOR
      $VKORG = "";
      $VTWEG = "";
      try {
        $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Datos_Vendedor/Datos_Vendedor.asmx?WSDL";
        $parametros1=array();
        $parametros1['P_USERN']="$usuario_vendedor";
        $client1 = new SoapClient($servicio1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
        $result1 = $client1->Vb_Datos_Vendedor($parametros1);
        $result1 = obj2array($result1);
        $noticias1=$result1['Vb_Datos_VendedorResult']['MyResultData'];
        $datos_vendedor = collect($noticias1)->first();
      } catch (Exception $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
      }
      //dd($datos_vendedor);
      $area_vendedor = $datos_vendedor['AREA1'];
      if ($area_vendedor == 'Exportaciones') {
        $VKORG = "IUS4";
      }else{
        $VKORG = "IUS2";
      }
      try {
        $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Vendedor_Cliente/Vendedor_Cliente.asmx?WSDL";
        $parametros1=array();
        $parametros1['P_USERNAME']="$usuario_vendedor";
        $parametros1['P_PUESTO']="$puesto";
        $client1 = new SoapClient($servicio1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
        $result1 = $client1->Vb_Vendedor_Cliente($parametros1);
        $result1 = obj2array($result1);
        $noticias1=$result1['Vb_Vendedor_ClienteResult']['MyResultData'];
        $lista_clientes_v = collect($noticias1);
      } catch (Exception $e) {
          trigger_error($e->getMessage(), E_USER_WARNING);
      }
      $dat_client = [];
      foreach ($lista_clientes_v as $constructor) {
        $dato = $constructor;
        if($dato['KUNNR'] == $request->idCliente){
          $dat_client = $dato;
        }
      }
      $VTWEG = $dat_client['VTWEG'];
      //FIN DETERMINAR EL CANAL VENDEDOR 
      $prueba_2 = DB::connection('Express_DES')->select("select * from vw_Sim_Order_Rec where KUNNR = '$request->idCliente' and Cant > 0 order by Id_Product asc");
      $prueba_2 = collect($prueba_2);
      $contador_recordatorios = count($prueba_2);

      if ($contador_recordatorios == 0) {
        $lista_recordatorios =[];
      }else{
      
       for ($i=0; $i < $contador_recordatorios ; $i++) {
              $material = ""; 
              $rec_list = $prueba_2[$i];
              $id_rec = $rec_list->IDORREC;
              $orden_compra = $rec_list->PURCHC;
              $fecha_programado = $rec_list->DateofSale;
              $codigo = $rec_list->Id_Product;
              $nombre = $rec_list->Description;
              $medida = $rec_list->UoM;
              $empaque = $rec_list->Empaque;      
              $empaqueLabel = $rec_list->Empaque;     
              $cantidad_pedida = $rec_list->Cant;     
              $datetimetoday = date("Ymd");      
              $datetime1 = date_create($fecha_programado);
              $datetime2 = date_create($datetimetoday);
              $programado_band = 0;
              //bandera si es material Kits
              $bandera_kits = 0;
              //$diferencia_fecha = date_diff($datetime1, $datetime2);
              if ($datetime1 > $datetime2) {
                $programado_band = 1;
              }             
              $n1=strlen($codigo);
              $n1_aux=18-$n1;
              $mat="";
              for ($j=0; $j <$n1_aux ; $j++) { 
                $mat.="0";
              }
              $material=$mat.$codigo;
              //dd($material);
            // session_start(); 
            //$usuario_vendedor = $_SESSION['usuario'];
                  try {
                      $servicio="http://172.16.171.10/WebServices/PGC360_Pro_Cliente_Datosgrales/Cliente_Datosgrales.asmx?WSDL"; //url del servicio
                      $parametros=array(); //parametros de la llamada
                      $parametros['Username']="$usuario_vendedor";
                      $parametros['KKBER']="217";
                      $parametros['KUNNR1']="$request->idCliente";                
                      $parametros['VKORG']=$VKORG;
                      $parametros['VTWEG']=$VTWEG;
                      $client = new SoapClient($servicio,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
      
                      $result = $client->Vb_Cliente_Datosgrales($parametros);//llamamos al métdo que nos interesa con los parámetros
                      //convertir result a array
                      $result = obj2array($result);
                     //dd($result);
                      $noticias=$result['Vb_Cliente_DatosgralesResult']['MyResultData'];
      
                      $collection = collect($noticias);                
                      $cliente_datos = $collection->first(); //primer arreglo a mandar (Información general del cliente)
      
                      } catch (Exception $e) {
                          trigger_error($e->getMessage(), E_USER_WARNING);
                      }
                      $central = $cliente_datos['VKBUR'];
              //********* WEBSERVICE PARA MATERIALES Y EXISTENCIAS
            try {
      
            //$servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios/Mater_Exist_Precios.asmx?WSDL"; //url del servicio
            $servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio ANTERIOR
            //$servicio5="http://172.16.171.10/webservices/PGC360_Pro_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio
            $parametros5=array(); //parametros de la llamada
      
            $parametros5['VKBUR']="$central";
            $parametros5['MATNR']="$material";
            $parametros5['KUNNR']="$request->idCliente";
            $parametros5['VTWEG']="$VTWEG";
            $parametros5['VKORG']="$VKORG";
            $parametros5['CANT']="1";
            $client5 = new SoapClient($servicio5,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result5 = $client5->Vb_Mater_Exist_Precios2($parametros5);//llamamos al métdo que nos interesa con los parámetros
            //dd($result5);
            $result5 = obj2array($result5);
            $noticias5=$result5['Vb_Mater_Exist_Precios2Result']['MyResultData'];
            $consultaMat = collect($noticias5);
            $n_collection_mat = count($consultaMat);
      
            $consultaMat = $consultaMat->first();
            $errormaterexist = "";
            }catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
              }//fin del servicio
              //dd($consultaMat);
               //validacion si el material se encuentra en carretes
               try {
                $servicio1="http://172.16.176.25/webservices/PGC360_Des_Carretes_Materiales/Carretes_Materiales.asmx?WSDL";
                //$servicio1="http://172.16.171.10/webservices/PGC360_Pro_Carretes_Materiales/Carretes_Materiales.asmx?WSDL";
                
                $client1 = new SoapClient($servicio1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
                $result1 = $client1->Vb_Carretes_Materiales();
                $result1 = obj2array($result1);
                $noticias1=$result1['Vb_Carretes_MaterialesResult']['MyResultData'];
                $lista_carretes = collect($noticias1);
              } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
              }
              
              foreach ($lista_carretes as $constructor) {
                $dato = $constructor;
              
                if($dato['MATNR'] == $material){
                  $programado_band = 2;
                }
              } 
      
              if (array_key_exists('LABST', $consultaMat)) {
                $existencia=$consultaMat['LABST'];
              }else{
                $existencia=0;
                $errormaterexist = "hay error";
              }
              
              if (array_key_exists('LABS1', $consultaMat)) {
                $existencia_cdpt=$consultaMat['LABS1'];
              }else{
                $existencia_cdpt=0;
                $errormaterexist = "hay error";
              }
      
              if (array_key_exists('BSTRF', $consultaMat)) {
                $empaque=$consultaMat['BSTRF'];
                $empaqueLabel = $consultaMat['BSTRF'];
              }else{
                $empaque=1;
                $empaqueLabel = 1;
                $errormaterexist = "hay error";
              }
      
              if ($empaque == "1.111") {
                $empaque = "1";
              }
              
      
              $aux1_existencia= floor($existencia/$empaque);
              $aux2_existencia=floor($existencia_cdpt/$empaque);
              $existencia_total=($aux1_existencia*$empaque)+($aux2_existencia*$empaque);
              
              //en caso de un material kits
              if($consultaMat['PSTYV'] == 'ZTP1'){
                $existencia_total=$consultaMat['KITS'];
                $existencia=$consultaMat['KITS'];
                $bandera_kits = 1;
              }
      
              $bandera_existencia="";
      
              if ($existencia_total>=$cantidad_pedida) {
               
                $bandera_existencia="SI";
              }else{
      
                $bandera_existencia="NO";
              }
      
              $filas_resumen="ERROR MATERIAL";
      
              $aux=$cantidad_pedida/$empaque;
      
              $aux_2=ceil($aux);
      
              $surtir=$aux_2*$empaque;
      
              if ($bandera_existencia=="SI") {
                 
                  $filas_resumen="Verde";
      
                }
      
                if ($bandera_existencia=="NO" && $existencia_total> 0) {
                  
                    $filas_resumen="naranja";
      
                }
      
                if ($bandera_existencia=="NO" && $existencia_total<=0) {
                  
                
      
                    $filas_resumen="blanco";
      
      
                }
      
                $lista_recordatorios[$i] =[
                                      'id' => $id_rec,
                                      'orden_compra' =>  $orden_compra, 
                                       'fecha_programado' =>  $fecha_programado,
                                       'codigo' =>  $codigo, 
                                       'nombre' =>  $nombre, 
                                       'medida' =>  $medida, 
                                        'empaque' => $empaqueLabel, 
                                       'cantidad_pedida' =>  $cantidad_pedida,
                                       'filas_resumen' => $filas_resumen,
                                       'band_programado'=> $programado_band,
                                       'seleccionado' => false,
                                       'errormaterexist' => $errormaterexist,
                                       'existencia_total' => $existencia_total,
                                       'existencia' => $existencia,
                                       'existencia_cdpt' => $existencia_cdpt,
                                       'bandera_kits' => $bandera_kits
                                       ];
      
      
             }//fin for
      
      }//fin else 

      return response()->json(
        $lista_recordatorios
        );

    }

    public function getInfoPedido(Request $request) 
    {
      function obj2array($obj) {
        $out = array();
        foreach ($obj as $key => $val) {
          switch(true) {
              case is_object($val):
               $out[$key] = obj2array($val);
               break;
            case is_array($val):
               $out[$key] = obj2array($val);
               break;
            default:
              $out[$key] = $val;
          }
        }
        return $out;
      }//fin funcion obj2arra

      $usuario_cliente = $request->cliente;
      $pedido = $request->id_pedido;
      //********* WEBSERVICE PARA CONSULTAR PEDIDO
        try {

          $servicio="http://172.16.176.25/WebServices/PGC360_Des_GetStatus/GetStatus.asmx?WSDL"; //url del servicio
          // $servicio="http://172.16.171.10/WebServices/PGC360_Pro_GetStatus/GetStatus.asmx?WSDL"; //url del servicio
          $parametros=array(); //parametros de la llamada
          $parametros['Kunnr']="$usuario_cliente";
          $parametros['Pedido']="$pedido";
          $client = new SoapClient($servicio,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
          $result = $client->Vb_GetStatus($parametros);//llamamos al métdo que nos interesa con los parámetros
          $result = obj2array($result);
          $noticias=$result['Vb_GetStatusResult']['MyResultData'];
          $collection = collect($noticias);
          $prueba_Pedido = $collection;

          $a = count($collection);
          $a = $a - 1;
          $i=0;
          $importe = 0;
          for ($i=0; $i <$a ; $i++) {
            $row2 = $collection[$i];
            $label=mb_strtoupper($row2['NETVA']);
            $importe = $importe+$label;
          }
          $datos = ['prueba_Pedido' => $prueba_Pedido, 'importe' => $importe];
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
      return response()->json($datos);
    }

}