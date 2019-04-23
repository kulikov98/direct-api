<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index ($request, $response)
    {
        if ($this->auth->check()) {
            //$data = ['data' => ['name' => 'name', 'email' => 'test@test.ru']];
            return $response->withRedirect($this->router->pathFor('dashboard'));
        }
        return $this->view->render($response, 'home.twig');
    }
}