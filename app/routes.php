<?php

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
