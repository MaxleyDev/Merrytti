<?php
require 'config.php';
require 'fonctions.php';
verifierAuthentification();   // tout utilisateur connecté (admin/secrétaire/client) peut changer son propre mot de passe
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
$ancien_mot_de_passe = $data['ancien_mot_de_passe'] ?? '';
$nouveau_mot_de_passe = $data['nouveau_mot_de_passe'] ?? '';

$userId = $_SESSION['utilisateur_id'];

if (empty($ancien_mot_de_passe) || empty($nouveau_mot_de_passe)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Tous les champs sont requis.']));
}

if (strlen($nouveau_mot_de_passe) < 6) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Le nouveau mot de passe doit avoir au moins 6 caractères.']));
}

// Vérifier l'ancien mot de passe
$req = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = :id");
$req->execute(['id' => $userId]);
$user = $req->fetch();

if (!$user || !password_verify($ancien_mot_de_passe, $user['mot_de_passe'])) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Ancien mot de passe incorrect.']));
}

// Mettre à jour
$hash = password_hash($nouveau_mot_de_passe, PASSWORD_BCRYPT);
$req = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id");
$req->execute(['mdp' => $hash, 'id' => $userId]);

echo json_encode(['succes' => true, 'message' => 'Mot de passe modifié avec succès.']);