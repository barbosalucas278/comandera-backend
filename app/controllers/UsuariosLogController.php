<?php

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\models\UsuarioLog as UsuarioLog;

require_once './models/UsuarioLog.php';

class UsuariosLogController
{
    public function TraerLosLogin(Request $request, Response $response, array $args)
    {
        try {
            $datosIngresados = $request->getParsedBody()["body"];
            $horarioInicio = null;
            $horarioFin = null;
            $fechaBuscada = null;
            //Validación de datosIngresados
            if (
                !isset($datosIngresados["fechaBuscada"]) && !isset($datosIngresados["horarioInicio"]) && !isset($datosIngresados["horarioFin"])
            ) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }

            if (isset($datosIngresados["horarioInicio"]) && isset($datosIngresados["horarioFin"])) {
                $horarioInicio = date($datosIngresados["horarioInicio"]);
                $horarioFin = date($datosIngresados["horarioFin"]);
            } else {
                $fechaBuscada = $datosIngresados["fechaBuscada"];
            }

            if (is_null($horarioInicio) || is_null($horarioFin)) {
                $usuarios = new UsuarioLog();
                $usuarios = $usuarios
                    ->where("FechaDeIngreso", "=", $fechaBuscada)
                    ->get();
                if (count($usuarios) == 0) {
                    throw new Exception("No se encontraron Logs");
                }
                $datos = json_encode($usuarios);
                $response->getBody()->write($datos);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            } else {
                $usuarios = new UsuarioLog();
                $usuarios = $usuarios
                    ->where("HoraDeIngreso", ">=", $horarioInicio)
                    ->where("HoraDeIngreso", "<=", $horarioFin)
                    ->get();
                $datos = json_encode($usuarios);
                if (count($usuarios) == 0) {
                    throw new Exception("No se encontraron Logs");
                }
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
}
