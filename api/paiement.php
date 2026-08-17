<?php
require 'config.php';
require 'fonctions.php';
verifierAuthentification();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

$data = json_decode(file_get_contents('php://input'), true);
$reservation_id = (int)($data['reservation_id'] ?? 0);
$titulaire = nettoyerEntree($data['titulaire'] ?? '');
$numero = $data['numero'] ?? '';
$expiration = $data['expiration'] ?? '';
$cvv = $data['cvv'] ?? '';

$userId = $_SESSION['utilisateur_id'];

// Vérifications basiques
if ($reservation_id <= 0 || empty($titulaire) || empty($numero) || empty($expiration) || empty($cvv)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Tous les champs sont requis.']));
}

// Simuler une validation de carte (accepter tout format correct minimal)
$numero_nettoye = preg_replace('/\s+/', '', $numero);
if (strlen($numero_nettoye) < 13) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Numéro de carte invalide.']));
}

// Vérifier que la réservation existe et appartient à l'utilisateur
$req = $pdo->prepare("SELECT id, est_payee FROM reservations WHERE id = :id AND utilisateur_id = :uid");
$req->execute(['id' => $reservation_id, 'uid' => $userId]);
$resa = $req->fetch();

if (!$resa) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Réservation introuvable.']));
}

if ($resa['est_payee']) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Cette réservation a déjà été payée.']));
}

// Paiement accepté (simulé)
$req = $pdo->prepare("UPDATE reservations SET est_payee = 1 WHERE id = :id");
$req->execute(['id' => $reservation_id]);

// Envoyer un email de confirmation d'achat à l'utilisateur
$req = $pdo->prepare("SELECT u.email, s.nom AS service, r.date_reservation
                      FROM reservations r
                      JOIN utilisateurs u ON r.utilisateur_id = u.id
                      JOIN services s ON r.service_id = s.id
                      WHERE r.id = :id");
$req->execute(['id' => $reservation_id]);
$info = $req->fetch();

if ($info) {
    require_once __DIR__ . '/email_helper.php';
    $sujet = "Confirmation de votre achat Merrytti";
    $corps = "Bonjour,\n\nNous vous confirmons l'achat de votre carte cadeau.\n\nService : {$info['service']}\nDate de réservation : {$info['date_reservation']}\n\nMerci de votre confiance.\nL'équipe Merrytti";
    envoyerEmail($info['email'], $sujet, $corps);
}

echo json_encode(['succes' => true, 'message' => 'Paiement accepté.']);