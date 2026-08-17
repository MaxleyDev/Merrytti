<?php
require 'config.php';
header('Content-Type: application/json');

if (isset($_SESSION['partenaire_id'])) {
    echo json_encode([
        'connecte' => true,
        'partenaire_id' => $_SESSION['partenaire_id'],
        'nom_entreprise' => $_SESSION['partenaire_nom']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['connecte' => false]);
}