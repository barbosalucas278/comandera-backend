<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';
class PedidosController extends Pedido implements IApiUsable
{
    public function TraerUno(Request $request, Response $response, array $args)
    {
        try {
            //Los datos ingresados por la url se buscan en args
            $id = $args["id"];
            $datos = json_encode(Pedido::FindById($id));
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'applocation/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'applocation/json')->withStatus(500);
        }
    }
    public function TraerTodos(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(Pedido::GetAll());
            $response->getBody()->write($datos);
            return $response
                ->withHeader('Content-Type', 'applocation/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'applocation/json')->withStatus(500);
        }
    }
    public function CargarUno(Request $request, Response $response, array $args)
    {
        try {
            $datosIngresados = $request->getParsedBody();
            //Validación de datosIngresados
            if (
                !isset($datosIngresados["codigoMesa"]) ||
                !isset($datosIngresados["productoId"]) ||
                !isset($datosIngresados["cantidad"]) ||
                !isset($datosIngresados["nombreCliente"])
            ) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'applocation/json')
                    ->withStatus(404);
            }
            $codigoMesa = $datosIngresados["codigoMesa"];
            $productoId = $datosIngresados["productoId"];
            $cantidad = $datosIngresados["cantidad"];
            $nombreCliente = $datosIngresados["nombreCliente"];
            if (isset($datosIngresados["urlFoto"])) {
                $urlFoto = $datosIngresados["urlFoto"];
            }
            $newPedido = new Pedido();
            $newPedido->MapearPedido($codigoMesa, $productoId, $cantidad, $nombreCliente, $urlFoto);
            if ($newPedido->GuardarPedido()) {
                $payload = json_encode(array("Resultado" => "Agregado"));
            }
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'applocation/json')
                ->withStatus(200);
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response
                ->withHeader('Content-Type', 'applocation/json')
                ->withStatus(500);
        }
    }
    public function BorrarUno(Request $request, Response $response, array $args)
    {
    }
    public function ModificarUno(Request $request, Response $response, array $args)
    {
    }
}
