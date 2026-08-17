<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

$jeton = recupererJetonCSRF();
if (!$jeton || !verifierJetonCSRF($jeton)) {
    http_response_code(403);
    exit(json_encode(['erreur' => 'Jeton CSRF invalide.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$statut = $data['statut'] ?? '';

$statuts_valides = ['generee', 'envoyee', 'utilisee', 'expiree'];
if ($id <= 0 || !in_array($statut, $statuts_valides)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'ID ou statut invalide.']));
}

// Si le statut passe à "envoyee", on enregistre la date d'envoi
$date_envoi = ($statut === 'envoyee') ? date('Y-m-d H:i:s') : null;

$req = $pdo->prepare("UPDATE cartes_cadeaux SET statut = :statut" . ($date_envoi ? ", date_envoi = :date_envoi" : "") . " WHERE id = :id");
$params = ['statut' => $statut, 'id' => $id];
if ($date_envoi) $params['date_envoi'] = $date_envoi;
$req->execute($params);

// Si le nouveau statut est 'envoyee', envoyer un email au destinataire
if ($statut === 'envoyee') {
    $req = $pdo->prepare("SELECT r.email_destinataire, c.code_unique, s.nom AS service
                          FROM cartes_cadeaux c
                          JOIN reservations r ON c.reservation_id = r.id
                          JOIN services s ON r.service_id = s.id
                          WHERE c.id = :id");
    $req->execute(['id' => $id]);
    $info = $req->fetch();
    
    if ($info && $info['email_destinataire']) {
        require_once __DIR__ . '/email_helper.php';
        $lien = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/carte-cadeau.html?code=' . $info['code_unique'];
        $sujet = "Vous avez reçu un cadeau Merrytti !";
        $corps = "Bonjour,\n\nQuelqu'un vous a offert un cadeau !\n\nService : {$info['service']}\nCode unique : {$info['code_unique']}\n\nPour voir votre cadeau, visitez : $lien\n\nProfitez bien !\nL'équipe Merrytti";
        envoyerEmail($info['email_destinataire'], $sujet, $corps);
    }
}

echo json_encode(['succes' => true]);