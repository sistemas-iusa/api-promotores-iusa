<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDetail;
use App\OrderDetailPRO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SoapClient;

class OrderEsporadicoController extends Controller
{

  public function getEsporadicos()
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
      try {
        $servicio1="http://172.16.176.25/webservices/PGC360_Des_GPOCTASDeudor/GPOCTASDeudor.asmx?WSDL";
        // $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Carretes_Materiales/Carretes_Materiales.asmx?WSDL"; 
        // $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Carretes_Materiales/Carretes_Materiales.asmx?WSDL";
        $parametros1=array(); //parametros de la llamada
        $parametros1['P_KTOKD']="CPD";
        $client1 = new SoapClient($servicio1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
        $result1 = $client1->Vb_GpoCtasDeudor($parametros1);//llamamos al métdo que nos interesa con los parámetros
        $result1 = obj2array($result1);
        $noticias1=$result1['Vb_GpoCtasDeudorResult']['MyResultData'];
        $collection1 = collect($noticias1);
        return response()->json($collection1);
        }catch (Exception $e) {
          trigger_error($e->getMessage(), E_USER_WARNING);
        }//fin del servicio
    }
    public function getMaterial(Request $request)
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

    $usuario_vendedor = $request->usuario;
    $puesto = $request->puesto;
    $bandera_programado = $request->bandera_pedido_programado;
    $mat_recibido = $request->code;
    $cantidad_pedida = $request->units;
    $cliente = $request->idCliente;
    $ID_lista = $request->id_lista;
    $VKORG = $request->VKORG;
    $VTWEG = $request->VTWEG;
    $central = $request->VKBUR;
    $mensage_error = "";//variable para mensage de errores 

