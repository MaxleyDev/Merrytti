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

$pdo->prepare("DELETE FROM employes WHERE id = :id")->execute(['id' => $id]);
echo json_encode(['succes' => true]);