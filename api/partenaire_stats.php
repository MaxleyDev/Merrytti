<?php
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['partenaire_id'])) {
    http_response_code(401);
    exit(json_encode(['connecte' => false]));
}

$partenaire_id = $_SESSION['partenaire_id'];

$total_validations = $pdo->prepare("SELECT COUNT(*) FROM validations WHERE partenaire_id = :pid");
$total_validations->execute(['pid' => $partenaire_id]);
$count = $total_validations->fetchColumn();

$total_montant = $pdo->prepare("SELECT SUM(s.prix) FROM validations v
                                JOIN cartes_cadeaux c ON v.carte_cadeau_id = c.id
                                JOIN reservations r ON c.reservation_id = r.id
                                JOIN services s ON r.service_id = s.id
                                WHERE v.partenaire_id = :pid");
$total_montant->execute(['pid' => $partenaire_id]);
$montant = $total_montant->fetchColumn() ?: 0;

echo json_encode([
    'total_validations' => (int)$count,
    'total_montant' => $montant
]);