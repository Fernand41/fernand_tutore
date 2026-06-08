-- Schema SQL pour GoûtsBénin
-- Base de données complète pour gestion des utilisateurs, recettes, commentaires, favoris, notes et sécurité.

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    est_actif TINYINT(1) NOT NULL DEFAULT 1,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories_recettes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recettes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    ingredients TEXT DEFAULT NULL,
    etapes TEXT DEFAULT NULL,
    difficulte ENUM('facile','moyen','difficile') NOT NULL DEFAULT 'moyen',
    temps_prep INT NOT NULL DEFAULT 0,
    temps_cuisson INT NOT NULL DEFAULT 0,
    nb_personnes INT NOT NULL DEFAULT 1,
    image VARCHAR(255) DEFAULT NULL,
    video_url VARCHAR(255) DEFAULT NULL,
    id_categorie INT NOT NULL,
    id_auteur INT NOT NULL,
    statut ENUM('en_attente','publie','rejete','brouillon') NOT NULL DEFAULT 'en_attente',
    note_moyenne DECIMAL(3,1) NOT NULL DEFAULT 0,
    nb_notes INT NOT NULL DEFAULT 0,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_publication DATETIME DEFAULT NULL,
    FOREIGN KEY (id_categorie) REFERENCES categories_recettes(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_auteur) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_recette INT NOT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_recette (id_user, id_recette),
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_recette) REFERENCES recettes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_recette INT NOT NULL,
    id_user INT DEFAULT NULL,
    nom_utilisateur VARCHAR(100) DEFAULT NULL,
    contenu TEXT NOT NULL,
    statut ENUM('en_attente','publie','rejete') NOT NULL DEFAULT 'en_attente',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_recette) REFERENCES recettes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_recette INT NOT NULL,
    id_user INT DEFAULT NULL,
    valeur TINYINT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_note_user_recette (id_user, id_recette),
    FOREIGN KEY (id_recette) REFERENCES recettes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_expiration DATETIME NOT NULL,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    reussite TINYINT(1) NOT NULL DEFAULT 0,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
