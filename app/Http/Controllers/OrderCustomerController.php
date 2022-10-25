<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDetail;
use App\OrderDetailPRO;
use Illuminate\Http\Request;
use SoapClient;

class OrderCustomerController extends Controller
{
    public function getCustomers(Request $request) {

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


      $usuario=$request->usuario;
      $puesto=$request->tipo;

      try {
        //$servicio1="http://172.16.176.25/webservices/PGC360_Des_Vendedor_Cliente/Vendedor_Cliente.asmx?WSDL";
        $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Vendedor_Cliente/Vendedor_Cliente.asmx?WSDL";
        $parametros1=array();
        $parametros1['P_USERNAME']="$usuario";
        $parametros1['P_PUESTO']="$puesto";
        $client1 = new SoapClient($servicio1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
        $result1 = $client1->Vb_Vendedor_Cliente($parametros1);
        $result1 = obj2array($result1);
        $noticias1=$result1['Vb_Vendedor_ClienteResult']['MyResultData'];
        $collection = collect($noticias1);  
        return response()->json($collection);
      } catch (Exception $e) {
          trigger_error($e->getMessage(), E_USER_WARNING);
      }
    }

    public function InfoCustomer(Request $request)
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
        $NumeroCliente = $request->cliente;
        $usuario_vendedor = $request->usuario;
        $puesto= $request->tipo;
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
        if($dato['KUNNR'] == $NumeroCliente){
          $dat_client = $dato;
        }
      }
      $VTWEG = $dat_client['VTWEG'];
      //FIN DETERMINAR EL CANAL VENDEDOR 
        //********* WEBSERVICE PARA CLIENTES 
         try {
                //$usuario_vendedor=session('usuario');
                //$servicio="http://172.16.176.25/WebServices/PGC360_Des_Cliente_Datosgrales/Cliente_Datosgrales.asmx?WSDL"; //url del servicio
                $servicio="http://172.16.171.10/WebServices/PGC360_Pro_Cliente_Datosgrales/Cliente_Datosgrales.asmx?WSDL"; //url del servicio
                $parametros=array(); //parametros de la llamada
                $parametros['Username']="$usuario_vendedor";
                $parametros['KKBER']="217";
                $parametros['KUNNR1']="$NumeroCliente";                
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
                 //variable para ver el TIPO DE CANAL 
                $t_canal = $cliente_datos['VTWEG'];
                } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
          //********* WEBSERVICE PARA DIAS CARTERA
          try {

                //$servicio="http://172.16.176.25/WebServices/PGC360_Des_Clientes_Agotacart/Clientes_Agotacart.asmx?WSDL"; //url del servicio
                 $servicio="http://172.16.171.10/WebServices/PGC360_Pro_Clientes_Agotacart/Clientes_Agotacart.asmx?WSDL"; //url del servicio
                $parametros=array(); //parametros de la llamada

                $parametros['KUNNR']="$NumeroCliente";
                $parametros['KUNNR2']="$NumeroCliente";
        

                $client = new SoapClient($servicio,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));