      //BLOQUEO TEMPORAL DE MATERIALES
      $bandera_bloqueo_mat = 0;
      /*if($mat_recibido == '267675'){
        $bandera_bloqueo_mat = 1;
      }
      if($mat_recibido == '267732'){
        $bandera_bloqueo_mat = 1;
      }*/
      //completar el código de material a 18 digitos
      $n1=strlen($mat_recibido);
      $n1_aux=18-$n1;
      $mat="";
      for ($i=0; $i <$n1_aux ; $i++) { 
        $mat.="0";
      }
      $material=$mat.$mat_recibido;
      //dd($cantidad_pedida)
        //validacion si el material se encuentra en carretes
        try {
            $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Carretes/Carretes.asmx?WSDL";
            // $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Carretes_Materiales/Carretes_Materiales.asmx?WSDL"; 
            // $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Carretes_Materiales/Carretes_Materiales.asmx?WSDL";

            $client1 = new SoapClient($servicio1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result1 = $client1->Vb_Carretes();
            $result1 = obj2array($result1);
            $noticias1=$result1['Vb_CarretesResult']['MyResultData'];
            $lista_carretes = collect($noticias1);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        
        $bandera_carrete = 0;
        foreach ($lista_carretes as $constructor) {
            $dato = $constructor;
        
            if($dato['MATNR'] == $material){
            $bandera_carrete = 1;
            }
        }
      //PRIMERA VUELTA PARA OBTENER LA CANTIDAD CORRECTA 
      //********* WEBSERVICE PARA MATERIALES Y EXISTENCIAS
      try {
        $servicio51="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL";
        //$servicio51="http://172.16.171.10/webservices/PGC360_Pro_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL";
        $parametros51=array(); //parametros de la llamada
  
        $parametros51['VKBUR']="$central";
        $parametros51['MATNR']="$material";
        $parametros51['KUNNR']="$cliente";
        $parametros51['VTWEG']="$VTWEG";
        $parametros51['VKORG']="$VKORG";
        $parametros51['CANT']="$cantidad_pedida";
        $client51 = new SoapClient($servicio51,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
        $result51 = $client51->Vb_Mater_Exist_Precios2($parametros51);//llamamos al métdo que nos interesa con los parámetros
         // dd($parametros51);
  
        $result51 = obj2array($result51);
        $noticias51=$result51['Vb_Mater_Exist_Precios2Result']['MyResultData'];
        $collection51 = collect($noticias51);
        $collection51 = $collection51->first();
        
        }catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
          }//fin del servicio
        //return $parametros51;
        $empaque51=$collection51['BSTRF'];
        $aux51=$cantidad_pedida/$empaque51;

        $aux_251=ceil($aux51);

        $surtir51= $aux_251*$empaque51;

        $surtir251 = $cantidad_pedida;

      //********* WEBSERVICE PARA MATERIALES Y EXISTENCIAS
      try {
      $servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL";
      //$servicio5="http://172.16.171.10/webservices/PGC360_Pro_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL";
      $parametros5=array(); //parametros de la llamada

      $parametros5['VKBUR']="$central";
      $parametros5['MATNR']="$material";
      $parametros5['KUNNR']="$cliente";
      $parametros5['VTWEG']="$VTWEG";
      $parametros5['VKORG']="$VKORG";
      $parametros5['CANT']="$cantidad_pedida";

      //dd($parametros5);
      $client5 = new SoapClient($servicio5,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
      $result5 = $client5->Vb_Mater_Exist_Precios2($parametros5);//llamamos al métdo que nos interesa con los parámetros
       // dd($result5);

      $result5 = obj2array($result5);
      $noticias5=$result5['Vb_Mater_Exist_Precios2Result']['MyResultData'];
      $collection = collect($noticias5);
      $collection = $collection->first();
      
      }catch (Exception $e) {
          trigger_error($e->getMessage(), E_USER_WARNING);
        }//fin del servicio
        //si obtubo un erro del ws
        
        if ($bandera_bloqueo_mat == 1) {
          $mensage_error = "Material temporalmente bloqueado";
           $cadena_result = [];
        }else if ($surtir251 <= 0) {
          $mensage_error = "Cantidad ingresada no debe ser cero";
           $cadena_result = [];
        }else if ($bandera_carrete == 1) {
          $mensage_error = "Codigo erroneo, producto asignado a carrete";
           $cadena_result = [];
        }else if ($collection['MYERR'] == 1 && $collection['FNMSG'] == 'Arithmetic operation resulted in an overflow.') {
          $mensage_error = "Codigo erroneo, revisar empaque, costo o bloqueos de cliente";
           $cadena_result = [];
        }else if ($collection['MYERR'] == 1) {
          $mensage_error = "Codigo erroneo, intente con otro codigo";
           $cadena_result = [];
        } else if ($collection == null) {
          $mensage_error = "Codigo no encontrado o erroneo";
           $cadena_result = [];
        }else{
          //obtener valores del WS resultado
          $codigo_material=$collection['MATNR'];
          $sucursal=$collection['VKBUR'];
          $aux_nombre=str_replace("\"", "",$collection['MAKTX']); 
          $aux_material_2=str_replace("'", "",$aux_nombre);
          $nombre_material=str_replace("#", "",$aux_material_2);
          $unidad_medida=$collection['MEINS'];
          $existencia=$collection['LABST'];
          $existencia_cdpt=$collection['LABS1'];
          $stock_transito=$collection['TRAME'];
          $empaque=$collection['BSTRF'];

          $empaqueLabel=$collection['BSTRF'];

          $importe=$collection['KBETR']; //ventas_presio
          $importe_descuento=$collection['PCDESC'];//presiodesc
          $importe_real=$collection['ZCOSTO3'];  
          $tipo_material=$collection['MAABC'];
          $error=$collection['MYERR'];
          $centro_informacion=$collection['WERKS'];
          //datos extra para alidaciones y guardar en bd 
          $descuento=$collection['PDPER'];//%descuento
          $ventas_centro=$collection['WERKS']; 
          $sector=$collection['SPART'];
          // fecha programada es del formulario
          $pormar=$collection['PORMAR'];//valores para valiaciones de credito
          $porcom=$collection['PROCOM'];//valores para valiaciones de credito
          $margen=$collection['ZCOSTO3'];
          $inventario=$collection['LABST'];
          //$inventario2=$collection['LABST1'];
          $ZK14=$collection['ZK14'];//
          $ZK71=$collection['ZK71'];
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
          $precio_lista=$collection['PLIST'];
//margenes comerciales
        $sector_id=$collection['SPART'];
        //conparaciones de sector con el margen minimo que debe tener 
        $margen_minimo_definido = 13;//
        //VALORES PARA DETERMINAR SU GPOM4
        $id_gpom4 = $collection['GERPRO']; 
        $nombre_gpom4 = $collection['BEZEI'];
        $imagen_url = $collection['IMGLS'];
        // Error de WS Mario 07/10/22
        $imagen_url = substr($imagen_url, 0, -20);
        $bandera_gpom4 = 0; 
  // comparacion si material empaque es = 1.111

  if ($empaque == "1.111" || $empaque == "0") {
          $empaque = "1";
        }
  //si el empaque es igual a 0
    if ($empaque == "0.000" || $empaque == "0") {
      $cadena_result = [];
      $mensage_error = "Código de material con empaque de cero";
    }else{
        // Tabla de margen minimo defindo actualizada 25/07/2022
      if ($sector_id != "") {
        if ($sector_id == "00") {
          //Alta tension
          $margen_minimo_definido = 15;
        }else if ($sector_id == "05") {
          //ARTEFACTOS ELECTRIC.
          $margen_minimo_definido = 15;
        }else if ($sector_id == "11") {
          //LAMPARAS LED
          $margen_minimo_definido = 13;
        }else if ($sector_id == "12") {
          //MICROINVERSOR
          $margen_minimo_definido = 5;
        }else if ($sector_id == "13") {
          //PANEL
          $margen_minimo_definido = 5;
        }else if ($sector_id == "14") {
          //KIT SOLAR
          $margen_minimo_definido = 5;
        }else if ($sector_id == "20") {
          //COBRE Y ALEACIONES
          $margen_minimo_definido = 8;
        }else if ($sector_id == "30") {
          //CONDUCTORES
          $margen_minimo_definido = 9;
        }else if ($sector_id == "35") {
          //CONTROLES
          $margen_minimo_definido = 15;
        }else if ($sector_id == "36") {
          //CONTROLES FORGAMEX
          $margen_minimo_definido = 15;
        }else if ($sector_id == "37") {
          //TRANSFORMADORES
          $margen_minimo_definido = 8;
        }else if ($sector_id == "38") {
          //CALENTADORES
          $margen_minimo_definido = 14;
        }else if ($sector_id == "39") {
          //TINACOS
          $margen_minimo_definido = 12;
        }else if ($sector_id == "40") {
          //ELECTROCERAMICA
          $margen_minimo_definido = 12;
        }else if ($sector_id == "45") {
          //ELECTROVIDRIO
          $margen_minimo_definido = 18;
        }else if ($sector_id == "51") {
          //EMPAQUES SINTETICOS
          $margen_minimo_definido = 14;
        }else if ($sector_id == "63") {
          //MERCADERIAS
          $margen_minimo_definido = 14;
        }else if ($sector_id == "70") {
          //MOLDEO DE PLASTICO
          $margen_minimo_definido = 14;
        }else if ($sector_id == "71") {
          //GABINETES
          $margen_minimo_definido = 14;
        }else if ($sector_id == "72") {
          //TUBERIA DE PLASTICO
          $margen_minimo_definido = 10;
        }else if ($sector_id == "76") {
          //TUBERIA CPVC
          $margen_minimo_definido = 10;
        }else if ($sector_id == "90") {
          //TUBERIA
          $margen_minimo_definido = 14;
        }else if ($sector_id == "C3") {
          //Material y EquipoMed
          $margen_minimo_definido = 20;
        }
    }
        //calculos de ventas        
        $ventas_pormar_cien = number_format((100 - $pormar),2);
        $ventas_precio_minimo = number_format((($margen / $ventas_pormar_cien) * 100),2);
        //$ventas_contribucion = number_format(($ventas_precio_minimo - $margen),2);
        $ventas_limite_gerente_pctj = number_format(($pormar * 0.8),2);
        $ventas_limite_comercial_pctj = number_format(($pormar * 0.1),2);
        $ventas_limite_gerente_cien = number_format((100 - $ventas_limite_gerente_pctj),2);
        $ventas_limite_comercial_cien = number_format((100 - $ventas_limite_comercial_pctj),2);
        $ventas_limite_gerente = number_format((($margen / $ventas_limite_gerente_cien) * 100),2);
        $ventas_limite_comercial = number_format((($margen / $ventas_limite_comercial_cien) * 100),2);

       

        $bandera_de_error = 0;
        $errorcomercial = 0;
        $errorgerente = 0;

        if ($importe_descuento <= $ventas_limite_comercial) {
          $errorcomercial = 1;
          $bandera_de_error = 1;
        }

        if ($importe_descuento >= $ventas_limite_comercial) {
          if ($importe_descuento < $ventas_limite_gerente &&  $bandera_de_error == 0) {
            $errorcomercial = 1;
            $bandera_de_error = 1;
          }
        }

        if ($importe_descuento >= $ventas_limite_gerente) {
          if ($importe_descuento < $ventas_precio_minimo &&  $bandera_de_error == 0) {
            
            $errorgerente = 1;
          }
        }
          //validaciones para obtener material existente.
          if ($existencia>0) {
           $aux1_existencia= floor($existencia/$empaque);//existencia en su almacen
          }else{
              $aux1_existencia=0;
          }
          if ($existencia_cdpt>0) {
            $aux2_existencia=floor($existencia_cdpt/$empaque);//existencia en cdpt
          }else{
             $aux2_existencia=0;
          }

          $existencia_total=($aux1_existencia*$empaque)+($aux2_existencia*$empaque);
          //si esto esta en modo pedido programado 
          if ($bandera_programado == true) {
            $existencia_total = 0;
            }

        //validamos si hay cantidades en existencia para este material
        $bandera_existencia="";
        if ($existencia_total>=$cantidad_pedida) {
          $bandera_existencia="SI";
        }else{
          $bandera_existencia="NO";
        }

        $aux=$cantidad_pedida/$empaque;

        $aux_2=ceil($aux);

        $surtir= $aux_2*$empaque;

        $surtir2 = (string)$surtir;

        //obtener lista de materiales sustitutos
        $query_surrogate_material= DB::connection('mysql')->table('surrogate_material_group as sm')
            ->select('sm.material_number', 'sm.material_name', 'sm.id_surrogate_group')
            ->where('sm.material_number', '=', $codigo_material)
            ->get();

        //$importe_total=$importe_total+$importe_producto;
        //Validacion de Materiales
        if ($tipo_material=="A" || $tipo_material=="B") {
          ////////// Inicio de Validacion de Precio
          //if ($importe_descuento>=$importe_real) {
            // Validacion de material por debajo precio Autorizacion de IGMAR Y JESUS 15/06/22 12:01 pm
            if ($importe_descuento>=$importe_real || $codigo_material == '267369'){
          // ALTERACION PARA EL PRECIO DESCUENTO 10/10/2020
          //if ($importe_descuento>=$importe_real || $bandera_gpom4 == 1) {
            if ($error=="0" && $bandera_existencia=="SI") {
              $importe_producto=($surtir*$importe_descuento);
              //$importe_producto=number_format(($surtir*$importe_descuento),2);
              //operacion para margen;
              $margen_utilidad=($importe_descuento - $margen) * $surtir2;
              $cadena_result = [
                'id_lista' => $ID_lista,
                'codigo_material' => $codigo_material,
                'nombre_material' => $nombre_material,
                'unidad_medida' => $unidad_medida,
                'existencia' => $existencia,
                'existencia_cdpt' => $existencia_cdpt,
                'empaque' => $empaqueLabel,
                'u_pedidas' => $surtir,
                'u_confirm' => $surtir2,
                'recordatorios' => "0",
                'importe_desciento' => $importe_descuento,
                'importe_producto' => $importe_producto,
                'validacion' => "Disponible",
               'descuento' => $descuento,
                'ventas_centro' =>$ventas_centro,
                'sector' =>$sector,
                'pormar' =>$pormar,//valores para valiaciones de credito
                'porcom' =>$porcom,//valores para valiaciones de credito
                'margen' =>$margen,
                'inventario' =>$inventario,
                'ZK14' =>$ZK14,
                'ZK71' =>$ZK71,
                'margen_utilidad' => $margen_utilidad,
                'margen_minimo_definido' => $margen_minimo_definido,
                'errorcomercial' => $errorcomercial,
                'errorgerente' => $errorgerente,
                'bandera_error' => $bandera_de_error,
                'precio_lista' => $precio_lista,
                'bandera_gpom4' => $bandera_gpom4,
                'imagen_url' => $imagen_url,
                'materiales_sustitutos' => $query_surrogate_material
                ];
            }//fin cuando la validacion es Disponible 
            if ($error=="0" && $bandera_existencia=="NO" && $existencia_total>0) {
              $recordatorio=$surtir-$existencia_total;
              $importe_producto=($existencia_total*$importe_descuento);
              //$importe_producto=number_format(($existencia_total*$importe_descuento),2);
              //operacion para margen;
              $margen_utilidad=($importe_descuento - $margen) * $existencia_total;
              $cadena_result = [
                'id_lista' => $ID_lista,
                'codigo_material' => $codigo_material,
                'nombre_material' => $nombre_material,
                'unidad_medida' => $unidad_medida,
                'existencia' => $existencia,
                'existencia_cdpt' => $existencia_cdpt,
                'empaque' => $empaqueLabel,
                'u_pedidas' => $surtir,
                'u_confirm' => $existencia_total,
                'recordatorios' => $recordatorio,
                'importe_desciento' => $importe_descuento,
                'importe_producto' => $importe_producto,
                'validacion' => "Parcial",
                //datos extra para alidaciones y guardar en bd 
               'descuento' => $descuento,
                'ventas_centro' =>$ventas_centro,
                'sector' =>$sector,
                // fecha programada es del formulario
                'pormar' =>$pormar,//valores para valiaciones de credito
                'porcom' =>$porcom,//valores para valiaciones de credito
                'margen' =>$margen,
                'inventario' =>$inventario,
                //'inventario1' =>$inventario2,
                'ZK14' =>$ZK14,
                'ZK71' =>$ZK71,
                'margen_utilidad' => $margen_utilidad,
                'margen_minimo_definido' => $margen_minimo_definido,
                'errorcomercial' => $errorcomercial,
                'errorgerente' => $errorgerente,
                'bandera_error' => $bandera_de_error,
                'precio_lista' => $precio_lista,
                'bandera_gpom4' => $bandera_gpom4,
                'imagen_url' => $imagen_url,
                'materiales_sustitutos' => $query_surrogate_material 
                ];
            }// fin validacion cuando es Parcial
            if ($error=="0" && $bandera_existencia=="NO" && $existencia_total==0) {
              $recordatorio=$surtir-$existencia_total;
              $importe_producto=($existencia_total*$importe_descuento);
              //$importe_producto=number_format(($existencia_total*$importe_descuento),2);
              //operacion para margen;
              $margen_utilidad=($importe_descuento - $margen) * $existencia_total;
              $cadena_result = [
                'id_lista' => $ID_lista,
                'codigo_material' => $codigo_material,
                'nombre_material' => $nombre_material,
                'unidad_medida' => $unidad_medida,
                'existencia' => $existencia,
                'existencia_cdpt' => $existencia_cdpt,
                'empaque' => $empaqueLabel,
                'u_pedidas' => $surtir,
                'u_confirm' => $existencia_total,
                'recordatorios' => $recordatorio,
                'importe_desciento' => $importe_descuento,
                'importe_producto' => $importe_producto,
                'validacion' => "Sin Existencia",
                //datos extra para alidaciones y guardar en bd 
               'descuento' => $descuento,
                'ventas_centro' =>$ventas_centro,
                'sector' =>$sector,
                // fecha programada es del formulario
                'pormar' =>$pormar,//valores para valiaciones de credito
                'porcom' =>$porcom,//valores para valiaciones de credito
                'margen' =>$margen,
                'inventario' =>$inventario,
                //'inventario1' =>$inventario2,
                'ZK14' =>$ZK14,
                'ZK71' =>$ZK71,
                'margen_utilidad' => $margen_utilidad,
                'margen_minimo_definido' => $margen_minimo_definido,
                'errorcomercial' => $errorcomercial,
                'errorgerente' => $errorgerente,
                'bandera_error' => $bandera_de_error,
                'precio_lista' => $precio_lista,
                'bandera_gpom4' => $bandera_gpom4,
                'imagen_url' => $imagen_url,
                'materiales_sustitutos' => $query_surrogate_material
                ];
            }//fin validacion cuando no hay existencia.
            //fin validacion precio
          }else{
            $cadena_result = [];
            $mensage_error = "Código erroneo. Revisar precio ";
          }
        }//fin validacion material A y B
        if ($tipo_material=="C") {
          $cadena_result = [];
          $mensage_error = "Código erroneo. Producto Bajo Pedido";
        }
        if ($tipo_material=="D") {
          $cadena_result = [];
          $mensage_error = "Código erroneo. Producto Descontinuado";
        }
        if ($tipo_material=="O") {
          $cadena_result = [];
          $mensage_error = "Código erroneo. Producto Obsoleto";
        }
        if ($tipo_material=="Z") {
          $cadena_result = [];
          $mensage_error = "Código erroneo. Producto Pendiente de Verificación Comercial";
        }
        if ($tipo_material=="") {
          $cadena_result = [];
          $mensage_error = "Código de material sin genetica de producto, Centro: ".$centro_informacion;
        }


      }//fin else error codigo
          
          }//validacion de empaque cero

        $datos_total = ['cadena_result' => $cadena_result, 'mensaje_error' => $mensage_error];
            return response()->json(
                        $datos_total
                        );

      }//fin de funcion material
    //

    public function setOrderEsporadico(Request $request) {


    date_default_timezone_set('America/Mexico_City');
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
    $puesto= $request->puesto;
    $VKORG = $request->VKORG;
    $VTWEG = $request->VTWEG;
    $contador=10; 
    $cliente= $request->idCliente;
    $orden_compra= $request->orden_compra;
    $numero_destinatario = "0000000000"; 
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

    //DATOS PARA EL EXPORADICO
    $nombre_exporadico = $request->nombre;
    $direccion_exporadico = $request->direccion;
    $ciudad_exporadico = $request->ciudad;
    $cp_exporadico = $request->cp;
    $pais_exporadico = $request->pais;
    $telefono_exporadico = $request->telefono;
    $fax_exporadico = $request->fax;
    $contacto_exporadico = $request->contacto;
    $region_exporadico = $request->region;
    $rfc_exporadico = $request->rfc;


    $refacturacion = $request->refacturacion;
    $documentos = $request->documentos;
    $refacturacionfinal = "";
    if ($documentos == null) {
    $documentos = "";
    }
    if ($refacturacion == null) {
    $refacturacionfinal = "";
    }

    //guardar en bd 
    $numero_pedido_final = $request->numero_pedido;
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

        $aux1_existencia= 1;
        $aux2_existencia= 1;
        $existencia_total=($aux1_existencia*$empaque)+($aux2_existencia*$empaque);
        if ($bandera_programado == true) {
          $existencia_total = 0;
        }
        $existencia_bien=$aux1_existencia*$empaque;
        $existencia_cdpt_bien=$aux2_existencia*$empaque;
        $cant=$unidades_solicitadas; 
        $toma2=0;
        if ($existencia_total>=$empaque) {
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

                    if ($existencia_cdpt_bien>=$empaque) {
                        if ($cant>0 && $cant>$existencia_cdpt_bien) {
                            $cant=$cant-$existencia_cdpt_bien;
                            $pocision=$pos.$contador;  
                            $parte1="$pocision,$material,";
                            $parte2="$existencia_cdpt_bien,$ruta_alterna,,$cdpt,;";
                            $z="$pocision,ZK13,0;";
                            $productos.=$parte1.$parte2;
                            $z13.=$z;
                            $recordatorio.="$cliente,$material,$cant,$fecha_recordatorios;";  
                        } else{
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
                        }else{
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
            
            $contador=$contador+10;
        }else{
            $recordatorio.="$cliente,$material,$cant,$fecha_recordatorios;";
        }

    }//fin for carrito
    
    //Fin de validacion de existencia total mayor a cero
    $fecha= date("Ymd");
    $productos = substr($productos, 0, -1);
    $z13 = substr($z13, 0, -1);
    $recordatorio = substr($recordatorio, 0, -1);
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
        $parametros['Username']=$usuario_vendedor;
        $parametros['Uv']=$usuario_vendedor;
        $parametros['Partn_Rolea']="AG";
        $parametros['Partn_Numba']=$cliente; //Numero de Cliente
        $parametros['Partn_Roleb']="WE";
        //$parametros['Partn_Numbb']=$numero_destinatario; //Destinatario de Mercancia
        $parametros['Partn_Numbb']=$cliente; //Destinatario de Mercancia
        $parametros['Name_2']="";
        //$parametros['CreCo']=$credi; //Bandera de Pedido
        $parametros['CreCo']="1"; //Bandera de Pedido
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
        $parametros['Name_1']="$nombre_exporadico";
        $parametros['Street']="$direccion_exporadico";
        $parametros['City']="$ciudad_exporadico";
        $parametros['Postl_Code']="$cp_exporadico";
        $parametros['Phone_Num']="$telefono_exporadico";
        $parametros['Phone_Num2']="$fax_exporadico";
        $parametros['Name_2']="$contacto_exporadico";
        $parametros['Region']="$region_exporadico"; 
        $parametros['RFC']="$rfc_exporadico"; 
       //return $parametros;
        $client = new SoapClient($servicio,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
        $result = $client->Vb_CrearPedido($parametros);//llamamos al métdo que nos interesa con los parámetros
        $result = obj2array($result);
        $noticias=$result['Vb_CrearPedidoResult'];
        $resultadoPedido = collect($noticias);
    } catch (Exception $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
    } 
    $error=$resultadoPedido['MYERR'];
    $mensaje=$resultadoPedido['FNMSG'];
    if ($error == 0) {
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
                //guardar en bd
                //$pedido_en_bd = Pedido::find($numero_pedido_final);
                        //$pedido_en_bd->facturado = true; 
                        //$pedido_en_bd->save();
            }else{

                    $resultado_pedido_ws=['mensaje' => $mensaje,
                                        'numero_pedido' => $numero_pedido,
                                        'numero_entrega' => $numero_entrega,
                                        'numero_factura' => $numero_factura,
                                        ];
                    //guardar en bd
                    //$pedido_en_bd = Pedido::find($numero_pedido_final);
                            //$pedido_en_bd->facturado = true; 
                            //$pedido_en_bd->save();
                } 
        }else{
            $resultado_pedido_ws=['mensaje' => 'Num Pedido no Generado', 
                                'numero_pedido' => $numero_pedido,
                                'numero_entrega' => $numero_entrega,
                                'numero_factura' => $numero_factura,                      
                                ];
        }
    }else{
        //en caso de error 
        $resultado_pedido_ws=['mensaje' => $mensaje , 
                          'numero_pedido' => 'ERROR EN SERVIDOR',
                          'numero_entrega' => '',
                          'numero_factura' => '',                      
                          ];
    }

    return response()->json($resultado_pedido_ws);
    }//fin metodo exporadico

    public function getMaterialSustitutos(Request $request)
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
      //obtener lista de materiales sustitutos
      $query_surrogate_material= DB::connection('mysql')->table('surrogate_material_group as sm')
      ->select('sm.material_number', 'sm.material_name', 'sm.id_surrogate_group')
      ->where('sm.id_surrogate_group', '=', $request->id_grupo_material)
      ->get();
      $query_surrogate_material_count = count($query_surrogate_material);
      $k=0;
      $cadena_result=[];
      for ($i=0; $i <$query_surrogate_material_count ; $i++) { 
        $item = $query_surrogate_material[$i];
        if($item->material_number != $request->material_consulta){
          //********* WEBSERVICE PARA MATERIALES Y EXISTENCIAS
          //completar el código de material a 18 digitos
          $n1=strlen($item->material_number);
          $n1_aux=18-$n1;
          $mat="";
          for ($j=0; $j <$n1_aux ; $j++) { 
            $mat.="0";
          }
          $material=$mat.$item->material_number;
          try {
            $servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL";
            //$servicio5="http://172.16.171.10/webservices/PGC360_Pro_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL";
            $parametros5=array(); //parametros de la llamada
      
            $parametros5['VKBUR']="$request->VKBUR";
            $parametros5['MATNR']="$material";
            $parametros5['KUNNR']="$request->idCliente";
            $parametros5['VTWEG']="$request->VTWEG";
            $parametros5['VKORG']="$request->VKORG";
            $parametros5['CANT']="$request->unidades_consulta";
      
            //dd($parametros5);
            $client5 = new SoapClient($servicio5,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result5 = $client5->Vb_Mater_Exist_Precios2($parametros5);//llamamos al métdo que nos interesa con los parámetros
            // dd($result5);
      
            $result5 = obj2array($result5);
            $noticias5=$result5['Vb_Mater_Exist_Precios2Result']['MyResultData'];
            $collection = collect($noticias5);
            $collection = $collection->first();
            
            }catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }//fin del servicio
            //obtener valores del WS resultado
            $codigo_material=$collection['MATNR'];
            $sucursal=$collection['VKBUR'];
            $aux_nombre=str_replace("\"", "",$collection['MAKTX']); 
            $aux_material_2=str_replace("'", "",$aux_nombre);
            $nombre_material=str_replace("#", "",$aux_material_2);
            $unidad_medida=$collection['MEINS'];
            $existencia=$collection['LABST'];
            $existencia_cdpt=$collection['LABS1'];
            $stock_transito=$collection['TRAME'];
            $empaque=$collection['BSTRF'];

            $empaqueLabel=$collection['BSTRF'];

            $importe=$collection['KBETR']; //ventas_presio
            $importe_descuento=$collection['PCDESC'];//presiodesc
            $importe_real=$collection['ZCOSTO3'];  
            $tipo_material=$collection['MAABC'];
            $error=$collection['MYERR'];
            $centro_informacion=$collection['WERKS'];
            //datos extra para alidaciones y guardar en bd 
            $descuento=$collection['PDPER'];//%descuento
            $ventas_centro=$collection['WERKS']; 
            $sector=$collection['SPART'];
            // fecha programada es del formulario
            $pormar=$collection['PORMAR'];//valores para valiaciones de credito
            $porcom=$collection['PROCOM'];//valores para valiaciones de credito
            $margen=$collection['ZCOSTO3'];
            $inventario=$collection['LABST'];
            //$inventario2=$collection['LABST1'];
            $ZK14=$collection['ZK14'];//
            $ZK71=$collection['ZK71'];
      //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $precio_lista=$collection['PLIST'];
      //margenes comerciales
          $sector_id=$collection['SPART'];
          //conparaciones de sector con el margen minimo que debe tener 
          $margen_minimo_definido = 13;//
          //VALORES PARA DETERMINAR SU GPOM4
          $id_gpom4 = $collection['GERPRO']; 
          $nombre_gpom4 = $collection['BEZEI'];
          $imagen_url = $collection['IMGLS'];
          $bandera_gpom4 = 0; 
      // comparacion si material empaque es = 1.111

      if ($empaque == "1.111") {
            $empaque = "1";
          }
      //si el empaque es igual a 0
      if ($empaque == "0.000" || $empaque == "0") {
        //$cadena_result = [];
        //$mensage_error = "Código de material con empaque de cero";
      }else{
          // Tabla de margen minimo defindo actualizada 25/07/2022
        if ($sector_id != "") {
          if ($sector_id == "00") {
            //Alta tension
            $margen_minimo_definido = 15;
          }else if ($sector_id == "05") {
            //ARTEFACTOS ELECTRIC.
            $margen_minimo_definido = 15;
          }else if ($sector_id == "11") {
            //LAMPARAS LED
            $margen_minimo_definido = 13;
          }else if ($sector_id == "12") {
            //MICROINVERSOR
            $margen_minimo_definido = 5;
          }else if ($sector_id == "13") {
            //PANEL
            $margen_minimo_definido = 5;
          }else if ($sector_id == "14") {
            //KIT SOLAR
            $margen_minimo_definido = 5;
          }else if ($sector_id == "20") {
            //COBRE Y ALEACIONES
            $margen_minimo_definido = 8;
          }else if ($sector_id == "30") {
            //CONDUCTORES
            $margen_minimo_definido = 9;
          }else if ($sector_id == "35") {
            //CONTROLES
            $margen_minimo_definido = 15;
          }else if ($sector_id == "36") {
            //CONTROLES FORGAMEX
            $margen_minimo_definido = 15;
          }else if ($sector_id == "37") {
            //TRANSFORMADORES
            $margen_minimo_definido = 8;
          }else if ($sector_id == "38") {
            //CALENTADORES
            $margen_minimo_definido = 14;
          }else if ($sector_id == "39") {
            //TINACOS
            $margen_minimo_definido = 12;
          }else if ($sector_id == "40") {
            //ELECTROCERAMICA
            $margen_minimo_definido = 12;
          }else if ($sector_id == "45") {
            //ELECTROVIDRIO
            $margen_minimo_definido = 18;
          }else if ($sector_id == "51") {
            //EMPAQUES SINTETICOS
            $margen_minimo_definido = 14;
          }else if ($sector_id == "63") {
            //MERCADERIAS
            $margen_minimo_definido = 14;
          }else if ($sector_id == "70") {
            //MOLDEO DE PLASTICO
            $margen_minimo_definido = 14;
          }else if ($sector_id == "71") {
            //GABINETES
            $margen_minimo_definido = 14;
          }else if ($sector_id == "72") {
            //TUBERIA DE PLASTICO
            $margen_minimo_definido = 10;
          }else if ($sector_id == "76") {
            //TUBERIA CPVC
            $margen_minimo_definido = 10;
          }else if ($sector_id == "90") {
            //TUBERIA
            $margen_minimo_definido = 14;
          }else if ($sector_id == "C3") {
            //Material y EquipoMed
            $margen_minimo_definido = 20;
          }
      }
          //calculos de ventas        
          $ventas_pormar_cien = number_format((100 - $pormar),2);
          $ventas_precio_minimo = number_format((($margen / $ventas_pormar_cien) * 100),2);
          //$ventas_contribucion = number_format(($ventas_precio_minimo - $margen),2);
          $ventas_limite_gerente_pctj = number_format(($pormar * 0.8),2);
          $ventas_limite_comercial_pctj = number_format(($pormar * 0.1),2);
          $ventas_limite_gerente_cien = number_format((100 - $ventas_limite_gerente_pctj),2);
          $ventas_limite_comercial_cien = number_format((100 - $ventas_limite_comercial_pctj),2);
          $ventas_limite_gerente = number_format((($margen / $ventas_limite_gerente_cien) * 100),2);
          $ventas_limite_comercial = number_format((($margen / $ventas_limite_comercial_cien) * 100),2);

          

          $bandera_de_error = 0;
          $errorcomercial = 0;
          $errorgerente = 0;

          if ($importe_descuento <= $ventas_limite_comercial) {
            $errorcomercial = 1;
            $bandera_de_error = 1;
          }

          if ($importe_descuento >= $ventas_limite_comercial) {
            if ($importe_descuento < $ventas_limite_gerente &&  $bandera_de_error == 0) {
              $errorcomercial = 1;
              $bandera_de_error = 1;
            }
          }

          if ($importe_descuento >= $ventas_limite_gerente) {
            if ($importe_descuento < $ventas_precio_minimo &&  $bandera_de_error == 0) {
              
              $errorgerente = 1;
            }
          }
            //validaciones para obtener material existente.
            if ($existencia>0) {
              $aux1_existencia= floor($existencia/$empaque);//existencia en su almacen
            }else{
                $aux1_existencia=0;
            }
            if ($existencia_cdpt>0) {
              $aux2_existencia=floor($existencia_cdpt/$empaque);//existencia en cdpt
            }else{
                $aux2_existencia=0;
            }

            $existencia_total=($aux1_existencia*$empaque)+($aux2_existencia*$empaque);

          //validamos si hay cantidades en existencia para este material
          $bandera_existencia="";
          if ($existencia_total>=$request->unidades_consulta) {
            $bandera_existencia="SI";
          }else{
            $bandera_existencia="NO";
          }

          $aux=$request->unidades_consulta/$empaque;

          $aux_2=ceil($aux);

          $surtir= $aux_2*$empaque;

          $surtir2 = (string)$surtir;

          if ($tipo_material=="A" || $tipo_material=="B") {
            ////////// Inicio de Validacion de Precio
            //if ($importe_descuento>=$importe_real) {
              // Validacion de material por debajo precio Autorizacion de IGMAR Y JESUS 15/06/22 12:01 pm
              if ($importe_descuento>=$importe_real || $codigo_material == '267369'){
            // ALTERACION PARA EL PRECIO DESCUENTO 10/10/2020
            //if ($importe_descuento>=$importe_real || $bandera_gpom4 == 1) {
              if ($error=="0" && $bandera_existencia=="SI") {
                $importe_producto=($surtir*$importe_descuento);
                //$importe_producto=number_format(($surtir*$importe_descuento),2);
                //operacion para margen;
                $margen_utilidad=($importe_descuento - $margen) * $surtir2;
                $cadena_result[$k] = [
                  'id_lista' => "",
                  'codigo_material' => $codigo_material,
                  'nombre_material' => $nombre_material,
                  'unidad_medida' => $unidad_medida,
                  'existencia' => $existencia,
                  'existencia_cdpt' => $existencia_cdpt,
                  'empaque' => $empaqueLabel,
                  'u_pedidas' => $surtir,
                  'u_confirm' => $surtir2,
                  'recordatorios' => "0",
                  'importe_desciento' => $importe_descuento,
                  'importe_producto' => $importe_producto,
                  'validacion' => "Disponible",
                  'descuento' => $descuento,
                  'ventas_centro' =>$ventas_centro,
                  'sector' =>$sector,
                  'pormar' =>$pormar,//valores para valiaciones de credito
                  'porcom' =>$porcom,//valores para valiaciones de credito
                  'margen' =>$margen,
                  'inventario' =>$inventario,
                  'ZK14' =>$ZK14,
                  'ZK71' =>$ZK71,
                  'margen_utilidad' => $margen_utilidad,
                  'margen_minimo_definido' => $margen_minimo_definido,
                  'errorcomercial' => $errorcomercial,
                  'errorgerente' => $errorgerente,
                  'bandera_error' => $bandera_de_error,
                  'precio_lista' => $precio_lista,
                  'bandera_gpom4' => $bandera_gpom4,
                  'imagen_url' => $imagen_url
                  ];
                  $k++;
              }//fin cuando la validacion es Disponible
            }
          }

        }
      }
      }//end for 
      return response()->json($cadena_result);
    }
}