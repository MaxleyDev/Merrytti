<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$employe_id = isset($_GET['employe_id']) ? (int)$_GET['employe_id'] : 0;

switch ($method) {
    case 'GET':
        if ($employe_id <= 0) {
            // Liste tous les événements à venir (pour le calendrier mensuel)
            $req = $pdo->query("SELECT e.id, e.type_evenement, e.date_evenement, e.description, 
                                       emp.nom, emp.prenom, ent.nom_entreprise
                                FROM evenements e
                                JOIN employes emp ON e.employe_id = emp.id
                                JOIN entreprises ent ON emp.entreprise_id = ent.id
                                WHERE e.date_evenement >= CURDATE()
                                ORDER BY e.date_evenement ASC");
            echo json_encode($req->fetchAll());
        } else {
            // Événements d'un employé spécifique
            $req = $pdo->prepare("SELECT id, type_evenement, date_evenement, description FROM evenements WHERE employe_id = :eid ORDER BY date_evenement ASC");
            $req->execute(['eid' => $employe_id]);
            echo json_encode($req->fetchAll());
        }
        break;

    case 'POST':
        $jeton = recupererJetonCSRF();
        if (!$jeton || !verifierJetonCSRF($jeton)) {
            http_response_code(403);
            exit(json_encode(['erreur' => 'Jeton CSRF invalide.']));
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $employe_id = (int)($data['employe_id'] ?? 0);
        $type = $data['type_evenement'] ?? '';
        $date = $data['date_evenement'] ?? '';
        $description = nettoyerEntree($data['description'] ?? '');

        $types_valides = ['anniversaire', 'embauche', 'depart', 'autre'];
        if ($employe_id <= 0 || !in_array($type, $types_valides) || empty($date)) {
            http_response_code(400);
            exit(json_encode(['erreur' => 'Employé, type et date requis.']));
        }

        $req = $pdo->prepare("INSERT INTO evenements (employe_id, type_evenement, date_evenement, description) VALUES (:eid, :type, :date, :desc)");
        $req->execute(['eid' => $employe_id, 'type' => $type, 'date' => $date, 'desc' => $description]);
        echo json_encode(['succes' => true, 'id' => $pdo->lastInsertId()]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erreur' => 'Méthode non autorisée']);
}