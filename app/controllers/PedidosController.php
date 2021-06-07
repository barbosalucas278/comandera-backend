<?php

use App\Models\Pedido;
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
        if (!isset($datosIngresados["estado"]) || !isset($datosIngresados["pedidoId"]) || !isset($datosIngresados["tiempoEstimado"])) {
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
            $tiempoEstimado = $datosIngresados["tiempoEstimado"];
            $pedidoModificado = Pedido::where("Id", "=", $id)->first();
            $tipoDeProducto = Producto::where("Id", "=", $pedidoModificado->ProductoId)->first()->TipoProductoId;
            if (!($tipoDeProducto == $sectorUsuario || $tipoUsuarioId == 2)) {
                throw new Exception("El empleado no puede tomar este pedido");
            }

            $pedidoModificado->EstadoPedidoId = $nuevoEstado;
            $this->ActualizarTiempoEstimado($pedidoModificado->CodigoPedido, $tiempoEstimado);
            $this->CalcularHoras($pedidoModificado);
            if ($pedidoModificado->save()) {
                #region Guardo el registro en tabla de relacion PedidoUsuario
                $UsuarioId = $request->getParsedBody()["token"]->Id;
                $newPedidoUsuario = new PedidoUsuario();
                $newPedidoUsuario->Usuario_Id = $UsuarioId;
                $newPedidoUsuario->Pedido_Id = $pedidoModificado->Id;
                $newPedidoUsuario->save();
                #endregion
                $datos = json_encode(array("Resultado" => "Modificado con exito"));
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
    private function ActualizarTiempoEstimado($codigoPedido, $tiempoEstipuladoNuevo)
    {
        $pedidoConTiempoMaximo = Pedido::all()
            ->where("CodigoPedido", "=", $codigoPedido)
            ->where("TiempoEstipulado", ">", $tiempoEstipuladoNuevo);
        $tiempoMaximo = 0;
        if (count($pedidoConTiempoMaximo) > 0) {
            $tiempoMaximo = $pedidoConTiempoMaximo->TiempoEstipulado;
        } else {
            $tiempoMaximo = $tiempoEstipuladoNuevo;
        }
        $pedidosEnComun = Pedido::all()->where("CodigoPedido", "=", $codigoPedido);
        foreach ($pedidosEnComun as $pedido) {
            $pedido->TiempoEstipulado = $tiempoMaximo;
            $pedido->save();
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
                $newPedido->ProductoId = $productosId[$i];
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
        if ($pedido->EstadoPedidoId == 2) {
            $pedido->HorarioInicio = date("G:i:s");
            $pedido->HorarioDeEntrega = null;
        } else {
            $pedido->HorarioDeEntrega = date("G:i:s");
        }
    }
}
