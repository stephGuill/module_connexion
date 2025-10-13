<?php
require_once 'config.php';

// -------------------------------------------------------------
// Page profil : permet à l'utilisateur connecté de modifier
// son login, prénom, nom et éventuellement son mot de passe.
// -------------------------------------------------------------

// Vérifie que l'utilisateur est connecté ; sinon redirige vers la page de connexion
if (!isLoggedIn()) {
    redirect('connexion.php');
}

$errors = [];      // tableau d'erreurs pour l'affichage
$success = false;  // indicateur de succès de la mise à jour

// Récupère les informations actuelles de l'utilisateur depuis la BDD
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT login, prenom, nom, password FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si l'utilisateur n'existe plus en base, on force la déconnexion
    if (!$user) {
        redirect('deconnexion.php');
    }
} catch (PDOException $e) {
    // Erreur de récupération : message générique pour ne pas divulguer d'infos sensibles
    die("Erreur lors de la récupération des données utilisateurs.");
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère et assainit les entrées
    $login = sanitize($_POST['login'] ?? '');
    $prenom = sanitize($_POST['prenom'] ?? '');
    $nom = sanitize($_POST['nom'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Validation des champs obligatoires
    if (empty($login)) {
        $errors[] = "Le login est obligatoire.";
    } elseif (strlen($login) < 3) {
        $errors[] = "Le login doit contenir au moins 3 caractères.";
    }

    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }

    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }

    // Vérifie si le login est déjà utilisé par un autre utilisateur
    if (empty($errors) && $login !== ($user['login'] ?? '')) {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
            $checkStmt->execute([$login, $_SESSION['user_id']]);

            if ($checkStmt->fetch()) {
                $errors[] = "Ce login est déjà utilisé par un autre utilisateur.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification du login.";
        }
    }

    // Validation du changement de mot de passe (optionnel)
    $changePassword = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_new_password)) {
        $changePassword = true;

        if (empty($current_password)) {
            $errors[] = "Veuillez saisir votre mot de passe actuel.";
        } else {
            // Vérification du mot de passe actuel
            try {
                $pwStmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE id = ?");
                $pwStmt->execute([$_SESSION['user_id']]);
                $userPassword = $pwStmt->fetchColumn();

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

    // Mise à jour des informations si aucune erreur
    if (empty($errors)) {
        try {
            if ($changePassword) {
                // Mise à jour avec nouveau mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, prenom = ?, nom = ?, password = ? WHERE id = ?");
                $result = $updateStmt->execute([$login, $prenom, $nom, $hashed_password, $_SESSION['user_id']]);
            } else {
                // Mise à jour sans changement de mot de passe
                $updateStmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, prenom = ?, nom = ? WHERE id = ?");
                $result = $updateStmt->execute([$login, $prenom, $nom, $_SESSION['user_id']]);
            }

            if ($result) {
                // Mise à jour des variables de session pour refléter les nouvelles valeurs
                $_SESSION['user_login'] = $login;
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_nom'] = $nom;

                // Mise à jour de la variable locale $user pour l'affichage
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
                <p>Connecté en tant que : <?= sanitize(($user['prenom'] ?? $_SESSION['user_prenom'] ?? '') . ' ' . ($user['nom'] ?? $_SESSION['user_nom'] ?? '')) ?></p>
            </div>

            <div class="form-container">
                <?php if ($success) : ?>
                    <div class="message success">
                        Vos informations ont été mises à jour avec succès !
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        Erreur(s) détectée(s) :
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?= sanitize($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="profil.php">
                    <h3>Informations personnelles</h3>

                    <div class="form-group">
                        <label for="login">Login :</label>
                        <input type="text" id="login" name="login" required
                               value="<?= sanitize($user['login'] ?? '') ?>"
                               placeholder="Votre nom d'utilisateur">
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom :</label>
                        <input type="text" id="prenom" name="prenom" required
                               value="<?= sanitize($user['prenom'] ?? '') ?>"
                               placeholder="Votre prénom">
                    </div>

                    <div class="form-group">
                        <label for="nom">Nom :</label>
                        <input type="text" id="nom" name="nom" required
                               value="<?= sanitize($user['nom'] ?? '') ?>"
                               placeholder="Votre nom de famille">
                    </div>

                    <h3 style="margin-top: 2rem;">Changer le mot de passe (optionnel)</h3>
                    <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">
                        Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe.
                    </p>

                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel :</label>
                        <input type="password" id="current_password" name="current_password"
                               placeholder="Votre mot de passe actuel">
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe :</label>
                        <input type="password" id="new_password" name="new_password"
                               placeholder="Au moins 6 caractères">
                    </div>

                    <div class="form-group">
                        <label for="confirm_new_password">Confirmer le nouveau mot de passe :</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password"
                               placeholder="Répétez le nouveau mot de passe">
                    </div>

                    <button type="submit" class="btn form-btn">Mettre à jour mon profil</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
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

                    <div class="form_group">
                        <label for="nom">Nom :</label>
                        <input type="text" id="nom" name="nom" required
                               value="<?= sanitize($user['nom']) ?>"
                               placeholder="Votre nom de famille">
                    </div>

                    <h3 style="margin-top: 2rem;">Changer le mot de passe (optionnel)</h3>
                    <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">
                        Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe.
                    </p>

                     <div class="form-group">
                        <label for="current_password">Mot de passe actuel :</label>
                        <input type="password" id="current_password" name="current_password" 
                               placeholder="Votre mot de passe actuel">
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe :</label>
                        <input type="password" id="new_password" name="new_password" 
                               placeholder="Au moins 6 caractères">
                    </div>

                     <div class="form-group">
                        <label for="confirm_new_password">Confirmer le nouveau mot de passe :</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" 
                               placeholder="Répétez le nouveau mot de passe">
                    </div>
                    
                    <button type="submit" class="btn form-btn">Mettre à jour mon profil</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>




