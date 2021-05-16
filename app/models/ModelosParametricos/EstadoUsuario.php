<?php
class EstadoUsuario
{
    public $Id;
    public $Detalle;

    public function __construct()
    {
    }

    public static function FindById($id)
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->RetornarConsulta("SELECT 
                Id AS Id,
                Detalle AS Detalle,
                FROM EstadoUsuario  WHERE Id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $EstadoUsuarioEncontrado = $consulta->fetchObject('EstadoUsuario');
            if (isset($EstadoUsuarioEncontrado)) {
                return $EstadoUsuarioEncontrado;
            } else {
                throw new Exception("No se encontró el estado");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex);
        }
    }
}
