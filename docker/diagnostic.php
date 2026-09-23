<?php
/**
 * Diagnostic de demarrage (conteneur Render).
 *
 * Verifie le format de MAILER_DSN et MONGODB_URI et affiche la vraie cause
 * d'une erreur, SANS jamais afficher les identifiants ni les mots de passe :
 * tout ce qui ressemble a "utilisateur:motdepasse@" est masque.
 */
require '/var/www/html/vendor/autoload.php';

function masquer(string $texte): string
{
    return preg_replace('#//[^/@\s]*@#', '//***:***@', $texte);
}

function analyser(string $nom): ?string
{
    $valeur = getenv($nom);
    if ($valeur === false) {
        echo "[diagnostic] $nom : ABSENTE des variables Render\n";
        return null;
    }
    $parts = parse_url(trim($valeur, " \"'")) ?: [];
    printf(
        "[diagnostic] %s : longueur %d | guillemet au debut : %s | espace au debut/fin : %s | schema : %s | hote : %s | port : %s\n",
        $nom,
        strlen($valeur),
        in_array($valeur[0] ?? '', ['"', "'"], true) ? 'OUI (a retirer)' : 'non',
        $valeur !== trim($valeur) ? 'OUI (a retirer)' : 'non',
        $parts['scheme'] ?? '?',
        $parts['host'] ?? '?',
        $parts['port'] ?? '-'
    );
    return $valeur;
}

$dsn = analyser('MAILER_DSN');
if ($dsn !== null) {
    try {
        Symfony\Component\Mailer\Transport::fromDsn($dsn);
        echo "[diagnostic] MAILER_DSN : format accepte par Symfony\n";
    } catch (Throwable $e) {
        echo '[diagnostic] MAILER_DSN refuse : ' . get_class($e) . ' : ' . masquer($e->getMessage()) . "\n";
    }
}

$uri = analyser('MONGODB_URI');
if ($uri !== null) {
    try {
        $client = new MongoDB\Client($uri, ['serverSelectionTimeoutMS' => 8000]);
        $client->selectDatabase(getenv('MONGODB_DB') ?: 'admin')->command(['ping' => 1]);
        echo "[diagnostic] MONGODB_URI : connexion reussie\n";
    } catch (Throwable $e) {
        echo '[diagnostic] MONGODB_URI echec : ' . get_class($e) . ' : ' . masquer($e->getMessage()) . "\n";
    }
}
