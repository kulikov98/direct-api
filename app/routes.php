<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', 'HomeController:index');

$app->get('/create', function (Request $request, Response $response, array $args) {
    
});
