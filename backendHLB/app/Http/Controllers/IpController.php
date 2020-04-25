<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ip;
use App\Models\Equipo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class IpController extends Controller
{
    public function listar_ips()
    {
        return Ip::all();
    }

    public function buscar_ip_por_codigo($id_ip)
    {
        return Ip::select('*')
        ->where('id_ip',$id_ip)
        ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function crear_equipo_ip(Request $request)
    {
        // var_dump($request->get('registro_ip_obj'));
        // var_dump($request->get('registro_equipo_obj'));

        var_dump($request->get('registro_ip_obj')['estado']);

        DB::beginTransaction();
        try {
            // Primero creo la ip, y luego el equipo
            $ip = new Ip();
            $dt = new \DateTime();
            $dt->format('Y-m-d');

            $ip->estado = $request->get('registro_ip_obj')['estado'];
            $ip->fecha_asignacion = $dt;
            $ip->direccion_ip = $request->get('registro_ip_obj')['direccion_ip'];
            $ip->hostname = $request->get('registro_ip_obj')['hostname'];
            $ip->subred = $request->get('registro_ip_obj')['subred'];
            $ip->fortigate = $request->get('registro_ip_obj')['fortigate'];
            $ip->observacion = $request->get('registro_ip_obj')['observacion'];
            $ip->maquinas_adicionales = $request->get('registro_ip_obj')['maquinas_adicionales'];

            // Estos dos campos se guardan directamente aqui, en el backend debido a que maneja la sesion.
            $ip->nombre_usuario = 'Samuel Braganza';
            $ip->encargado_registro = 'admin';

            $ip->save();

            $equipo= new Equipo();
            // Aprovecho el id_ip saliente del insert anterior para referenciarlo en la tabla equipos
            $equipo->ip = $ip->id_ip;

            $equipo->fecha_registro = $request->get('registro_equipo_obj')['fecha_registro'];
            $equipo->estado_operativo  = $request->get('registro_equipo_obj')['estado_operativo'];
            $equipo->codigo = $request->get('registro_equipo_obj')['codigo'];
            $equipo->tipo_equipo = $request->get('registro_equipo_obj')['tipo_equipo'];
            $equipo->modelo = $request->get('registro_equipo_obj')['modelo'];
            $equipo->descripcion = $request->get('registro_equipo_obj')['descripcion'];
            $equipo->numero_serie = $request->get('registro_equipo_obj')['numero_serie'];
            $equipo->encargado_registro = $request->get('registro_equipo_obj')['encargado_registro'];
            $equipo->componente_principal = $request->get('registro_equipo_obj')['componente_principal'];

            $equipo->save();


            DB::commit();
            return response()->json(['status'=>'success'], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => $e,
                ], 400);
        }
    }

    public function filtrar_ip($direccion_ip)
    {
        // $direccion_ip = $request->get('direccion_ip');
        return Ip::select('*')
            ->where('direccion_ip', 'like', "%" . $direccion_ip . "%")
            ->get();
    }

    public function crear_ip(Request $request)
    {
        $ip= new Ip();
        try{
        $ip->direccion_ip=$request->get('direccion_ip');
        $ip->hostname=$request->get('hostname');
        $ip->subred=$request->get('subred');
        $ip->estado=$request->get('estado');
        $ip->fortigate=$request->get('fortigate');
        $ip->observacion=$request->get('observacion');
        $ip->maquinas_adicionales=$request->get('maquinas_adicionales');
        $ip->nombre_usuario=$request->get('nombre_usuario');
        $ip->encargado_registro=$request->get('encargado_registro');
        $ip->save();
        }catch(QueryException $e){
            $error_code = $e->errorInfo[1];
            if($error_code == 1062){
                return response()->json(['log'=>'La IP ingresada ya existe'],500);
            }
            return response()->json(['log'=>$e],500);
        }
    }

    public function ips_libres()
    {
      return Ip::select('id_ip', 'direccion_ip')
      ->where('estado','=','L')
      ->get();
    }

    public function ip_asignada($id_ip)
    {
      $ip = Ip::find($id_ip);
      $ip->estado = 'EU';
      $ip->save();
    }

    public function editar_ip(Request $request)
    {
        try{
        $ip= Ip::find($request->get('key')); #key es el id de la ip.
        $ip->direccion_ip=$request->get('direccion_ip');
        $ip->hostname=$request->get('hostname');
        $ip->subred=$request->get('subred');
        $ip->estado=$request->get('estado');
        $ip->fortigate=$request->get('fortigate');
        $ip->observacion=$request->get('observacion');
        $ip->maquinas_adicionales=$request->get('maquinas_adicionales');
        $ip->nombre_usuario=$request->get('nombre_usuario');
        $ip->encargado_registro=$request->get('encargado_registro');
        $ip->save();
        }catch(QueryException $e){
            $error_code = $e->errorInfo[1];
            if($error_code == 1062){
                return response()->json(['log'=>'La IP ingresada ya existe'],500);
            }
            return response()->json(['log'=>$e],500);
        }
    }

    public function es_ip_enuso($ip){
        $reg_ip = Ip::find($ip);
        if($reg_ip->estado === 'EU'){
            return $reg_ip->direccion_ip;
        }
    }
    
    public function Ip_ID_Only($id){
        $list_ip = Ip::select('id_ip', 'direccion_ip')->where("estado","=","L");
        if($id!=null||$id!=""||$id!=-1){
            $list_ip =  $list_ip->orWhere("id_ip","=",$id);
        }

        return response()->json( $list_ip->get());

    }

    /* Servicio para obtener datos de la ip a partir de su ID */
    public function ip_id($id_ip){
        return Ip::SelectRaw('ips.*, bspi_punto, departamentos.nombre as departamento,
         empleados.nombre, empleados.apellido, equipos.codigo, equipos.tipo_equipo')
        ->leftjoin('equipos','id_ip','=','equipos.ip')
        ->leftjoin('empleados','cedula','=','asignado')
        ->leftjoin('departamentos','departamentos.id_departamento','=','empleados.id_departamento')
        ->leftjoin('organizaciones','organizaciones.id_organizacion','=','departamentos.id_organizacion')
        ->where('ips.id_ip',$id_ip)
        ->get();
    }

 
    public function eliminar_ip($id_ip){
        try{
             # Elimino la Ip
            $ip= Ip::find($id_ip);
            $ip->delete();
            return response()->json(['log'=>'Registro eliminado satisfactoriamente'],200); 
        }catch(Exception $e){
            return response()->json(['log'=>$e],400);
        }
}


}