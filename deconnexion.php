<?php
require_once 'config.php';

// destruction des variables session
$_SESSION = array();

// condition si une session cookie existe, on la supprime
if (init_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(seeion_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destruction de la session
session_destroy();

// redirige vers la page d'accueil
redirect('index.php');
?>