<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

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
                $log->UsuarioId = $dataResponse->Id;
                $log->GuardarUsuarioLog();
            }
            return $response;
        } catch (Exception $ex) {
            throw new Exception("Error al verificar los datos dle empleado " . $ex->getMessage(), 0, $ex);;
        }
    }
}
