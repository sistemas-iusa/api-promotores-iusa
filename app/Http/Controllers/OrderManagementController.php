<?php

namespace App\Http\Controllers;

use App\User;
use App\OpportunitiesINEGI;
use App\Order;
use App\OrderDetail;
use Illuminate\Http\Request;
use Mail;
use SoapClient;

class OrderManagementController extends Controller
{
    //
    public function getOrders(Request $request)
    {
        $orders = Order::where("id_promotor", $request->id)->get();
        return response()->json(
            $orders
        );
    }
    public function getOrder(Request $request)
    {
        $order = OrderDetail::where("orden_compra_id", $request->id)->get();
        return response()->json(
            $order
        );
    }
    public function getDealerEmail(Request $request)
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
        }
        if ($request->tipo == 'DISTRIBUIDOR') {
            try {
                $servicio = "http://172.16.171.10/WebServices/PGC360_Pro_Cliente_Datosgrales/Cliente_Datosgrales.asmx?WSDL";
                $parametros = array();
                $parametros['Username'] = "facastillo";
                $parametros['KKBER'] = "217";
                $parametros['KUNNR1'] = "$request->id";
                $parametros['VKORG'] = 'IUS2';
                $parametros['VTWEG'] = 'PR';
                $client = new SoapClient($servicio, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
                $result = $client->Vb_Cliente_Datosgrales($parametros);
                $result = obj2array($result);
                $noticias = $result['Vb_Cliente_DatosgralesResult']['MyResultData'];
                $collection = collect($noticias)->first();
                return response()->json(
                    $collection['SMTP_ADDR']
                );
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }
        if ($request->tipo == 'VENDEDOR') {
            try {
                $servicio = "http://172.16.171.10/webservices/PGC360_Pro_Datos_Vendedor/Datos_Vendedor.asmx?WSDL";
                $parametros = array();
                $parametros['P_USERN'] = "$request->id";
                $client = new SoapClient($servicio, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
                $result = $client->Vb_Datos_Vendedor($parametros);
                $result = obj2array($result);
                $noticias = $result['Vb_Datos_VendedorResult']['MyResultData'];
                $collection = collect($noticias)->first();
                return response()->json(
                    $collection['EMAIL']
                );
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }
    }
    public function sendOrder(Request $request)
    {
        $orderId = $request->orderId;
        $email = $request->email;
        $oportunidad = Order::find($orderId);
        $oportunidades_detalle = OrderDetail::where('orden_compra_id', $orderId)->get();
        $promotor = User::find($oportunidad->id_promotor);
        $oportunidad->name_promotor = $promotor->name; 
        $oportunidad->email_promotor = $promotor->email; 
        $view = \View::make('order_format', compact('oportunidad', 'oportunidades_detalle'))->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($view);
        $pdf->setPaper('letter', 'portrait');
        try {
            $data["email"] = "$email";
            $data["client_name"] = "DISTRIBUIDOR";
            $data["subject"] = "PEDIDO SUGERIDO";

            Mail::send('mails.account_message', $data, function ($message) use ($data, $pdf) {
                $message->to($data["email"], $data["client_name"])
                    ->subject($data["subject"])
                    ->attachData($pdf->output(), "ORDEN.pdf");
            });
            $oc = Order::find($orderId);
            $oc->estatus = 'ENVIADO';
            $oc->save();
            return "Correo enviado";
        } catch (Exception $e) {
            return "Error en el envio de correo, verifique la dirección e intentelo mas tarde";
        }
    }

    public function sendEvidencia(Request $request)
        {
          //obtener info Oportunidad
          
          $opportunity = OpportunitiesINEGI::where('id', $request->id_oportunidad)->get();
          $opportunity = $opportunity->first();

          if($request->file('foto')){
            $file = $request->file('foto');
            //return $file->getClientOriginalName();
            $nombre = 'img/'.$opportunity->clave_entidad."/".$opportunity->clave_municipio."/".$opportunity->id."/Pedidos"."/".$file->getClientOriginalName();
            $path = public_path().'/img/'.$opportunity->clave_entidad."/".$opportunity->clave_municipio."/".$opportunity->id."/Pedidos";
            $file->move($path, $nombre);

            $oc = Order::find($request->orderId);
            $oc->evidencia =  $nombre;
            $oc->concluciones =  $request->concluciones;
            $oc->estatus = 'TERMINADO';
            $oc->save();

            }            
            return 'guardado exitoso';
        }
}
