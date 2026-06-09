<?php
require_once __DIR__ . "/../config/database.php";
try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("SELECT c.*, COALESCE(u.pseudo, c.nom_utilisateur) AS auteur_nom FROM commentaires c LEFT JOIN utilisateurs u ON u.id = c.id_utilisateur WHERE c.id_recette = ? AND c.statut = 'approuve' ORDER BY c.date_creation DESC");
    $stmt->execute([12]);
    echo json_encode(["ok"=>true,"rows"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(["ok"=>false,"err"=>$e->getMessage()]);
}
?>
