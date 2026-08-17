<?php
session_start();

define('DB_HOTE', 'localhost');
define('DB_NOM', 'merrytti_db');
define('DB_UTILISATEUR', 'root');
define('DB_MOT_DE_PASSE', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOTE . ";dbname=" . DB_NOM . ";charset=utf8mb4", DB_UTILISATEUR, DB_MOT_DE_PASSE, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Erreur de connexion à la base de données']);
    exit;
}

// Protection CSRF disponible pour tous les API
require_once __DIR__ . '/csrf.php';