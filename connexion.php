<?php
require_once 'config.php';

$error = '';

// Utilisateur déja connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = sanitize($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
           $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id, login, prenom, nom, password FROM utilisateurs WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_nom'] = $user['nom'];
                
                redirect('index.php');
            } else {
                $error = "Login ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la connexion : " . $e->getMessage();
        }
    }

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Module de Connexion</title>
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
                <h2>Connexion</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="message error">
                         <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="connexion.php">
                    <div class="form-group">
                        <label for="login">Login :</label>
                        <input type="text" id="login" name="login" required 
                               value="<?= isset($_POST['login']) ? sanitize($_POST['login']) : '' ?>"
                               placeholder="Votre nom d'utilisateur">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe :</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Votre mot de passe">
                    </div>
                    
                    <button type="submit" class="btn form-btn">Se connecter</button>
                    <button type="submit" class="btn form-btn">Se déconnecter</button>
                </form>
                
                <div class="link-container">
                    <p>Vous n'avez pas encore de compte ? <a href="inscription.php">Inscrivez-vous ici</a></p>
                </div>
                
                <!-- Informations de test -->
                <!-- <div style="margin-top: 2rem; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 8px; font-size: 0.9rem;">
                    <h4 style="margin-bottom: 0.5rem;">🔧 Compte de test :</h4>
                    <p><strong>Login :</strong> admin</p>
                    <p><strong>Mot de passe :</strong> admin</p>
                    <p style="margin-top: 0.5rem; font-style: italic;">Ce compte dispose des droits d'administration.</p>
                </div> -->
            </div>
        </main>
    </div>
</body>
</html>