<?php
require 'config.php';
require 'fonctions.php';
header('Content-Type: application/json');

if (isset($_SESSION['utilisateur_id'])) {
    echo json_encode([
        'connecte' => true,
        'nom_utilisateur' => $_SESSION['nom_utilisateur'],
        'role' => $_SESSION['role']
    ]);
} else {
    // Même si pas connecté, on renvoie 200
    echo json_encode(['connecte' => false]);
}