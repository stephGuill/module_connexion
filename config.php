<?php
// configuration centrale et fonctions utilitaires
// Ce fichier est inclus dans les pages de l'application pour fournir :
// - la configuration de la base de données (constantes)
// - une fonction de connexion PDO réutilisable
// - des helpers de session (isLoggedIn, isAdmin)
// - des helpers de sécurité (sanitize)

// --- paramètres de connexion à la base de données ---
// Je modifie ces constantes selon votre environnement WAMP/MAMP/serveur
define('DB_HOST', 'localhost');
define('DB_NAME', 'moduleconnexion');
define('DB_USER', 'root');
// Mot de passe de la base (vide par défaut en configuration WAMP locale)
define('DB_PASS', '');


// --- Fonction de connexion à la base de données ---
// Je retourne un objet PDO configuré avec les options recommandées.
// J'utilise getDbConnection() au lieu de créer plusieurs connexions PDO partout.
function getDbConnection() {
    try {
        // Le paramètre charset est important pour éviter les problèmes d'encodage
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $option = [
            // Je lance des exceptions en cas d'erreur SQL facilite le debug
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Je retourne des tableaux associatifs par défaut
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Ne pas émuler les requêtes préparées pour des raisons de sécurité
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $option);
    } catch (PDOException $e) {
        // En production, remplacer ce die() par un log et un message générique.
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}


// --- gestion de session ---
// Je démarre la session uniquement si elle n'est pas déjà démarrée.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// --- Helpers de session et sécurité ---

// isLoggedIn : indique si un utilisateur est connecté (présence d'un user_id en session)
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// redirect : utilitaire simple pour rediriger et arrêter l'exécution
function redirect($url) {
    header("Location: $url");
    exit();
}

// sanitize : nettoie une donnée pour affichage HTML (prévention XSS)
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}


// isAdmin : vérifie si l'utilisateur connecté a les droits d'administration.
// Plusieurs projets stockent l'information d'administration différemment ;
// cette fonction essaye plusieurs clés de session courantes pour être robuste.
function isAdmin() {
    // si pas connecté, pas admin
    if (!isLoggedIn()) {
        return false;
    }

    // Cas 1 : flag booléen explicite
    if (isset($_SESSION['user_is_admin'])) {
        return (bool) $_SESSION['user_is_admin'];
    }

    // Cas 2 : autre clé booléenne
    if (isset($_SESSION['is_admin'])) {
        return (bool) $_SESSION['is_admin'];
    }

    // Cas 3 : rôle sous forme de chaîne (ex: 'admin', 'administrator')
    if (isset($_SESSION['user_role'])) {
        $role = strtolower((string) $_SESSION['user_role']);
        return in_array($role, ['admin', 'administrator', 'superadmin', 'super_admin'], true);
    }

    // Cas 4 : niveau numérique (par ex. 9+ est administrateur)
    if (isset($_SESSION['user_level'])) {
        return (int) $_SESSION['user_level'] >= 9;
    }

    // Par défaut, non administrateur
    return false;
}

// -----------------------------------------------------------------------------
// Exemple d'utilisation : comment initialiser la session après authentification
// (à placer dans votre script de connexion, ex: connexion.php après vérification)
// -----------------------------------------------------------------------------
/*
// Exemple : après avoir vérifié l'email/mot de passe de l'utilisateur :
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_login'] = $user['login'];
$_SESSION['user_prenom'] = $user['prenom']; // facultatif
// Définir un indicateur d'admin selon votre logique
$_SESSION['user_role'] = $user['role']; // ex: 'user' ou 'admin'
// ou
$_SESSION['user_is_admin'] = ($user['role'] === 'admin');

// Puis rediriger vers la page d'accueil :
redirect('index.php');
*/

// Note de sécurité :
// - En production, évitez d'exposer des messages d'erreur détaillés.
// - Utilisez des logs sécurisés (fichiers ou systèmes de logging) et affichez
//   des messages génériques à l'utilisateur.
// - Protégez la session (cookie secure, SameSite, régénération d'ID après login).