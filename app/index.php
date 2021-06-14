<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // "../"  __DIR__
$dotenv->safeLoad(); // load() safeLoad

require_once './middlewares/MWAutenticar.php';
require_once './middlewares/MWAccesos.php';
require_once './middlewares/MWLogger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/VentasController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ProductosController.php';
require_once './controllers/PedidosController.php';
require_once './controllers/UsuariosLogController.php';
require_once './controllers/PedidoUsuarioController.php';
require_once './controllers/EncuestasController.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
// Instantiate App
$app = AppFactory::create();

$app->setBasePath('/app');
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
// Add error middleware
$customErrorHandler = function (
  ServerRequestInterface $request,
  Throwable $exception,
  bool $displayErrorDetails,
  bool $logErrors,
  bool $logErrorDetails
) use ($app) {

  $payload = ['error' => $exception->getMessage()];

  $response = $app->getResponseFactory()->createResponse();
  $response->getBody()->write(
    json_encode($payload, JSON_UNESCAPED_UNICODE)
  );

  return $response;
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

// Eloquent
$container = $app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
  'driver'    => 'mysql',
  'host'      => $_ENV['MYSQL_HOST'],
  'database'  => $_ENV['MYSQL_DB'],
  'username'  => $_ENV['MYSQL_USER'],
  'password'  => $_ENV['MYSQL_PASS'],
  'charset'   => 'utf8',
  'collation' => 'utf8_unicode_ci',
  'prefix'    => '',
  'strict' => false,
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

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

  $group->put('/estado', \UsuarioController::class . ':cambiarEstado');

  //$group->put('/', \UsuarioController::class . ':ModificarUno');
})->add(\MWAccesos::class . ':soloAdministradores')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region MESAS
$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':traerTodos');

  $group->get('/{id}', \MesaController::class . ':traerUno');

  $group->get('/usos/{busqueda}', \MesaController::class . ':UsoDeMesas')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->get('/ventas/{busqueda}', \MesaController::class . ':VentasMesas')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->get('/facturas/{busqueda}', \MesaController::class . ':FacturasMesas')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->get('/facturasPorMesa/{id}', \MesaController::class . ':FacturasPorMesas')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->post('[/]', \MesaController::class . ':CargarUno')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->put('/estado', \MesaController::class . ':cambiarEstado');
})->add(\MWAccesos::class . ':administradoresYMozos')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region PRODUCTOS
$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('/csv', \ProductosController::class . ':descargaCSV')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->post('/csv', \ProductosController::class . ':cargaCSV')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->get('/pdf', \ProductosController::class . ':descargaPDF')
    ->add(\MWAccesos::class . ':soloAdministradores');

  $group->get('[/]', \ProductosController::class . ':traerTodos');

  $group->get('/{id}', \ProductosController::class . ':traerUno');

  $group->post('[/]', \ProductosController::class . ':CargarUno')
    ->add(\MWAccesos::class . ':soloAdministradores');
})->add(\MWAccesos::class . ':administradoresYMozos')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region PEDIDOS
$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('/productosVendidos/{busqueda}', \PedidosController::class . ':ProductosVendidos')
    ->add(\MWAccesos::class . ':soloAdministradores'); //

  $group->get('/fueraDeTiempo', \PedidosController::class . ':PedidosFueraDeTiempo')
    ->add(\MWAccesos::class . ':soloAdministradores'); //

  $group->get('/cancelados', \PedidosController::class . ':PedidosCancelados')
    ->add(\MWAccesos::class . ':soloAdministradores'); //

  $group->get('[/]', \PedidosController::class . ':traerTodos');

  $group->get('/sector/{sectorId}', \PedidosController::class . ':traerPendientesPorSector');

  $group->get('/listos', \PedidosController::class . ':traerPedidosListos')
    ->add(\MWAccesos::class . ':administradoresYMozos');

  $group->get('/listosPorCodigo', \PedidosController::class . ':traerPedidosListosPorCodigo')
    ->add(\MWAccesos::class . ':administradoresYMozos');

  $group->get('/{id}', \PedidosController::class . ':traerUno');

  $group->post('[/]', \PedidosController::class . ':CargarUno')
    ->add(\MWAccesos::class . ':administradoresYMozos');

  $group->put('/estado', \PedidosController::class . ':CambiarEstado');
})->add(\MWAccesos::class . ':todosLosUsuarios')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region Ventas
$app->group('/ventas', function (RouteCollectorProxy $group) {
  $group->put('/estado', \VentasController::class . ':modificarUno');
})->add(\MWAccesos::class . ':soloAdministradores')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region INFORMES 
$app->group('/informesEmpleados', function (RouteCollectorProxy $group) {
  $group->get('/usuariosLog', \UsuariosLogController::class . ':traerLosLogin'); //
  $group->get('/sector', \PedidoUsuarioController::class . ':operacionesPorSector'); //
  $group->get('/sectorPorEmpleado', \PedidoUsuarioController::class . ':operacionesPorSectorEmpleado'); //
  $group->get('/operacionesPorEmpleado', \PedidoUsuarioController::class . ':operacionesByEmpleado'); // 
})->add(\MWAccesos::class . ':soloAdministradores')
  ->add(\MWAutenticar::class . ':verificarUsuario');
#endregion

#region Encuestas
$app->group('/encuestas', function (RouteCollectorProxy $group) {
  $group->get('/comentarios/{mesaId}', \EncuestasController::class . ':ComentariosPorMesa'); //
  $group->post('/', \EncuestasController::class . ':CargarUno');
});
#endregion
$app->get('[/]', function (Request $request, Response $response) {
  $response->getBody()->write("Slim Framework 4 PHP");
  return $response;
});
$app->run();
