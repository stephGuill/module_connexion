<?php
require_once 'config.php';

$errors = [];
$success = false;

// Si l'utilisateur est déjà connecté, redirection vers l'accueil
if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $login = sanitize($_POST['login'] ?? '');
    $prenom = sanitize($_POST['prenom'] ?? '');
    $nom = sanitize($_POST['nom'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation des champs
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
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Vérification si le login existe déjà
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");
            $stmt->execute([$login]);
            
            if ($stmt->fetch()) {
                $errors[] = "Ce login est déjà utilisé.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification du login.";
        }
    }
    
    // Insertion en base de données
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, prenom, nom, password) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$login, $prenom, $nom, $hashed_password]);
            
            if ($result) {
                $success = true;
                // Redirection vers la page de connexion après 2 secondes
                header("refresh:2;url=connexion.php");
            } else {
                $errors[] = "Erreur lors de la création du compte.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Module de Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo"> ModuleConnect</div>
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="connexion.php">Connexion</a></li>
                    <li><a href="inscription.php">Inscription</a></li>
                </ul>
            </nav>
        </header>

        <main class="main-content">
            <div class="form-container">
                <h2>Créer un compte</h2>
                
                <?php if ($success): ?>
                    <div class="message success">
                         Votre compte a été créé avec succès ! Redirection vers la page de connexion...
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
                
                <?php if (!$success): ?>
                    <form method="POST" action="inscription.php">
                        <div class="form-group">
                            <label for="login">Login :</label>
                            <input type="text" id="login" name="login" required 
                                   value="<?= isset($_POST['login']) ? sanitize($_POST['login']) : '' ?>"
                                   placeholder="Votre nom d'utilisateur">
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" id="prenom" name="prenom" required 
                                   value="<?= isset($_POST['prenom']) ? sanitize($_POST['prenom']) : '' ?>"
                                   placeholder="Votre prénom">
                        </div>
                        
                        <div class="form-group">
                            <label for="nom">Nom :</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?= isset($_POST['nom']) ? sanitize($_POST['nom']) : '' ?>"
                                   placeholder="Votre nom de famille">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe :</label>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Au moins 6 caractères">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe :</label>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   placeholder="Répétez votre mot de passe">
                        </div>
                        
                        <button type="submit" class="btn form-btn">Créer mon compte</button>
                    </form>
                <?php endif; ?>
                
                <div class="link-container">
                    <p>Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous ici</a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>