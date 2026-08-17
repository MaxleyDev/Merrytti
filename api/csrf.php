<?php
/**
 * Gestion simplifiée des jetons CSRF.
 * À inclure après config.php (qui démarre la session).
 */

/**
 * Génère un jeton CSRF unique stocké en session.
 * @return string Le jeton.
 */
function genererJetonCSRF(): string {
    if (empty($_SESSION['csrf_jeton'])) {
        $_SESSION['csrf_jeton'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_jeton'];
}

/**
 * Vérifie que le jeton CSRF envoyé correspond à celui de la session.
 * @param string $jeton Le jeton reçu (par ex. $_POST['csrf_jeton'] ou json).
 * @return bool true si valide.
 */
function verifierJetonCSRF(string $jeton): bool {
    if (!isset($_SESSION['csrf_jeton']) || empty($jeton)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_jeton'], $jeton);
}

/**
 * Récupère le jeton depuis une requête JSON (POST) ou des données POST classiques.
 * @return string|null
 */
function recupererJetonCSRF(): ?string {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
        $contenu = file_get_contents('php://input');
        $data = json_decode($contenu, true);
        if (isset($data['csrf_jeton'])) {
            return $data['csrf_jeton'];
        }
        // Sinon, essayer dans $_POST
        if (isset($_POST['csrf_jeton'])) {
            return $_POST['csrf_jeton'];
        }
    }
    return null;
}