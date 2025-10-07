<?php
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
            <nav>
                <div class="logo"> ModuleConnect</div>
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="profil.php">Mon Profil</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin.php">Administration</a></li>
                        <?php endif; ?>
                        <li><a href="deconnexion.php">Déconnexion</a></li>
                    <?php else: ?>
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
                        // Safely obtain a display name from session with fallbacks
                        if (!empty($_SESSION['user_prenom'])) {
                            $displayName = sanitize($_SESSION['user_prenom']);
                        } elseif (!empty($_SESSION['user_login'])) {
                            $displayName = sanitize($_SESSION['user_login']);
                        } elseif (!empty($_SESSION['user_nom'])) {
                            $displayName = sanitize($_SESSION['user_nom']);
                        } else {
                            $displayName = 'Utilisateur';
                        }
                    ?>
                    <h1>Bienvenue, <?= $displayName; ?> !</h1>
                    <p>Vous êtes connecté(e) avec succès. Vous pouvez maintenant accéder à votre profil et gérer vos informations personnelles.</p>
                <?php else: ?>
                    <h1>Bienvenue sur ModuleConnect</h1>
                    <p>Une plateforme sécurisée pour la gestion des comptes utilisateurs. Créez votre compte ou connectez-vous pour accéder à votre espace personnel.</p>
                <?php endif; ?>

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

        <!-- Section Documentation séparée -->
        <div class="container">
            <div class="documentation-section">
                <h3> Documentation du Projet</h3>
                <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                    Accédez à la documentation complète et aux outils de développement
                </p>
                
                <div class="doc-links">
                    <a href="rapport.html" target="_blank" class="doc-link">
                        <span class="icon"></span>
                        <div class="title">Rapport Complet</div>
                        <div class="description">Documentation technique détaillée avec code</div>
                    </a>
                    
                    <a href="generate-pdf.php" target="_blank" class="doc-link">
                        <span class="icon"></span>
                        <div class="title">Version PDF</div>
                        <div class="description">Télécharger le rapport au format PDF</div>
                    </a>
                    
                    <a href="SYNTHESE.md" target="_blank" class="doc-link">
                        <span class="icon"></span>
                        <div class="title">Synthèse Technique</div>
                        <div class="description">Résumé exécutif et architecture</div>
                    </a>
                </div>
                
                <?php if (isAdmin()): ?>
                    <div class="admin-tools">
                        <strong> Outils d'Administration</strong>
                        <a href="diagnostic.php"> Diagnostic</a>
                        <a href="fix-admin.php"> Réparation Admin</a>
                        <a href="init.php"> Réinstallation</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <footer style="text-align: center; padding: 2rem; color: rgba(255,255,255,0.8);">
            <p>&copy; 2025 ModuleConnect - Tous droits réservés</p>
        </footer>
    </div>
</body>
</html>