<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

$data = json_decode(file_get_contents('php://input'), true);
$code = trim($data['code'] ?? '');

if (empty($code)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Veuillez entrer un code.']));
}

// Rechercher la carte par son code unique
$req = $pdo->prepare("SELECT c.id, c.code_unique, c.statut, c.date_expiration, c.date_envoi,
                              s.nom AS service, s.description AS service_description, s.prix,
                              r.date_reservation
                       FROM cartes_cadeaux c
                       JOIN reservations r ON c.reservation_id = r.id
                       JOIN services s ON r.service_id = s.id
                       WHERE c.code_unique = :code");
$req->execute(['code' => $code]);
$carte = $req->fetch();

if (!$carte) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Code invalide. Aucune carte cadeau trouvée.']));
}

// Vérifier si la carte est expirée
$aujourdhui = date('Y-m-d');
$est_expiree = ($carte['date_expiration'] < $aujourdhui);
if ($est_expiree && $carte['statut'] !== 'expiree') {
    // Mettre à jour le statut en "expiree" si la date est passée
    $pdo->prepare("UPDATE cartes_cadeaux SET statut = 'expiree' WHERE id = :id")
        ->execute(['id' => $carte['id']]);
    $carte['statut'] = 'expiree';
}

$reponse = [
    'trouvee' => true,
    'code' => $carte['code_unique'],
    'service' => $carte['service'],
    'description' => $carte['service_description'],
    'prix' => $carte['prix'],
    'date_expiration' => $carte['date_expiration'],
    'statut' => $carte['statut'],
    'date_reservation' => $carte['date_reservation'],
    'message' => ''
];

switch ($carte['statut']) {
    case 'generee':
        $reponse['message'] = 'Cette carte est prête à être offerte.';
        break;
    case 'envoyee':
        $reponse['message'] = 'Carte envoyée, en attente d\'utilisation.';
        break;
    case 'utilisee':
        $reponse['message'] = 'Cette carte a déjà été utilisée.';
        break;
    case 'expiree':
        $reponse['message'] = 'Cette carte a expiré.';
        break;
}

echo json_encode($reponse);