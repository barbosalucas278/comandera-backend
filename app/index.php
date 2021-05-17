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
require_once './controllers/MesaController.php';
require_once './controllers/ProductosController.php';
require_once './controllers/PedidosController.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

$app->group('/usuarios', function (RouteCollectorProxy $group) {

  $group->get('[/]', \UsuarioController::class . ':traerTodos'); //

  $group->get('/{id}', \UsuarioController::class . ':traerUno'); //

  $group->post('[/]', \UsuarioController::class . ':CargarUno'); //

  /*$group->post('/login', \UsuarioController::class . ':Login');

  $group->delete('/{id}', \UsuarioController::class . ':BorrarUno'); //

  $group->put('/', \UsuarioController::class . ':ModificarUno');*/
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':traerTodos'); //

  $group->get('/{id}', \MesaController::class . ':traerUno'); //

  $group->post('[/]', \MesaController::class . ':CargarUno'); //
});

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductosController::class . ':traerTodos'); //

  $group->get('/{id}', \ProductosController::class . ':traerUno'); //

  $group->post('[/]', \ProductosController::class . ':CargarUno'); //
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidosController::class . ':traerTodos'); //

  $group->get('/{id}', \PedidosController::class . ':traerUno'); // 

  $group->post('[/]', \PedidosController::class . ':CargarUno'); //
});

$app->run();
