<?php

class UsuarioLog
{
    public $Id;
    public $UsuarioId;
    #region Datos Usuario
    public $UsuarioNombre;
    public $UsuarioApellido;
    public $UsuarioMail;
    #endregion 
    public $FechaDeIngreso;
    public $HoraDeIngreso;

    public function __construct()
    {
    }

    public static function GetAllSector()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayUsuariosLogs = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
                UsuarioLog.Id AS Id,
                UsuarioId AS  UsuarioId, 
                Usuario.Nombre AS UsuarioNombre,
                Usuario.Apellido AS UsuarioApellido,
                Usuario.Mail AS UsuarioMail,           
                FechaDeIngreso AS FechaDeIngreso,
                HoraDeIngreso AS HoraDeIngreso
                 FROM UsuarioLog 
                 INNER JOIN Usuario ON Usuario.Id = UsuarioLog.UsuarioId");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_CLASS, "UsuarioLog");
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $usuarioLog) {
                array_push($arrayUsuariosLogs, $usuarioLog);
            }
            return $arrayUsuariosLogs;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
    public static function GetAll()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayUsuariosLogs = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
                UsuarioLog.Id AS Id,
                UsuarioId AS  UsuarioId, 
                Usuario.Nombre AS UsuarioNombre,
                Usuario.Apellido AS UsuarioApellido,
                Usuario.Mail AS UsuarioMail,           
                FechaDeIngreso AS FechaDeIngreso,
                HoraDeIngreso AS HoraDeIngreso
                 FROM UsuarioLog 
                 INNER JOIN Usuario ON Usuario.Id = UsuarioLog.UsuarioId");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_CLASS, "UsuarioLog");
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $usuarioLog) {
                array_push($arrayUsuariosLogs, $usuarioLog);
            }
            return $arrayUsuariosLogs;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
    public function GuardarUsuarioLog()
    {
        try {
            $fechaDeIngreso = date("y-m-d");
            $horaDeIngreso = date("G:i:s");
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("INSERT 
            INTO UsuarioLog(UsuarioId,FechaDeIngreso,HoraDeIngreso) 
            VALUES (:usuarioId,:fechaDeIngreso,:horaDeIngreso);");
            $consulta->bindValue(':usuarioId', $this->UsuarioId, PDO::PARAM_INT);
            $consulta->bindValue(':fechaDeIngreso', $fechaDeIngreso, PDO::PARAM_STR);
            $consulta->bindValue(':horaDeIngreso', $horaDeIngreso, PDO::PARAM_STR);
            return $consulta->execute();
        } catch (Exception $th) {
            throw new Exception("No agrego correctamente " . $th->getMessage(), 1, $th);
        }
    }
}
