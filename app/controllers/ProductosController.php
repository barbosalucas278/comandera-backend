<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
class ProductosController extends Producto implements IApiUsable
{
  public function TraerUno(Request $request, Response $response, array $args)
  {
    try {
      //Los datos ingresados por la url se buscan en args
      $id = $args["id"];
      $datos = json_encode(Producto::FindById($id));
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
      $datos = json_encode(Producto::GetAll());
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
      $datosIngresados = $request->getParsedBody();
      //Validación de datosIngresados
      if (
        !isset($datosIngresados["codigo"])
        || !isset($datosIngresados["tipoProductoId"])
        || !isset($datosIngresados["nombre"])
        || !isset($datosIngresados["stock"])
        || !isset($datosIngresados["precio"])
      ) {
        $error = json_encode(array("Error" => "Datos incompletos"));
        $response->getBody()->write($error);
        return $response
          ->withHeader('Content-Type', 'applocation/json')
          ->withStatus(404);
      }
      $codigo = $datosIngresados["codigo"];
      $tipoProductoId = $datosIngresados["tipoProductoId"];
      $nombre = $datosIngresados["nombre"];
      $stock = $datosIngresados["stock"];
      $precio = $datosIngresados["precio"];
      $newProducto = new Producto();
      $newProducto->MapeoProducto($codigo, $tipoProductoId, $nombre, $stock, $precio);
      if ($newProducto->GuardarProducto()) {
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
