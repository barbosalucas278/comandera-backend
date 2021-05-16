<?php
class Sector
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
                FROM Sector  WHERE Id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $SectorEncontrado = $consulta->fetchObject('Sector');
            if (isset($SectorEncontrado)) {
                return $SectorEncontrado;
            } else {
                throw new Exception("No se encontró el sector");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex);
        }
    }
}
