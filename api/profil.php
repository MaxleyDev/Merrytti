<?php
require 'config.php';
require 'fonctions.php';
verifierAuthentification();
header('Content-Type: application/json');

$userId = $_SESSION['utilisateur_id'];

// Infos utilisateur
$req = $pdo->prepare("SELECT nom_utilisateur, email, date_creation FROM utilisateurs WHERE id = :id");
$req->execute(['id' => $userId]);
$user = $req->fetch();

// Réservations
$req = $pdo->prepare("SELECT r.id, s.nom as service, r.date_reservation, r.statut
                      FROM reservations r
                      JOIN services s ON r.service_id = s.id
                      WHERE r.utilisateur_id = :uid
                      ORDER BY r.date_creation DESC");
$req->execute(['uid' => $userId]);
$reservations = $req->fetchAll();

echo json_encode([
    'utilisateur' => $user,
    'reservations' => array_map(function($r) {
        $r['date_formatee'] = formaterDate($r['date_reservation']);
        $r['statut_libelle'] = [
            'en_attente' => 'En attente',
            'confirmee' => 'Confirmée',
            'annulee' => 'Annulée',
            'terminee' => 'Terminée'
        ][$r['statut']] ?? $r['statut'];
        return $r;
    }, $reservations)
]);