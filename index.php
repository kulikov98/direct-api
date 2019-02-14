<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$config = [
    'displayErrorDetails' => true,
    'addContentLengthHeader' => false
];

$app = new \Slim\App(["settings" => $config]);
$app->get('/', function (Request $request, Response $response, array $args) use ($app) {
    
    $response->getBody()->write('Hello world');

    return $response;
});
$app->run();



// echo '<pre>';
// print_r($app);
// echo '</pre>';