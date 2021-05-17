<?php

class Mesa
{
    public $Id;
    public $EstadoMesaId;
    public $Estado;
    public $Codigo;

    public function __construct()
    {
    }
    private function GenerarCodigoMesa($nroMesa)
    {
        $codigo = "M";
        $caracteresFaltantes = 3;
        for ($i = 0; $i < $caracteresFaltantes; $i++) {
            $codigo .= "0";
        }
        return $codigo . $nroMesa;
    }
    public function MapeoUsuario($nroMesa)
    {
        $this->Codigo = $this->GenerarCodigoMesa($nroMesa);
    }
    public function GuardarMesa()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("INSERT 
            INTO Mesa(Codigo) 
            VALUES (:codigo);");
            $consulta->bindValue(':codigo', $this->Codigo, PDO::PARAM_STR);
            return $consulta->execute();
        } catch (Exception $th) {
            throw new Exception("No agrego correctamente " . $th->getMessage(), 1, $th);
        }
    }
    public static function FindById($id)
    {
        try {
            if (!isset($id)) {
                throw new Exception("El id no puede ser vacio");
            }
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("SELECT                 
            Mesa.Id AS Id,
            EstadoMesaId AS EstadoMesaId, 
            EstadoMesa.Detalle AS Estado, 
            Codigo AS Codigo            
            FROM Mesa 
            INNER JOIN EstadoMesa ON Mesa.EstadoMesaId = EstadoMesa.Id 
            WHERE Mesa.Id = :id;");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $MesaEncontrada = $consulta->fetchObject('Mesa');
            if (isset($MesaEncontrada) && $MesaEncontrada) {
                return $MesaEncontrada;
            } else {
                throw new Exception("No se encontró la mesa");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    public static function GetAll()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayMesas
                = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
            Mesa.Id AS Id,
            EstadoMesaId AS EstadoMesaId, 
            EstadoMesa.Detalle AS Estado, 
            Codigo AS Codigo            
            FROM Mesa 
            INNER JOIN EstadoMesa ON Mesa.EstadoMesaId = EstadoMesa.Id");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_CLASS, "Mesa");
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $Mesa) {
                array_push(
                    $arrayMesas,
                    $Mesa
                );
            }
            return $arrayMesas;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
}
