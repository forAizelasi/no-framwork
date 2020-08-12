<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use App\HelloWorld;
use FastRoute\RouteCollector;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;
use Relay\Relay;
use Laminas\Diactoros\Response;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\ServerRequestFactory;
use function DI\create;
use function DI\get;
use function FastRoute\simpleDispatcher;

require_once dirname (__DIR__).'/vendor/autoload.php';


$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations (false);
$containerBuilder->useAutowiring (false);
$containerBuilder->addDefinitions ([
    HelloWorld::class => create (HelloWorld::class)->constructor (get ('Foo'),get ('Response')),
    'Foo'   =>  'bar',
    'Response'  =>  function() {
    return new Response();
    }
]);

$container = $containerBuilder->build ();

$routes = simpleDispatcher (function (RouteCollector $r) {
    $r->get ('/hello',HelloWorld::class);
});

$middlewareQueue[] = new FastRoute($routes);
$middlewareQueue[] = new RequestHandler($container);

$requestHandler = new Relay($middlewareQueue);
$response = $requestHandler->handle (ServerRequestFactory::fromGlobals ());

$emitter = new SapiEmitter();
$emitter->emit ($response);
