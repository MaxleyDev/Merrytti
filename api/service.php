<?php
require 'config.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit(json_encode(['erreur' => 'ID invalide']));
}

$req = $pdo->prepare("SELECT id, nom, description, prix, image FROM services WHERE id = :id AND actif = 1");
$req->execute(['id' => $id]);
$service = $req->fetch();

if (!$service) {
    http_response_code(404);
    exit(json_encode(['erreur' => 'Service introuvable']));
}

echo json_encode($service);