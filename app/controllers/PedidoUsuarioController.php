<?php

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\PedidoUsuario;
use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as Capsule;

class PedidoUsuarioController
{
    //TODO:: REFACTORIZAR, LA LINEA 17 ME TRAE EL PEDIDOUSUARIO Y ME TRA EL USUARIO FUNCIONA!!!
    public function OperacionesPorSector(Request $request, Response $response, array $args)
    {
        try {
            $datos = $request->getQueryParams();
            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));

            $pedidosUsuario = Capsule::table("PedidoUsuario")
                ->where("PedidoUsuario.FechaCreacion", ">=", $fechaInicio)
                ->where("PedidoUsuario.FechaCreacion", "<=", $fechaFin)
                ->where("PedidoUsuario.Entregado", "=", true)
                ->get();

            if ($pedidosUsuario->count() == 0) {
                $datos = json_encode(array("Resultado" => "No se encontraron datos para la consulta"));
            } else {
                $datos = json_encode($pedidosUsuario);
            }
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
    public function operacionesByEmpleado(Request $request, Response $response, array $args)
    {
        try {
            $datos = $request->getQueryParams();
            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));

            $listaDeUsuariosConSusPedidos = Usuario::all()
                ->where("SectorId", "!=", 6);
            $cantidadDePedidosPorUsuario = $this->CantidadPedidosPorUsuario($listaDeUsuariosConSusPedidos, $fechaInicio, $fechaFin);
            $datos = json_encode($cantidadDePedidosPorUsuario);

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
    private function CantidadPedidosPorUsuario($listaDeUsuarios, $fechaInicio, $fechaFin)
    {
        $listaNueva = collect();
        foreach ($listaDeUsuarios as $usuario) {
            $contador = 0;
            $usuarioId = $usuario["Id"];
            foreach ($usuario->pedidosUsuarios as $pedido) {
                if ($pedido->FechaCreacion >= $fechaInicio && $pedido->FechaCracion <= $fechaFin) {
                    $contador++;
                }
            }
            $listaNueva->push(array("usuario_id" => $usuarioId, "cantidad_operaciones" => $contador));
        }
        return $listaNueva;
    }
    private function CantidadPedidosPorUsuarioSector($sectorId, $fechaInicio, $fechaFin)
    {
        $listaDeUsuarios = Usuario::all();
        $listaNueva = collect();
        foreach ($listaDeUsuarios as $usuario) {
            if ($usuario['SectorId'] == $sectorId) {
                $contador = 0;
                $usuarioId = $usuario["Id"];
                foreach ($usuario->pedidosUsuarios as $pedido) {
                    if ($pedido->FechaCreacion >= $fechaInicio && $pedido->FechaCreacion <= $fechaFin) {
                        $contador++;
                    }
                }
                $listaNueva->push(array("usuario_id" => $usuarioId, "cantidad_operaciones" => $contador));
            }
        }
        return $listaNueva;
    }
    public function OperacionesPorSectorEmpleado(Request $request, Response $response, array $args)
    {
        try {
            $datos = $request->getQueryParams();

            $id = $datos["sectorId"];
            $fechaInicio = ($datos["fechaInicio"] ?? date_format(new DateTime(), "Y-m-d"));
            $fechaFin = ($datos["fechaFin"] ?? date_format(new DateTime(), "Y-m-d"));
            $cantidadDePedidosPorUsuario = $this->CantidadPedidosPorUsuarioSector($id, $fechaInicio, $fechaFin);
            $datos = json_encode($cantidadDePedidosPorUsuario);
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
