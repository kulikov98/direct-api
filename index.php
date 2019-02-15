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
    
    $opt = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION    
    );
    $dsn = "pgsql:host=localhost;dbname=direct-api";
    $dbuser="kulikov98";
    $dbpass="direct-api";

    $pdo = new \PDO($dsn, $dbuser, $dbpass, $opt);

    //Creating first table width test data
    $createTable="CREATE TABLE users (
        id int PRIMARY KEY GENERATED BY DEFAULT AS IDENTITY,
        name varchar(255),
        email varchar(255),
        api_key varchar(255)
    );
    INSERT INTO users (name, email, api_key) VALUES ('Qpiter', 'qpiter-shop@yandex.ru', 'AQAAAAAYme7MAAV9akPT5xNsFUgssz7RLnrF78w')";

    //$pdo->exec($createTable);

    $stmt = $pdo->query("SELECT * FROM users");
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);

    $response->getBody()->write("<table><tr><td>".$result['name']."</td><td>".$result['email']."</td><td>".$result['api_key']."</td></tr></table>");

    return $response;
});
$app->run();



// echo '<pre>';
// print_r($app);
// echo '</pre>';