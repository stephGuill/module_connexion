<?php
//  Je configure la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'moduleconnexion');
define('DB_USER', 'root');
//  A modifier selon la configuration de WAMP
define('DB_PASS', '');

// Fonction de connexion à la base de données
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";chartset=utf8mb4";
        $option = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $option);
    } catch (PDOExeption $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// je démarre la session
session_start();

// La fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

}

//  La fonction pour rediriger
function rediect($url) {
    header("Location: $url");
    exit();   
}

// La fonction pour sécuriser
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}