<?php
require 'config.php';
require 'fonctions.php';
header('Content-Type: application/json');

// Ce fichier est utilisé par le tableau de bord pour vérifier l'accès
verifierAdminOuSecretaire();   // autorise admin et secrétaire

echo json_encode(['ok' => true, 'role' => $_SESSION['role']]);