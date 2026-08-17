<?php
require 'config.php';
require 'fonctions.php';
verifierAuthentification();
header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'ID invalide']));
}

$userId = $_SESSION['utilisateur_id'];

$req = $pdo->prepare("SELECT r.id, s.nom AS service, s.prix, r.date_reservation, r.est_payee
                      FROM reservations r
                      JOIN services s ON r.service_id = s.id
                      WHERE r.id = :id AND r.utilisateur_id = :uid");
$req->execute(['id' => $id, 'uid' => $userId]);
$resa = $req->fetch();

if (!$resa) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Réservation non trouvée']));
}

$resa['date_formatee'] = formaterDate($resa['date_reservation']);
$resa['est_payee'] = (bool)$resa['est_payee'];

echo json_encode($resa);