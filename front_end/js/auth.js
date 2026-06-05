/**
 * auth.js - Gestionnaire de session et de garde d'accès (Guard)
 */

// Liste des pages nécessitant une authentification obligatoire
const PROTECTED_PAGES = [
    'profil.php',
    'soumettre-recette.php'
];

/**
 * Gardien de page (Guard) : redirige vers login.php si non connecté sur une page protégée
 */
function enforceAuthGuard() {
    const token = localStorage.getItem('jwt_token');
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';

    if (PROTECTED_PAGES.includes(currentPage) && !token) {
        window.location.href = 'login.php';
    }
}

/**
 * Déconnexion de l'utilisateur
 */
function logout() {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('user_pseudo');
    localStorage.removeItem('user_email');
    window.location.href = 'index.php';
}

/**
 * Met à jour dynamiquement la barre de navigation selon l'état de connexion
 */
function updateNavbarAuth() {
    const token = localStorage.getItem('jwt_token');
    const pseudo = localStorage.getItem('user_pseudo') || 'Profil';
    const placeholder = document.getElementById('nav-auth-btn-placeholder');
    
    if (!placeholder) return;

    if (token) {
        placeholder.innerHTML = `
            <a href="profil.php" class="nav-link nav-cta d-inline-block px-3 py-1"><i class="fas fa-user me-1"></i>${pseudo}</a>
            <a href="soumettre-recette.php" class="nav-link nav-cta d-inline-block ms-1 px-2 py-1" style="background: var(--secondary); border-color: var(--secondary);" title="Proposer une recette"><i class="fas fa-plus"></i></a>
            <a href="#" onclick="logout(); return false;" class="nav-link d-inline-block text-danger ms-2 align-middle" title="Déconnexion" style="font-size: 1.15rem;"><i class="fas fa-sign-out-alt"></i></a>
        `;
    } else {
        placeholder.innerHTML = `
            <a href="login.php" class="nav-link nav-cta"><i class="fas fa-user me-1"></i>Connexion</a>
        `;
    }
}

// Initialisation immédiate au chargement du fichier
enforceAuthGuard();
document.addEventListener("DOMContentLoaded", () => {
    updateNavbarAuth();
});
