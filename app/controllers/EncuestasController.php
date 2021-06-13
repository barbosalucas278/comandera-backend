<?php

use App\Models\Encuesta;
use App\Models\EncuestaMesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once './utilities/Validacion.php';
require_once './models/Encuesta.php';
require_once './models/EncuestaMesa.php';
class EncuestasController implements IApiUsable
{
    public function CargarUno(Request $request, Response $response, array $args)
    {
        try {
            $datosIngresados = $request->getParsedBody();
            //Validación de datosIngresados
            if (
                !isset($datosIngresados["mesa_id"]) ||
                !Validacion::ValidarPuntuacion($datosIngresados["puntuacionRestaurante"]) ||
                !Validacion::ValidarPuntuacion($datosIngresados["puntuacionMozo"]) ||
                !Validacion::ValidarPuntuacion($datosIngresados["puntuacionComida"]) ||
                !isset($datosIngresados["comentario"])
            ) {
                $error = json_encode(array("Error" => "Datos incompletos o incorrectos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $mesaId = $datosIngresados["mesa_id"];
            $puntuacionRestaurante = $datosIngresados["puntuacionRestaurante"];
            $puntuacionMozo = $datosIngresados["puntuacionMozo"];
            $puntuacionComida = $datosIngresados["puntuacionComida"];
            $comentario = $datosIngresados["comentario"];

            $newEncuesta = new Encuesta();
            $newEncuesta->mesa_id = $mesaId;
            $newEncuesta->Restaurante = $puntuacionRestaurante;
            $newEncuesta->Mozo = $puntuacionMozo;
            $newEncuesta->Comida = $puntuacionComida;
            $newEncuesta->Comentario = $comentario;
            $newEncuesta->FechaCreacion = date("Y-m-d");
            $newEncuesta->HorarioCreacion = date("G:i:s");

            if ($newEncuesta->save()) {
                $newEncuestaMesa = new EncuestaMesa();
                $newEncuestaMesa->mesa_id = $mesaId;
                $newEncuestaMesa->encuesta_id = Encuesta::all()->last()->Id;
                $newEncuestaMesa->save();

                $payload = json_encode(array("Resultado" => "Guardada"));
                $response->getBody()->write($payload);
            }
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
    public function TraerUno(Request $request, Response $response, array $args)
    {
    }
    public function TraerTodos(Request $request, Response $response, array $args)
    {
    }
    public function ModificarUno(Request $request, Response $response, array $args)
    {
    }
    public function BorrarUno(Request $request, Response $response, array $args)
    {
    }
}
