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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // récupère les données du formulaire
    $login = sanitize($_POST['login'] ?? '');
    $prenom = sanitize($_POST['prenom'] ?? '');
    $nom = sanitize($_POST['nom'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // valide les champs obligatoires
    if (empty($login)) {
        $errors[] = "Le login est obligatoire";
    } elseif (strlen($login) < 3) {
        $errors[] = "Le login doit contenir au moins 3 caratères.";
    }

    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }

    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }

    // vérifie si le login est déjà utilisé par un autre utilisateur
    if (empty($errors) && $login !== $user['login']) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
            $stmt->execute([$login, $_SESSION['user_id']]);

            if ($stmt->fetch()) {
                $errors[] = "Ce login est déjà utilisé par un autre utilisateur.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification du login."
        }
    }

    // Validation du changement de mot de passe
    $changePassword = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_new_password)) {
        $changePassword = true;

        if (empty($current_password)) {
            $errors[] = "Veuillez saisir votre mot de passe actuel.";
        } else {
            // Vérification du mot de passe actuel
            try {
                $stmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userPassword = $stmt->fetchColumn();
                
                if (!password_verify($current_password, $userPassword)) {
                    $errors[] = "Le mot de passe actuel est incorrect.";
                }
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de la vérification du mot de passe.";
            }
        }
        
        if (empty($new_password)) {
            $errors[] = "Veuillez saisir un nouveau mot de passe.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        }
        
        if ($new_password !== $confirm_new_password) {
            $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
        }
    }
    
    // Mise à jour des informations
    if (empty($errors)) {
        try {
            if ($changePassword) {
                // Mise à jour avec nouveau mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, prenom = ?, nom = ?, password = ? WHERE id = ?");
                $result = $stmt->execute([$login, $prenom, $nom, $hashed_password, $_SESSION['user_id']]);
            } else {
                // Mise à jour sans changement de mot de passe
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, prenom = ?, nom = ? WHERE id = ?");
                $result = $stmt->execute([$login, $prenom, $nom, $_SESSION['user_id']]);
            }
            
            if ($result) {
                // Mise à jour des variables de session
                $_SESSION['user_login'] = $login;
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_nom'] = $nom;
                
                // Mise à jour des données locales pour l'affichage
                $user['login'] = $login;
                $user['prenom'] = $prenom;
                $user['nom'] = $nom;
                
                $success = true;
            } else {
                $errors[] = "Erreur lors de la mise à jour des informations.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Module de Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">ModuleConnect</div>
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="profil.php">Mon Profil</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin.php">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="deconnexion.php">Déconnexion</a></li>
                </ul>
            </nav>
        </header>

        <main class="main-content">
            <div class="user-info">
                <h2>Mon Profil</h2>
                <p>Connecté en tant que :<?= sanitize($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></p>
                    </div>

                    <div class="form-container">
                        <?php if ($succes) : ?>
                            <div class="message success">
                                Vos informations ont été mises à jour avec succès !
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="message error">
                                  Erreur(s) détectée(s) :
                                <ul style="margin-left: 20px; margin-top: 10px;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="profil.php">
                    <h3>Informations personnelles</h3>
                    
                    <div class="form-group">
                        <label for="login">Login :</label>
                        <input type="text" id="login" name="login" required 
                               value="<?= sanitize($user['login']) ?>"
                               placeholder="Votre nom d'utilisateur">
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom :</label>
                        <input type="text" id="prenom" name="prenom" required 
                               value="<?= sanitize($user['prenom']) ?>"
                               placeholder="Votre prénom">
                    </div>





