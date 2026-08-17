<?php
/**
 * Fonction d'envoi d'email simulée (log dans un fichier).
 * Sur un serveur réel, remplacez par mail() ou une bibliothèque SMTP.
 */
function envoyerEmail($destinataire, $sujet, $corps) {
    $logFile = __DIR__ . '/../logs/emails.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $date = date('Y-m-d H:i:s');
    $entry = "[$date] À: $destinataire | Sujet: $sujet\n$corps\n----------------------------------------\n";
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    
    // Pour un vrai envoi, décommentez :
    // $headers = "From: no-reply@merrytti.com\r\nContent-Type: text/plain; charset=UTF-8";
    // mail($destinataire, $sujet, $corps, $headers);
    
    return true;
}