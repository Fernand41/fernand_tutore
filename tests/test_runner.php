<?php
// tests/test_runner.php
// Script de test automatique pour GoûtsBénin (fonctionnels basiques)

$base = 'http://localhost/gouts_benin';
$front = $base . '/front_end';
$cookieFile = __DIR__ . '/cookie.txt';
@unlink($cookieFile);

require_once __DIR__ . '/../config/database.php';

define('VERBOSE', true);

function db_execute($sql, $params = []) {
    static $pdo;
    if (!$pdo) {
        $pdo = Database::getInstance();
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_fetchOne($sql, $params = []) {
    return db_execute($sql, $params)->fetch();
}

function http_get($url) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GoûtsBénin-TestClient/1.0');
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['body'=>$body, 'info'=>$info];
}

function http_post($url, $data, $files = []) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GoûtsBénin-TestClient/1.0');
    // si $files non vide -> multipart
    if (!empty($files)) {
        foreach ($files as $k => $path) {
            if (file_exists($path)) {
                $data[$k] = new CURLFile($path);
            }
        }
    }
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['body'=>$body, 'info'=>$info];
}

function extract_csrf($html) {
    if (preg_match('/name=["\']csrf_token["\'] value=["\']([^"\']+)["\']/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name=csrf_token\s+value="([^"]+)"/', $html, $m)) return $m[1];
    return null;
}

function save_result($name, $ok, $msg = '') {
    echo ($ok ? "[OK] " : "[FAIL]") . " $name" . ($msg ? " - $msg" : "") . "\n";
}

// 1) Test Inscription
$time = time();
$testEmail = "testuser_{$time}@example.com";
$testPseudo = "TestUser{$time}";
$testPass = 'Password123';

$res = http_get($front . '/inscription.php');
$csrf = extract_csrf($res['body']);
if (!$csrf) { save_result('Récup CSRF inscription', false, 'token introuvable'); exit(1); }

$post = [
    'pseudo' => $testPseudo,
    'email' => $testEmail,
    'mot_de_passe' => $testPass,
    'confirmation' => $testPass,
    'csrf_token' => $csrf,
];

$res2 = http_post($base . '/actions/auth_register.php', $post);
// après inscription l'application redirige vers profil et crée la session
if (strpos($res2['body'], 'Compte créé avec succès') !== false || strpos($res2['body'], 'Mon Profil') !== false) {
    save_result('Inscription utilisateur', true, $testEmail);
} else {
    // essayer d'aller sur profil pour confirmer connexion
    $p = http_get($front . '/profil.php');
    if (strpos($p['body'], $testPseudo) !== false || strpos($p['body'], 'Mon Profil') !== false) {
        save_result('Inscription utilisateur', true, $testEmail);
    } else {
        save_result('Inscription utilisateur', false, 'redir ou contenu inattendu');
    }
}

// 2) Test Déconnexion puis Connexion
// Déconnexion si existante
http_get($base . '/actions/auth_logout.php');
$res = http_get($front . '/login.php');
$csrf = extract_csrf($res['body']);
if (!$csrf) { save_result('Récup CSRF login', false, 'token introuvable'); exit(1); }

$post = [
    'email' => $testEmail,
    'mot_de_passe' => $testPass,
    'csrf_token' => $csrf,
];
$res2 = http_post($base . '/actions/auth_login.php', $post);
if (strpos($res2['body'], 'Bienvenue') !== false || strpos(http_get($front . '/profil.php')['body'], $testPseudo) !== false) {
    save_result('Connexion utilisateur', true);
} else {
    save_result('Connexion utilisateur', false, 'Impossible de se connecter');
}

// 3) Trouver une recette publiée pour les tests commentaires/favoris
$list = http_get($front . '/recettes.php');
$recipeSlug = null;
if (preg_match('/recette.php\?slug=([^"\']+)/', $list['body'], $m)) {
    $recipeSlug = $m[1];
}
if (!$recipeSlug) {
    // fallback : tenter de récupérer la première recette via index
    if (preg_match('/recette.php\?slug=([^"\']+)/', http_get($front . '/index.php')['body'], $m2)) {
        $recipeSlug = $m2[1];
    }
}

if (!$recipeSlug) {
    save_result('Recherche recette publiée', false, 'aucune recette publiée trouvée');
} else {
    save_result('Recherche recette publiée', true, $recipeSlug);
}

