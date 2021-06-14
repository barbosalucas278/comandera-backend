<?php

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Ventas;
use App\Models\PedidoUsuario;
use App\Models\Venta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once './models/Mesa.php';
require_once './models/Ventas.php';
require_once './interfaces/IApiUsable.php';
class MesaController implements IApiUsable
{
    public function CambiarEstado(Request $request, Response $response, array $args)
    {
        $datosIngresados = $request->getParsedBody()["body"];
        $idMozo = $request->getParsedBody()["token"]->Id;
        if (
            !isset($datosIngresados["estado"]) ||
            !isset($datosIngresados["mesaId"])
        ) {
            $error = json_encode(array("Error" => "Datos incompletos"));
            $response->getBody()->write($error);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        try {
            $id = $datosIngresados["mesaId"];
            $nuevoEstado = $datosIngresados["estado"];
            $mesaModificado = Mesa::where("Id", "=", $id)->first();
            $mesaModificado->EstadoMesaId = $nuevoEstado;
            $codigoPedido = $datosIngresados["codigoPedido"] ?? -1;
            if ($mesaModificado->save()) {
                if ($mesaModificado->EstadoMesaId == 2) {
                    $pedidosAModificado = Pedido::all()->where("CodigoPedido", "=", $codigoPedido);
                    foreach ($pedidosAModificado as $pedido) {
                        $pedido->HorarioDeEntrega = date("G:i:s");
                        PedidoUsuario::where("Pedido_id", $pedido->Id)
                            ->update(["Entregado" => 1, "usuario_Id" => $idMozo]);
                        $pedido->save();
                    }
                } else if ($mesaModificado->EstadoMesaId == 3) {
                    $pedidosACobrar = Pedido::all()->where("CodigoPedido", "=", $codigoPedido);
                    $importe = 0;
                    foreach ($pedidosACobrar as $pedido) {
                        $importe += $pedido->Importe;
                    }
                    $newVenta = new Ventas();
                    $newVenta->mesa_id = $id;
                    $newVenta->importe = $importe;
                    $newVenta->fecha = date("Y-m-d");
                    $newVenta->Pagado = false;
                    $newVenta->save();
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

    public function UsoDeMesas(Request $request, Response $response, array $args)
    {
        try {
            if (!isset($args["busqueda"]) || ($args["busqueda"] != "mayor" && $args["busqueda"] != "menor")) {
                $error = json_encode(array("Error" => "Datos incorrectos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $busqueda = $args["busqueda"];
            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            //Validación de datosIngresados
            if ($busqueda == "mayor") {
                $mesas = Capsule::table("Ventas")
                    ->select(Capsule::raw('COUNT(*) as cantidad_usos_total, mesa_id'))
                    ->where("fecha", ">=", $fechaInicio)
                    ->where("fecha", "<=", $fechaFin)
                    ->where("Pagado", "=", 1)
                    ->orderByDesc("cantidad_usos_total")
                    ->groupBy("mesa_id")
                    ->limit(1)
                    ->get();
            } else if ($busqueda == "menor") {
                $mesas = Capsule::table("Ventas")
                    ->select(Capsule::raw('COUNT(*) as cantidad_usos_total, mesa_id'))
                    ->where("fecha", ">=", $fechaInicio)
                    ->where("fecha", "<=", $fechaFin)
                    ->where("Pagado", "=", 1)
                    ->orderBy("cantidad_usos_total", "asc")
                    ->groupBy("mesa_id")
                    ->limit(1)
                    ->get();
            }
            $datos = json_encode($mesas);
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
    public function VentasMesas(Request $request, Response $response, array $args)
    {
        try {
            if (!isset($args["busqueda"]) || ($args["busqueda"] != "mayor" && $args["busqueda"] != "menor")) {
                $error = json_encode(array("Error" => "Datos incorrectos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $busqueda = $args["busqueda"];
            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            //Validación de datosIngresados
            if ($busqueda == "mayor") {
                $mesas = Capsule::table("Ventas")
                    ->select(Capsule::raw('SUM(importe) as cantidad_vendida_total, mesa_id'))
                    ->where("fecha", ">=", $fechaInicio)
                    ->where("fecha", "<=", $fechaFin)
                    ->where("Pagado", "=", 1)
                    ->orderByDesc("cantidad_vendida_total")
                    ->groupBy("mesa_id")
                    ->limit(1)
                    ->get();
            } else if ($busqueda == "menor") {
                $mesas = Capsule::table("Ventas")
                    ->select(Capsule::raw('SUM(importe) as cantidad_vendida_total, mesa_id'))
                    ->where("fecha", ">=", $fechaInicio)
                    ->where("fecha", "<=", $fechaFin)
                    ->where("Pagado", "=", 1)
                    ->orderBy("cantidad_vendida_total", "asc")
                    ->groupBy("mesa_id")
                    ->limit(1)
                    ->get();
            }
            $datos = json_encode($mesas);
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
    public function FacturasPorMesas(Request $request, Response $response, array $args)
    {
        try {
            if (!isset($args["id"])) {
                $error = json_encode(array("Error" => "Datos incorrectos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $idMesa = $args["id"];
            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));

            //Validación de datosIngresados
            $mesas = Capsule::table("Ventas")
                ->select(Capsule::raw('SUM(importe) as cantidad_vendida_total, mesa_id'))
                ->where("fecha", ">=", $fechaInicio)
                ->where("fecha", "<=", $fechaFin)
                ->where("Pagado", "=", 1)
                ->where("mesa_id", "=", $idMesa)
                ->get();

            $datos = json_encode($mesas);
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
    public function FacturasMesas(Request $request, Response $response, array $args)
    {
        try {
            if (!isset($args["busqueda"]) || ($args["busqueda"] != "mayor" && $args["busqueda"] != "menor")) {
                $error = json_encode(array("Error" => "Datos incorrectos"));
                $response->getBody()->write($error);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $busqueda = $args["busqueda"];
            $datos = $request->getQueryParams();

            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            $limit = ($datos["limit"] ?? 10);
            //Validación de datosIngresados
            if ($busqueda == "mayor") {
                $mesas = Capsule::table("Ventas")
                    ->where("fecha", ">=", $fechaInicio)
                    ->where("fecha", "<=", $fechaFin)
                    ->where("Pagado", "=", 1)
                    ->orderByDesc("importe")
                    ->groupBy("mesa_id")
                    ->limit($limit)
                    ->get();
            } else if ($busqueda == "menor") {
                $mesas = Capsule::table("Ventas")
                    ->where("fecha", ">=", $fechaInicio)
                    ->where("fecha", "<=", $fechaFin)
                    ->where("Pagado", "=", 1)
                    ->orderBy("importe", "asc")
                    ->groupBy("mesa_id")
                    ->limit($limit)
                    ->get();
            }
            $datos = json_encode($mesas);
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
