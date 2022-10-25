<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\OpportunitiesINEGI;

use App\Gallery;

class GalleryController extends Controller
{

  public function getPicktures(Request $request)
      {
        $gallery = Gallery::where('id_oportunidad', $request->id_oportunidad)->get();
        return response()->json(
          $gallery
        );
      }
    public function saveCamera(Request $request)
        {

          date_default_timezone_set('America/Mexico_City');
          $fechaactual= date("Y-m-d");
          $horaactual= date("H:i:s");
          //obtener info Oportunidad
          
          $opportunity = OpportunitiesINEGI::where('id', $request->id_oportunidad)->get();
          $opportunity = $opportunity->first();
          if($request->file('foto')){
            $file = $request->file('foto');
            //return $file->getClientOriginalName();
            $nombre = 'img/'.$opportunity->clave_entidad."/".$opportunity->clave_municipio."/".$opportunity->id."/".$file->getClientOriginalName();
            $path = public_path().'/img/'.$opportunity->clave_entidad."/".$opportunity->clave_municipio."/".$opportunity->id;
            $file->move($path, $nombre);

            $galeria = new Gallery;
            $galeria->foto =  $nombre;
            $galeria->fecha = $fechaactual;
            $galeria->hora = $horaactual;
            $galeria->latitud = $request->latitud;
            $galeria->longitud = $request->longitud;
            $galeria->id_oportunidad = $request->id_oportunidad;
            $galeria->save();

            }            
            return 'guardado exitoso';
        }

        
}
