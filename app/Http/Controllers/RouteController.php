<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\RouteList;

use App\OpportunitiesINEGI;
use DB;

class RouteController extends Controller
{
    //
    public function getRouteList(Request $request){
        //$routelist = RouteList::all()->where("id_promotor","=",$request->id);
        $routelist = DB::connection()->select("SELECT * FROM route_list WHERE id_promotor like '$request->id' ORDER BY id_entidad ASC , id_municipio ASC , numero_ruta ASC");
        return response()->json(
            $routelist
        );
    }

    public function getRouteMap(Request $request){
        $NumeroRuta = $request->idRoute;
        $registro_ruta = [];
        $contador_ruta = 0;
        $origen = '';
        $destino = '';

        $rutas = DB::connection()->select("SELECT * FROM opportunities_inegi WHERE id_ruta like '$NumeroRuta' ORDER BY orden_ruta ASC");

        foreach ($rutas as $constructor) {
            $dato = $constructor;
            //dd($dato);
            if($contador_ruta == 0){
                $origen = $dato->latitud.",".$dato->longitud;
                $location = ['lat' => (float)$dato->latitud,'lng' => (float)$dato->longitud];
                $location  = (object)$location;
                $registro_ruta[$contador_ruta] = ['location' => $location, 'stopover' => true];
                $contador_ruta++;
            }else{                      
                $destino = $dato->latitud.",".$dato->longitud;
                $location = ['lat' => (float)$dato->latitud,'lng' => (float)$dato->longitud];
                $location  = (object)$location;
                $registro_ruta[$contador_ruta-1] = ['location' => $location, 'stopover' => true];
                $contador_ruta++; 
            }
        }//fin foreach
        $datos = [
                'registro_gps' => $rutas, 
                'registro_rutas' => $registro_ruta,
                'origen' => $origen,
                'destino' => $destino];                           
        return response()->json(
            $datos
            );
    }//fin

    public function startRoute(Request $request){
        $idRuta = $request->id_ruta;
        date_default_timezone_set('America/Mexico_City');
        $fechaactual= date("Y-m-d");
        $horaactual= date("H:i:s");
        //actualizar estado de encuesta
        $rutaInfo = RouteList::find($idRuta);
        if($rutaInfo->fecha_inicio == null){
            $rutaInfo->fecha_inicio = $fechaactual;
        }
        if($rutaInfo->hora_inicio == null){
            $rutaInfo->hora_inicio = $horaactual;
            $rutaInfo->latitud_inicio = $request->latitud;
            $rutaInfo->longitud_inicio = $request->longitud; 
        }
        $rutaInfo->estatus = 'En proceso';
        $rutaInfo->save();
        return 'Inicio Ruta';
    }

    public function getRouteInfo(Request $request){      
        $rutaInfo = RouteList::find($request->id);
        return response()->json(
            $rutaInfo
            );;
    }

    public function pauseRoute(Request $request){
        $idRuta = $request->id_ruta;
        date_default_timezone_set('America/Mexico_City');
        $fechaactual= date("Y-m-d");
        $horaactual= date("H:i:s");
        //actualizar estado de encuesta
        $rutaInfo = RouteList::find($idRuta);
        $rutaInfo->estatus = 'En pausa';
        $rutaInfo->save();
        return 'Pausa Ruta';
    }

    public function getRutasDisponibles(Request $request){
        $routelist = RouteList::where("id_entidad",$request->idEntidad)->where("id_municipio",$request->idMunicipio)->where("id_promotor",null)->get();
        return response()->json(
            $routelist
        );
    }

    public function asignarRutaPromotor(Request $request){
        //actualizar rutas asinadas
        foreach ($request->rutasSelect as $punto) {
            $actualizar_ruta = RouteList::find($punto['id']);
            $actualizar_ruta->id_promotor = $request->promotorSelect;
            $actualizar_ruta->save();
            //actualizar Opportunidades
            $opportunities = OpportunitiesINEGI::where("id_ruta", $punto['id'])->get();
            foreach ($opportunities as $punto1){
                $actualizar_oportunidad = OpportunitiesINEGI::find($punto1['id']);
                $actualizar_oportunidad->id_promotor = $request->promotorSelect;
                $actualizar_oportunidad->save();
            }
        }
        return 'Ruta Asignada';
        
    }

    public function quitarRutaPromotor(Request $request){
        //dd($request);
        //actualizar rutas asinadas
            $actualizar_ruta = RouteList::find($request->rutasSelect);
            $actualizar_ruta->id_promotor = null;
            $actualizar_ruta->save();
            //actualizar Opportunidades
            $opportunities = OpportunitiesINEGI::where("id_ruta", $request->rutasSelect)->get();
            foreach ($opportunities as $punto1){
                $actualizar_oportunidad = OpportunitiesINEGI::find($punto1['id']);
                $actualizar_oportunidad->id_promotor = null;
                $actualizar_oportunidad->save();
            }
        
        return 'Ruta liberada';
        
    }
}
