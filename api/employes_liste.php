<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$entreprise_id = isset($_GET['entreprise_id']) ? (int)$_GET['entreprise_id'] : 0;

switch ($method) {
    case 'GET':
        if ($entreprise_id <= 0) {
            http_response_code(400);
            exit(json_encode(['erreur' => 'ID entreprise requis']));
        }
        $req = $pdo->prepare("SELECT id, nom, prenom, date_naissance, date_embauche, date_depart, notes FROM employes WHERE entreprise_id = :eid ORDER BY nom ASC");
        $req->execute(['eid' => $entreprise_id]);
        echo json_encode($req->fetchAll());
        break;

    case 'POST':
        $jeton = recupererJetonCSRF();
        if (!$jeton || !verifierJetonCSRF($jeton)) {
            http_response_code(403);
            exit(json_encode(['erreur' => 'Jeton CSRF invalide.']));
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $entreprise_id = (int)($data['entreprise_id'] ?? 0);
        $nom = nettoyerEntree($data['nom'] ?? '');
        $prenom = nettoyerEntree($data['prenom'] ?? '');
        $date_naissance = $data['date_naissance'] ?? null;
        $date_embauche = $data['date_embauche'] ?? null;
        $date_depart = $data['date_depart'] ?? null;
        $notes = nettoyerEntree($data['notes'] ?? '');

        if ($entreprise_id <= 0 || empty($nom) || empty($prenom)) {
            http_response_code(400);
            exit(json_encode(['erreur' => 'Entreprise, nom et prénom requis.']));
        }

        $req = $pdo->prepare("INSERT INTO employes (entreprise_id, nom, prenom, date_naissance, date_embauche, date_depart, notes) VALUES (:eid, :nom, :prenom, :dn, :de, :dd, :notes)");
        $req->execute([
            'eid' => $entreprise_id,
            'nom' => $nom,
            'prenom' => $prenom,
            'dn' => $date_naissance,
            'de' => $date_embauche,
            'dd' => $date_depart,
            'notes' => $notes
        ]);
        echo json_encode(['succes' => true, 'id' => $pdo->lastInsertId()]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erreur' => 'Méthode non autorisée']);
}