<?php
// Nettoyage des logs dans le dossier tests (cross-platform)
$dir = __DIR__ . DIRECTORY_SEPARATOR;
$patterns = ['debug_*.log', '*.log'];
$removed = [];
foreach ($patterns as $pat) {
    foreach (glob($dir . $pat) as $file) {
        // protection: ne pas supprimer ce script
        if (realpath($file) === realpath(__FILE__)) continue;
        if (@unlink($file)) {
            $removed[] = basename($file);
        }
    }
}
if (empty($removed)) {
    echo "Aucun fichier de log trouvé.\n";
    exit(0);
}
echo "Fichiers supprimés:\n";
foreach ($removed as $f) echo " - $f\n";
exit(0);
