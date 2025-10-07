-- Module de connexion - Structure de base de données
-- Créé le 27 septembre 2025

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS moduleconnexion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE moduleconnexion;

-- Création de la table utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(191) NOT NULL UNIQUE,
    prenom VARCHAR(255) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion de l'utilisateur administrateur
-- Mot de passe hashé avec password_hash() pour "admin"
INSERT INTO utilisateurs (login, prenom, nom, password) VALUES 
('admin', 'admin', 'admin', '$2y$10$Ww6L5dFhyOnQOJhVnBBpCe4F4qJ8xPr9k3JvCEXvPyQ9vKA4xI7W.');

-- Note: Le mot de passe haché correspond à "admin"
-- Il est recommandé d'utiliser password_hash() en PHP plutôt que de stocker le mot de passe en clair