// 4) Test Favoris (toggle)
if ($recipeSlug) {
    // charger page recette pour récupérer id_recette et csrf
    $recPage = http_get($front . '/recette.php?slug=' . $recipeSlug);
    $csrf = extract_csrf($recPage['body']);
    // extraire id dans le formulaire hidden id_recette
    $idRec = null;
    if (preg_match('/name=["\']id_recette["\'] value=["\']?(\d+)["\']?/', $recPage['body'], $mm)) {
        $idRec = $mm[1];
    } elseif (preg_match('/name="id_recette" value="(\d+)"/', $recPage['body'], $mm2)) {
        $idRec = $mm2[1];
    }
    if (!$csrf || !$idRec) {
        save_result('Préparer toggle favoris', false, 'csrf ou id_recette introuvable');
    } else {
        $post = ['id_recette' => $idRec, 'csrf_token' => $csrf];
        $resFav = http_post($base . '/actions/recette_favorite.php', $post);
        if (strpos($resFav['body'], 'Recette ajoutée') !== false || strpos($resFav['body'], 'Recette retirée') !== false) {
            save_result('Toggle favoris', true);
        } else {
            // vérifier profil pour présence/absence
            $prof = http_get($front . '/profil.php');
            if (strpos($prof['body'], 'Mes Recettes Favorites') !== false) {
                save_result('Toggle favoris', true, 'vérification profil ok');
            } else {
                save_result('Toggle favoris', false, 'réponse inattendue');
            }
        }
    }
}

// 5) Test Commentaire
if ($recipeSlug && isset($idRec)) {
    // recharger recette page pour csrf si nécessaire
    $recPage = http_get($front . '/recette.php?slug=' . $recipeSlug);
    $csrf = extract_csrf($recPage['body']);
    if (!$csrf) {
        save_result('Préparer commentaire', false, 'csrf introuvable');
    } else {
        $post = [
            'id_recette' => $idRec,
            'note' => 4,
            'contenu' => 'Test automatique: très bonne recette',
            'csrf_token' => $csrf,
        ];
        $resC = http_post($base . '/actions/recette_comment.php', $post);
        if (strpos($resC['body'], 'Merci pour votre avis') !== false) {
            save_result('Soumission commentaire', true);
        } else {
            // vérifier redirection/flash
            $recAfter = http_get($front . '/recette.php?slug=' . $recipeSlug);
            if (strpos($recAfter['body'], 'Merci pour votre avis') !== false) {
                save_result('Soumission commentaire', true);
            } else {
                save_result('Soumission commentaire', false, 'réponse inattendue');
                if (VERBOSE) {
                    echo "--- réponse brute action commentaire ---\n";
                    echo substr($resC['body'], 0, 4000) . "\n";
                    echo "--- page recette après tentative ---\n";
                    echo substr($recAfter['body'], 0, 4000) . "\n";
                }
            }
        }
    }
}

// 6) Test Soumission de recette (rapide) - sans image
$submitPage = http_get($front . '/soumettre-recette.php');
$csrf = extract_csrf($submitPage['body']);
if (!$csrf) {
    save_result('Préparer soumission recette', false, 'csrf introuvable');
} else {
    // choisir une catégorie existante (1)
    $post = [
        'titre' => 'Test Recette ' . $time,
        'description' => 'Description test automatique',
        'ingredients' => 'Ingrédient A\nIngrédient B',
        'etapes' => 'Étape 1\nÉtape 2',
        'video_youtube' => '',
        'difficulte' => 'facile',
        'temps_prep' => 10,
        'temps_cuisson' => 5,
        'portion' => 2,
        'id_categorie' => 1,
        'csrf_token' => $csrf,
    ];
    $resSub = http_post($base . '/actions/recette_submit.php', $post);
    if (strpos($resSub['body'], 'Votre recette') !== false || strpos(http_get($front . '/profil.php')['body'], 'Votre recette') !== false) {
        save_result('Soumission recette', true);
        $submittedTitle = $post['titre'];

        // Récupérer l'id et le slug de la recette soumise
        $submittedRecipe = db_fetchOne(
            'SELECT id, slug FROM recettes WHERE titre = ? AND id_auteur = (SELECT id FROM utilisateurs WHERE email = ? LIMIT 1) ORDER BY id DESC LIMIT 1',
            [$submittedTitle, $testEmail]
        );
        if ($submittedRecipe) {
            $submittedRecipeId = $submittedRecipe['id'];
            $submittedRecipeSlug = $submittedRecipe['slug'];
            save_result('Récup recette soumise', true, "id={$submittedRecipeId} slug={$submittedRecipeSlug}");
        } else {
            save_result('Récup recette soumise', false, 'introuvable en base');
        }
    } else {
        save_result('Soumission recette', false, 'réponse inattendue');
        if (VERBOSE) {
            echo "--- réponse brute action soumission ---\n";
            echo substr($resSub['body'], 0, 4000) . "\n";
            echo "--- page soumettre-recette (form) ---\n";
            echo substr($submitPage['body'], 0, 4000) . "\n";
        }
    }
}

