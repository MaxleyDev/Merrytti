<?php
require 'config.php';
require 'fonctions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée']);
    exit;
}

// Vérification CSRF
$jeton = recupererJetonCSRF();
if (!$jeton || !verifierJetonCSRF($jeton)) {
    http_response_code(403);
    exit(json_encode(['erreur' => 'Jeton CSRF invalide.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$nom_utilisateur = nettoyerEntree($data['nom_utilisateur'] ?? '');
$email = nettoyerEntree($data['email'] ?? '');
$mot_de_passe = $data['mot_de_passe'] ?? '';

if (empty($nom_utilisateur) || empty($email) || empty($mot_de_passe)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Tous les champs sont requis.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Adresse email invalide.']);
    exit;
}

// Vérifier unicité
$req = $pdo->prepare("SELECT id FROM utilisateurs WHERE nom_utilisateur = :nom OR email = :email");
$req->execute(['nom' => $nom_utilisateur, 'email' => $email]);
if ($req->fetch()) {
    http_response_code(409);
    echo json_encode(['erreur' => 'Nom d\'utilisateur ou email déjà utilisé.']);
    exit;
}

$hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
$req = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, role) VALUES (:nom, :email, :mdp, 'client')");
$req->execute(['nom' => $nom_utilisateur, 'email' => $email, 'mdp' => $hash]);

// Connexion automatique
$_SESSION['utilisateur_id'] = $pdo->lastInsertId();
$_SESSION['nom_utilisateur'] = $nom_utilisateur;
$_SESSION['role'] = 'client';

echo json_encode(['succes' => true, 'message' => 'Compte créé avec succès.']);