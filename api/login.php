<?php
require_once 'config.php';
require_once 'fonctions.php';

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

$donnees = json_decode(file_get_contents('php://input'), true);
$nom_utilisateur = nettoyerEntree($donnees['nom_utilisateur'] ?? '');
$mot_de_passe   = $donnees['mot_de_passe'] ?? '';

if (empty($nom_utilisateur) || empty($mot_de_passe)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Nom d\'utilisateur et mot de passe requis']);
    exit;
}

$requete = $pdo->prepare('SELECT id, nom_utilisateur, mot_de_passe, role FROM utilisateurs WHERE nom_utilisateur = :nom');
$requete->execute(['nom' => $nom_utilisateur]);
$utilisateur = $requete->fetch();

if (!$utilisateur || !password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
    http_response_code(401);
    echo json_encode(['erreur' => 'Identifiants incorrects']);
    exit;
}

// L'utilisateur doit être admin ou secrétaire (selon le rôle pour accès à la partie admin)
if (!in_array($utilisateur['role'], ['admin', 'secretaire'])) {
    http_response_code(403);
    echo json_encode(['erreur' => 'Accès réservé au personnel interne']);
    exit;
}

$_SESSION['utilisateur_id'] = $utilisateur['id'];
$_SESSION['nom_utilisateur'] = $utilisateur['nom_utilisateur'];
$_SESSION['role'] = $utilisateur['role'];

echo json_encode([
    'succes' => true,
    'message' => 'Connexion réussie',
    'role' => $utilisateur['role']
]);