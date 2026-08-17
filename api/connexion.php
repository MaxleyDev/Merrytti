<?php
require 'config.php';
require 'fonctions.php';
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
$identifiant = nettoyerEntree($data['identifiant'] ?? '');
$mot_de_passe = $data['mot_de_passe'] ?? '';

if (empty($identifiant) || empty($mot_de_passe)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Identifiant et mot de passe requis.']));
}

$req = $pdo->prepare("SELECT id, nom_utilisateur, mot_de_passe, role FROM utilisateurs WHERE nom_utilisateur = :id OR email = :id");
$req->execute(['id' => $identifiant]);
$user = $req->fetch();

if (!$user || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
    http_response_code(401);
    exit(json_encode(['erreur' => 'Identifiants incorrects.']));
}

$_SESSION['utilisateur_id'] = $user['id'];
$_SESSION['nom_utilisateur'] = $user['nom_utilisateur'];
$_SESSION['role'] = $user['role'];

echo json_encode(['succes' => true, 'role' => $user['role']]);