<?php

namespace App\Http\Controllers;

use App\OpportunitiesINEGI;
use App\RouteList;
use Illuminate\Http\Request;
use DB;

class OpportunityController extends Controller
{
    //
    public function getOpportunities(Request $request)
    {
        $opportunities = OpportunitiesINEGI::where("id_promotor", $request->id)->get();
        return response()->json(
            $opportunities
        );
    }

    public function getProspects(Request $request)
    {
        $prospects = OpportunitiesINEGI::where('id_promotor', $request->id)->where('bandera_prospecto', 1)->get();
        return response()->json(
            $prospects
        );
    }

    public function getOpportunity(Request $request)
    {
        $opportunity = OpportunitiesINEGI::where('id', $request->id)->get();
        return response()->json(
            $opportunity
        );
    }

    public function deleteOportunity(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');
        $fechaactual = date("Y-m-d");
        $horaactual = date("H:i:s");
        //actualizar la lista de Oportunidades
        $oportunidad = OpportunitiesINEGI::find($request->idOportunidad);
        $oportunidad->bandera_cancelada = 1;
        $oportunidad->motivo_cancelacion = $request->motivo;
        $oportunidad->latitud_cancelado = $request->latitud;
        $oportunidad->longitud_cancelado = $request->longitud;
        //numero de ruta
        $NumeroRuta = $oportunidad->numero_ruta;
        $oportunidad->save();
        //actualizar estatus ruta
        $buscar_infoRuta = RouteList::where('id_promotor', $request->idPromotor)->where('numero_ruta', $NumeroRuta)->get();

        $info_ruta_select = $buscar_infoRuta->first();
        $actualizar_infoRuta = RouteList::find($info_ruta_select->id);
        $actualizar_infoRuta->encuestas_realizadas++;
        $actualizar_infoRuta->save();
        $ultimo_ruta = intval($actualizar_infoRuta->orden_ruta);
        if ($ultimo_ruta <= $actualizar_infoRuta->encuestas_realizadas) {
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

    public function getOpportunitiesProspecto(Request $request)
    {
        $opportunities = OpportunitiesINEGI::all()->where("id_promotor", "=", $request->id)->where('bandera_prospecto', '!=', 1);
        $prospectos = [];
        $contador = 0;
        foreach ($opportunities as $constructor) {
            $dato = $constructor;
            $object = (object) $dato;
            $prospectos[$contador] = $object;
            $contador++;
        }
        return response()->json(
            $prospectos
        );
    }

    public function newOpportunities(Request $request){
        
        //actualizar la ordenruta 
        $infoRuta = RouteList::find($request->idRuta);
        //return $infoRuta;
        $nueva_ordenRuta= $infoRuta->encuestas_realizadas;
        $nueva_ordenRuta++;
        $rutaId = 'FMR'.$request->numeroRuta.'_'.$nueva_ordenRuta;
        $infoRuta->orden_ruta = str_pad($infoRuta->orden_ruta+1,"2","0", STR_PAD_LEFT);
        $nueva_ordenRuta1 = str_pad($nueva_ordenRuta,"2","0", STR_PAD_LEFT);
        $infoRuta->save(); 

        //actualizar marcadores
        $marcadores = DB::connection()->select("SELECT * FROM opportunities_inegi WHERE id_ruta like '$request->idRuta' ORDER BY orden_ruta ASC");
        foreach ($marcadores as $constructor) {
            $dato = $constructor;
            $orden_ruta_convertido = intval($dato->orden_ruta);
            if($orden_ruta_convertido == $nueva_ordenRuta){
                $nueva_ordenRuta++;
                $orden_ruta_text = $nueva_ordenRuta;
                if($nueva_ordenRuta < 10){
                    $orden_ruta_text = str_pad($nueva_ordenRuta,"2","0", STR_PAD_LEFT);
                }
                $rutaIdLista = 'FMR'.$request->numeroRuta.'_'.$orden_ruta_text;

                $actualizar_oportunidad = OpportunitiesINEGI::find($dato->id);                
                $actualizar_oportunidad->ruta_id = $rutaIdLista;
                $actualizar_oportunidad->orden_ruta = $orden_ruta_text;
                $actualizar_oportunidad->save();                
            }
        }
        
        //guardar nueva oportunidad
        $new_oportunidad = new OpportunitiesINEGI();
        $new_oportunidad->ruta_id = $rutaId;
        $new_oportunidad->nombre = $request->nombre;
        $new_oportunidad->razon_social = $request->razon_social;
        $new_oportunidad->direccion = $request->direccion;
        $new_oportunidad->latitud = $request->latitud;
        $new_oportunidad->longitud = $request->longitud;
        $new_oportunidad->numero_ruta = $request->numeroRuta;
        $new_oportunidad->orden_ruta = $nueva_ordenRuta1;
        $new_oportunidad->id_ruta = $request->idRuta;
        $new_oportunidad->clave_entidad = $infoRuta->id_entidad;
        $new_oportunidad->entidad = $infoRuta->entidad;
        $new_oportunidad->clave_municipio = $infoRuta->id_municipio;
        $new_oportunidad->municipio = $infoRuta->municipio;
        $new_oportunidad->bandera_nuevo = '1';
        $new_oportunidad->id_promotor =  $request->idPromotor;
        $new_oportunidad->save();


        return 'guardado exitoso';
    }

    public function upadteProspecto(Request $request)
    {
       
        //actualizar la lista de Oportunidades
        $oportunidad = OpportunitiesINEGI::find($request->prospecto_id);
        $oportunidad->nombre = $request->prospecto_nombre;
        $oportunidad->direccion = $request->prospecto_direccion;
        $oportunidad->telefono = $request->prospecto_telefono;
        $oportunidad->contacto_nombre = $request->prospecto_contacto_nombre;
        $oportunidad->contacto_telefono = $request->prospecto_contacto_telefono;
        $oportunidad->save();
        

        return 'guardado exitoso';
    }
}
