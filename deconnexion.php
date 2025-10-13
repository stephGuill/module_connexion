<?php
require_once 'config.php';

// destruction des variables session
$_SESSION = array();

// condition si une session cookie existe, on la supprime
// Vérifier si PHP utilise des cookies pour la session avant de supprimer le cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Supprimer le cookie de session côté client en le réinitialisant dans le passé
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destruction de la session
session_destroy();

// redirige vers la page d'accueil
redirect('index.php');

?>