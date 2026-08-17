<?php
require 'config.php';
require 'fonctions.php';   // ajout important
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

$data = json_decode(file_get_contents('php://input'), true);
$email = nettoyerEntree($data['email'] ?? '');
$mot_de_passe = $data['mot_de_passe'] ?? '';

if (empty($email) || empty($mot_de_passe)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Email et mot de passe requis.']));
}

$req = $pdo->prepare("SELECT id, nom_entreprise, mot_de_passe FROM partenaires WHERE email = :email AND actif = 1");
$req->execute(['email' => $email]);
$partenaire = $req->fetch();

if (!$partenaire || !password_verify($mot_de_passe, $partenaire['mot_de_passe'])) {
    http_response_code(401);
    exit(json_encode(['erreur' => 'Identifiants incorrects.']));
}

$_SESSION['partenaire_id'] = $partenaire['id'];
$_SESSION['partenaire_nom'] = $partenaire['nom_entreprise'];

echo json_encode(['succes' => true, 'nom_entreprise' => $partenaire['nom_entreprise']]);