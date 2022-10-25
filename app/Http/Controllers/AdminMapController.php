<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\OpportunitiesINEGI;

use App\Entidad;

use App\Municipio;

use App\RouteList;

class AdminMapController extends Controller
{

    public function getEntidades(Request $request){
        $entidades = Entidad::all();
        //dd($oportunidades);
        return response()->json(
            $entidades
              );
    }

    public function getMunicipio(Request $request){
        $idEntidad = $request->id;
        $municipios = Municipio::where('clave_entidad',$idEntidad)->get();
        //dd($oportunidades);
        return response()->json(
            $municipios
              );
    }

    public function getOpportunitiesInegi(Request $request){
        $oportunidades = OpportunitiesINEGI::where('clave_entidad',$request->idEntidad)->where('clave_municipio',$request->idMunicipio)->get();
        //dd($oportunidades);
        return response()->json(
            $oportunidades
              );
    }

    public function getProcesoOpportunitiesInegi(Request $request){
        $oportunidades_lat = OpportunitiesINEGI::where('clave_entidad',$request->idEntidad)->where('clave_municipio',$request->idMunicipio)->where('id_ruta',null)->orderBy('latitud', 'DESC')->get();
        $oportunidades_lng = OpportunitiesINEGI::where('clave_entidad',$request->idEntidad)->where('clave_municipio',$request->idMunicipio)->where('id_ruta',null)->orderBy('longitud', 'DESC')->get();
        $rutas_existentes = RouteList::where('id_entidad',$request->idEntidad)->where('id_municipio',$request->idMunicipio)->orderBy('numero_ruta', 'ASC')->get();
        $oportunidades_lat_count = count($oportunidades_lat);
        $rutas_existentes_count= count($rutas_existentes);
        $opportunitiesTotal = OpportunitiesINEGI::where('clave_entidad',$request->idEntidad)->where('clave_municipio',$request->idMunicipio)->where('id','!=', null)->count();
        $ruta_info = [];
        $contador_rutas = 1;
        $contador_rutas_array = 0;
        $response = [];
        //en caso que no tenga ninguna oportunidad
        if($oportunidades_lat_count == 0 && $rutas_existentes_count == 0){
            $response = ['message' => 'Sin Oportunidad ni rutas registradas', 'data' => $ruta_info];
            return response()->json($response);
        }
        //en caso que ya no tenga mas rutas por generar
        if($oportunidades_lat_count == 0 && $rutas_existentes_count != 0){
            //enlistar las rutas ya generadas
            foreach ($rutas_existentes as $checar_ruta) {
                $numero_ruta_check =  str_pad($contador_rutas,"2","0", STR_PAD_LEFT);
                if($checar_ruta->numero_ruta == $numero_ruta_check){
                    $nombre_ruta = 'RG_'.$numero_ruta_check;
                    $ruta_generada = OpportunitiesINEGI::where('id_ruta',$checar_ruta->id)->orderBy('orden_ruta', 'ASC')->get();
                    $dato = [
                        'nombre' => $nombre_ruta ,
                        'entidad' => $request->idEntidad,
                        'municipio' => $request->idMunicipio,
                        'numero_ruta' => $numero_ruta_check,
                        'puntos'=> $ruta_generada,
                        'estatus' => 'guardada'];
                    $ruta_info[$contador_rutas_array] = $dato;
                    $contador_rutas++;
                    $contador_rutas_array++;
                }//fin foreach
                $response = ['message' => 'Municipio Completado', 'data' => $ruta_info];
                return response()->json($response);
            }
        }
        //en caso que tenga menos de un punto
        if($oportunidades_lat_count < 2){
            //ruta de 1
            $ruta = [];
            $ruta[0]= $oportunidades_lat->first();
            $numero_ruta=  str_pad($contador_rutas,"2","0", STR_PAD_LEFT);
            $nombre_ruta = 'RG_'.$numero_ruta;
           
            $dato = [
                'nombre' => $nombre_ruta ,
                'entidad' => $request->idEntidad,
                'municipio' => $request->idMunicipio,
                'numero_ruta' => $numero_ruta,
                'puntos'=> $ruta,
                'estatus' => 'nuevo'];
            $ruta_info[$contador_rutas_array] = $dato;
            $contador_rutas++;
            $contador_rutas_array++;
            //quitar la ultima oportunidad de los comparadores
            $oportunidades_lat = [];
            $oportunidades_lng = [];
            $ruta = [];
            $response = ['message' => 'Ruta con 1 Oportunidad', 'data' => $ruta_info];
            return response()->json($response);
        }else{
            $ruta[0]= $oportunidades_lat[0];
            while (count($oportunidades_lat) != 0) {
                //en caso de tener 1 oportunidad sobrante
                if(count($oportunidades_lat) == 1)
                {
                    //ruta de 1
                    $ruta = [];
                    $ruta[0]= $oportunidades_lat->first();
                    $numero_ruta=  str_pad($contador_rutas,"2","0", STR_PAD_LEFT);
                    $nombre_ruta = 'RG_'.$numero_ruta;
                   
                    $dato = [
                        'nombre' => $nombre_ruta ,
                        'entidad' => $request->idEntidad,
                        'municipio' => $request->idMunicipio,
                        'numero_ruta' => $numero_ruta,
                        'puntos'=> $ruta,
                        'estatus' => 'nuevo'];
                    $ruta_info[$contador_rutas_array] = $dato;
                    $contador_rutas++;
                    $contador_rutas_array++;
                    //quitar la ultima oportunidad de los comparadores
                    $oportunidades_lat = [];
                    $oportunidades_lng = [];
                    $ruta = [];
                }else {
                    $ruta = [];
                    $ruta[0]= $oportunidades_lat->first();
                    //ver si hay mas rutas
                    if(count($oportunidades_lat) >= 10){
                        $limite_for=9;
                    }else{
                        $limite_for=count($oportunidades_lat)-1;
                    }
                    //for para las ruta en curso
                    for($i=0;$i<$limite_for;$i++){                
                        $primer_punto = $ruta[$i];
                        foreach ($oportunidades_lng as $comparador) {
                            //asegurar que comparador no sea igual que primer punto
                            if($primer_punto->id != $comparador->id){
                                //medir distancia entre dos coordenadas
                                $lat1 = $primer_punto->latitud;
                                $lon1 = $primer_punto->longitud;
                                $lat2 = $comparador->latitud;
                                $lon2 = $comparador->longitud;
                                
                                $pi80 = M_PI / 180;
                                $lat1 *= $pi80;
                                $lon1 *= $pi80;
                                $lat2 *= $pi80;
                                $lon2 *= $pi80;

                                $r = 6372.797; // mean radius of Earth in km
                                $dlat = $lat2 - $lat1;
                                $dlon = $lon2 - $lon1;
                                $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
                                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                                $km = $r * $c;

                                //comparar si ya tiene un siguiente en ruta 
                                if(count($ruta) == $i+1){
                                    $comparador->km = $km;
                                    $ruta[$i+1]= $comparador;
                                }else if(count($ruta) == $i+2){
                                    //comparamos con KM anterior para ver si es menor
                                    $km_anterior = $ruta[$i+1]->km;
                                    if($km_anterior > $km){
                                        $comparador->km = $km;
                                        $ruta[$i+1]= $comparador; 
                                    }
                                }
                            }//fin if primer punto
                        }// fin foreach
                        //ver si ruta tiene mas puntos,
                        if(count($ruta) == 1){
                            //eliminar los puntos obtenidos en los arreglos de lat y lng
                            $id1 = $ruta[$i]->id;

                            $oportunidades_lat = $oportunidades_lat->filter(function ($value, $key) use ($id1) {
                                return $value->id != $id1;
                            });
                            $oportunidades_lat->all();

                            $oportunidades_lng = $oportunidades_lng->filter(function ($value, $key) use ($id1) {
                                return $value->id != $id1;
                            });
                            $oportunidades_lng->all();   
                        }else{
                            //eliminar los puntos obtenidos en los arreglos de lat y lng
                            $id1 = $ruta[$i]->id;
                            $id2 = $ruta[$i+1]->id;
                            
                            $oportunidades_lat = $oportunidades_lat->filter(function ($value, $key) use ($id1,$id2) {
                                return $value->id != $id1 && $value->id != $id2;
                            });
                            $oportunidades_lat->all();

                            $oportunidades_lng = $oportunidades_lng->filter(function ($value, $key) use ($id1,$id2) {
                                return $value->id != $id1 && $value->id != $id2;
                            });
                            $oportunidades_lng->all();   
                        }
                                
                    }//fin for ruta
                    //verificar si ya hay ruta creada
                    foreach ($rutas_existentes as $checar_ruta) {
                        $numero_ruta_check =  str_pad($contador_rutas,"2","0", STR_PAD_LEFT);

                        if($checar_ruta->numero_ruta == $numero_ruta_check){
                            //return $checar_ruta;
                            $nombre_ruta = 'RG_'.$numero_ruta_check;
                            $ruta_generada = OpportunitiesINEGI::where('id_ruta',$checar_ruta->id)->orderBy('orden_ruta', 'ASC')->get();
                            $dato = [
                                'nombre' => $nombre_ruta ,
                                'entidad' => $request->idEntidad,
                                'municipio' => $request->idMunicipio,
                                'numero_ruta' => $numero_ruta_check,
                                'puntos'=> $ruta_generada,
                                'estatus' => 'guardada'];
                            $ruta_info[$contador_rutas_array] = $dato;
                            $contador_rutas++;
                            $contador_rutas_array++;
                        }
                       
                    }
                    //nombre de ruta
                    $numero_ruta=  str_pad($contador_rutas,"2","0", STR_PAD_LEFT);
                    $nombre_ruta = 'RG_'.$numero_ruta;
                    $dato = [
                        'nombre' => $nombre_ruta ,
                        'entidad' => $request->idEntidad,
                        'municipio' => $request->idMunicipio,
                        'numero_ruta' => $numero_ruta,
                        'puntos'=> $ruta,
                        'estatus' => 'nuevo'];
                    $ruta_info[$contador_rutas_array] = $dato;
                    $contador_rutas++;
                    $contador_rutas_array++;
                }//fin de else
                
            }//fin while
            $response = ['message' => 'Exito', 'data' => $ruta_info, 'num_oportunidades' => $opportunitiesTotal];
        }//fin else


        return response()->json(
            $response 
              );
    }

