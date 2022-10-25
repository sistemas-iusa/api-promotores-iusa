<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use SoapClient;

use App\RouteList;

use App\OpportunitiesINEGI;

use App\Forms;

use App\Distributor;

use DB;

class FormController extends Controller
{
    public function obtenerDistribuidores(){        
        $usuario="facastillo";  
        $puesto="E";
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
          /******** WEBSERVICE PARA CLIENTES VENDEDORES ********/
          try {
            $servicio1="http://172.16.171.10/webservices/PGC360_Pro_Vendedor_Cliente/Vendedor_Cliente.asmx?WSDL"; //url del servicio
            $parametros1=array(); //parametros de la llamada
            $parametros1['P_USERNAME']="$usuario";
            $parametros1['P_PUESTO']="$puesto";
            $client1 = new SoapClient($servicio1,array('cache_wsdl' => WSDL_CACHE_NONE,'trace' => TRUE));
            $result1 = $client1->Vb_Vendedor_Cliente($parametros1);//llamamos al métdo que nos interesa con los parámetros
            $result1 = obj2array($result1);
            $noticias1=$result1['Vb_Vendedor_ClienteResult']['MyResultData'];
            $collection = collect($noticias1);
            
          } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
          }
          $n_clientes = count($collection);
          $array_clientes = [];
          for ($i=0; $i < $n_clientes-1 ; $i++) {
            $array_clientes = $collection[$i];
           // dd($array_clientes);
            $array_clientes['NOMBRELIST'] = $array_clientes['KUNNR']." - ".$array_clientes['NAME1'];
           
            $collection[$i] = $array_clientes;
          }
          return response()->json(
          $collection
            );
        }

        public function saveForm(Request $request)
        {
           //dd($request);
            date_default_timezone_set('America/Mexico_City');
            $fechaactual= date("Y-m-d");
            $horaactual= date("H:i:s");
    
            $listaD = $request->distribuidores;
            foreach($listaD as $infoLista){
              $nuevo_distribuidor = new Distributor();
            
              $nuevo_distribuidor->nombre = $infoLista['nombre'];
              $nuevo_distribuidor->idIusa = $infoLista['idIusa'];
              $nuevo_distribuidor->tipo = $infoLista['tipo'];
              $nuevo_distribuidor->direccion = $infoLista['direccion'];
              $nuevo_distribuidor->telefono = $infoLista['telefono'];
              $nuevo_distribuidor->correo = $infoLista['correo'];
              $nuevo_distribuidor->cp = $infoLista['cp'];
              $nuevo_distribuidor->calificacion = $infoLista['calificacion'];
              $nuevo_distribuidor->id_oportunidad = $request->idOportunidad;
              $nuevo_distribuidor->save();
            }
            //guardar encuesta
            $nueva_encuesta= new Forms();
            $nueva_encuesta->pregunta1 = $request->pregunta1;
            $nueva_encuesta->pregunta2 = $request->pregunta2;
            $nueva_encuesta->pregunta3 = $request->pregunta3;
            $nueva_encuesta->pregunta4 = $request->pregunta4;
            $nueva_encuesta->pregunta5 = $request->pregunta5;
            $nueva_encuesta->pregunta6 = $request->pregunta6;
            $nueva_encuesta->pregunta7 = $request->pregunta7;
            $nueva_encuesta->pregunta8 = $request->pregunta8;
            $nueva_encuesta->pregunta9 = $request->pregunta9;
            $nueva_encuesta->pregunta10= $request->pregunta10;
            $nueva_encuesta->pregunta11 = $request->pregunta11;
            $nueva_encuesta->pregunta12 = $request->pregunta12;
            
            $nueva_encuesta->motivo_fin_encuesta= $request->motivo_fin_encuesta;
            $nueva_encuesta->fecha = $fechaactual;
            $nueva_encuesta->hora = $horaactual;
            $nueva_encuesta->latitud = $request->latitud;
            $nueva_encuesta->longitud = $request->longitud;
            $nueva_encuesta->id_oportunidad = $request->idOportunidad;
            $nueva_encuesta->save();
    
            //actualizar estado de encuesta
            $actualizar_oportunidad = OpportunitiesINEGI::find($request->idOportunidad);
            $actualizar_oportunidad->bandera_encuesta = 1;
            $actualizar_oportunidad->bandera_prospecto = 1;
            //numero de ruta
            $NumeroRuta = $actualizar_oportunidad->numero_ruta;
            $actualizar_oportunidad->save();
    
            //actualizar InfoRuta 
            $buscar_infoRuta = RouteList::all()->where('id_promotor',$request->id_promotor)->where('numero_ruta',$NumeroRuta);
            
            $info_ruta_select = $buscar_infoRuta->first();        
            $actualizar_infoRuta = RouteList::find($info_ruta_select->id);       
            $actualizar_infoRuta->encuestas_realizadas++;
            $actualizar_infoRuta->save();
            $ultimo_ruta = intval($actualizar_infoRuta->orden_ruta);
            if($ultimo_ruta == $actualizar_infoRuta->encuestas_realizadas){
                $actualizar_infoRuta = RouteList::find($info_ruta_select->id);  
                $actualizar_infoRuta->estatus = 'Terminado'; 
                $actualizar_infoRuta->fecha_final = $fechaactual;
                $actualizar_infoRuta->hora_final = $horaactual;
                $actualizar_infoRuta->latitud_final = $request->latitud;
                $actualizar_infoRuta->longitud_final = $request->longitud; 
                $actualizar_infoRuta->save();
                return 'el ultimo';
            }        
            
            return 'guardado exitoso';
        }
    
        
        public function show(Request $request)
        {
            $id_oportunidad = $request->id;
            $respuesta_encuesta = DB::connection()->select("SELECT * FROM forms WHERE id_oportunidad like '$id_oportunidad'");
            $distribuidores_lista = DB::connection()->select("SELECT * FROM distributor WHERE id_oportunidad like '$id_oportunidad' ORDER BY tipo ASC");
            $datos = ['respuestas' => $respuesta_encuesta[0],'distribuidores' => $distribuidores_lista];
            return response()->json(
                $datos
                );
        }

        public function getFormDetail(Request $request)
        {
            $id_oportunidad = $request->idOportunidad;
            $id_promotor = $request->idPromotor;
            $buscar_encuesta = Forms::all()->where('id_oportunidad',$request->idOportunidad);
            $encuesta_select = $buscar_encuesta->first();
            return response()->json(
                $encuesta_select
                );
        }

        public function saveFormPromotor(Request $request)
        {
           //dd($request);
    
           $buscar_encuesta = Forms::all()->where('id_oportunidad',$request->idOportunidad);
           $buscar_encuesta = $buscar_encuesta->first();
            //guardar encuesta
            $nueva_encuesta = Forms::find($buscar_encuesta->id);
            $nueva_encuesta->pregunta13 = $request->pregunta13;
            $nueva_encuesta->comentario13 = $request->comentario13;
            $nueva_encuesta->pregunta14 = $request->pregunta14;
            $nueva_encuesta->save(); 
             
            return 'guardado exitoso';
        }
}
