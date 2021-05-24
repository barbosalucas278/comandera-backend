<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
class MesaController extends Mesa implements IApiUsable
{
    public function CambiarEstado(Request $request, Response $response, array $args)
    {
        $datosIngresados = $request->getParsedBody()["body"];
        if (!isset($datosIngresados["estado"]) || !isset($datosIngresados["mesaId"])) {
            $error = json_encode(array("Error" => "Datos incompletos"));
            $response->getBody()->write($error);
            return $response
                ->withHeader('Content-Type', 'applocation/json')
                ->withStatus(404);
        }
        try {
            $id = $datosIngresados["mesaId"];
            $nuevoEstado = $datosIngresados["estado"];
            $mesaModificado = new Mesa();
            $mesaModificado->Id = $id;
            $mesaModificado->EstadoMesaId = $nuevoEstado;
            if (Mesa::ModificarEstadoMesa($mesaModificado)) {
                $datos = json_encode(array("Resultado" => "Modificado con exito"));
                $response->getBody()->write($datos);
                return $response
                    ->withHeader('Content-Type', 'applocation/json')
                    ->withStatus(200);
            }
        } catch (Exception $ex) {
            $error = $ex->getMessage();
            $datosError = json_encode(array("Error" => $error));
            $response->getBody()->write($datosError);
            return $response->withHeader('Content-Type', 'applocation/json')->withStatus(500);
        }
    }
    public function TraerUno(Request $request, Response $response, array $args)
    {
        try {
            //Los datos ingresados por la url se buscan en args
            $id = $args["id"];
            $datos = json_encode(array(Mesa::FindById($id)));
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
            $datos = json_encode(Mesa::GetAll());
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
            $datosIngresados = $request->getParsedBody()["body"];
            //Validación de datosIngresados
            if (!isset($datosIngresados["nroMesa"])) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'applocation/json')
                    ->withStatus(404);
            }
            $nroMesa = $datosIngresados["nroMesa"];
            $newMesa = new Mesa();
            $newMesa->MapeoUsuario($nroMesa);
            if ($newMesa->GuardarMesa()) {
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