    public function getRouteMapPrueba(Request $request){
       
        $registro_ruta = [];
        $contador_ruta = 0;
        $origen = '';
        $destino = '';

        $rutas = $request->ruta['puntos'];

        foreach ($rutas as $constructor) {
            $dato = $constructor;
            //dd($dato['longitud']);
            if($contador_ruta == 0){
                $origen = $dato['latitud'].",".$dato['longitud'];
                $location = ['lat' => (float)$dato['latitud'],'lng' => (float)$dato['longitud']];
                $location  = (object)$location;
                $registro_ruta[$contador_ruta] = ['location' => $location, 'stopover' => true];
                $contador_ruta++;
            }else{                      
                $destino = $dato['latitud'].",".$dato['longitud'];
                $location = ['lat' => (float)$dato['latitud'],'lng' => (float)$dato['longitud']];
                $location  = (object)$location;
                $registro_ruta[$contador_ruta-1] = ['location' => $location, 'stopover' => true];
                $contador_ruta++; 
            }
        }//fin foreach
        //dd($rutas);
        $datos = [
                'registro_gps' => $rutas, 
                'registro_rutas' => $registro_ruta,
                'origen' => $origen,
                'destino' => $destino];                           
        return response()->json(
            $datos
            );
    }//fin

    public function saveRuta(Request $request){
        //dd(count($request->ruta['puntos']));
        //guardamos ruta generada en route_list 
        //Y asignamos el id de ruta en cada oportunidad.
        //obtener nombre Entidad
        $nombre_Entidad = Entidad::find($request->ruta['entidad']);
        //obtener Nombre Municipio
        $nombre_Municipio = Municipio::where('clave_entidad',$request->ruta['entidad'])->where('clave_municipio',$request->ruta['municipio'])->get();
        $nombre_Municipio = $nombre_Municipio[0];
        //return $nombre_Municipio->nombre;
        $nueva_ruta = new RouteList();
        $nueva_ruta->id_entidad = $request->ruta['entidad'];
        $nueva_ruta->entidad = $nombre_Entidad->nombre;
        $nueva_ruta->id_municipio = $request->ruta['municipio'];
        $nueva_ruta->municipio = $nombre_Municipio->nombre;
        $nueva_ruta->numero_ruta = $request->ruta['numero_ruta'];
        $nueva_ruta->orden_ruta = count($request->ruta['puntos']);
        $nueva_ruta->encuestas_realizadas = 0;
        $nueva_ruta->estatus = 'Sin iniciar';
        $nueva_ruta->save();
        //guadamos id_ruta en Oportunidades Inegi
        $rutas = $request->ruta['puntos'];
        $contador_orden_ruta = 1;
        foreach ($rutas as $punto) {
            $orden_ruta = str_pad($contador_orden_ruta,"2","0", STR_PAD_LEFT);
            $nombre_ruta_id= $request->ruta['nombre'].'_'.$orden_ruta;
            //dd($nombre_ruta_id);
            $actualizar_oportunidad = OpportunitiesINEGI::find($punto['id']);
            $actualizar_oportunidad->ruta_id = $nombre_ruta_id;
            $actualizar_oportunidad->numero_ruta = $request->ruta['numero_ruta'];
            $actualizar_oportunidad->orden_ruta = $orden_ruta;
            $actualizar_oportunidad->id_ruta =$nueva_ruta->id;
            $actualizar_oportunidad->save();
            $contador_orden_ruta++;
        }
        return 'Ruta Guardada';
    }

    public function getReporteEstadosMunicipios(Request $request){
        $idEntidad = $request->id;
        $municipios = Municipio::where('clave_entidad',$idEntidad)->get();
        $municipios_count = count($municipios);
        $conteo_estatal = 0;
        for ($i=0; $i < $municipios_count; $i++) { 
            $object = $municipios[$i];
            //return $object->clave_entidad;
            $conteoOportunidades = OpportunitiesINEGI::where('clave_entidad',$object->clave_entidad)->where('clave_municipio',$object->clave_municipio)->count();
            $object->conteoOportunidades = $conteoOportunidades;
            $conteo_estatal = $conteo_estatal + $conteoOportunidades;
            $municipios[$i] = $object;
        }
        //dd($oportunidades);
        $data = ['municipios' => $municipios,'conteo_estatal'=>$conteo_estatal];
        return response()->json(
            $data
              );
    }

    public function getOpportunitiesInegiEstado(Request $request){
        $oportunidades = OpportunitiesINEGI::where('clave_entidad',$request->idEntidad)->get();
        //dd($oportunidades);
        return response()->json(
            $oportunidades
              );
    }
}
