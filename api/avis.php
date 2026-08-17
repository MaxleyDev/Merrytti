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
$reservation_id = (int)($data['reservation_id'] ?? 0);
$note = (int)($data['note'] ?? 0);
$commentaire = nettoyerEntree($data['commentaire'] ?? '');
$userId = $_SESSION['utilisateur_id'];

// Vérifier que la réservation appartient à l'utilisateur et est terminée
$req = $pdo->prepare("SELECT id, service_id, statut FROM reservations WHERE id = :id AND utilisateur_id = :uid");
$req->execute(['id' => $reservation_id, 'uid' => $userId]);
$res = $req->fetch();

if (!$res) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Réservation introuvable.']));
}
if ($res['statut'] !== 'terminee') {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Vous ne pouvez laisser un avis que sur une réservation terminée.']));
}
if ($note < 1 || $note > 5) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Note de 1 à 5 requise.']));
}

// Vérifier si un avis existe déjà pour cette réservation
$req = $pdo->prepare("SELECT id FROM avis WHERE reservation_id = :rid");
$req->execute(['rid' => $reservation_id]);
if ($req->fetch()) {
    http_response_code(409);
    exit(json_encode(['erreur' => 'Un avis a déjà été soumis pour cette réservation.']));
}

$req = $pdo->prepare("INSERT INTO avis (utilisateur_id, reservation_id, service_id, note, commentaire) VALUES (:uid, :rid, :sid, :note, :com)");
$req->execute([
    'uid' => $userId,
    'rid' => $reservation_id,
    'sid' => $res['service_id'],
    'note' => $note,
    'com' => $commentaire
]);

echo json_encode(['succes' => true, 'message' => 'Avis soumis avec succès.']);