<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class MWAccesos
{
    public function SoloAdministradores(Request $request, RequestHandler $handler)
    {
        try {
            $data = $request->getParsedBody()["token"];
            if ($data->TipoUsuarioId == 2) {
                $response = $handler->handle($request);
                return $response;
            } else {
                throw new Exception("No tiene permisos para este endpoint");
            }
        } catch (Exception $ex) {
            throw new Exception("Error al verificar los datos dle empleado " . $ex->getMessage(), 0, $ex);;
        }
    }
    public function AdministradoresYMozos(Request $request, RequestHandler $handler)
    {
        try {
            $data = $request->getParsedBody()["token"];
            if ($data->TipoUsuarioId == 2 || ($data->TipoUsuarioId == 1 && $data->SectorId == 5)) {
                $response = $handler->handle($request);
                return $response;
            } else {
                throw new Exception("No tiene permisos para este endpoint");
            }
        } catch (Exception $ex) {
            throw new Exception("Error al verificar los datos dle empleado " . $ex->getMessage(), 0, $ex);;
        }
    }
    public function TodosLosUsuarios(Request $request, RequestHandler $handler)
    {
        try {
            $data = $request->getParsedBody()["token"];
            if ($data->TipoUsuarioId == 2 || $data->TipoUsuarioId == 1) {
                $response = $handler->handle($request);
                return $response;
            } else {
                var_dump($data->TipoUsuarioId);
                throw new Exception("No tiene permisos para este endpoint");
            }
        } catch (Exception $ex) {
            throw new Exception("Error al verificar los datos dle empleado " . $ex->getMessage(), 0, $ex);;
        }
    }
}
