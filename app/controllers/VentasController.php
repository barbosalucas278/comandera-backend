<?php

use App\Models\Venta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as Capsule;

class VentasController implements IApiUsable
{
    public function TraerUno(Request $request, Response $response, array $args)
    {
    }
    public function TraerTodos(Request $request, Response $response, array $args)
    {
    }
    public function CargarUno(Request $request, Response $response, array $args)
    {
    }
    public function ModificarUno(Request $request, Response $response, array $args)
    {
        try {
            $datosIngresados = $request->getParsedBody()["body"];
            //Validación de datosIngresados
            if (!isset($datosIngresados["estado"]) || !isset($datosIngresados["mesaId"])) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $estado = false;
            $mesaId = $datosIngresados["mesaId"];
            if ($datosIngresados["estado"] == 1) {
                $estado = true;
            }
            Capsule::table("Ventas")
                ->where("mesa_id", "=", $mesaId)
                ->where("Pagado", "=", 0)
                ->update(["Pagado" => $estado]);

            $payload = json_encode(array("Resultado" => "Pagado"));
            $response->getBody()->write($payload);
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
    public function BorrarUno(Request $request, Response $response, array $args)
    {
    }
}
