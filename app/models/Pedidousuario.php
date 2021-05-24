<?php

class PedidoUsuario
{
    public $Id;
    public $PedidoId;
    public $UsuarioId;
    public $Sector;
    public $CantidadOperaciones;

    public function __construct()
    {
    }

    public static function GetAllByEmpleado()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayPedidosUsuario = array();
            $consulta = $acceso->prepararConsulta("SELECT                            
            Usuario.Mail AS Usuario,                           
            COUNT(Usuario.Id) AS CantidadOperaciones                       
            FROM PedidoUsuario
            INNER JOIN Usuario ON PedidoUsuario.UsuarioId = Usuario.Id             
            GROUP BY Usuario.Mail");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_OBJ);
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $PedidoUsuario) {
                array_push(
                    $arrayPedidosUsuario,
                    $PedidoUsuario
                );
            }
            return $arrayPedidosUsuario;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
    public static function GetAllSectorPorEmpleado()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayPedidosUsuario = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
            Sector.Detalle AS Sector,
            Usuario.Mail AS Usuario,            
            COUNT(Usuario.SectorId) AS CantidadOperaciones                       
            FROM PedidoUsuario
            INNER JOIN Usuario ON PedidoUsuario.UsuarioId = Usuario.Id 
            INNER JOIN Sector ON Usuario.SectorId = Sector.Id
            GROUP BY Sector.Detalle");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_OBJ);
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $PedidoUsuario) {
                array_push(
                    $arrayPedidosUsuario,
                    $PedidoUsuario
                );
            }
            return $arrayPedidosUsuario;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
    public static function GetAllSector()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayPedidosUsuario = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
            Sector.Detalle AS Sector,            
            COUNT(Usuario.SectorId) AS CantidadOperaciones                       
            FROM PedidoUsuario
            INNER JOIN Usuario ON PedidoUsuario.UsuarioId = Usuario.Id 
            INNER JOIN Sector ON Usuario.SectorId = Sector.Id
            GROUP BY Sector.Detalle");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_OBJ);
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $PedidoUsuario) {
                array_push(
                    $arrayPedidosUsuario,
                    $PedidoUsuario
                );
            }
            return $arrayPedidosUsuario;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
    public static function GuardarPedidoUsuario($pedidoUsuario)
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("INSERT 
            INTO PedidoUsuario(PedidoId,UsuarioId) 
            VALUES (:pedidoId,:usuarioId);");
            $consulta->bindValue(':pedidoId', $pedidoUsuario->PedidoId, PDO::PARAM_INT);
            $consulta->bindValue(':usuarioId', $pedidoUsuario->UsuarioId, PDO::PARAM_INT);
            return $consulta->execute();
        } catch (Exception $th) {
            throw new Exception("No agrego correctamente " . $th->getMessage(), 1, $th);
        }
    }
}
