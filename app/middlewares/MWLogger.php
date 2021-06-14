<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use \App\models\UsuarioLog as UsuarioLog;

require_once './models/UsuarioLog.php';

class MWLogger
{
    public function UsuarioLogger(Request $request, RequestHandler $handler)
    {
        try {
            $response = $handler->handle($request);
            $dataResponse = MWAutenticar::ObtenerDataToken($response->getBody());
            if ($dataResponse->Id != 2) {
                $log = new UsuarioLog();
                $log->Usuario_Id = $dataResponse->Id;
                $log->HoraDeIngreso = date("G:i:s");
                $log->FechaDeIngreso = date("Y-m-d");
                $log->save();
            }
            return $response;
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error al verificar los datos del empleado " => $error));
            $response->getBody()->write($datosError);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