                $result = $client->Vb_Clientes_Agotacart($parametros);//llamamos al métdo que nos interesa con los parámetros
                //convertir result a array
                $result = obj2array($result);
                $noticias=$result['Vb_Clientes_AgotacartResult']['MyResultData'];
                $collection = collect($noticias);
                $cliente_agotacart = $collection->first();//segundo arreglo
                //dd($cliente_agotacart);
                } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
           //********* WEBSERVICE PARA DESTINOS DE MERCANCIA
          try {

                
                //$servicio3="http://172.16.176.25/WebServices/PGC360_Dest_Mercancia/Dest_Mercancia.asmx?WSDL"; //url del servicio
                $servicio3="http://172.16.171.10/WebServices/PGC360_Pro_Dest_Mercancia/Dest_Mercancia.asmx?WSDL"; //url del servicio
                $parametros3=array(); //parametros de la llamada

                $parametros3['KUNNR']="$NumeroCliente";
                $parametros3['SPART']="90";
                $parametros3['VKORG']="IUS2";
                $parametros3['VTWEG']="$t_canal";
        

                $client3 = new SoapClient($servicio3,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));

                $result3 = $client3->Vb_Dest_Mercancia($parametros3);//llamamos al métdo que nos interesa con los parámetros
                $result3 = obj2array($result3);
                $noticias3=$result3['Vb_Dest_MercanciaResult']['MyResultData'];
                $collection3 = collect($noticias3);
                $destinos_cliente = $collection3;

                } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }

                //********* WEBSERVICE PARA PARTIDAS VENCIDAS
                $vkbur =  $cliente_datos['VKBUR'];
          try {

                
                $servicio4="http://172.16.171.10/WebServices/PGC360_Pro_Partvenc_Oficclte/Partvenc_Oficclte.asmx?WSDL"; //url del servicio
                $parametros4=array(); //parametros de la llamada

                $parametros4['KUNNR']="$NumeroCliente";
                $parametros4['VKBUR']="$vkbur";
                
        

                $client4 = new SoapClient($servicio4,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));

                $result4 = $client4->Vb_Partvenc_Oficclte($parametros4);//llamamos al métdo que nos interesa con los parámetros
                $result4 = obj2array($result4);
                $noticias4=$result4['Vb_Partvenc_OficclteResult']['MyResultData'];
                $collection4 = collect($noticias4);
                $Partidas = $collection4;

                } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }

                $N_partidas = count($Partidas);
                $id_vencidas = 0;
                for ($i=0; $i < $N_partidas ; $i++) { 
                  $partida_check = $Partidas[$i];
                  $partida_estatus = $partida_check['ESTAT'];
                  if ($partida_estatus == "Vencido") {
                    $id_vencidas++;
                  }
                }

                if ( $cliente_agotacart == null) {
                   $cliente_agotacart = ['T7' => -1];
                }
            
            $datos_total = ['cliente_datos' => $cliente_datos, 'cliente_agotacart' => $cliente_agotacart, 'destinos_cliente' => $destinos_cliente, 'vencidas' => $id_vencidas];
            return response()->json($datos_total);
    }

    public function setOrderCustomer(Request $request) {

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

      $central = $request->VKBUR;
      $canal_cliente = $request->VTWEG;
      $usuario_vendedor = $request->vendedor;
      $id_vendedor = $request->vendedor;
      $puesto= $request->puesto;
      $VKORG = $request->VKORG;
      $VTWEG = $request->VTWEG;
      $contador=10; 
      $cliente= $request->idCliente;
      $orden_compra= $request->orden_compra;
      $numero_destinatario = $request->destino_compra;
      $carrito = $request->carrito;
      $estatus_pedido = $request->estatus_pedido;
       $pedido_id="09093";//folio completo = userid / pedidoid
       $productos="";
        $z13="";
        $recordatorio="";
         $credi= $request->credi;

         $metodos_de_pago = $request->metodos_de_pago;
      $via_de_pago = $request->via_de_pago;
      $uso_cfdi = $request->uso_cfdi;
      if ($metodos_de_pago == null) {
        $metodos_de_pago = "";
      }
      if ($via_de_pago == null) {
        $via_de_pago = "";
      }
      if ($uso_cfdi == null) {
        $uso_cfdi = "";
      }
      $refacturacion = $request->refacturacion;
      $documentos = $request->documentos;
      $refacturacionfinal = "";
      if ($documentos == null) {
        $documentos = "";
      }
      if ($refacturacion == null) {
        $refacturacionfinal = "";
      }


    //validaciones del registro programado

    $bandera_programado = $request->bandera_programado;
    $fecharecordatorio = $request->fecha_rec;

    if ($bandera_programado == true) {
      $fecha_recordatorios = str_replace("-", ".",$fecharecordatorio);
      $fecha_recordatorios = str_replace("/", ".",$fecharecordatorio);
    }else{
    $fecha_recordatorios = date("m.d.Y");
    }


    $fechaactual= date("Y-m-d H:i:s");
    $n_carrito=count($carrito);

      for ($i=0; $i < $n_carrito ; $i++) {
        $pedido = $carrito[$i];
        
        $codigo=$pedido['codigo_material'];
        $descripcion=$pedido['nombre_material'];
        $unidad_medida=$pedido['unidad_medida'];
        $empaque=$pedido['empaque'];
        $unidades_solicitadas=$pedido['u_pedidas'];
        $confirmadas=$pedido['u_confirm'];
        $recordatorios=$pedido['recordatorios'];
        $precio_descuento=$pedido['importe_desciento'];
        $importe=$pedido['importe_producto'];
        $importe=number_format($importe,2);
        $validacion=$pedido['validacion'];
        
        $envio_WS = 'NO';

        if ($empaque == "1.111") {
          $empaque = "1";
        }

    $n2=strlen($contador);
    $n2_aux=6-$n2;
    $pos="";

    for ($j1=0; $j1 <$n2_aux ; $j1++) { 
      $pos.="0";
    }

    $pocision=$pos.$contador;

    $n1=strlen($codigo);
    $n1_aux=18-$n1;
    $mat="";

    for ($k2=0; $k2 <$n1_aux ; $k2++) { 
      $mat.="0";
    }


    $material=$mat. $codigo;


    
    //********* WEBSERVICE PARA MATERIALES Y EXISTENCIAS
      try {

        $servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio ANTERIOR
      //$servicio5="http://172.16.171.10/webservices/PGC360_Pro_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio
      $parametros5=array(); //parametros de la llamada

      $parametros5['VKBUR']="$central";
      $parametros5['MATNR']="$material";
      $parametros5['KUNNR']="$cliente";
      $parametros5['VTWEG']="$VTWEG";
      $parametros5['VKORG']="$VKORG";
      $parametros5['CANT']="$unidades_solicitadas";
      $client5 = new SoapClient($servicio5,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
      $result5 = $client5->Vb_Mater_Exist_Precios2($parametros5);//llamamos al métdo que nos interesa con los parámetros
      $result5 = obj2array($result5);
      $noticias5=$result5['Vb_Mater_Exist_Precios2Result']['MyResultData'];
      $consultaMat = collect($noticias5);
      $consultaMat = $consultaMat->first();
      
      }catch (Exception $e) {
          trigger_error($e->getMessage(), E_USER_WARNING);
        }//fin del servicio

      
        
        $codigo_material=$consultaMat['MATNR'];
        $sucursal=$consultaMat['VKBUR'];
        $nombre_material=$consultaMat['MAKTX'];
        $unidad_medida=$consultaMat['MEINS'];

        $existencia=$consultaMat['LABST'];
        $ruta=$consultaMat['WERKS'];

        $existencia_cdpt=$consultaMat['LABS1'];
        $cdpt=$consultaMat['RUCDP'];
        $ruta_alterna=$consultaMat['CECDP'];

         $empaque=$consultaMat['BSTRF'];
        $error=$consultaMat['MYERR'];

        if ($empaque == "1.111") {
          $empaque = "1";
        }


       

        $aux1_existencia= floor($existencia/$empaque);
        $aux2_existencia= floor($existencia_cdpt/$empaque);

        $existencia_total=($aux1_existencia*$empaque)+($aux2_existencia*$empaque);

        if ($bandera_programado == true) {
          $existencia_total = 0;
        }


        $existencia_bien=$aux1_existencia*$empaque;
        $existencia_cdpt_bien=$aux2_existencia*$empaque;


        $cant=$unidades_solicitadas; $toma2=0;
      if ($existencia_total>=$empaque) {
 // echo "Se procesa con posicion $material";

        if ($existencia_bien>=$cant) {
         //Proceso de CEDIS
           $pocision=$pos.$contador;  
        $parte1="$pocision,$material,";
        $z="$pocision,ZK13,0;";

          $parte2="$cant,$ruta,,,;";

          $productos.=$parte1.$parte2;
      $z13.=$z;


          $cant=0;
      }

      if ($existencia_bien>=$empaque && $existencia_bien<$cant) {
        //Proceso COMBINADO
         
        $parte1="$pocision,$material,";
        $z="$pocision,ZK13,0;";

          $cant=$cant-$existencia_bien;

          $parte2="$existencia_bien,$ruta,,,;";

      $productos.=$parte1.$parte2;
      $z13.=$z;

          $toma2=1;
        //Dos lineas una de cdpt con partidas diferentes
          //echo "Esto vale $cant y esto vale Toma $toma2";

      }
      if($cant>0){

//Proceso de CDPT

if ($toma2==1) {

$contador=$contador+10;

$n2=strlen($contador);
$n2_aux=6-$n2;
$pos="";

for ($i1=0; $i1 <$n2_aux ; $i1++) { 
  $pos.="0";
}


//echo "Entro a proceso de CDPT para generar otra posicion....  esto vale $cant en este punto ";

if ($existencia_cdpt_bien>=$empaque) {

  if ($cant>0 && $cant>$existencia_cdpt_bien) {
  
  $cant=$cant-$existencia_cdpt_bien;

    $pocision=$pos.$contador;  
  $parte1="$pocision,$material,";
  $parte2="$existencia_cdpt_bien,$ruta_alterna,,$cdpt,;";
  $z="$pocision,ZK13,0;";

  $productos.=$parte1.$parte2;
$z13.=$z;

//$fecha_recordatorios= date("m.d.Y");
 $recordatorio.="$cliente,$material,$cant,$fecha_recordatorios;";  
 
} else{

//echo "Entro al este proceso de.. sepa que ";

  $pocision=$pos.$contador;  
  $parte1="$pocision,$material,";

  $z="$pocision,ZK13,0;";

  $parte2="$cant,$ruta_alterna,,$cdpt,;";


$productos.=$parte1.$parte2;
$z13.=$z;

  
}


}else{

$cant=$cant-$existencia_cdpt_bien;



if ($cant>0) {

 //$fecha_recordatorios= date("m.d.Y");
 $recordatorio.="$cliente,$material,$cant,$fecha_recordatorios;";

}else{

  $pocision=$pos.$contador;  
  $parte1="$pocision,$material,";

  $z="$pocision,ZK13,0;";

  $parte2="$cant,$ruta_alterna,,$cdpt,;";


$productos.=$parte1.$parte2;
$z13.=$z;

}




}


}else{

//echo "Esto vale Cant al entrar: $cant y $existencia_cdpt_bien";

if ($existencia_cdpt_bien>=$empaque) {

//echo "Esto vale Cant proceso 1: $cant";
if ($cant>0 && $cant>$existencia_cdpt_bien) {
  
  $cant=$cant-$existencia_cdpt_bien;

    $pocision=$pos.$contador;  
  $parte1="$pocision,$material,";
  $parte2="$existencia_cdpt_bien,$ruta_alterna,,$cdpt,;";
  $z="$pocision,ZK13,0;";

  $productos.=$parte1.$parte2;
$z13.=$z;

//$fecha_recordatorios= date("m.d.Y");
 $recordatorio.="$cliente,$material,$cant,$fecha_recordatorios;";  
 
}else{

//echo "Entro al este proceso de.. sepa que ";

  $pocision=$pos.$contador;  
  $parte1="$pocision,$material,";

  $z="$pocision,ZK13,0;";

  $parte2="$cant,$ruta_alterna,,$cdpt,;";


$productos.=$parte1.$parte2;
$z13.=$z;

  
}

}else{

$cant=$cant-$existencia_cdpt_bien;
//echo "Esto vale Cant 2: $cant";
if ($cant>0) {
  
 $fecha_recordatorios= date("m.d.Y");
 $recordatorio.="$cliente,$material,$cant,$fecha_recordatorios;";
}else{

     $pocision=$pos.$contador;  
  $parte1="$pocision,$material,";
  $parte2="$cant,$ruta_alterna,,$cdpt,;";
  $z="$pocision,ZK13,0;";

  $productos.=$parte1.$parte2;
$z13.=$z;

}


}



}

}

////////////////

$contador=$contador+10;


}else{
  //echo "Se va directo a Recordatorios $material";
  //$fecha_recordatorios= date("m.d.Y");
 $recordatorio.="$cliente,$material,$cant,$fecha_recordatorios;";
}


}//fin for carrito


 //Fin de validacion de existencia total mayor a cero
$fecha= date("Ymd");
$productos = substr($productos, 0, -1);
$z13 = substr($z13, 0, -1);
$recordatorio = substr($recordatorio, 0, -1);

//dd($productos);
//dd($recordatorio);

//**************************************************************************************************************************
//********************************     CREAR PEDIDO WS *****************************************************************
//session_start(); 
//$id_vendedor = $_SESSION['usuario'];
try {

  $servicio="http://172.16.176.25/WebServices/PGC360_Des_CrearPedido/CrearPedido.asmx?WSDL"; //url del servicio
//$servicio="http://172.16.171.10/WebServices/PGC360_Pro_CrearPedido/CrearPedido.asmx?WSDL"; //url del servicio
$parametros=array(); //parametros de la llamada


$parametros['ZTERM']="";
$parametros['Doc_Type']="PSIU";
$parametros['Sales_Org']=$VKORG;
$parametros['Distr_Chan']=$canal_cliente;
$parametros['Division']="90";
$parametros['Folio']="";
$parametros['Purch_No_C']=$orden_compra; //orden de compra
$parametros['Purch_No_S']="";
$parametros['Purch_Date']=$fecha; //fecha
$parametros['Username']=$id_vendedor;
$parametros['Uv']=$id_vendedor;
$parametros['Partn_Rolea']="AG";
$parametros['Partn_Numba']=$cliente; //Numero de Cliente
$parametros['Partn_Roleb']="WE";
$parametros['Partn_Numbb']=$numero_destinatario; //Destinatario de Mercancia
$parametros['Name_2']="";

$parametros['CreCo']=$credi; //Bandera de Pedido
$parametros['ItemArray_Rec']=$recordatorio; //Recordatorios
$parametros['ItemArray_S']=$productos; //Array de Materiales
$parametros['ItemArrayZK_S']=$z13; //Array Complementario


$parametros['IDORRDEM']="0"; // es IDORD cuando el tipo es D
$parametros['IDSE']="0";
$parametros['VTWEG']=$canal_cliente;

$parametros['MPago']="$metodos_de_pago"; // valores para cliente contado
$parametros['FPago']="$via_de_pago";
$parametros['UCFDI']="$uso_cfdi";

$parametros['DocRel']="$documentos";
$parametros['TRCFDI']="$refacturacionfinal";
 
$client = new SoapClient($servicio,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));

$result = $client->Vb_CrearPedido($parametros);//llamamos al métdo que nos interesa con los parámetros
//dd($result); 

$result = obj2array($result);
$noticias=$result['Vb_CrearPedidoResult'];
$resultadoPedido = collect($noticias);


} catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_WARNING);
} 
//dd($resultadoPedido);
$error=$resultadoPedido['MYERR'];
$mensaje=$resultadoPedido['FNMSG'];
if ($error == 0) {
  # code...


$numero_pedido=$resultadoPedido['ORNUM'];

$numero_factura=$resultadoPedido['FACNUM'];


$numero_entrega=$resultadoPedido['ENTNUM'];
$actividad=$resultadoPedido['ACTIV'];



//validacion de resultado del pedido 
if ($numero_pedido!="" || $numero_pedido!="" && $numero_factura!="" && $numero_entrega!="") {


if ($numero_pedido!="0" && $numero_factura!="0000000000" && $numero_entrega!="0000000000" || $numero_pedido!="0" && $numero_factura=="0000000000" && $numero_entrega=="0000000000") {

$estado="";

if ($credi==1) {
  $estado="Generado Correctamente";
}

if ($credi==2) {
  $estado="Bloqueado por Crédito";
}


$resultado_pedido_ws=['mensaje' => 'Generación de Pedido', 
                        'numero_pedido' => $numero_pedido,
                        'numero_entrega' => $numero_entrega,
                        'numero_factura' => $numero_factura,                      
                         ];

}else{

  $resultado_pedido_ws=['mensaje' => $mensaje,
                        'numero_pedido' => $numero_pedido,
                        'numero_entrega' => $numero_entrega,
                        'numero_factura' => $numero_factura,
                         ];

} 

}//Fin de Validacion de datos Nullos
}else{
  //en caso de error 
  $resultado_pedido_ws=['mensaje' => $mensaje , 
                        'numero_pedido' => 'ERROR EN SERVIDOR',
                        'numero_entrega' => '',
                        'numero_factura' => '',                      
                         ];
}



            return response()->json(
                        $resultado_pedido_ws
                        );



      


    }//fin metodo
}