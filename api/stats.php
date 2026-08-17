<?php
require 'config.php';
require 'fonctions.php';
verifierAdmin();   // au lieu de verifierAuthentification()
header('Content-Type: application/json');

$total_reservations = $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
$total_confirmees   = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'confirmee'")->fetchColumn();
$total_en_attente   = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente'")->fetchColumn();
$revenus = $total_confirmees * 1300;

echo json_encode([
    'reservations' => (int)$total_reservations,
    'revenus'      => $revenus,
    'acheteurs'    => (int)$total_reservations,
    'cadeaux'      => (int)$total_confirmees
]);