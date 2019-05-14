<?php
spl_autoload_register(function ($class_name) {
  include $class_name . '.php';
});

$request_uri = substr($_SERVER['REQUEST_URI'], 0, 6);

if(!@include(__DIR__."/config.php")) {
  if(!($request_uri == "/setup")) {
    header('Location: /setup.php');
    die("Redirecting...");
  }
}

if(@include(__DIR__."/config.php")) {
  if($request_uri == "/setup") {
    header('Location: /');
    die("Redirecting...");
  }
}

if(!($request_uri == "/setup")) {
  $pdo = new \PDO('mysql:dbname='.DB_NAME.';host='.DB_HOST.';charset=utf8mb4', DB_USER, DB_PASS);
  $auth = new \Bloggr\Auth($pdo);

  if ($auth->isLoggedIn()) {
    echo 'Eingeloggt als <b>'.$auth->getUsernameById($auth->getId()).'</b>';
  }
}
