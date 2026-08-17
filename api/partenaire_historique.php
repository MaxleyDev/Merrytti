<?php
require 'config.php';
require 'fonctions.php';   // optionnel, mais sécurise l'affichage ultérieur
header('Content-Type: application/json');

if (!isset($_SESSION['partenaire_id'])) {
    http_response_code(401);
    exit(json_encode(['connecte' => false]));
}

$partenaire_id = $_SESSION['partenaire_id'];

$req = $pdo->prepare("SELECT v.date_validation, c.code_unique, s.nom AS service, s.prix
                      FROM validations v
                      JOIN cartes_cadeaux c ON v.carte_cadeau_id = c.id
                      JOIN reservations r ON c.reservation_id = r.id
                      JOIN services s ON r.service_id = s.id
                      WHERE v.partenaire_id = :pid
                      ORDER BY v.date_validation DESC");
$req->execute(['pid' => $partenaire_id]);
$validations = $req->fetchAll();

echo json_encode(array_map(function($v) {
    return [
        'date_validation' => $v['date_validation'],
        'code' => $v['code_unique'],
        'service' => $v['service'],
        'prix' => $v['prix']
    ];
}, $validations));