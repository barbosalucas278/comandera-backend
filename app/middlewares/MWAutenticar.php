<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;
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
            $token = trim(explode("Bearer", $header)[1]);
            self::VerificarToken($token);
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
            $tokenDecodificado = JWT::decode($token, getenv('SECRET_KEY'), [getenv('TIPO_ENCRYP')]);
        } catch (Exception $ex) {
            throw $ex;
        }
        if ($tokenDecodificado->aud !== UsuarioController::Aud()) {
            throw new Exception("No es un usuario válido ");
        }
    }
}
