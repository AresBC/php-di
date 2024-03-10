<?php declare(strict_types=1);


use App\Debug\Debug;
use App\Logger\ILogger;
use App\Logger\Logger;
use Slim\Factory\AppFactory;
use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


set_error_handler(/** @throws ErrorException */ function ($errno, $errstr, $errfile, $errline) {
    if ($errno === E_WARNING) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    return false;
});


Debug::$logger = new Logger();
Debug::$logger->addHandler(Logger::DEBUG, Debug::getDefaultLogHandler());
Debug::$logger->addHandler(Logger::INFO, Debug::getDefaultLogHandler());


function view(string $path, ...$params): string
{
    extract($params);
    ob_start();
    include __DIR__ . '/assets/view/page/' . $path . '.php';
    $renderedView = ob_get_clean();
    if (false === $renderedView) {
        throw new RuntimeException('View rendering went wrong!');
    }

    return $renderedView;
}


$container = new Container();
$container->set(ILogger::class, function () {
    return new Logger();
});


class MyCustomMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Call the next middleware and get the response
        $response = $handler->handle($request);

        // Modify the response to add a custom header
        return $response->withHeader('X-My-Custom-Header', 'MyValue');
    }
}

class ExampleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // RouteContext aus der Anfrage extrahieren
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if (!is_null($route)) {
            $routeName = $route->getName();
            $routeArguments = $route->getArguments();

            // Verwende $routeName und $routeArguments wie benötigt
        }

        var_dump($handler);
//        $logger->info('ROUTE PATTERN', $route->getPattern());

        // Führe den Rest der Middleware-Stack aus
        return $handler->handle($request);
    }
}


AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
//$app->add(ExampleMiddleware::class);
$app->add(new MyCustomMiddleware());



$app->get('/', function (Request  $request, Response $response) {

    Debug::mark('BINGO', [
        'test1' => 123,
        'test2' => 123,
        'test3' => 123,
        'test4' => 123,
    ]);

    $response->getBody()->write(view('home'));
    return $response;
});

$app->get('/hello2/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});
$app->get('/hello/{id}', function (Request  $request, Response $response) {

    var_dump(RouteContext::fromRequest($request)->getRoute()->getPattern());

    Debug::mark('BINGO', [
        'test1' => 123,
        'test2' => 123,
        'test3' => 123,
        'test4' => 123,
    ]);

    $response->getBody()->write(view('home'));
    return $response;
})->add(new ExampleMiddleware());


$routes = array_map(fn($route) => $route->getPattern(), $app->getRouteCollector()->getRoutes());

$logger = $container->get(ILogger::class);
$routes = array_map(fn($route) => $route->getPattern(), $app->getRouteCollector()->getRoutes());
$logger->info('ROUTES ', $routes);

$app->run();

