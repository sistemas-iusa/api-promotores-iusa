<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Daily;

class DailyController extends Controller
{

    public function getEvents(Request $request){       
        $id_promotor = $request->id;
        $citas_asociados = Daily::all()->where('id_promotor',$id_promotor)->where('cancelado','!=',1);
        $citas_asociados_query = $citas_asociados->values();
        $citas_asociados_query->all();
        return response()->json(
            $citas_asociados_query
            );
    }
    public function getEventsDay(Request $request){
        //dd($request);
        $id_promotor = $request->id_promotor;
        $fecha = $request->fecha;
        $citas_asociados = Daily::all()->where('id_promotor',$id_promotor)->where('fecha',$fecha)->where('cancelado','!=',1);
        $citas_asociados_query = $citas_asociados->values();
        $citas_asociados_query->all();
        return response()->json(
            $citas_asociados_query
            );
    }

    public function date_update()
    {
		$fechaActual = date('d-m-Y');
		return response()->json(
	        $fechaActual
	        );
	}

    public function crearNuevaCita(Request $request)
    {
		$cita_en_bd = new Daily();
        $cita_en_bd->start = $request->start;
        $cita_en_bd->end = $request->end;
        $cita_en_bd->name = $request->name;
        $cita_en_bd->content = $request->content;
        $cita_en_bd->color = $request->color;
        $cita_en_bd->class = $request->class;
        $cita_en_bd->cliente = $request->cliente;
        $cita_en_bd->nombre_cliente = $request->nombre_cliente;
        $cita_en_bd->lat_cliente = $request->lat_cliente;
        $cita_en_bd->lon_cliente = $request->lon_cliente;        
        $cita_en_bd->forma_contacto = $request->cliente_forma;
        $cita_en_bd->objetivo_contacto = $request->cliente_objetivo;
        $cita_en_bd->tipo_cita = $request->tipo_cita;
        $cita_en_bd->descripcion_detalle = $request->descripcion_detalle;
        $cita_en_bd->fecha = $request->fecha;
        $cita_en_bd->hora_inicio = $request->hora_inicio;
        $cita_en_bd->hora_fin = $request->hora_fin;
        $cita_en_bd->id_promotor = $request->id_promotor;        
        $cita_en_bd->save();
        $id_nuevo = $cita_en_bd->id;
        return response()->json(
            $cita_en_bd
            );
	}

    public function BorrarCita(Request $request){
        //dd($request);
        $id_cita = $request->cita_id;
        $citas = Daily::find($id_cita);
        $citas->cancelado = 1;
        $citas->motivo_cancelacion = $request->motivo;
        $citas->save();

        $ok = ['mensaje' => 'ok'];
       return response()->json(
        $ok
        );
    }

    public function agendaActivarCheckIn(Request $request){
        //dd($request);
        $fechahoy = date('d-m-Y');
        $hora = date('G:i:s');
     
        $lat = round($request->lat, 6);
        $lng = round($request->lng, 6); 
        $id_cita = $request->cita_id;

        $citas = Daily::find($id_cita);
        $citas->checkin = 1;
        $citas->fecha_checkin = $fechahoy;
        $citas->hora_checkin = $hora;
        $citas->lat_checkin = $lat;
        $citas->lng_checkin = $lng;
        $citas->save();
        $ok = ['mensaje' => 'ok'];
       return response()->json(
        $ok
        );
    }

    public function agendaActivarCheckOut(Request $request){
        //dd($request);
        $fechahoy = date('d-m-Y');
        $hora = date('G:i:s');
     
        $lat = round($request->lat, 6);
        $lng = round($request->lng, 6); 
        $id_cita = $request->cita_id;
        $decripcion_final = $request->descripcion_final;

        $citas = Daily::find($id_cita);
        $citas->color = 'finalizado';
        $citas->class = '#41bbf3';
        $citas->checkout = 1;
        $citas->fecha_checkout = $fechahoy;
        $citas->hora_checkout = $hora;
        $citas->lat_checkout = $lat;
        $citas->lng_checkout = $lng;
        $citas->finalizo = 1;
        $citas->observacion_final = $decripcion_final;
        $citas->save();

            $ok = ['mensaje' => 'ok'];
       return response()->json(
        $ok
        );
    }

    
}
