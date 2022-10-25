<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
class PromotorController extends Controller
{
    public function getPromotores(Request $request){
        $promotores = User::where('type','1')->get();
        return response()->json(
            $promotores
              );
    }

    public function getPromotoresAll(Request $request){
        $promotores = User::where('id','!=',$request->id)->get();
        return response()->json(
            $promotores
              );
    }

    protected function create(Request $request)
    {
        if($request->employee_number != '' || $request->employee_number != null){
            $request->employee_type = 'V';
        }
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'sucursal' => $request->sucursal,
            'telefono' => $request->telefono,
            'type' => $request->type,
            'employee_number' => $request->employee_number,
            'employee_type' => $request->employee_type,
            'password' => Hash::make($request->password),
        ]);
    }

    protected function updateUser(Request $request)
    {
        if($request->employee_number != '' || $request->employee_number != null){
            $request->employee_type = 'V';
        }
        $user = User::find($request->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->type = $request->type;
        $user->employee_number = $request->employee_number;
        $user->employee_type = $request->employee_type;
        $user->sucursal = $request->sucursal;
        $user->telefono = $request->telefono;
        $user->save();
        return $user;
    }

    protected function updatePassword(Request $request)
    {
        //dd($request);
        $user = User::find($request->id);
        $user->password = Hash::make($request->password);
        $user->save();
        return $user;
    }

    protected function altaPromotor(Request $request)
    {
        //dd($request);
        $user = User::find($request->id);
        $user->estatus = 1;
        $user->save();
        return $user;
    }

    protected function bajaPromotor(Request $request)
    {
        //dd($request);
        $user = User::find($request->id);
        $user->estatus = 2;
        $user->save();
        return $user;
    }

    protected function deletePromotor(Request $request)
    {
        //dd($request);
        $user = User::find($request->id);
        $user->delete();
        return $user;
    }

    protected function getUserInfo(Request $request)
    {
        //dd($request);
        $user = User::find($request->id);
        return $user;
    }

    protected function updateImageUser(Request $request)
    {
        $user = User::find($request->idUser);
        $this->validate($request, [
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
          ]);
        if($request->file('foto')){
            $file = $request->file('foto');
            $nombre = 'img/Usuarios/'.$request->idUser."/".$file->getClientOriginalName();
            $path = public_path().'/img/Usuarios/'.$request->idUser;
            $file->move($path, $nombre);            
            $user->image_perfil = $nombre;
            $user->save();
            $user->resultado = "ok";
            } 
       
        return $user;
    }
}
