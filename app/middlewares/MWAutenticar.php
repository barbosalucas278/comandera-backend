<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;

class MWAutenticar
{
    public function VerificarUsuario(Request $request, RequestHandler $handler)
    {
        try {
            if (empty($request->getHeaderLine('Authorization'))) {
                throw new Exception("Falta token de autorización");
            }
            $header = $request->getHeaderLine('Authorization');
            $contenidoRequest = $request->getParsedBody();
            $token = trim(explode("Bearer", $header)[1]);
            self::VerificarToken($token);
            //Agrego el payload dle token y el contenido dle boy del request  a un array
            //para despues pasarlo al controlador
            $payload = array("body" => $contenidoRequest, "token" => self::ObtenerDataToken($token));
            //el withParsedBody retorna un nuevo objeto Request y que lo asigno a otra variable.
            $request = $request->withParsedBody($payload);
            $response = $handler->handle($request);
            return  $response;
        } catch (Exception $ex) {

            throw new Exception("Ocurrio un problema " . $ex->getMessage(), 0, $ex);
        }
    }
    private static function VerificarToken($token)
    {
        if (empty($token)) {
            throw new Exception("El token se encuentra vacio");
        }
        try {
            $tokenDecodificado = JWT::decode($token, $_ENV['SECRET_KEY'], [$_ENV['TIPO_ENCRYP']]);
            if ($tokenDecodificado->aud !== UsuarioController::Aud()) {
                throw new Exception("No es un usuario válido ");
            }
            return $tokenDecodificado;
        } catch (Exception $ex) {
            throw new Exception("Verificar token " . $ex->getMessage(), 0, $ex);
        }
    }
    public static function ObtenerDataToken($token)
    {
        if (empty($token)) {
            throw new Exception("El token se encuentra vacio");
        }
        try {
            $tokenDecodificado = JWT::decode($token, $_ENV['SECRET_KEY'], [$_ENV['TIPO_ENCRYP']]);
            return $tokenDecodificado->data;
        } catch (Exception $ex) {
            throw $ex;
        }
        if ($tokenDecodificado->aud !== UsuarioController::Aud()) {
            throw new Exception("No es un usuario válido ");
        }
    }
}
