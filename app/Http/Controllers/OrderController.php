<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDetail;
use App\OrderDetailPRO;
use Illuminate\Http\Request;
use SoapClient;

class OrderController extends Controller
{
    //
    public function getMaterial(Request $request)
    {
        function obj2array($obj)
        {
            $out = array();
            foreach ($obj as $key => $val) {
                switch (true) {
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
        } //fin funcion obj2array

        $usuario_vendedor = $request->usuario;
        $puesto = $request->puesto;
        $bandera_programado = $request->bandera_pedido_programado;
        $mat_recibido = $request->code;
        $cantidad_pedida = $request->units;
        $cliente = $request->idCliente;
        $ID_lista = $request->id_lista;
        $VKORG = $request->VKORG;
        $VTWEG = $request->VTWEG;
        $VKBUR = $request->VKBUR;
        $mensage_error = ""; //variable para mensage de errores
        //completar el código de material a 18 digitos
        $n1 = strlen($mat_recibido);
        $n1_aux = 18 - $n1;
        $mat = "";
        for ($i = 0; $i < $n1_aux; $i++) {
            $mat .= "0";
        }
        $material = $mat . $mat_recibido;
        
        //validacion si el material se encuentra en carretes
        try {
            //$servicio1="http://172.16.176.25/webservices/PGC360_Des_Carretes/Carretes.asmx?WSDL";
            $servicio1 = "http://172.16.171.10/webservices/PGC360_Pro_Carretes/Carretes.asmx?WSDL";
            $client1 = new SoapClient($servicio1, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
            $result1 = $client1->Vb_Carretes();
            $result1 = obj2array($result1);
            $noticias1 = $result1['Vb_CarretesResult']['MyResultData'];
            $lista_carretes = collect($noticias1);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        $bandera_carrete = 0;
        foreach ($lista_carretes as $constructor) {
            $dato = $constructor;
            if ($dato['MATNR'] == $material) {
                $bandera_carrete = 1;
            }
        }
        //********* WEBSERVICE PARA MATERIALES Y EXISTENCIAS
        try {
            //$servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios/Mater_Exist_Precios.asmx?WSDL"; //url del servicio
            // $servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio antiguo
            $servicio5 = "http://172.16.171.10/webservices/PGC360_Pro_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio antiguo
            $parametros5 = array(); //parametros de la llamada

            $parametros5['VKBUR'] = "$VKBUR";
            $parametros5['MATNR'] = "$material";
            $parametros5['KUNNR'] = "$cliente";
            $parametros5['VTWEG'] = "$VTWEG";
            $parametros5['VKORG'] = "$VKORG";
            $parametros5['CANT'] = "1";
            //dd($parametros5);
            $client5 = new SoapClient($servicio5, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
            $result5 = $client5->Vb_Mater_Exist_Precios2($parametros5); //llamamos al métdo que nos interesa con los parámetros
            $result5 = obj2array($result5);
            $noticias5 = $result5['Vb_Mater_Exist_Precios2Result']['MyResultData'];
            $collection = collect($noticias5);
            $collection = $collection->first();

        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        } //fin del servicio
        //si obtubo un erro del ws
        //si hay cantidad menor a 1
        
        if ($cantidad_pedida <= 0) {
            $mensage_error = "Unidad ingresada no debe ser cero.";
            $cadena_result = [];
        } else if ($bandera_carrete == 1) {
            $mensage_error = "Codigo erroneo, producto asignado a carrete.";
            $cadena_result = [];
        } else if ($collection['MYERR'] == 1 && $collection['FNMSG'] == 'Arithmetic operation resulted in an overflow.') {
            $mensage_error = "Codigo erroneo, revisar empaque, costo o bloqueos de cliente.";
            $cadena_result = [];
        } else if ($collection['MYERR'] == 1) {
            $mensage_error = "Codigo erroneo, intente con otro codigo.";
            $cadena_result = [];
        } else if ($collection == null) {
            $mensage_error = "Codigo no encontrado o erroneo.";
            $cadena_result = [];
        } else {
            //obtener valores del WS resultado
            $codigo_material = $collection['MATNR'];
            $sucursal = $collection['VKBUR'];
            $aux_nombre = str_replace("\"", "", $collection['MAKTX']);
            $aux_material_2 = str_replace("'", "", $aux_nombre);
            $nombre_material = str_replace("#", "", $aux_material_2);
            $unidad_medida = $collection['MEINS'];
            $existencia = $collection['LABST'];
            $existencia_cdpt = $collection['LABS1'];
            $stock_transito = $collection['TRAME'];
            $empaque = $collection['BSTRF'];

            $empaqueLabel = $collection['BSTRF'];

            $importe = $collection['KBETR']; //ventas_presio
            $importe_descuento = $collection['PCDESC']; //presiodesc
            $importe_real = $collection['ZCOSTO3'];
            $tipo_material = $collection['MAABC'];
            $error = $collection['MYERR'];
            $centro_informacion = $collection['WERKS'];
            //datos extra para alidaciones y guardar en bd
            $descuento = $collection['PDPER']; //%descuento
            $ventas_centro = $collection['WERKS'];
            $sector = $collection['SPART'];
            // fecha programada es del formulario
            $pormar = $collection['PORMAR']; //valores para valiaciones de credito
            $porcom = $collection['PROCOM']; //valores para valiaciones de credito
            $margen = $collection['ZCOSTO3'];
            $inventario = $collection['LABST'];
            //$inventario2=$collection['LABST1'];
            $ZK14 = $collection['ZK14']; //
            $ZK71 = $collection['ZK71'];
            $ZK73 = $collection['ZK73']; //
            $ZK08 = $collection['ZK08'];
            $ZK66 = $collection['ZK66'];
            $ZK69 = $collection['ZK69']; //
            $ZK25 = $collection['ZK25'];
            //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $precio_lista = $collection['PLIST'];
            //margenes comerciales
            $sector_id = $collection['SPART'];
            //conparaciones de sector con el margen minimo que debe tener
            $margen_minimo_definido = 13; //
            //VALORES PARA DETERMINAR SU GPOM4
            $id_gpom4 = $collection['GERPRO'];
            $nombre_gpom4 = $collection['BEZEI'];
            $bandera_gpom4 = 0;
            $division = "";
            $segmento = "";
            $division_comercial = "";

            //********* WEBSERVICE PARA OBTENER INFO DE DIVISION
            try {
                //$servicio9="http://172.16.176.25/webservices/PGC360_Des_GPO4_Info/GPO4_Info.asmx?WSDL"; //url del servicio
                $servicio9 = "http://172.16.171.10/webservices/PGC360_pro_GPO4_Info/GPO4_Info.asmx?WSDL"; //url del servicio antiguo
                $parametros9 = array(); //parametros de la llamada
                $parametros9['P_GPO4'] = $id_gpom4;

                $client9 = new SoapClient($servicio9, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
                $result9 = $client9->Vb_GPO4_Info($parametros9); //llamamos al métdo que nos interesa con los parámetros
                $result9 = obj2array($result9);
                $noticias9 = $result9['Vb_GPO4_InfoResult']['MyResultData'];
                $collection9 = collect($noticias9);
                $collection9 = $collection9->first();

            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            } //fin del servicio
            $division = $collection9['DIVISION'];
            $segmento = $collection9['SEGMENTO'];
            $division_comercial = $collection9['DIVISIONCOMERCIAL'];

            // comparacion si material empaque es = 1.111

            if ($empaque == "1.111") {
                $empaque = "1";
            }
            //si el empaque es igual a 0
            if ($empaque == "0.000" || $empaque == "0") {
                $cadena_result = [];
                $mensage_error = "Código de material con empaque de cero";
            } else {

                // Tabla de margen minimo defindo actualizada 30/09/2020
                if ($sector_id != "") {
                    if ($sector_id == "00") {
                        //Alta tension
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "05") {
                        //ARTEFACTOS ELECTRIC.
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "11") {
                        //LAMPARAS LED
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "12") {
                        //MICROINVERSOR
                        $margen_minimo_definido = 7;
                    } else if ($sector_id == "13") {
                        //PANEL
                        $margen_minimo_definido = 7;
                    } else if ($sector_id == "14") {
                        //KIT SOLAR
                        $margen_minimo_definido = 7;
                    } else if ($sector_id == "20") {
                        //COBRE Y ALEACIONES
                        $margen_minimo_definido = 10;
                    } else if ($sector_id == "30") {
                        //CONDUCTORES
                        $margen_minimo_definido = 10;
                    } else if ($sector_id == "35") {
                        //CONTROLES
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "36") {
                        //CONTROLES FORGAMEX
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "37") {
                        //TRANSFORMADORES
                        $margen_minimo_definido = 10;
                    } else if ($sector_id == "38") {
                        //CALENTADORES
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "39") {
                        //TINACOS
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "40") {
                        //ELECTROCERAMICA
                        $margen_minimo_definido = 13;
                    } else if ($sector_id == "45") {
                        //ELECTROVIDRIO
                        $margen_minimo_definido = 20;
                    } else if ($sector_id == "51") {
                        //EMPAQUES SINTETICOS
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "63") {
                        //MERCADERIAS
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "70") {
                        //MOLDEO DE PLASTICO
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "71") {
                        //GABINETES
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "72") {
                        //TUBERIA DE PLASTICO
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "76") {
                        //TUBERIA CPVC
                        $margen_minimo_definido = 10;
                    } else if ($sector_id == "90") {
                        //TUBERIA
                        $margen_minimo_definido = 15;
                    } else if ($sector_id == "C3") {
                        //Material y EquipoMed
                        $margen_minimo_definido = 35;
                    }
                }
                //calculos de ventas
                $ventas_pormar_cien = number_format((100 - $pormar), 2);
                $ventas_precio_minimo = number_format((($margen / $ventas_pormar_cien) * 100), 2);
                //$ventas_contribucion = number_format(($ventas_precio_minimo - $margen),2);
                $ventas_limite_gerente_pctj = number_format(($pormar * 0.8), 2);
                $ventas_limite_comercial_pctj = number_format(($pormar * 0.1), 2);
                $ventas_limite_gerente_cien = number_format((100 - $ventas_limite_gerente_pctj), 2);
                $ventas_limite_comercial_cien = number_format((100 - $ventas_limite_comercial_pctj), 2);
                $ventas_limite_gerente = number_format((($margen / $ventas_limite_gerente_cien) * 100), 2);
                $ventas_limite_comercial = number_format((($margen / $ventas_limite_comercial_cien) * 100), 2);

                $bandera_de_error = 0;
                $errorcomercial = 0;
                $errorgerente = 0;

                if ($importe_descuento <= $ventas_limite_comercial) {
                    $errorcomercial = 1;
                    $bandera_de_error = 1;
                }

                if ($importe_descuento >= $ventas_limite_comercial) {
                    if ($importe_descuento < $ventas_limite_gerente && $bandera_de_error == 0) {
                        $errorcomercial = 1;
                        $bandera_de_error = 1;
                    }
                }

                if ($importe_descuento >= $ventas_limite_gerente) {
                    if ($importe_descuento < $ventas_precio_minimo && $bandera_de_error == 0) {

                        $errorgerente = 1;
                    }
                }

                //validaciones para obtener material existente.
                if ($existencia > 0) {
                    $aux1_existencia = floor($existencia / $empaque); //existencia en su almacen
                } else {
                    $aux1_existencia = 0;
                }
                if ($existencia_cdpt > 0) {
                    $aux2_existencia = floor($existencia_cdpt / $empaque); //existencia en cdpt
                } else {
                    $aux2_existencia = 0;
                }

                $existencia_total = ($aux1_existencia * $empaque) + ($aux2_existencia * $empaque);
                //si esto esta en modo pedido programado
                if ($bandera_programado == true) {
                    $existencia_total = 0;
                }

                //validamos si hay cantidades en existencia para este material
                $bandera_existencia = "";
                if ($existencia_total >= $cantidad_pedida) {
                    $bandera_existencia = "SI";
                } else {
                    $bandera_existencia = "NO";
                }

                $aux = $cantidad_pedida / $empaque;

                $aux_2 = ceil($aux);

                $surtir = $aux_2 * $empaque;

                $surtir2 = (string) $surtir;

                //Validacion de Materiales
                if ($tipo_material == "A" || $tipo_material == "B" || $tipo_material == "C") {

                    if ($error == "0" && $bandera_existencia == "SI") {
                        $importe_producto = ($surtir * $importe_descuento);

                        $margen_utilidad = ($importe_descuento - $margen) * $surtir2;
                        $cadena_result = [
                            'id_lista' => $ID_lista,
                            'codigo_material' => $codigo_material,
                            'nombre_material' => $nombre_material,
                            'unidad_medida' => $unidad_medida,
                            'existencia' => $existencia,
                            'existencia_cdpt' => $existencia_cdpt,
                            'empaque' => $empaqueLabel,
                            'u_pedidas' => $cantidad_pedida,
                            'u_confirm' => $cantidad_pedida,
                            'recordatorios' => "0",
                            'importe_desciento' => $importe_descuento,
                            'importe_producto' => $importe_producto,
                            'validacion' => "Disponible",
                            //datos extra para alidaciones y guardar en bd
                            'descuento' => $descuento,
                            'ventas_centro' => $ventas_centro,
                            'sector' => $sector,
                            // fecha programada es del formulario
                            'pormar' => $pormar, //valores para valiaciones de credito
                            'porcom' => $porcom, //valores para valiaciones de credito
                            'margen' => $margen,
                            'inventario' => $inventario,
                            //'inventario1' =>$inventario2,
                            'ZK14' => $ZK14,
                            'ZK71' => $ZK71,
                            'ZK73' => $ZK73,
                            'ZK08' => $ZK08,
                            'ZK66' => $ZK66,
                            'ZK69' => $ZK69,
                            'ZK25' => $ZK25,
                            'gpom4' => $id_gpom4,
                            'margen_utilidad' => $margen_utilidad,
                            'margen_minimo_definido' => $margen_minimo_definido,
                            'errorcomercial' => $errorcomercial,
                            'errorgerente' => $errorgerente,
                            'bandera_error' => $bandera_de_error,
                            'precio_lista' => $precio_lista,
                            'bandera_gpom4' => $bandera_gpom4,
                            'gpom4_nombre' => $nombre_gpom4,
                            'division' => $division,
                            'segmento' => $segmento,
                            'division_comercial' => $division_comercial,
                        ];
                    } //fin cuando la validacion es Disponible
                    if ($error == "0" && $bandera_existencia == "NO" && $existencia_total > 0) {
                        $recordatorio = $surtir - $existencia_total;
                        $importe_producto = ($existencia_total * $importe_descuento);

                        $margen_utilidad = ($importe_descuento - $margen) * $existencia_total;
                        $cadena_result = [
                            'id_lista' => $ID_lista,
                            'codigo_material' => $codigo_material,
                            'nombre_material' => $nombre_material,
                            'unidad_medida' => $unidad_medida,
                            'existencia' => $existencia,
                            'existencia_cdpt' => $existencia_cdpt,
                            'empaque' => $empaqueLabel,
                            'u_pedidas' => $cantidad_pedida,
                            'u_confirm' => $cantidad_pedida,
                            'recordatorios' => $recordatorio,
                            'importe_desciento' => $importe_descuento,
                            'importe_producto' => $importe_producto,
                            'validacion' => "Parcial",
                            //datos extra para alidaciones y guardar en bd
                            'descuento' => $descuento,
                            'ventas_centro' => $ventas_centro,
                            'sector' => $sector,
                            // fecha programada es del formulario
                            'pormar' => $pormar, //valores para valiaciones de credito
                            'porcom' => $porcom, //valores para valiaciones de credito
                            'margen' => $margen,
                            'inventario' => $inventario,
                            //'inventario1' =>$inventario2,
                            'ZK14' => $ZK14,
                            'ZK71' => $ZK71,
                            'ZK73' => $ZK73,
                            'ZK08' => $ZK08,
                            'ZK66' => $ZK66,
                            'ZK69' => $ZK69,
                            'ZK25' => $ZK25,
                            'gpom4' => $id_gpom4,
                            'margen_utilidad' => $margen_utilidad,
                            'margen_minimo_definido' => $margen_minimo_definido,
                            'errorcomercial' => $errorcomercial,
                            'errorgerente' => $errorgerente,
                            'bandera_error' => $bandera_de_error,
                            'precio_lista' => $precio_lista,
                            'bandera_gpom4' => $bandera_gpom4,
                            'gpom4_nombre' => $nombre_gpom4,
                            'division' => $division,
                            'segmento' => $segmento,
                            'division_comercial' => $division_comercial,
                        ];
                    } // fin validacion cuando es Parcial
                    if ($error == "0" && $bandera_existencia == "NO" && $existencia_total == 0) {
                        $recordatorio = $surtir - $existencia_total;
                        $importe_producto = ($existencia_total * $importe_descuento);

                        $margen_utilidad = ($importe_descuento - $margen) * $existencia_total;
                        $cadena_result = [
                            'id_lista' => $ID_lista,
                            'codigo_material' => $codigo_material,
                            'nombre_material' => $nombre_material,
                            'unidad_medida' => $unidad_medida,
                            'existencia' => $existencia,
                            'existencia_cdpt' => $existencia_cdpt,
                            'empaque' => $empaqueLabel,
                            'u_pedidas' => $cantidad_pedida,
                            'u_confirm' => $cantidad_pedida,
                            'recordatorios' => $recordatorio,
                            'importe_desciento' => $importe_descuento,
                            'importe_producto' => $importe_producto,
                            'validacion' => "Sin Existencia",
                            //datos extra para alidaciones y guardar en bd
                            'descuento' => $descuento,
                            'ventas_centro' => $ventas_centro,
                            'sector' => $sector,
                            // fecha programada es del formulario
                            'pormar' => $pormar, //valores para valiaciones de credito
                            'porcom' => $porcom, //valores para valiaciones de credito
                            'margen' => $margen,
                            'inventario' => $inventario,
                            //'inventario1' =>$inventario2,
                            'ZK14' => $ZK14,
                            'ZK71' => $ZK71,
                            'ZK73' => $ZK73,
                            'ZK08' => $ZK08,
                            'ZK66' => $ZK66,
                            'ZK69' => $ZK69,
                            'ZK25' => $ZK25,
                            'gpom4' => $id_gpom4,
                            'margen_utilidad' => $margen_utilidad,
                            'margen_minimo_definido' => $margen_minimo_definido,
                            'errorcomercial' => $errorcomercial,
                            'errorgerente' => $errorgerente,
                            'bandera_error' => $bandera_de_error,
                            'precio_lista' => $precio_lista,
                            'bandera_gpom4' => $bandera_gpom4,
                            'gpom4_nombre' => $nombre_gpom4,
                            'division' => $division,
                            'segmento' => $segmento,
                            'division_comercial' => $division_comercial,
                        ];
                    } //fin validacion cuando no hay existencia.
                    //fin validacion precio

                } //fin validacion material A y B y C
                if ($tipo_material == "D") {
                    $cadena_result = [];
                    $mensage_error = "Código erroneo. Producto Descontinuado";
                }
                if ($tipo_material == "O") {
                    $cadena_result = [];
                    $mensage_error = "Código erroneo. Producto Obsoleto";
                }
                if ($tipo_material == "Z") {
                    $cadena_result = [];
                    $mensage_error = "Código erroneo. Producto Pendiente de Verificación Comercial";
                }
                if ($tipo_material == "") {
                    $cadena_result = [];
                    $mensage_error = "Código de material sin genetica de producto, Centro: " . $centro_informacion;
                }

            } //fin else error codigo

        } //validacion de empaque cero

        $datos_total = ['cadena_result' => $cadena_result, 'mensaje_error' => $mensage_error];
        return response()->json(
            $datos_total
        );
    }

    public function setOrder(Request $request)
    {

        $opportunities = Order::where('id_promotor', $request->promotor_id)->get();
        $numero_oportunidad = count($opportunities);
        $numero_oportunidad++;
        $folio = 'OC-' . $request->promotor_id . '-' . $numero_oportunidad;

        date_default_timezone_set('America/Mexico_City');
        $fecha_actual = date("Y-m-d");
        $hora_actual = date("H:i:s");
        $orden_compra = new Order();
        $orden_compra->folio = $folio;
        $orden_compra->idUsuario = $request->idUsuario;
        $orden_compra->nombreUsuario = $request->nombreUsuario;
        $orden_compra->tipoDistribuidor = $request->type;
        $orden_compra->idDistribuidor = $request->idDistribuidor;
        $orden_compra->nombreDistribuidor = $request->nombreDistribuidor;
        $orden_compra->orden_compra = $request->orden_compra;
        $orden_compra->estatus = 'PENDIENTE';
        $orden_compra->hora = $hora_actual;
        $orden_compra->fecha = $fecha_actual;
        $orden_compra->id_promotor = $request->promotor_id;
        $orden_compra->save();

        $list = $request->list;
        foreach ($list as $listInformation) {
            $order_detail = new OrderDetail();
            $order_detail->codigo_material = $listInformation['codigo_material'];
            $order_detail->nombre_material = $listInformation['nombre_material'];
            $order_detail->unidades_confirmadas = $listInformation['u_confirm'];
            $order_detail->gpo4 = $listInformation['gpom4'];
            $order_detail->gpo4_nombre = $listInformation['gpom4_nombre'];
            $order_detail->division = $listInformation['division'];
            $order_detail->segmento = $listInformation['segmento'];
            $order_detail->division_comercial = $listInformation['division_comercial'];
            $order_detail->orden_compra_id = $orden_compra->id;
            $order_detail->save();

        }
        return $folio;
    }

    public function actualizarDivisionOrder(Request $request)
    {

        function obj2array($obj)
        {
            $out = array();
            foreach ($obj as $key => $val) {
                switch (true) {
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
        } //fin funcion obj2array

        $ordenes = OrderDetailPRO::where('id', "!=", null)->get();  
      
        foreach ($ordenes as $ordenes_det) {
            $material = '000000000000'.$ordenes_det['codigo_material'];
            $id = $ordenes_det['id'];
            //********* WEBSERVICE PARA MATERIALES Y EXISTENCIAS
            try {
                //$servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios/Mater_Exist_Precios.asmx?WSDL"; //url del servicio
                // $servicio5="http://172.16.176.25/webservices/PGC360_Des_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio antiguo
                $servicio5 = "http://172.16.171.10/webservices/PGC360_Pro_Mater_Exist_Precios2/Mater_Exist_Precios2.asmx?WSDL"; //url del servicio antiguo
                $parametros5 = array(); //parametros de la llamada

                $parametros5['VKBUR'] = "IU00";
                $parametros5['MATNR'] = "$material";
                $parametros5['KUNNR'] = "0000064419";
                $parametros5['VTWEG'] = "PR";
                $parametros5['VKORG'] = "IUS2";
                $parametros5['CANT'] = "1";
                //dd($parametros5);
                $client5 = new SoapClient($servicio5, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
                $result5 = $client5->Vb_Mater_Exist_Precios2($parametros5); //llamamos al métdo que nos interesa con los parámetros
                $result5 = obj2array($result5);
                $noticias5 = $result5['Vb_Mater_Exist_Precios2Result']['MyResultData'];
                $collection = collect($noticias5);
                $collection = $collection->first();

            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            } //fin del servicio
            
            //VALORES PARA DETERMINAR SU GPOM4
            $id_gpom4 = $collection['GERPRO'];
            $nombre_gpom4 = $collection['BEZEI'];
            $division = "";
            $segmento = "";
            $division_comercial = "";

            //********* WEBSERVICE PARA OBTENER INFO DE DIVISION
            try {
                //$servicio9="http://172.16.176.25/webservices/PGC360_Des_GPO4_Info/GPO4_Info.asmx?WSDL"; //url del servicio
                $servicio9 = "http://172.16.171.10/webservices/PGC360_pro_GPO4_Info/GPO4_Info.asmx?WSDL"; //url del servicio antiguo
                $parametros9 = array(); //parametros de la llamada
                $parametros9['P_GPO4'] = $id_gpom4;

                $client9 = new SoapClient($servicio9, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
                $result9 = $client9->Vb_GPO4_Info($parametros9); //llamamos al métdo que nos interesa con los parámetros
                $result9 = obj2array($result9);
                $noticias9 = $result9['Vb_GPO4_InfoResult']['MyResultData'];
                $collection9 = collect($noticias9);
                $collection9 = $collection9->first();

            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            } //fin del servicio
            $division = $collection9['DIVISION'];
            $segmento = $collection9['SEGMENTO'];
            $division_comercial = $collection9['DIVISIONCOMERCIAL'];

            $order_detail = OrderDetailPRO::find($id);
            $order_detail->gpo4 = $id_gpom4;
            $order_detail->gpo4_nombre = $nombre_gpom4;
            $order_detail->division = $division;
            $order_detail->segmento = $segmento;
            $order_detail->division_comercial = $division_comercial;
            $order_detail->save();
        }
        return 'ok';
    }
}