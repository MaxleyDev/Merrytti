<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    exit(json_encode(['erreur' => 'Méthode non autorisée']));
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'ID invalide']));
}

$req = $pdo->prepare("DELETE FROM cartes_cadeaux WHERE id = :id");
$req->execute(['id' => $id]);
echo json_encode(['succes' => true]);