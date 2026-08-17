<?php
require 'config.php';
require 'fonctions.php';
verifierAuthentification();
header('Content-Type: application/json');

$userId = $_SESSION['utilisateur_id'];

$req = $pdo->prepare("SELECT c.id, c.code_unique, c.statut, c.date_expiration, 
                             s.nom as service, r.date_reservation
                      FROM cartes_cadeaux c
                      JOIN reservations r ON c.reservation_id = r.id
                      JOIN services s ON r.service_id = s.id
                      WHERE r.utilisateur_id = :uid
                      ORDER BY c.id DESC");
$req->execute(['uid' => $userId]);
$cartes = $req->fetchAll();

echo json_encode(array_map(function($c) {
    return [
        'id' => $c['id'],
        'code_unique' => $c['code_unique'],
        'statut' => $c['statut'],
        'date_expiration' => $c['date_expiration'],
        'service' => $c['service'],
        'date_reservation' => formaterDate($c['date_reservation'])
    ];
}, $cartes));