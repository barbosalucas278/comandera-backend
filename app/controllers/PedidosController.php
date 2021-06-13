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
            if ($pedidoModificado->save()) {
                #region Guardo el registro en tabla de relacion PedidoUsuario
                //TODO:Si cambia a listo para servir agregar marca de uso en pedidousuario de entregado a tiempo
                $UsuarioId = $request->getParsedBody()["token"]->Id;
                $newPedidoUsuario = new PedidoUsuario();
                $newPedidoUsuario->Usuario_Id = $UsuarioId;
                $newPedidoUsuario->Pedido_Id = $pedidoModificado->Id;
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
}
