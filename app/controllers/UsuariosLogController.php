<?php

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\models\UsuarioLog as UsuarioLog;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once './models/UsuarioLog.php';

class UsuariosLogController
{
    public function TraerLosLogin(Request $request, Response $response, array $args)
    {
        try {
            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            //Validación de datosIngresados

            $usuarios = Capsule::table("UsuarioLog")
                ->where("FechaDeIngreso", ">=", $fechaInicio)
                ->where("FechaDeIngreso", "<=", $fechaFin)
                ->get();

            if (count($usuarios) == 0) {
                throw new Exception("No se encontraron Logs");
            }
            $datos = json_encode($usuarios);
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
}
