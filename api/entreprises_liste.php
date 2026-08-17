<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Lister toutes les entreprises
        $req = $pdo->query("SELECT id, nom_entreprise, adresse, telephone, email FROM entreprises ORDER BY nom_entreprise ASC");
        echo json_encode($req->fetchAll());
        break;

    case 'POST':
        // Ajouter une entreprise
        $jeton = recupererJetonCSRF();
        if (!$jeton || !verifierJetonCSRF($jeton)) {
            http_response_code(403);
            exit(json_encode(['erreur' => 'Jeton CSRF invalide.']));
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $nom = nettoyerEntree($data['nom_entreprise'] ?? '');
        $adresse = nettoyerEntree($data['adresse'] ?? '');
        $telephone = nettoyerEntree($data['telephone'] ?? '');
        $email = nettoyerEntree($data['email'] ?? '');

        if (empty($nom)) {
            http_response_code(400);
            exit(json_encode(['erreur' => 'Le nom de l\'entreprise est obligatoire.']));
        }

        $req = $pdo->prepare("INSERT INTO entreprises (nom_entreprise, adresse, telephone, email) VALUES (:nom, :adresse, :tel, :email)");
        $req->execute(['nom' => $nom, 'adresse' => $adresse, 'tel' => $telephone, 'email' => $email]);
        echo json_encode(['succes' => true, 'id' => $pdo->lastInsertId()]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erreur' => 'Méthode non autorisée']);
}