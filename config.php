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
        // corrected charset param name
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $option = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $option);
    } catch (PDOException $e) {
        // show a useful message during development
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// je démarre la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// La fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

//  La fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit();
}

// La fonction pour sécuriser
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// La fonction pour vérifier si l'utilisateur est administrateur
// The project might store admin information in different session keys.
// Check common patterns: 'user_role' (string), 'user_is_admin' (bool/int), 'is_admin'.
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }

    // direct boolean flag
    if (isset($_SESSION['user_is_admin'])) {
        return (bool) $_SESSION['user_is_admin'];
    }

    if (isset($_SESSION['is_admin'])) {
        return (bool) $_SESSION['is_admin'];
    }

    // role string: 'admin' or 'administrator'
    if (isset($_SESSION['user_role'])) {
        $role = strtolower((string) $_SESSION['user_role']);
        return in_array($role, ['admin', 'administrator', 'superadmin', 'super_admin'], true);
    }

    // fallback: if there's a user_level or user_rank numeric field
    if (isset($_SESSION['user_level'])) {
        return (int) $_SESSION['user_level'] >= 9; // assume 9+ admin
    }

    return false;
}