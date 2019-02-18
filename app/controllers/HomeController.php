<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index ($request, $response)
    {
        $opt = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION    
        );
        $dsn = "pgsql:host=localhost;dbname=direct-api";
        $dbuser="kulikov98";
        $dbpass="direct-api";
    
        $pdo = new \PDO($dsn, $dbuser, $dbpass, $opt);
    
        $stmt = $pdo->query("SELECT * FROM users");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    
        $params = [
            'result' => $result
        ];

        $user = $this->db->table('users')->find(1);
    
        return $this->view->render($response, 'home.twig', $params);
    }
}