<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

$req = $pdo->query("SELECT emp.id, emp.nom, emp.prenom, ent.nom_entreprise 
                    FROM employes emp 
                    JOIN entreprises ent ON emp.entreprise_id = ent.id 
                    ORDER BY ent.nom_entreprise, emp.nom");
echo json_encode($req->fetchAll());