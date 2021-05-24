<?php
error_reporting(-1);
ini_set('display_errors', 1);

use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/MWAutenticar.php';
require_once './middlewares/MWAccesos.php';
require_once './middlewares/MWLogger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ProductosController.php';
require_once './controllers/PedidosController.php';
require_once './controllers/UsuariosLogController.php';
require_once './controllers/PedidoUsuarioController.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

#region LOGIN
$app->group('/login', function (RouteCollectorProxy $group) {
  $group->post('/', \UsuarioController::class . ':login');
})->add(\MWLogger::class . ':usuarioLogger');
#endregion

#region USUARIOS
$app->group('/usuarios', function (RouteCollectorProxy $group) {

  $group->get('[/]', \UsuarioController::class . ':traerTodos');

  $group->get('/{id}', \UsuarioController::class . ':traerUno');

  $group->post('[/]', \UsuarioController::class . ':cargarUno');

  $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');

  $group->post('/estado', \UsuarioController::class . ':cambiarEstado');

  //$group->put('/', \UsuarioController::class . ':ModificarUno');
})->add(\MWAccesos::class . ':soloAdministradores')->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region MESAS
$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':traerTodos');

  $group->get('/{id}', \MesaController::class . ':traerUno');

  $group->post('[/]', \MesaController::class . ':CargarUno')->add(\MWAccesos::class . ':soloAdministradores');

  $group->post('/estado', \MesaController::class . ':cambiarEstado');
})->add(\MWAccesos::class . ':administradoresYMozos')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region PRODUCTOS
$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductosController::class . ':traerTodos');

  $group->get('/{id}', \ProductosController::class . ':traerUno');

  $group->post('[/]', \ProductosController::class . ':CargarUno')->add(\MWAccesos::class . ':soloAdministradores');
})->add(\MWAccesos::class . ':administradoresYMozos')->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region PEDIDOS
$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidosController::class . ':traerTodos');

  $group->get('/{id}', \PedidosController::class . ':traerUno');

  $group->post('[/]', \PedidosController::class . ':CargarUno')->add(\MWAccesos::class . ':administradoresYMozos');

  $group->post('/estado', \PedidosController::class . ':CambiarEstado');
})->add(\MWAccesos::class . ':todosLosUsuarios')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region INFORMES EMPLEADOS
$app->group('/informesEmpleados', function (RouteCollectorProxy $group) {
  $group->get('/usuariosLog', \UsuariosLogController::class . ':traerLosLogin');
  $group->get('/sector', \PedidoUsuarioController::class . ':operacionesPorSector');
  $group->get('/sectorPorEmpleado', \PedidoUsuarioController::class . ':operacionesPorSectorEmpleado'); // falta
  $group->get('/operacionesPorEmpleado', \PedidoUsuarioController::class . ':operacionesByEmpleado');
})->add(\MWAccesos::class . ':soloAdministradores')->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

$app->run();
