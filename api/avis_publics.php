<?php
require 'config.php';
header('Content-Type: application/json');

// Nettoyer l'emplacement sans dépendre de fonctions.php
$emplacement = isset($_GET['emplacement']) ? trim(strip_tags($_GET['emplacement'])) : 'accueil';

// Vérifier que l'emplacement est valide
$emplacements_valides = ['accueil', 'temoignages', 'produit'];
if (!in_array($emplacement, $emplacements_valides)) {
    $emplacement = 'accueil';
}

$req = $pdo->prepare("SELECT a.note, a.commentaire, u.nom_utilisateur AS auteur, s.nom AS service
                      FROM avis_publics ap
                      JOIN avis a ON ap.avis_id = a.id
                      JOIN utilisateurs u ON a.utilisateur_id = u.id
                      JOIN services s ON a.service_id = s.id
                      WHERE ap.emplacement = :emp
                      AND a.statut = 'approuve'
                      ORDER BY ap.date_publication DESC");
$req->execute(['emp' => $emplacement]);
$avis = $req->fetchAll();

echo json_encode($avis);