<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\models\Usuario as Usuario;
use Firebase\JWT\JWT;

require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './utilities/Validacion.php';
class UsuarioController implements IApiUsable
{
  public function CambiarEstado(Request $request, Response $response, array $args)
  {
    $datosIngresados = $request->getParsedBody()["body"];
    if (!isset($datosIngresados["estado"]) || !isset($datosIngresados["usuarioId"])) {
      $error = json_encode(array("Error" => "Datos incompletos"));
      $response->getBody()->write($error);
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
    }
    try {
      $nombreUsuarioModificacion = $request->getParsedBody()["token"]->Nombre;
      $id = $datosIngresados["usuarioId"];
      $nuevoEstado = $datosIngresados["estado"];
      $usuarioModificado = Usuario::where('Id', '=', $id)->first();
      if (is_null($usuarioModificado)) {
        throw new Exception("usuario no encontrado");
      }
      $usuarioModificado->EstadoUsuarioId = $nuevoEstado;
      $usuarioModificado->UsuarioModificacion = $nombreUsuarioModificacion;
      if ($usuarioModificado->save()) {
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
  public function CargarUno(Request $request, Response $response, array $args)
  {
    try {
      $datosIngresados = $request->getParsedBody()["body"];
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
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(404);
      }
      $usuarioAlta = $request->getParsedBody()["token"]->Nombre;
      $nombre = $datosIngresados["nombre"];
      $apellido = $datosIngresados["apellido"];
      //para comparar en hash usamos password_verify(pass, passIngresada)
      $clave = password_hash($datosIngresados["clave"], PASSWORD_DEFAULT);
      $tipoUsuarioId = $datosIngresados["tipoUsuarioId"];
      $sectorId = $datosIngresados["sectorId"];
      $mail = $datosIngresados["mail"];
      $newUsuario = new Usuario();
      $newUsuario->Nombre = $nombre;
      $newUsuario->Apellido = $apellido;
      $newUsuario->Clave = $clave;
      $newUsuario->TipoUsuarioId = $tipoUsuarioId;
      $newUsuario->SectorId = $sectorId;
      $newUsuario->Mail = $mail;
      $newUsuario->Usuarioalta = $usuarioAlta;
      if ($newUsuario->save()) {
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

  public function TraerUno(Request $request, Response $response, $args)
  {
    try {
      //Los datos ingresados por la url se buscan en args
      $id = $args["id"];
      $usuario = Usuario::where('Id', '=', $id)->first();
      if (is_null($usuario)) {
        throw new Exception("El usuario no existe");
      }
      $datos = json_encode($usuario);
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
      $datos = json_encode(Usuario::all());
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
        ->withHeader('Content-Type', 'application/json')
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

  public function BorrarUno(Request $request, Response $response, $args)
  {
    try {
      if (!isset($args["id"])) {
        $error = json_encode(array("Error" => "Datos incompletos"));
        $response->getBody()->write($error);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(404);
      }
      $id = $args["id"];
      if (Usuario::BorrarUsuario($id)) {
        $datos = json_encode(array("Resultado" => "Borrado con exito"));
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
  public function Login(Request $request, Response $response)
  {
    $datosIngresados = $request->getParsedBody();
    if (!isset($datosIngresados["clave"]) && !Validacion::EsMail($datosIngresados["mail"])) {
      $error = json_encode(array("Error" => "Datos incompletos"));
      $response->getBody()->write($error);
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
    }
    try {
      $clave = $datosIngresados["clave"];
      $mail = $datosIngresados["mail"];
      $listado = Usuario::all();
      if (!is_null($listado)) {
        foreach ($listado as $usuario) {
          if ($usuario->Mail == $mail) {
            if (password_verify($clave, $usuario->Clave)) {
              $datos = [
                "Id" => $usuario->Id,
                "Nombre" => $usuario->Nombre, "Apellido" => $usuario->Apellido, "Mail" => $usuario->Mail,
                "TipoUsuarioId" => $usuario->TipoUsuarioId, "SectorId" => $usuario->SectorId, "EstadoUsuarioId" => $usuario->EstadoUsuarioId
              ];
              $token = self::CrearToken($datos);
              $response->getBody()->write($token);
              return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
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
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  }

  public static function CrearToken($datos)
  {
    $timeNow = time();
    $payload = array(
      "iat" => $timeNow,
      "exp" => $timeNow + (63600), // se agregan segundos
      "aud" => self::Aud(),
      "data" => $datos,
      "app" => 'La Comanda'
    );
    return JWT::encode($payload, $_ENV['SECRET_KEY']);
  }
  public static function Aud()
  {
    $aud = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $aud = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $aud = $_SERVER['REMOTE_ADDR'];
    }

    $aud .= @$_SERVER['HTTP_USER_AGENT'];
    $aud .= gethostname();

    return sha1($aud);
  }
}
