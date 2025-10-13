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
    <head>
        <meta chartset="UTF-8">
        <meta name="Viewport" content="width=device-width, initial-scale=1.0">
        <title>Adinistration - Module de connexion</title>
        <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo"> ModuleConnect</div>
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="profil.php">Mon Profil</a></li>
                    <li><a href="admin.php">administrateurs</a></li>
                    <li><a href="deconnexion.php">Déconnexion</a></li>
                </ul> 
            </nav>
        </header>  

        <main class="main-content">
            <div class="user_info">
                <h2> Administration</h2>
                <p>Connecté en tant qu'administrateur : <?= snitize($_SESSION['user_prenom'] . ' ' .$_SESSION['user_nom']); ?></p>
    </div>

    <?php if (!empty($message)) : ?>
          <div class="message <?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
                    <?= $message ?>
          </div>
          <?php endif; ?>
          <h3>Liste des utilisateurs (<?= count($users) ?> utilisateur<?= count($users) > 1 ? 's' : '' ?>)</h3>
          
          <?php if (empty($users)) : ?>
            <p>Aucun utilisateur trouvé.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>login</th>
                                <th>Prenom</th>
                                <th>Nom</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <?= sanitize($user['login']) ?>
                                        <?php if ($user['login'] === 'admin') : ?>
                                            <span style="color: #667eea; font-weight: bold;"></span>
                                            <?php endif; ?>
                                    </td>
                                    <td><?= sanitize($user['prenom']) ?></td>
                                    <td><?= sanitize($user['nom']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span style="color: #666; font-style: italic;">C'est vous</span>
                                        <?php elseif ($user['login'] === 'admin'): ?>
                                            <span style="color: #666; font-style: italic;">Administrateur</span>
                                            <?php else: ?>
                                                <a href="?action=delete&id=<?= $user['id'] ?>"
                                                style="color: #dc3545; text-decoration : none; font-weight: bold;"
                                                title="Supprimer l'utilisateur <?= sanitize($user['prenom'] . ' ' . $user['nom']) ?>">
                                                Supprimer
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div style="margin-top: 2rem; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 8px;">
                <h4>Information :</h4>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>Seuls les comptes non-administrateurs peuvent être supprimés</li>
                    <li>Vous ne pouvez pas supprimer votre propre compte</li>
                    <li>Les suppressions sont définitives</li>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>


