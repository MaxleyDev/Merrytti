<?php
require 'config.php';
require 'fonctions.php';
verifierAdminOuSecretaire();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $req = $pdo->query("SELECT r.id, r.utilisateur_id, IFNULL(u.nom_utilisateur, 'Inconnu') as client, 
                               s.nom as service, r.date_reservation, r.statut
                        FROM reservations r
                        LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                        LEFT JOIN services s ON r.service_id = s.id
                        ORDER BY r.date_creation DESC");
    $reservations = $req->fetchAll();

    echo json_encode(array_map(function($r) {
        return [
            'id' => '#' . $r['id'],
            'client' => $r['client'],
            'service' => $r['service'] ?? 'Service supprimé',
            'date_reservation' => $r['date_reservation'],
            'date_formatee' => formaterDate($r['date_reservation']),
            'statut' => $r['statut'],
            'statut_libelle' => [
                'en_attente' => 'En attente',
                'confirmee'  => 'Confirmée',
                'annulee'    => 'Annulée',
                'terminee'   => 'Terminée'
            ][$r['statut']]
        ];
    }, $reservations));
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    $jeton = recupererJetonCSRF();
    if (!$jeton || !verifierJetonCSRF($jeton)) {
        http_response_code(403);
        exit(json_encode(['erreur' => 'Jeton CSRF invalide.']));
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $nom = nettoyerEntree($data['nom'] ?? '');
    $service_id = (int)($data['service_id'] ?? 0);
    $date = $data['date_reservation'] ?? '';

    if (empty($nom) || $service_id <= 0 || empty($date)) {
        http_response_code(400);
        exit(json_encode(['erreur' => 'Nom, service et date requis.']));
    }

    // Créer ou récupérer l'utilisateur
    $req = $pdo->prepare("SELECT id FROM utilisateurs WHERE nom_utilisateur = :nom");
    $req->execute(['nom' => $nom]);
    $user = $req->fetch();
    if (!$user) {
        $email = strtolower(str_replace(' ', '.', $nom)) . '@guest.merrytti.com';
        $hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
        $req = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, role) VALUES (:nom, :email, :mdp, 'client')");
        $req->execute(['nom' => $nom, 'email' => $email, 'mdp' => $hash]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }

    $req = $pdo->prepare("INSERT INTO reservations (utilisateur_id, service_id, date_reservation) VALUES (:uid, :sid, :date)");
    $req->execute(['uid' => $userId, 'sid' => $service_id, 'date' => $date]);

    echo json_encode(['succes' => true, 'message' => 'Réservation créée.']);
} else {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée']);
}