<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './utilities/Validacion.php';
class UsuarioController extends Usuario implements IApiUsable
{
  public function CargarUno(Request $request, Response $response, array $args)
  {
    try {
      $datosIngresados = $request->getParsedBody();
      //Validación de datosIngresados
      if (
        !isset($datosIngresados["nombre"])
        || !isset($datosIngresados["apellido"])
        || !isset($datosIngresados["tipoUsuarioId"])
        || !isset($datosIngresados["sectorId"])
        || !isset($datosIngresados["clave"])
        || !Validacion::EsMail($datosIngresados["mail"])
      ) {
        $error = json_encode(array("Error" => "Datos incompletos"));
        $response->getBody()->write($error);
        return $response
          ->withHeader('Content-Type', 'applocation/json')
          ->withStatus(404);
      }
      $nombre = $datosIngresados["nombre"];
      $apellido = $datosIngresados["apellido"];
      //para comparar en hash usamos password_verify(pass, passIngresada)
      $clave = password_hash($datosIngresados["clave"], PASSWORD_DEFAULT);
      $tipoUsuarioId = $datosIngresados["tipoUsuarioId"];
      $sectorId = $datosIngresados["sectorId"];
      $mail = $datosIngresados["mail"];
      $newUsuario = new Usuario();
      $newUsuario->MapeoUsuario($nombre, $apellido, $clave, $mail, $tipoUsuarioId, $sectorId);
      if ($newUsuario->GuardarUsuario()) {
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

  public function TraerUno(Request $request, Response $response, $args)
  {
    try {
      //Los datos ingresados por la url se buscan en args
      $id = $args["id"];
      $datos = json_encode(array(Usuario::FindById($id)));
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
      $datos = json_encode(Usuario::GetAll());
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

  public function ModificarUno(Request $request, Response $response, array $args)
  {
    $datosIngresados = $request->getParsedBody();
    var_dump($datosIngresados);
    if (
      !isset($datosIngresados["nombre"])
      || !isset($datosIngresados["apellido"])
      || !isset($datosIngresados["sectorId"])
      || !isset($datosIngresados["clave"])
      || !Validacion::EsMail($datosIngresados["mail"])
    ) {
      $error = json_encode(array("Error" => "Datos incompletos"));
      $response->getBody()->write($error);
      return $response
        ->withHeader('Content-Type', 'applocation/json')
        ->withStatus(404);
    }
    try {
      $id = $datosIngresados["id"];
      $sectorIdNuevo = $datosIngresados["sectorId"];
      $nombreNuevo = $datosIngresados["nombre"];
      $apellidoNuevo = $datosIngresados["apellido"];
      $mailNuevo = $datosIngresados["mail"];
      $claveNueva = $datosIngresados["clave"];
      $usuarioModificado = new Usuario;
      $usuarioModificado->MapeoUsuario($sectorIdNuevo, $nombreNuevo, $apellidoNuevo, $claveNueva, $mailNuevo);
      if (Usuario::ModificarUsuario($usuarioModificado, $id)) {
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

  public function BorrarUno(Request $request, Response $response, $args)
  {
    try {
      if (!isset($args["id"])) {
        $error = json_encode(array("Error" => "Datos incompletos"));
        $response->getBody()->write($error);
        return $response
          ->withHeader('Content-Type', 'applocation/json')
          ->withStatus(404);
      }
      $id = $args["id"];
      if (Usuario::BorrarUsuario($id)) {
        $datos = json_encode(array("Resultado" => "Borrado con exito"));
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
  /*public function Login(Request $request, Response $response)
  {
    $datosIngresados = $request->getParsedBody();
    if (!isset($datosIngresados["clave"]) && !Validacion::EsMail($datosIngresados["mail"])) {
      $error = ["Error" => "Datos incompletos"];
      return $response->withJson($error, 400);
    }
    try {
      $clave = $datosIngresados["clave"];
      $mail = $datosIngresados["mail"];
      $listado = Usuario::GetAll();
      if (!is_null($listado)) {
        foreach ($listado as $usuario) {
          if ($usuario->GetMail() == $mail) {
            if (password_verify($clave, $usuario->GetClave())) {
              $resultado = ["Id" => $usuario->Id];
              return $response->withJson($resultado, 200);
            } else {
              throw new Exception("La contraseña es incorrecta");
            }
          }
        }
        throw new Exception("El mail no existe");
      }
    } catch (Exception $ex) {
      $error = $ex->getMessage();
      $datosError = json_encode(array("Ocurrio un problema al logear " . $ex->getMessage() => $error));
      $response->getBody()->write($datosError);
      return $response->withHeader('Content-Type', 'applocation/json')->withStatus(500);
    }
  }*/
}
