<?php

class Producto
{
    public $Id;
    public $Codigo;
    public $TipoProductoId;
    public $TipoProducto;
    public $Nombre;
    public $Stock;
    public $Precio;
    public $FechaCreacion;
    public $FechaUltimaModificacion;
    public $Activo;

    public function __construct()
    {
    }
    public function MapeoProducto($codigo, $tipoProductoId, $nombre, $stock, $precio)
    {
        $this->Codigo = $codigo;
        $this->TipoProductoId = $tipoProductoId;
        $this->Nombre = $nombre;
        $this->Stock = $stock;
        $this->Precio = $precio;
    }
    public function GuardarProducto()
    {
        try {
            $fechaCreacion = date("y-m-d");
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("INSERT 
            INTO Producto(Codigo,TipoProductoId,Nombre, Stock, Precio, FechaCreacion) 
            VALUES (:codigo,:tipoProductoId,:nombre,:stock,:precio,:fechaCreacion);");
            $consulta->bindValue(':codigo', $this->Codigo, PDO::PARAM_STR);
            $consulta->bindValue(':tipoProductoId', $this->TipoProductoId, PDO::PARAM_INT);
            $consulta->bindValue(':nombre', $this->Nombre, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->Stock, PDO::PARAM_INT);
            $consulta->bindValue(':precio', $this->Precio, PDO::PARAM_INT);
            $consulta->bindValue(':fechaCreacion', $fechaCreacion, PDO::PARAM_STR);
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
            Producto.Id AS Id,
            Codigo AS Codigo,
            TipoProductoId AS TipoProductoId, 
            TipoProducto.Detalle AS TipoProducto, 
            Nombre AS Nombre,            
            Stock AS Stock,            
            Precio AS Precio,            
            FechaCreacion AS FechaCreacion,            
            FechaUltimaModificacion AS FechaUltimaModificacion,            
            Activo AS Activo            
            FROM Producto 
            INNER JOIN TipoProducto ON Producto.TipoProductoId = TipoProducto.Id 
            WHERE Producto.Id = :id;");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $ProductoEncontrado = $consulta->fetchObject('Producto');
            if (isset($ProductoEncontrado) && $ProductoEncontrado) {
                return $ProductoEncontrado;
            } else {
                throw new Exception("No se encontró el producto");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    public static function GetAll()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayProductos
                = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
            Producto.Id AS Id,
            Codigo AS Codigo,
            TipoProductoId AS TipoProductoId, 
            TipoProducto.Detalle AS TipoProducto, 
            Nombre AS Nombre,            
            Stock AS Stock,            
            Precio AS Precio,            
            FechaCreacion AS FechaCreacion,            
            FechaUltimaModificacion AS FechaUltimaModificacion,            
            Activo AS Activo            
            FROM Producto 
            INNER JOIN TipoProducto ON Producto.TipoProductoId = TipoProducto.Id ");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_CLASS, "Producto");
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $Producto) {
                array_push(
                    $arrayProductos,
                    $Producto
                );
            }
            return $arrayProductos;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
}
