<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

session_start();

use Respect\Validation\Validator as v;

$dbopts = parse_url(getenv('DATABASE_URL'));
$settings = [
    'displayErrorDetails' => true,
    'addContentLengthHeader' => false,
    'db' => [
        'driver' => 'pgsql',
        'user' => $dbopts["user"],
        'password' => $dbopts["pass"],
        'host' => $dbopts["host"],
        'port' => $dbopts["port"],
        'dbname' => ltrim($dbopts["path"], '/')
    ]
];

$app = new \Slim\App(["settings" => $settings]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};
$container['auth'] = function ($container) {
    return new \App\Auth\Auth;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
        'debug' => true
    ]);
    $view->addExtension(new \Twig\Extension\DebugExtension());


    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);

    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

$container['validator'] = function ($container) {
    return new \App\Validation\Validator;
};

$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};
$container['AuthController'] = function ($container) {
    return new \App\Controllers\Auth\AuthController($container);
};
$container['DashboardController'] = function ($container) {
    return new \App\Controllers\DashboardController($container);
};

$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};
$container['flash'] = function () {
    return new \Slim\Flash\Messages;
};


$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$app->add($container->csrf);

v::with('App\\Validation\\Rules');

require __DIR__ . '/../app/routes.php';
use \App\Middleware\AuthMiddleware;
use \App\Middleware\GuestMiddleware;

$app->get('/', 'HomeController:index')->setName('home');

$app->group('', function() {
    $this->get('/auth/signup/', 'AuthController:getSignUp')->setName('auth.signup');
    $this->post('/auth/signup/', 'AuthController:postSignUp');
    
    $this->get('/auth/signin/', 'AuthController:getSignIn')->setName('auth.signin');
    $this->post('/auth/signin/', 'AuthController:postSignIn');
})->add(new GuestMiddleware($container));

$app->group('', function () {
    $this->get('/auth/signout/', 'AuthController:getSignOut')->setName('auth.signout');

    $this->get('/auth/password/change/', 'AuthController:getChangePassword')->setName('auth.password.change');
    $this->post('/auth/password/change/', 'AuthController:postChangePassword');


    $this->get('/dashboard/', 'DashboardController:getShowAllAccounts')->setName('dashboard');
    $this->post('/dashboard/', 'DashboardController:postShowAllAccounts');

    $this->get('/dashboard/add/', 'DashboardController:getAddAccount')->setName('dashboard.add.account');
    $this->post('/dashboard/add/', 'DashboardController:postAddAccount');

    $this->get('/dashboard/add/resource/', 'DashboardController:getAddResource')->setName('dashboard.add.resource');
    $this->post('/dashboard/add/resource/', 'DashboardController:postAddResource');
})->add(new AuthMiddleware($container));

$app->run();
