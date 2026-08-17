<?php
require 'config.php';
require_once 'email_helper.php';

$aujourdhui = date('Y-m-d');
$req = $pdo->prepare("SELECT e.type_evenement, e.date_evenement, e.description, emp.nom, emp.prenom, ent.nom_entreprise
                      FROM evenements e
                      JOIN employes emp ON e.employe_id = emp.id
                      JOIN entreprises ent ON emp.entreprise_id = ent.id
                      WHERE e.date_evenement = :date");
$req->execute(['date' => $aujourdhui]);
$evenements = $req->fetchAll();

if (count($evenements) > 0) {
    $corps = "Événements du jour :\n\n";
    foreach ($evenements as $ev) {
        $corps .= "- {$ev['prenom']} {$ev['nom']} ({$ev['nom_entreprise']}) : {$ev['type_evenement']} le {$ev['date_evenement']}\n";
    }
    $corps .= "\nPréparez les cadeaux si nécessaire.\nL'équipe Merrytti";
    // Envoyer au secrétaire/admin (vous pouvez configurer l'email)
    envoyerEmail('secretaire@merrytti.com', 'Rappel : Événements du jour', $corps);
}

echo json_encode(['succes' => true, 'evenements_trouves' => count($evenements)]);