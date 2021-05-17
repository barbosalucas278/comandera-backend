<?php
require_once 'Producto.php';
class Pedido
{
    public $Id;
    public $EstadoPedidoId;
    public $EstadoPedido;
    public $CodigoMesa;
    public $CodigoPedido;
    public $ProductoId;
    public $Producto;
    public $Cantidad;
    public $Importe;
    public $FechaCreacion;
    public $HorarioCreacion;
    public $HorarioInicio;
    public $TiempoEstipulado;
    public $HorarioDeEntrega;
    public $NombreCliente;
    public $UrlFoto;

    public function __construct()
    {
    }

    public function MapearPedido($codigoMesa, $productoId, $cantidad, $nombreCliente, $urlFoto)
    {
        $this->CodigoMesa = $codigoMesa;
        $this->ProductoId = $productoId;
        $this->Cantidad = $cantidad;
        $this->NombreCliente = $nombreCliente;
        $this->UrlFoto = $urlFoto;
    }
    private function CalcularImporte()
    {
        try {
            $producto = Producto::FindById($this->ProductoId);
            return $producto->Precio * $this->Cantidad;
        } catch (Exception $ex) {
            throw new Exception("No se puede calcular el importe " . $ex->getMessage(), 0, $ex);
        }
    }
    public function GuardarPedido()
    {
        try {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaCreacion = date("y-m-d");
            $HorarioCreacion = date("G:i:s");
            $CodigoPedido = substr(md5(time()), 0, 5);
            $Importe = $this->CalcularImporte();
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("INSERT 
            INTO Pedido(CodigoMesa,CodigoPedido,ProductoId, Cantidad, Importe, NombreCliente, UrlFoto, FechaCreacion, HorarioCreacion) 
            VALUES (:codigoMesa,:codigoPedido,:productoId,:cantidad,:importe,:nombreCliente,:urlFoto,:fechaCreacion,:horarioCreacion);");
            $consulta->bindValue(':codigoMesa', $this->CodigoMesa, PDO::PARAM_STR);
            $consulta->bindValue(':codigoPedido', $CodigoPedido, PDO::PARAM_STR);
            $consulta->bindValue(':productoId', $this->ProductoId, PDO::PARAM_INT);
            $consulta->bindValue(':cantidad', $this->Cantidad, PDO::PARAM_INT);
            $consulta->bindValue(':importe', $Importe, PDO::PARAM_INT);
            $consulta->bindValue(':nombreCliente', $this->NombreCliente, PDO::PARAM_STR);
            $consulta->bindValue(':urlFoto', $this->UrlFoto, PDO::PARAM_STR);
            $consulta->bindValue(':fechaCreacion', $FechaCreacion, PDO::PARAM_STR);
            $consulta->bindValue(':horarioCreacion', $HorarioCreacion, PDO::PARAM_STR);
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
            Pedido.Id AS Id,
            EstadoPedidoId AS EstadoPedidoId, 
            EstadoPedido.Detalle AS EstadoPedido, 
            CodigoMesa AS CodigoMesa,            
            CodigoPedido AS CodigoPedido,            
            ProductoId AS ProductoId,            
            Producto.Nombre AS Producto,            
            Cantidad AS Cantidad,            
            Importe AS Importe,            
            Pedido.FechaCreacion AS FechaCreacion,            
            HorarioCreacion AS HorarioCreacion,            
            HorarioInicio AS HorarioInicio,            
            TiempoEstipulado AS TiempoEstipulado,            
            HorarioDeEntrega AS HorarioDeEntrega,            
            NombreCliente AS NombreCliente,            
            UrlFoto AS UrlFoto            
            FROM Pedido 
            INNER JOIN EstadoPedido ON Pedido.EstadoPedidoId = EstadoPedido.Id 
            INNER JOIN Producto ON Pedido.ProductoId = Producto.Id 
            WHERE Pedido.Id = :id;");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $PedidoEncontrada = $consulta->fetchObject('Pedido');
            if (isset($PedidoEncontrada) && $PedidoEncontrada) {
                return $PedidoEncontrada;
            } else {
                throw new Exception("No se encontró elpedido");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    public static function GetAll()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayPedidos
                = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
            Pedido.Id AS Id,
            EstadoPedidoId AS EstadoPedidoId, 
            EstadoPedido.Detalle AS EstadoPedido, 
            CodigoMesa AS CodigoMesa,            
            CodigoPedido AS CodigoPedido,            
            ProductoId AS ProductoId,            
            Producto.Nombre AS Producto,            
            Cantidad AS Cantidad,            
            Importe AS Importe,            
            Pedido.FechaCreacion AS FechaCreacion,            
            HorarioCreacion AS HorarioCreacion,            
            HorarioInicio AS HorarioInicio,            
            TiempoEstipulado AS TiempoEstipulado,            
            HorarioDeEntrega AS HorarioDeEntrega,            
            NombreCliente AS NombreCliente,            
            UrlFoto AS UrlFoto            
            FROM Pedido 
            INNER JOIN EstadoPedido ON Pedido.EstadoPedidoId = EstadoPedido.Id 
            INNER JOIN Producto ON Pedido.ProductoId = Producto.Id");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $Pedido) {
                array_push(
                    $arrayPedidos,
                    $Pedido
                );
            }
            return $arrayPedidos;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
}
