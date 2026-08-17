<?php
require 'config.php';  // démarre session et inclut csrf.php
header('Content-Type: application/json');
$jeton = genererJetonCSRF();
echo json_encode(['jeton' => $jeton]);