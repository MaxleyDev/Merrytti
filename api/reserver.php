<?php
require 'config.php';
require 'fonctions.php';
verifierAuthentification();
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
$service_id = (int)($data['service_id'] ?? 0);
$date = nettoyerEntree($data['date_reservation'] ?? '');
$email_destinataire = isset($data['email_destinataire']) ? nettoyerEntree($data['email_destinataire']) : null;
$userId = $_SESSION['utilisateur_id'];

if ($service_id <= 0 || empty($date)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Service et date requis.']));
}

// Convertir le format datetime-local en datetime MySQL
$date_reservation = str_replace('T', ' ', $date) . ':00';

// Vérifier service existant
$req = $pdo->prepare("SELECT id FROM services WHERE id = :id AND actif = 1");
$req->execute(['id' => $service_id]);
if (!$req->fetch()) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Service inexistant.']));
}

// Vérifier que la date est dans le futur (optionnel mais conseillé)
if (strtotime($date_reservation) < time()) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'La date de réservation doit être dans le futur.']));
}

$email_destinataire = isset($data['email_destinataire']) ? nettoyerEntree($data['email_destinataire']) : null;

$req = $pdo->prepare("INSERT INTO reservations (utilisateur_id, service_id, date_reservation, email_destinataire) VALUES (:uid, :sid, :date, :email)");
$req->execute([
    'uid' => $userId,
    'sid' => $service_id,
    'date' => $date_reservation,
    'email' => $email_destinataire
]);

$reservation_id = $pdo->lastInsertId();

echo json_encode([
    'succes' => true,
    'message' => 'Réservation créée.',
    'reservation_id' => $reservation_id
]);