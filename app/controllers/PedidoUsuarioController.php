<?php

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\PedidoUsuario;
use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class PedidoUsuarioController
{
    //TODO:: REFACTORIZAR LA LINEA 17 ME TRAE EL PEDIDOUSUARIO Y ME TRA EL USUARIO FUNCIONA!!!
    public function OperacionesPorSector(Request $request, Response $response, array $args)
    {
        try {
            $datos = json_encode(PedidoUsuario::all()->load("usuario")->groupBy("usuario.SectorId"));
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
            $listaDeUsuariosConSusPedidos = Usuario::all();
            $cantidadDePedidosPorUsuario = $this->CantidadPedidosPorUsuario($listaDeUsuariosConSusPedidos);
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
    private function CantidadPedidosPorUsuario($listaDeUsuarios)
    {
        $listaNueva = collect();
        foreach ($listaDeUsuarios as $usuario) {
            $contador = 0;
            $usuarioId = $usuario["Id"];
            foreach ($usuario->pedidosUsuarios as $pedido) {
                $contador++;
            }
            $listaNueva->push(array("usuario_id" => $usuarioId, "cantidad_operaciones" => $contador));
        }
        return $listaNueva;
    }
    private function CantidadPedidosPorUsuarioSector($listaDeUsuarios, $sectorId)
    {
        $listaNueva = collect();
        foreach ($listaDeUsuarios as $usuario) {
            if ($usuario['SectorId'] == $sectorId) {
                $contador = 0;
                $usuarioId = $usuario["Id"];
                foreach ($usuario->pedidosUsuarios as $pedido) {
                    $contador++;
                }
                $listaNueva->push(array("usuario_id" => $usuarioId, "cantidad_operaciones" => $contador));
            }
        }
        return $listaNueva;
    }
    public function OperacionesPorSectorEmpleado(Request $request, Response $response, array $args)
    {
        try {
            $id = $args["sectorId"];
            $listaDeUsuariosConSusPedidos = Usuario::all();
            $cantidadDePedidosPorUsuario = $this->CantidadPedidosPorUsuarioSector($listaDeUsuariosConSusPedidos, $id);
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
