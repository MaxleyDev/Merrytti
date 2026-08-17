<?php
require 'config.php';
require 'fonctions.php';
verifierAdmin();   // seulement administrateur
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch ($method) {
    case 'GET':
        $req = $pdo->query("SELECT a.id, a.note, a.commentaire, a.statut, a.date_creation,
                                   u.nom_utilisateur AS auteur, s.nom AS service
                            FROM avis a
                            JOIN utilisateurs u ON a.utilisateur_id = u.id
                            JOIN services s ON a.service_id = s.id
                            ORDER BY a.date_creation DESC");
        echo json_encode($req->fetchAll());
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['statut'])) {
            $statut = $data['statut'];
            if (!in_array($statut, ['approuve', 'rejete'])) {
                http_response_code(400);
                echo json_encode(['erreur' => 'Statut invalide']);
                exit;
            }
            $req = $pdo->prepare("UPDATE avis SET statut = :statut WHERE id = :id");
            $req->execute(['statut' => $statut, 'id' => $id]);

            // ---------------------------------------------------------------
            // AUTOMATIC PUBLISHING TO 'temoignages' WHEN APPROVED
            // ---------------------------------------------------------------
            if ($statut === 'approuve') {
                // Prevent duplicate entries
                $exists = $pdo->prepare("SELECT id FROM avis_publics WHERE avis_id = :aid AND emplacement = 'temoignages'");
                $exists->execute(['aid' => $id]);
                if (!$exists->fetch()) {
                    $insert = $pdo->prepare("INSERT INTO avis_publics (avis_id, emplacement) VALUES (:aid, 'temoignages')");
                    $insert->execute(['aid' => $id]);
                }
            }
            // ---------------------------------------------------------------

            echo json_encode(['succes' => true]);
            break;
        }

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erreur' => 'ID requis']);
            exit;
        }
        $note = isset($data['note']) ? (int)$data['note'] : null;
        $commentaire = isset($data['commentaire']) ? nettoyerEntree($data['commentaire']) : null;

        if ($note && ($note < 1 || $note > 5)) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Note entre 1 et 5 requise']);
            exit;
        }

        $sets = [];
        $params = ['id' => $id];
        if ($note !== null) {
            $sets[] = 'note = :note';
            $params['note'] = $note;
        }
        if ($commentaire !== null) {
            $sets[] = 'commentaire = :commentaire';
            $params['commentaire'] = $commentaire;
        }

        if (empty($sets)) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Aucune donnée à modifier']);
            exit;
        }

        $sql = "UPDATE avis SET " . implode(', ', $sets) . " WHERE id = :id";
        $req = $pdo->prepare($sql);
        $req->execute($params);
        echo json_encode(['succes' => true]);
        break;

    case 'DELETE':
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erreur' => 'ID requis']);
            exit;
        }
        $pdo->prepare("DELETE FROM avis_publics WHERE avis_id = :id")->execute(['id' => $id]);
        $pdo->prepare("DELETE FROM avis WHERE id = :id")->execute(['id' => $id]);
        echo json_encode(['succes' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erreur' => 'Méthode non autorisée']);
}