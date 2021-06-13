<?php

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Ventas;
use App\Models\PedidoUsuario;
use App\Models\Venta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Mesa.php';
require_once './models/Ventas.php';
require_once './interfaces/IApiUsable.php';
class MesaController implements IApiUsable
{
    public function CambiarEstado(Request $request, Response $response, array $args)
    {
        $datosIngresados = $request->getParsedBody()["body"];
        if (
            !isset($datosIngresados["estado"]) ||
            !isset($datosIngresados["mesaId"])
        ) {
            $error = json_encode(array("Error" => "Datos incompletos"));
            $response->getBody()->write($error);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        try {
            $id = $datosIngresados["mesaId"];
            $nuevoEstado = $datosIngresados["estado"];
            $mesaModificado = Mesa::where("Id", "=", $id)->first();
            $mesaModificado->EstadoMesaId = $nuevoEstado;
            $codigoPedido = $datosIngresados["codigoPedido"] ?? -1;
            if ($mesaModificado->save()) {
                if ($mesaModificado->EstadoMesaId == 2) {
                    $pedidosAModificado = Pedido::all()->where("CodigoPedido", "=", $codigoPedido);
                    foreach ($pedidosAModificado as $pedido) {
                        $pedido->HorarioDeEntrega = date("G:i:s");
                        PedidoUsuario::where("Pedido_id", $pedido->Id)
                            ->update(["Entregado" => 1]);
                        $pedido->save();
                    }
                } else if ($mesaModificado->EstadoMesaId == 3) {
                    $pedidosACobrar = Pedido::all()->where("CodigoPedido", "=", $codigoPedido);
                    $importe = 0;
                    foreach ($pedidosACobrar as $pedido) {
                        $importe += $pedido->Importe;
                    }
                    $newVenta = new Ventas();
                    $newVenta->mesa_id = $id;
                    $newVenta->importe = $importe;
                    $newVenta->fecha = date("Y-m-d");
                    $newVenta->Pagado = false;
                    $newVenta->save();
                }
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
    public function TraerUno(Request $request, Response $response, array $args)
    {
        try {
            //Los datos ingresados por la url se buscan en args
            $id = $args["id"];
            $datos = json_encode(Mesa::where("Id", "=", $id)->first());
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
            $datos = json_encode(Mesa::all());
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
            if (!isset($datosIngresados["nroMesa"])) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $nroMesa = $datosIngresados["nroMesa"];
            $newMesa = new Mesa();
            $newMesa->Codigo = $this->GenerarCodigoMesa($nroMesa);
            if ($newMesa->save()) {
                $payload = json_encode(array("Resultado" => "Agregado"));
            }
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
    private function GenerarCodigoMesa($nroMesa)
    {
        $codigo = "M";
        $caracteresFaltantes = 3;
        for ($i = 0; $i < $caracteresFaltantes; $i++) {
            $codigo .= "0";
        }
        return $codigo . $nroMesa;
    }

    public function BorrarUno(Request $request, Response $response, array $args)
    {
    }
    public function ModificarUno(Request $request, Response $response, array $args)
    {
    }
}
