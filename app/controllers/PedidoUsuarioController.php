<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/PedidoUsuario.php';
class PedidoUsuarioController
{
    public function OperacionesPorSector(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(PedidoUsuario::GetAllSector());
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
    public function operacionesByEmpleado(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(PedidoUsuario::GetAllByEmpleado());
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
    public function OperacionesPorSectorEmpleado(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(PedidoUsuario::GetAllSectorPorEmpleado());
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
}