// 7) Promotion du compte en admin et publication de la recette soumise
if (!empty($submittedRecipeId) && !empty($submittedRecipeSlug)) {
    // Donner le rôle admin à l'utilisateur courant
    db_execute('UPDATE utilisateurs SET role = ? WHERE email = ?', ['admin', $testEmail]);
    save_result('Promotion du compte en admin', true);

    // Déconnexion puis reconnexion en tant qu'admin
    http_get($base . '/actions/auth_logout.php');
    $loginPage = http_get($front . '/login.php');
    $csrf = extract_csrf($loginPage['body']);
    $post = [
        'email' => $testEmail,
        'mot_de_passe' => $testPass,
        'csrf_token' => $csrf,
    ];
    $resAdminLogin = http_post($base . '/actions/auth_login.php', $post);
    if (strpos($resAdminLogin['body'], 'Administration') !== false || strpos($resAdminLogin['body'], 'Tableau de bord') !== false) {
        save_result('Connexion admin', true);
    } else {
        save_result('Connexion admin', false, 'Impossible de se connecter en admin');
    }

    // Charger la page d'administration et extraire le token CSRF
    $adminPage = http_get($base . '/admin/recettes.php');
    $csrfAdmin = extract_csrf($adminPage['body']);
    if (!$csrfAdmin) {
        save_result('Préparer publication admin', false, 'csrf introuvable');
    } else {
        $post = [
            'action' => 'publier',
            'id' => $submittedRecipeId,
            'csrf_token' => $csrfAdmin,
        ];
        $resPublish = http_post($base . '/admin/recettes.php', $post);
        if (strpos($resPublish['body'], 'Statut de la recette mis à jour') !== false) {
            save_result('Publication admin', true);
        } else {
            save_result('Publication admin', false, 'réponse inattendue');
            if (VERBOSE) {
                echo "--- réponse brute publication admin ---\n";
                echo substr($resPublish['body'], 0, 4000) . "\n";
            }
        }
    }

    // Vérifier l'affichage sur la page des recettes front-end
    $recipesPage = http_get($front . '/recettes.php');
    if (strpos($recipesPage['body'], $submittedTitle) !== false || strpos($recipesPage['body'], $submittedRecipeSlug) !== false) {
        save_result('Recette publiée visible dans les recettes', true);
    } else {
        save_result('Recette publiée visible dans les recettes', false, 'titre ou slug introuvable');
    }

    // Vérifier la page détail de la recette publiée
    $recDetail = http_get($front . '/recette.php?slug=' . $submittedRecipeSlug);
    if (strpos($recDetail['body'], $submittedTitle) !== false || strpos($recDetail['body'], 'Commentaires') !== false) {
        save_result('Page détail recette publiée', true);
    } else {
        save_result('Page détail recette publiée', false, 'détail introuvable');
    }

    // Commentaire sur la recette publiée
    $csrf = extract_csrf($recDetail['body']);
    $commentPost = [
        'id_recette' => $submittedRecipeId,
        'note' => 5,
        'contenu' => 'Commentaire automatique après publication',
        'csrf_token' => $csrf,
    ];
    $resComment = http_post($base . '/actions/recette_comment.php', $commentPost);
    if (strpos($resComment['body'], 'Merci pour votre avis') !== false || strpos(http_get($front . '/recette.php?slug=' . $submittedRecipeSlug)['body'], 'Merci pour votre avis') !== false) {
        save_result('Commentaire sur recette publiée', true);
    } else {
        save_result('Commentaire sur recette publiée', false, 'réponse inattendue');
    }

    // Favoris sur la recette publiée
    $recPage = http_get($front . '/recette.php?slug=' . $submittedRecipeSlug);
    $csrf = extract_csrf($recPage['body']);
    $favPost = ['id_recette' => $submittedRecipeId, 'csrf_token' => $csrf];
    $resFav = http_post($base . '/actions/recette_favorite.php', $favPost);
    $favSuccess = strpos($resFav['body'], 'Recette ajoutée') !== false || strpos($resFav['body'], 'Recette retirée') !== false;
    $profile = http_get($front . '/profil.php');
    $profileHasFavorite = strpos($profile['body'], $submittedTitle) !== false || strpos($profile['body'], $submittedRecipeSlug) !== false;
    if ($favSuccess) {
        save_result('Favoris sur recette publiée', true);
    } elseif ($profileHasFavorite) {
        save_result('Favoris sur recette publiée', true, 'vérifié via profil');
    } else {
        save_result('Favoris sur recette publiée', false, 'réponse inattendue');
    }

    if ($profileHasFavorite) {
        save_result('Favoris visible dans le profil', true);
    } else {
        save_result('Favoris visible dans le profil', false, 'non trouvé dans profil');
    }
}

echo "\nTests terminés.\n";

