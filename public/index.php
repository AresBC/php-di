<?php declare(strict_types=1);


use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\MemoryUsageProcessor;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_error_handler(/**
 * @throws ErrorException
 */ function ($errno, $errstr, $errfile, $errline) {
    if ($errno === E_WARNING) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    return false;
});

class MyProcessor
{
    /**
     * @param array $record
     * @return array
     */
    public function __invoke(LogRecord $record)
    {
        var_dump($record);
        $info = $this->findFile();
        $record->extra['file_info'] = $info['file'] . ':' . $info['line'];
        return $record;
    }

    public function findFile()
    {
        $debug = debug_backtrace();
        return [
            'file' => $debug[3] ? basename($debug[3]['file']) : '',
            'line' => $debug[3] ? $debug[3]['line'] : ''
        ];
    }
}





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


// Erstelle einen DI-Container
$container = new Container();
// Register Monolog service with the container
$container->set('logger', function () {
    $logger = new Logger('DEBUG');
    $file_handler = new StreamHandler('C:\laragon\tmp\debug.log', Level::Debug);
    $file_handler->pushProcessor(new MyProcessor());
    $file_handler->pushProcessor(new MemoryUsageProcessor());
// the default date format is "Y-m-d\TH:i:sP"
    $dateFormat = "Y-m-d\H:i:s";
// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
    $output = "\n[%datetime%] %channel%.%level_name%\n\tmessage | %message%\n\tcontext | %context%\n\tinfo    | %extra%\n";
// finally, create a formatter
    $formatter = new LineFormatter($output, $dateFormat);
    $file_handler->setFormatter($formatter);
    $logger->pushHandler($file_handler);
    return $logger;
});
$container->set('MyService', function () {
    return new class {
        function foo(): void
        {
            var_dump('BINGO');
        }
    };
});


// Konfiguriere Slim, um den DI-Container zu verwenden
AppFactory::setContainer($container);

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', function (Request $request, Response $response) {

    $logger = $this->get('logger');
//    $logger->info("Slim-Skeleton '/' route");

    $response->getBody()->write(view('home'));
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$logger = $container->get('logger');
$logger->info(json_encode($app));
$routes = array_map(fn($route) => $route->getPattern(), $app->getRouteCollector()->getRoutes());
var_dump($routes);
$app->run();