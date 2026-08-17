<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'reservations';
$debut = $_GET['debut'] ?? '';
$fin = $_GET['fin'] ?? '';

if (empty($debut) || empty($fin)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Dates de début et fin requises.']));
}

// Ajouter l'heure pour inclure toute la journée de fin
$fin .= ' 23:59:59';

switch ($type) {
    case 'reservations':
        $req = $pdo->prepare("SELECT r.id, u.nom_utilisateur AS client, s.nom AS service, r.date_reservation, r.statut, r.est_payee
                              FROM reservations r
                              LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                              LEFT JOIN services s ON r.service_id = s.id
                              WHERE r.date_reservation BETWEEN :debut AND :fin
                              ORDER BY r.date_reservation DESC");
        $req->execute(['debut' => $debut, 'fin' => $fin]);
        $results = $req->fetchAll();
        
        echo json_encode(array_map(function($r) {
            $r['date_formatee'] = formaterDate($r['date_reservation']);
            $r['est_payee'] = (bool)$r['est_payee'];
            return $r;
        }, $results));
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['erreur' => 'Type de rapport non supporté.']);
}


