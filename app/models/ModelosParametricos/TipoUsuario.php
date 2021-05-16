<?php
class TipoUsuario
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
                FROM TipoUsuario  WHERE Id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $TipoUsuarioEncontrado = $consulta->fetchObject('TipoUsuario');
            if (isset($TipoUsuarioEncontrado)) {
                return $TipoUsuarioEncontrado;
            } else {
                throw new Exception("No se encontró el tipo de usuario");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex);
        }
    }
}
