<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$req = $pdo->query("SELECT r.id, u.nom_utilisateur as client, s.nom as service
                    FROM reservations r
                    JOIN utilisateurs u ON r.utilisateur_id = u.id
                    JOIN services s ON r.service_id = s.id
                    WHERE r.statut = 'confirmee' 
                    AND r.id NOT IN (SELECT reservation_id FROM cartes_cadeaux)
                    ORDER BY r.date_reservation DESC");
echo json_encode($req->fetchAll());