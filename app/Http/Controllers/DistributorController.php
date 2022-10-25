<?php

namespace App\Http\Controllers;

use App\Distributor;
use Illuminate\Http\Request;
use SoapClient;

class DistributorController extends Controller
{
    //
    public function getDistributors(Request $request)
    {
        $distributors = Distributor::where("id_oportunidad", $request->id)->where('tipo', 'IUSA')->get();
        return response()->json(
            $distributors
        );
    }

    public function getDistributorsAll(Request $request)
    {
        $distributors = Distributor::where("id_oportunidad", $request->id)->get();
        return response()->json(
            $distributors
        );
    }

    public function getDealers(Request $request)
    {
        $usuario = 'dsgonzalez';
        $puesto = 'E';
        function objarray($obj)
        {
            $out = array();
            foreach ($obj as $key => $val) {
                switch (true) {
                    case is_object($val):
                        $out[$key] = objarray($val);
                        break;
                    case is_array($val):
                        $out[$key] = objarray($val);
                        break;
                    default:
                        $out[$key] = $val;
                }
            }
            return $out;
        }
        /******** WEBSERVICE VENDEDOR CLIENTE ********/
        try {
            $servicio = "http://172.16.171.10/webservices/PGC360_Pro_Vendedor_Cliente/Vendedor_Cliente.asmx?WSDL"; //url servicio
            $parametros = array(); //parametros
            $parametros['P_USERNAME'] = "$usuario";
            $parametros['P_PUESTO'] = "$puesto";
            $client = new SoapClient($servicio, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
            $result = $client->Vb_Vendedor_Cliente($parametros); //llamada al método
            $result = objarray($result);
            $noticias = $result['Vb_Vendedor_ClienteResult']['MyResultData'];
            $collection = collect($noticias);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        $n_clientes = count($collection);
        $client_array = [];
        for ($i = 0; $i < $n_clientes - 1; $i++) {
            $client_array = $collection[$i];
            $client_array['DEALER'] = $client_array['KUNNR'] . " - " . $client_array['NAME1'];
            $collection[$i] = $client_array;
        }
        return response()->json(
            $collection
        );
    }

    public function getSellers(Request $request)
    {
        $usuario = 'TODOS';
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
        /******** WEBSERVICE GERENTE VENDEDOR ********/
        try {
            $servicio = "http://172.16.171.10/webservices/PGC360_Pro_GteVen/GteVen.asmx?WSDL"; //url servicio
            $parametros = array(); //parametros
            $parametros['P_GERENTE'] = "$usuario";
            $client = new SoapClient($servicio, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true));
            $result = $client->Vb_Basecliente($parametros); //llamada al método
            $result = obj2array($result);
            $noticias = $result['Vb_BaseclienteResult']['MyResultData'];
            $seller_list = collect($noticias);
            return response()->json(
                $seller_list
            );
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }

    public function deleteDistributor(Request $request)
    {
        $distributor = Distributor::find($request->id);
        $distributor->delete();
        return 'Se borro';
    }

    public function addDistributor(Request $request)
    {
        $distribuidor = $request->distribuidor;
        $distributor = new Distributor();
        $distributor->nombre = $distribuidor['nombre'];
        $distributor->idIusa = $distribuidor['idIusa'];
        $distributor->tipo = $distribuidor['tipo'];
        $distributor->direccion = $distribuidor['direccion'];
        $distributor->telefono = $distribuidor['telefono'];
        $distributor->correo = $distribuidor['correo'];
        $distributor->cp = $distribuidor['cp'];
        $distributor->calificacion = $distribuidor['calificacion'];
        $distributor->id_oportunidad = $request->id;
        $distributor->save();

        return response()->json(
            $distributor
        );
    }
}
