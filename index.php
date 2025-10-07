<?php
// Fichier principal d'accueil du module de connexion
// - Charge les fonctions et la configuration depuis `config.php`
// - Affiche la navigation et le contenu en fonction de l'état de session
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module de Connexion - Accueil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <!-- Navigation principale : link selon l'état de session -->
            <nav>
                <div class="logo"> ModuleConnect</div>
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <?php if (isLoggedIn()): ?>
                        <!-- Liens visibles uniquement aux utilisateurs connectés -->
                        <li><a href="profil.php">Mon Profil</a></li>
                        <?php if (isAdmin()): ?>
                            <!-- Lien administration visible uniquement aux admins -->
                            <li><a href="admin.php">Administration</a></li>
                        <?php endif; ?>
                        <li><a href="deconnexion.php">Déconnexion</a></li>
                    <?php else: ?>
                        <!-- Liens pour visiteurs non authentifiés -->
                        <li><a href="connexion.php">Connexion</a></li>
                        <li><a href="inscription.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main class="main-content">
            <div class="welcome-section">
                <?php if (isLoggedIn()): ?>
                    <?php
                        // Obtenir en toute sécurité un nom d'affichage depuis la session.
                        // On utilise sanitize() pour éviter les injections HTML et on prévoit
                        // différentes clés qui peuvent contenir le nom selon l'implémentation.
                        if (!empty($_SESSION['user_prenom'])) {
                            // prénom stocké dans la session (préféré)
                            $displayName = sanitize($_SESSION['user_prenom']);
                        } elseif (!empty($_SESSION['user_login'])) {
                            // identifiant de connexion utilisé comme repli
                            $displayName = sanitize($_SESSION['user_login']);
                        } elseif (!empty($_SESSION['user_nom'])) {
                            // nom de famille utilisé comme repli
                            $displayName = sanitize($_SESSION['user_nom']);
                        } else {
                            // nom générique si aucune info disponible
                            $displayName = 'Utilisateur';
                        }
                    ?>
                    <h1>Bienvenue, <?= $displayName; ?> !</h1>
                    <p>Vous êtes connecté(e) avec succès. Vous pouvez maintenant accéder à votre profil et gérer vos informations personnelles.</p>
                <?php else: ?>
                    <h1>Bienvenue sur ModuleConnect</h1>
                    <p>Une plateforme sécurisée pour la gestion des comptes utilisateurs. Créez votre compte ou connectez-vous pour accéder à votre espace personnel.</p>
                <?php endif; ?>

                <!-- Section présentant des fonctionnalités / avantages -->
                <div class="features">
                    <div class="feature-card">
                        <div class="feature-icon">👤</div>
                        <h3>Gestion de Profil</h3>
                        <p>Créez et gérez facilement votre profil utilisateur avec toutes vos informations personnelles.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"></div>
                        <h3>Sécurité Avancée</h3>
                        <p>Vos données sont protégées par un système de sécurité robuste avec chiffrement des mots de passe.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"></div>
                        <h3>Interface Moderne</h3>
                        <p>Une interface utilisateur intuitive et moderne pour une expérience utilisateur optimale.</p>
                    </div>
                </div>

                <?php if (!isLoggedIn()): ?>
                    <div class="link-container">
                        <h3>Commencez dès maintenant !</h3>
                        <div class="btn-group">
                            <a href="inscription.php" class="btn"> Créer un compte</a>
                            <a href="connexion.php" class="btn btn-outline"> Se connecter</a>
                        </div>
                        <p style="margin-top: 1rem; color: #666; font-size: 0.95rem;">
                            Rejoignez notre plateforme sécurisée en quelques clics
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Pied de page : informations légales et copyright -->
        <footer style="text-align: center; padding: 2rem; color: rgba(255,255,255,0.8);">
            <p>&copy; 2025 ModuleConnect - Tous droits réservés</p>
        </footer>
    </div>
</body>
</html>