<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\OpportunitiesINEGI;

use App\Entidad;

use App\Municipio;

class ProcesoInegiController extends Controller
{
    //
    public function startInegiApi(Request $request)
    {
        //Funcion para restablecer los valores en bd por municipios
        /*$opportunities = OpportunitiesINEGI::where("clave_entidad",$request->idEntidad)->where("clave_municipio",$request->idMunicipio)->get();
        foreach ($opportunities as $punto2){                
            //return $punto2;
            $cambiar = OpportunitiesINEGI::find($punto2['id']);
            $cambiar->ruta_id = NULL;
            $cambiar->numero_ruta = NULL;
            $cambiar->orden_ruta = NULL;
            $cambiar->bandera_prospecto = NULL;
            $cambiar->bandera_encuesta = NULL;
            $cambiar->bandera_cancelada = NULL;
            $cambiar->motivo_cancelacion = NULL;
            $cambiar->latitud_cancelado = NULL;
            $cambiar->longitud_cancelado = NULL;
            $cambiar->id_ruta = NULL;
            $cambiar->id_promotor = NULL;
            $cambiar->save();
            //return $cambiar;
        }
        return "OK";*/
        $listaNueva = $request->listaNueva;
        $listaNueva2 = $request->listaNueva2;
        $opportunities = OpportunitiesINEGI::where("clave_entidad",$request->idEntidad)->where("clave_municipio",$request->idMunicipio)->get();
        $lista_final = [];
        $contador_lista_final = 0;
        $bandera_nuevos = 0;
        
        foreach ($listaNueva as $punto1) {
            $bandera_existente = 0;
            foreach ($opportunities as $punto2){
                
                if($punto1['Latitud'] == $punto2['latitud'])
                {
                    if($punto1['Longitud'] == $punto2['longitud'])
                    {
                        $bandera_existente = 1;   
                    } 
                }
            }
            if($bandera_existente == 1){
                $punto1['Estatus'] = 'Registrado';
            }else{
                $punto1['Estatus'] = 'Nuevo';
                $bandera_nuevos = 1;
            }
            $lista_final[$contador_lista_final] = $punto1;
            $contador_lista_final++;
        }

        foreach ($listaNueva2 as $punto1) {
            $bandera_existente = 0;

            foreach ($opportunities as $punto2){
                if($punto1['Latitud'] == $punto2['latitud'])
                {
                    if($punto1['Longitud'] == $punto2['longitud'])
                    {
                        $bandera_existente = 1;   
                    } 
                }
            }
            if($bandera_existente == 1){
                $punto1['Estatus'] = 'Registrado';
            }else{
                $punto1['Estatus'] = 'Nuevo';
                $bandera_nuevos = 1;
            }
            $lista_final[$contador_lista_final] = $punto1;
            $contador_lista_final++;
        }
        
        $response = ['data' => $lista_final, 'bandera' => $bandera_nuevos];
        return response()->json(
            $response
        );
    }

    public function guardarNuevoInegiApi(Request $request)
    {
        $datos = $request->nuevo;
        //return $datos['Nombre'];
        //obtener datos entidad y municipio
        $entidad = Entidad::find($request->idEntidad);
        $municipio = Municipio::where('clave_entidad',$request->idEntidad)->where('clave_municipio',$request->idMunicipio)->get()->first();
        //guardar nueva oportunidad
        $new_oportunidad = new OpportunitiesINEGI();
        
        $new_oportunidad->nombre = $datos['Nombre'];
        $new_oportunidad->razon_social = $datos['Razon_social'];
        $new_oportunidad->estrato = $datos['Estrato'];
        $new_oportunidad->direccion = $datos['Tipo_vialidad'].' '.$datos['Calle'];
        $new_oportunidad->codigo_postal = $datos['CP'];
        $new_oportunidad->clave_entidad = $request->idEntidad;
        $new_oportunidad->entidad = $entidad->nombre;
        $new_oportunidad->clave_municipio = $request->idMunicipio;
        $new_oportunidad->municipio = $municipio->nombre;
        //$new_oportunidad->clave_localidad = ;
        //$new_oportunidad->localidad = ;
        $new_oportunidad->telefono = $datos['Telefono'];
        $new_oportunidad->correo_electronico = $datos['Correo_e'];
        $new_oportunidad->sitio_internet = $datos['Sitio_internet'];
        $new_oportunidad->latitud = $datos['Latitud'];
        $new_oportunidad->longitud = $datos['Longitud'];
        $new_oportunidad->save();
        return $new_oportunidad;
    }

    public function guardarNuevoInegiApiLista(Request $request)
    {
        //dd($request);
        $lista = $request->nuevo;
         //obtener datos entidad y municipio
         $entidad = Entidad::find($request->idEntidad);
         $municipio = Municipio::where('clave_entidad',$request->idEntidad)->where('clave_municipio',$request->idMunicipio)->get()->first();
        foreach($lista as $key =>$datos){
            if($datos['Estatus'] == "Nuevo"){
                //cambiamos estatus
                $datos['Estatus'] = 'Registrado';
                $lista[$key] = $datos;
                //guardar nueva oportunidad
                $new_oportunidad = new OpportunitiesINEGI();
                
                $new_oportunidad->nombre = $datos['Nombre'];
                $new_oportunidad->razon_social = $datos['Razon_social'];
                $new_oportunidad->estrato = $datos['Estrato'];
                $new_oportunidad->direccion = $datos['Tipo_vialidad'].' '.$datos['Calle'];
                $new_oportunidad->codigo_postal = $datos['CP'];
                $new_oportunidad->clave_entidad = $request->idEntidad;
                $new_oportunidad->entidad = $entidad->nombre;
                $new_oportunidad->clave_municipio = $request->idMunicipio;
                $new_oportunidad->municipio = $municipio->nombre;
                //$new_oportunidad->clave_localidad = ;
                //$new_oportunidad->localidad = ;
                $new_oportunidad->telefono = $datos['Telefono'];
                $new_oportunidad->correo_electronico = $datos['Correo_e'];
                $new_oportunidad->sitio_internet = $datos['Sitio_internet'];
                $new_oportunidad->latitud = $datos['Latitud'];
                $new_oportunidad->longitud = $datos['Longitud'];
                $new_oportunidad->save();
            }//end if
        }
        return $lista;
    }
}
