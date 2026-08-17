<?php
require 'config.php';
require 'fonctions.php';   // ajout important
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

if (!isset($_SESSION['partenaire_id'])) {
    http_response_code(401);
    exit(json_encode(['erreur' => 'Non connecté']));
}

$data = json_decode(file_get_contents('php://input'), true);
$code = nettoyerEntree($data['code'] ?? '');

if (empty($code)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Code requis.']));
}

// Trouver la carte
$req = $pdo->prepare("SELECT c.id, c.statut, c.date_expiration, s.nom AS service, s.prix,
                             r.utilisateur_id
                      FROM cartes_cadeaux c
                      JOIN reservations r ON c.reservation_id = r.id
                      JOIN services s ON r.service_id = s.id
                      WHERE c.code_unique = :code");
$req->execute(['code' => $code]);
$carte = $req->fetch();

if (!$carte) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Carte introuvable.']));
}

if ($carte['statut'] !== 'envoyee') {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Cette carte n\'est pas valide (statut : ' . $carte['statut'] . ').']));
}

if (date('Y-m-d') > $carte['date_expiration']) {
    $pdo->prepare("UPDATE cartes_cadeaux SET statut = 'expiree' WHERE id = :id")->execute(['id' => $carte['id']]);
    http_response_code(400);
    exit(json_encode(['erreur' => 'Cette carte a expiré.']));
}

// Valider
$pdo->prepare("UPDATE cartes_cadeaux SET statut = 'utilisee' WHERE id = :id")->execute(['id' => $carte['id']]);

$req = $pdo->prepare("INSERT INTO validations (carte_cadeau_id, partenaire_id) VALUES (:cid, :pid)");
$req->execute(['cid' => $carte['id'], 'pid' => $_SESSION['partenaire_id']]);

echo json_encode([
    'succes' => true,
    'message' => 'Carte validée avec succès !',
    'service' => $carte['service'],
    'prix' => $carte['prix']
]);