<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/UsuarioLog.php';

class UsuariosLogController
{
    public function TraerLosLogin(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(UsuarioLog::GetAll());
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
