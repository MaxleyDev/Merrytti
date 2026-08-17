<?php
require 'config.php';
require 'fonctions.php';
verifierAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

$data = json_decode(file_get_contents('php://input'), true);
$avis_id = (int)($data['avis_id'] ?? 0);
$emplacement = nettoyer($data['emplacement'] ?? '');

// Vérifier que l'avis existe et est approuvé
$req = $pdo->prepare("SELECT id, statut FROM avis WHERE id = :id");
$req->execute(['id' => $avis_id]);
$avis = $req->fetch();
if (!$avis || $avis['statut'] !== 'approuve') {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Avis non trouvé ou non approuvé.']));
}

// Vérifier emplacement valide
$emplacements = ['accueil', 'temoignages', 'produit'];
if (!in_array($emplacement, $emplacements)) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'Emplacement invalide. Choisir parmi: ' . implode(', ', $emplacements)]));
}

// Éviter doublon
$req = $pdo->prepare("SELECT id FROM avis_publics WHERE avis_id = :aid AND emplacement = :emp");
$req->execute(['aid' => $avis_id, 'emp' => $emplacement]);
if ($req->fetch()) {
    http_response_code(409);
    exit(json_encode(['erreur' => 'Cet avis est déjà publié à cet emplacement.']));
}

$req = $pdo->prepare("INSERT INTO avis_publics (avis_id, emplacement) VALUES (:aid, :emp)");
$req->execute(['aid' => $avis_id, 'emp' => $emplacement]);

echo json_encode(['succes' => true, 'message' => 'Avis publié avec succès.']);