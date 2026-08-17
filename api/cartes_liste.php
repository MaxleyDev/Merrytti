<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$req = $pdo->query("SELECT c.id, c.code_unique, c.statut, c.date_envoi, c.date_expiration, 
                           r.id as reservation_id, u.nom_utilisateur as client, s.nom as service
                    FROM cartes_cadeaux c
                    JOIN reservations r ON c.reservation_id = r.id
                    JOIN utilisateurs u ON r.utilisateur_id = u.id
                    JOIN services s ON r.service_id = s.id
                    ORDER BY c.id DESC");
$cartes = $req->fetchAll();

echo json_encode(array_map(function($c) {
    return [
        'id' => $c['id'],
        'code_unique' => $c['code_unique'],
        'statut' => $c['statut'],
        'date_envoi' => $c['date_envoi'] ? formaterDate($c['date_envoi']) : null,
        'date_expiration' => $c['date_expiration'],
        'reservation_id' => $c['reservation_id'],
        'client' => $c['client'],
        'service' => $c['service']
    ];
}, $cartes));