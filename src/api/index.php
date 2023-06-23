<?php
// Create a new instance of the Router

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");

include('Router.php');

$router = new Router();

// Route the request
$router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
