<?php

use App\Models\Mesa;
use App\Models\Pedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
class MesaController implements IApiUsable
{
    public function CambiarEstado(Request $request, Response $response, array $args)
    {
        $datosIngresados = $request->getParsedBody()["body"];
        if (
            !isset($datosIngresados["estado"]) ||
            !isset($datosIngresados["mesaId"]) ||
            !isset($datosIngresados["pedidoId"])
        ) {
            $error = json_encode(array("Error" => "Datos incompletos"));
            $response->getBody()->write($error);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        try {
            $id = $datosIngresados["mesaId"];
            $pedidoId = $datosIngresados["pedidoId"];
            $nuevoEstado = $datosIngresados["estado"];
            $mesaModificado = Mesa::where("Id", "=", $id)->first();
            $mesaModificado->EstadoMesaId = $nuevoEstado;
            if ($mesaModificado->save()) {
                if ($mesaModificado->Id == 2) {
                    $pedidoModificado = Pedido::where("Id", "=", $pedidoId)->first();
                    $pedidoModificado->HorarioDeEntrega = date("G:i:s");
                    $pedidoModificado->save();
                }
                $datos = json_encode(array("Resultado" => "Modificado con exito"));
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
    public function TraerUno(Request $request, Response $response, array $args)
    {
        try {
            //Los datos ingresados por la url se buscan en args
            $id = $args["id"];
            $datos = json_encode(Mesa::where("Id", "=", $id)->first());
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

    public function TraerTodos(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(Mesa::all());
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
    public function CargarUno(Request $request, Response $response, array $args)
    {
        try {
            $datosIngresados = $request->getParsedBody()["body"];
            //Validación de datosIngresados
            if (!isset($datosIngresados["nroMesa"])) {
                $error = json_encode(array("Error" => "Datos incompletos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $nroMesa = $datosIngresados["nroMesa"];
            $newMesa = new Mesa();
            $newMesa->Codigo = $this->GenerarCodigoMesa($nroMesa);
            if ($newMesa->save()) {
                $payload = json_encode(array("Resultado" => "Agregado"));
            }
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
    private function GenerarCodigoMesa($nroMesa)
    {
        $codigo = "M";
        $caracteresFaltantes = 3;
        for ($i = 0; $i < $caracteresFaltantes; $i++) {
            $codigo .= "0";
        }
        return $codigo . $nroMesa;
    }

    public function BorrarUno(Request $request, Response $response, array $args)
    {
    }
    public function ModificarUno(Request $request, Response $response, array $args)
    {
    }
}
