<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

$app->group('/usuarios', function (RouteCollectorProxy $group) {

  $group->get('[/]', \UsuarioController::class . ':traerTodos');

  $group->get('/{id}', \UsuarioController::class . ':traerUno');

  //$group->post('/login', \Usuario::class . ':Login');

  $group->post('[/]', \UsuarioController::class . ':CargarUno');

  $group->delete('[/]', \UsuarioController::class . ':BorrarUno');

  $group->put('[/]', \UsuarioController::class . ':ModificarUno');
});


$app->run();
