<?php
require_once 'config.php';

// vérifies la connexion et les droits admin
if (!isLoggedIn()) {
    redirect('connexion.php');

}

if (!isAdmin()) {
    die("Accès refusé. Cette page est réservée aux administrateurs.");

}
$message = '';

// gestion de la suppression d'utilisateurs
 if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userid = (int)$_GET['id'];

    // empêche la suppression de l'admin lui même
    if ($userId !== $_SESSION['user_id']) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id =  AND login != 'admin'");
            $result = $stmt->execute([$uers_id]);

            if ($result && $stmt->rowCount() > 0) {
                $message = "Utilisateur supprimé avec succès.";
            } else {
                $message = "Erreur lors de la suppression ou utilisateur introuvable.";
            }
        } catch (PDOExeption $e) {
            $message = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        $message = "Vous ne pouvez pas supprimer votre propre compte.";
    }
 } 

//  récupère tous les utilisateurs
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT id, login, prénom, nom, created_at FROM utilisateurs ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des utilisateurs : " .$e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">