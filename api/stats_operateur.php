<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();  // accessible aux deux rôles
header('Content-Type: application/json');

$total_reservations = $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
$total_confirmees   = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'confirmee'")->fetchColumn();

echo json_encode([
    'reservations' => (int)$total_reservations,
    'acheteurs'    => (int)$total_reservations, // provisoire
    'cadeaux'      => (int)$total_confirmees
]);