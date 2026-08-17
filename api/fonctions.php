<?php
/**
 * Fonctions utilitaires partagées.
 */

function verifierAuthentification() {
    if (!isset($_SESSION['utilisateur_id'])) {
        http_response_code(401);
        echo json_encode(['erreur' => 'Non authentifié']);
        exit;
    }
}

/**
 * Vérifie que l'utilisateur connecté possède l'un des rôles autorisés.
 * Exemple : verifierRole(['admin', 'secretaire']);
 */
function verifierRole(array $roles_autorises) {
    verifierAuthentification();
    if (!in_array($_SESSION['role'], $roles_autorises)) {
        http_response_code(403);
        echo json_encode(['erreur' => 'Accès interdit']);
        exit;
    }
}

/**
 * Vérifie que l'utilisateur est admin (utilisé pour les anciens appels).
 */
function verifierAdmin() {
    verifierRole(['admin']);
}

/**
 * Vérifie que l'utilisateur est admin ou secrétaire (pour le portail interne).
 */
function verifierAdminOuSecretaire() {
    verifierRole(['admin', 'secretaire']);
}

/**
 * Nettoie une chaîne pour l'affichage (échappe les caractères HTML).
 * NE PAS utiliser pour les données insérées en base.
 */
function nettoyerAffichage($donnee) {
    return htmlspecialchars(trim($donnee), ENT_QUOTES, 'UTF-8');
}

/**
 * Nettoie une chaîne pour insertion en base : supprime juste les espaces inutiles.
 */
function nettoyerEntree($donnee) {
    return trim($donnee);
}

/**
 * Formate une date SQL (Y-m-d H:i:s) en format français.
 */
function formaterDate($date_sql) {
    if (empty($date_sql)) return '';
    $dt = new DateTime($date_sql);
    return $dt->format('d/m/Y H:i');
}
?>