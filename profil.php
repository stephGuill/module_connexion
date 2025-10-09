<?php
require_once 'config.php';

// vérifie la connexion
if (!isLoggedIn()) {
    redirect('connexion.php');
}

$error = [];
$succes = false;

// récupère des informations actuelles de l'utilisateur
try {
    $pdo = getDbConnection();
    $smt = $pdo->prepare("SELECT login, prenom, nom, FROM utilisateurs WHERE id = ?");
    $smt->execute([$_SESSION['user_id']]);
    $user = $smt->fetch();

    if (!$usuer) {
        redirect('deconnexion.php');
    }
}  catch (PDOException $e) {
    die("Erreur lors de la récupération des données utilisateurs.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {}