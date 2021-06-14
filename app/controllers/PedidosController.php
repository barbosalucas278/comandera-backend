<?php

use App\Models\Pedido;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\Producto;
use App\Models\PedidoUsuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Pedido.php';
require_once './models/Pedidousuario.php';
require_once './interfaces/IApiUsable.php';
class PedidosController implements IApiUsable
{
    public function CambiarEstado(Request $request, Response $response, array $args)
    {
        $datosIngresados = $request->getParsedBody()["body"];
        if (!isset($datosIngresados["estado"]) || !isset($datosIngresados["pedidoId"])) {
            $error = json_encode(array("Error" => "Datos incompletos"));
            $response->getBody()->write($error);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        try {
            $sectorUsuario = $request->getParsedBody()["token"]->SectorId;
            $tipoUsuarioId = $request->getParsedBody()["token"]->TipoUsuarioId;
            $id = $datosIngresados["pedidoId"];
            $nuevoEstado = $datosIngresados["estado"];
            $tiempoEstimado = $datosIngresados["tiempoEstimado"] ?? 1800;
            $pedidoModificado = Pedido::where("Id", "=", $id)->first();
            $tipoDeProducto = Producto::where("Id", "=", $pedidoModificado->producto_id)->first()->TipoProductoId;
            if (!($tipoDeProducto == $sectorUsuario || $tipoUsuarioId == 2)) {
                throw new Exception("El empleado no puede tomar este pedido");
            }

            $pedidoModificado->EstadoPedidoId = $nuevoEstado;
            if ($pedidoModificado->EstadoPedidoId == 2) {
                $this->CalcularHoras($pedidoModificado);
                $this->ActualizarHorarioEstimado($pedidoModificado->CodigoPedido, $tiempoEstimado);
            }
            $pedidoModificado->FechaModificacion = date("Y-m-d");
            if ($pedidoModificado->save()) {
                #region Guardo el registro en tabla de relacion PedidoUsuario
                //TODO:Si cambia a listo para servir agregar marca de uso en pedidousuario de entregado a tiempo
                $UsuarioId = $request->getParsedBody()["token"]->Id;
                $newPedidoUsuario = new PedidoUsuario();
                $newPedidoUsuario->Usuario_Id = $UsuarioId;
                $newPedidoUsuario->Pedido_Id = $pedidoModificado->Id;
                $newPedidoUsuario->FechaCreacion = date("Y-m-d");
                $newPedidoUsuario->save();
                #endregion
                $cantidadDePedidosPendientes = $this->EstadoDelPedidoCompleto($pedidoModificado->CodigoPedido);
                $datos = json_encode(array("Resultado" => "Modificado con exito", "PedidosPendientesDeLaMesa" => $cantidadDePedidosPendientes));
                $response->getBody()->write($datos);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    private function ActualizarHorarioEstimado($codigoPedido, $tiempoEstipuladoNuevo)
    {
        $horarioActual = new DateTime();
        $horarioActual->add(new DateInterval('PT' . $tiempoEstipuladoNuevo . 'S'));
        $horarioMaximoNuevo = $horarioActual->format("G:i:s");
        $pedidoConHorarioMaximo = Pedido::all()
            ->where("CodigoPedido", "=", $codigoPedido)
            ->where("HorarioEstipulado", "!=", null)
            ->where("HorarioEstipulado", ">", $horarioMaximoNuevo)
            ->first();
        $tiempoMaximo = 0;
        if (!is_null($pedidoConHorarioMaximo)) {
            $tiempoMaximo = $pedidoConHorarioMaximo->HorarioEstipulado;
        } else {
            $tiempoMaximo = $horarioMaximoNuevo;
        }
        $pedidosEnComun = Pedido::all()->where("CodigoPedido", "=", $codigoPedido);
        foreach ($pedidosEnComun as $pedido) {
            $pedido->HorarioEstipulado = $tiempoMaximo;
            $pedido->FechaModificacion = date("Y-m-d");
            $pedido->save();
        }
    }
    public function traerPedidosListosPorCodigo(Request $request, Response $response, array $args)
    {
        try {
            $datos = $request->getQueryParams();
            if (!isset($datos["codigoPedido"])) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $codigoBuscado = $datos["codigoPedido"];
            $cantidadDePedidosListos = Capsule::table("Pedido")->where("CodigoPedido", "=", $codigoBuscado)->where("EstadoPedidoId", "=", 3)->count();
            $cantidadDePedidos = Capsule::table("Pedido")->where("CodigoPedido", "=", $codigoBuscado)->count();
            if ($cantidadDePedidos == $cantidadDePedidosListos) {

                $datos = json_encode(array("Estado del pedido" => "Listo para servir completo"));
            } else {
                $datos = json_encode(array("Estado del pedido" => "Faltan platos"));
            }
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function traerPedidosListos(Request $request, Response $response, array $args)
    {
        try {
            $pedidosPorCodigo = Pedido::all()
                ->where("EstadoPedidoId", "=", 3)
                ->groupBy('CodigoPedido');
            foreach ($pedidosPorCodigo as $codigo => $value) {
                $cantidadDePedidosListos = Capsule::table("Pedido")->where("CodigoPedido", "=", $codigo)->where("EstadoPedidoId", "=", 3)->count();
                $cantidadDePedidos = Capsule::table("Pedido")->where("CodigoPedido", "=", $codigo)->count();
                $value->add(array(
                    "Cantidad de pedidos" => $cantidadDePedidos,
                    "Cantidad de pedidos listos" => $cantidadDePedidosListos
                ));
            }
            if (count($pedidosPorCodigo) == 0) {
                throw new Exception("No se encontraron Logs");
            }
            $datos = json_encode($pedidosPorCodigo);
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function traerPendientesPorSector(Request $request, Response $response, array $args)
    {
        try {
            if (!isset($args["sectorId"])) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $sectorId = $args["sectorId"];
            $datos = json_encode(Pedido::all()->load("producto")
                ->where("producto.TipoProductoId", "=", $sectorId)
                ->where("EstadoPedidoId", "!=", 3)
                ->groupBy('CodigoPedido'));

            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function TraerUno(Request $request, Response $response, array $args)
    {
        try {
            //Los datos ingresados por la url se buscan en args
            $id = $args["id"];
            $pedido = Pedido::where("Id", "=", $id)->first();
            $datos = json_encode($pedido);
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function TraerTodos(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(Pedido::all());
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function CargarUno(Request $request, Response $response, array $args)
    {
        try {
            $datosIngresados = $request->getParsedBody()["body"];
            //Validación de datosIngresados
            if (
                !isset($datosIngresados["mesaId"]) ||
                !isset($datosIngresados["productoId"]) ||
                !isset($datosIngresados["cantidad"]) ||
                !isset($datosIngresados["nombreCliente"])
            ) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $mesaId = $datosIngresados["mesaId"];
            $productosId = explode(",", $datosIngresados["productoId"]);
            $cantidades = explode(",", $datosIngresados["cantidad"]);
            $nombreCliente = $datosIngresados["nombreCliente"];
            $codigoPedido = substr(md5(time()), 0, 5);
            $horarioCreacion = date("G:i:s");
            $urlFoto = "";
            #region Lógica de la foto
            if ($request->getUploadedFiles()) {
                //archivos foto
                $archivo = $request->getUploadedFiles();
                $destino = "./assets/fotos/";
                $nombreAnterior = $archivo["foto"]->getClientFileName();
                $extension = explode(".", $nombreAnterior);
                $extension = array_reverse($extension)[0];
                if ($extension != "jpg") {
                    throw new Exception("El tipo de foto es incorrecto");
                }
                $urlFoto = $nombreCliente . "." . $extension;
                $archivoFoto = $archivo["foto"];
                $archivoFoto->moveTo($destino . $urlFoto);
            }
            #endregion
            for ($i = 0; $i < count($productosId); $i++) {
                $newPedido = new Pedido();
                $newPedido->MesaId = $mesaId;
                $newPedido->CodigoPedido = $codigoPedido;
                $newPedido->Cantidad = $cantidades[$i];
                $newPedido->producto_id = $productosId[$i];
                $newPedido->Importe = $this->CalcularImporte($productosId[$i], $cantidades[$i]);
                $newPedido->NombreCliente = $nombreCliente;
                $newPedido->Foto = $urlFoto;
                $newPedido->FechaCreacion = date("Y-m-d");
                $newPedido->HorarioCreacion = $horarioCreacion;
                $newPedido->save();
            }

            $payload = json_encode(array("Resultado" => "Agregado"));

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
    public function BorrarUno(Request $request, Response $response, array $args)
    {
    }
    public function ModificarUno(Request $request, Response $response, array $args)
    {
    }
    private function CalcularImporte($productoId, $cantidad)
    {
        try {
            $producto = Producto::where("Id", "=", $productoId)->first();
            return $producto->Precio * $cantidad;
        } catch (Exception $ex) {
            throw new Exception("No se puede calcular el importe " . $ex->getMessage(), 0, $ex);
        }
    }
    public function CalcularHoras($pedido)
    {
        $pedido->HorarioInicio = date("G:i:s");
        $pedido->HorarioDeEntrega = null;
    }
    public function EstadoDelPedidoCompleto($codigoPedido)
    {
        $pedidosDeLaMesa = Pedido::all()->where("CodigoPedido", "=", $codigoPedido);
        $cantidadPedidosPendientes = 0;
        foreach ($pedidosDeLaMesa as $pedido) {
            if ($pedido->EstadoPedidoId != 3) {
                $cantidadPedidosPendientes++;
            }
        }
        return $cantidadPedidosPendientes;
    }
    public function PedidosFueraDeTiempo(Request $request, Response $response, array $args)
    {
        try {

            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            //Validación de datosIngresados

            $pedidos = Capsule::select('SELECT * FROM Pedido WHERE Eliminado is null AND HorarioDeEntrega > HorarioEstipulado AND FechaCreacion >= ? AND FechaCreacion <= ?', [$fechaInicio, $fechaFin]);
            if (count($pedidos) == 0) {
                throw new Exception("No se encontraron Logs");
            }
            $datos = json_encode($pedidos);
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function PedidosCancelados(Request $request, Response $response, array $args)
    {
        try {

            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            //Validación de datosIngresados

            $pedidos = Capsule::select('SELECT * FROM Pedido WHERE Eliminado = 1 AND FechaCreacion >= ? AND FechaCreacion <= ?', [$fechaInicio, $fechaFin]);
            if (count($pedidos) == 0) {
                throw new Exception("No se encontraron Logs");
            }
            $datos = json_encode($pedidos);
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function ProductosVendidos(Request $request, Response $response, array $args)
    {
        try {
            if (!isset($args["busqueda"]) || ($args["busqueda"] != "mayor" && $args["busqueda"] != "menor")) {
                $error = json_encode(array("Error" => "Datos incorrectos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $busqueda = $args["busqueda"];
            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            //Validación de datosIngresados
            if ($busqueda == "mayor") {

                $producto = Capsule::table("Pedido")
                    ->select(Capsule::raw('SUM(Cantidad) as cantidad_total, producto_id'))
                    ->where("FechaCreacion", ">=", $fechaInicio)
                    ->where("FechaCreacion", "<=", $fechaFin)
                    ->groupBy("producto_id")
                    ->orderByDesc("cantidad_total")
                    ->limit(1)
                    ->get();
            } else if ($busqueda == "menor") {
                $producto = Capsule::table("Pedido")
                    ->select(Capsule::raw('SUM(Cantidad) as cantidad_total, producto_id'))
                    ->where("FechaCreacion", ">=", $fechaInicio)
                    ->where("FechaCreacion", "<=", $fechaFin)
                    ->groupBy("producto_id")
                    ->orderBy("cantidad_total", "asc")
                    ->limit(1)
                    ->get();
            } else {
                $producto = Capsule::table("Pedido")
                    ->select(Capsule::raw('SUM(Cantidad) as cantidad_total, producto_id'))
                    ->where("FechaCreacion", ">=", $fechaInicio)
                    ->where("FechaCreacion", "<=", $fechaFin)
                    ->groupBy("producto_id")
                    ->orderBy("cantidad_total", "asc")
                    ->get();
            }
            if (count($producto) == 0) {
                throw new Exception("No se encontraron Logs");
            }
            $datos = json_encode($producto);
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
