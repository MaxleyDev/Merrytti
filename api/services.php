<?php
require 'config.php';
header('Content-Type: application/json');

$req = $pdo->query("SELECT id, nom, description, prix, image FROM services WHERE actif = 1");
echo json_encode($req->fetchAll());