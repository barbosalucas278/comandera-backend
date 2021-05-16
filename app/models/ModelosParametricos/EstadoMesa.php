<?php
class EstadoMesa
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
                FROM EstadoMesa  WHERE Id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $EstadoMesaEncontrado = $consulta->fetchObject('EstadoMesa');
            if (isset($EstadoMesaEncontrado)) {
                return $EstadoMesaEncontrado;
            } else {
                throw new Exception("No se encontró el estado");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex);
        }
    }
}
