<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'ID invalide']));
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $statut = $data['statut'] ?? '';
    if (!in_array($statut, ['confirmee', 'annulee', 'terminee'])) {
        http_response_code(400);
        exit(json_encode(['erreur' => 'Statut invalide']));
    }
    $req = $pdo->prepare("UPDATE reservations SET statut = :statut WHERE id = :id");
    $req->execute(['statut' => $statut, 'id' => $id]);
    echo json_encode(['succes' => true]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $req = $pdo->prepare("DELETE FROM reservations WHERE id = :id");
    $req->execute(['id' => $id]);
    echo json_encode(['succes' => true]);
}