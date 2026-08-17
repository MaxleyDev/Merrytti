<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

// CSRF
$jeton = recupererJetonCSRF();
if (!$jeton || !verifierJetonCSRF($jeton)) {
    http_response_code(403);
    exit(json_encode(['erreur' => 'Jeton CSRF invalide.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$reservation_id = (int)($data['reservation_id'] ?? 0);

if ($reservation_id <= 0) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'ID de réservation invalide.']));
}

// Vérifier que la réservation existe et est confirmée
$req = $pdo->prepare("SELECT id, statut FROM reservations WHERE id = :id");
$req->execute(['id' => $reservation_id]);
$res = $req->fetch();

if (!$res) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Réservation introuvable.']));
}
if ($res['statut'] !== 'confirmee') {
    http_response_code(400);
    exit(json_encode(['erreur' => 'La réservation doit être confirmée pour générer une carte.']));
}

// Vérifier si une carte existe déjà pour cette réservation
$req = $pdo->prepare("SELECT id FROM cartes_cadeaux WHERE reservation_id = :rid");
$req->execute(['rid' => $reservation_id]);
if ($req->fetch()) {
    http_response_code(409);
    exit(json_encode(['erreur' => 'Une carte cadeau a déjà été générée pour cette réservation.']));
}

// Générer un code unique
$code_unique = strtoupper(bin2hex(random_bytes(6))); // 12 caractères hexadécimaux

// Date d'expiration : aujourd'hui + 1 mois
$date_expiration = date('Y-m-d', strtotime('+1 month'));

$req = $pdo->prepare("INSERT INTO cartes_cadeaux (reservation_id, code_unique, statut, date_expiration) VALUES (:rid, :code, 'generee', :exp)");
$req->execute([
    'rid' => $reservation_id,
    'code' => $code_unique,
    'exp' => $date_expiration
]);

$id_carte = $pdo->lastInsertId();

echo json_encode([
    'succes' => true,
    'message' => 'Carte cadeau générée.',
    'carte' => [
        'id' => $id_carte,
        'code_unique' => $code_unique,
        'date_expiration' => $date_expiration,
        'statut' => 'generee'
    ]
]);