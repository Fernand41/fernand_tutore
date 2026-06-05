/**
 * app-auth.js - Gestionnaire d'authentification et requêtes API
 */

const API_BASE_URL = 'http://localhost/gout_benin/back_end/gouts_benin/api';

// ─── MODE DÉVELOPPEMENT ───────────────────────────────────────────────────────
// Token JWT admin valide pour le dev local (expire dans 1 an).
// À RETIRER et remplacer par le vrai système de connexion en production.
const DEV_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwibm9tIjoiQWRtaW4iLCJlbWFpbCI6ImFkbWluQGdvdXQtYmVuaW4uYmoiLCJyb2xlIjoiYWRtaW4iLCJpYXQiOjE3ODA2MTg0NzEsImV4cCI6MTgxMjE1NDQ3MX0.zVWlidIVTxSIxtZc4WvUDgLB7XUC3lXuj9ZJ2pbYKw4';
const DEV_USER  = { id: 1, nom: 'Admin', email: 'marilucmetchihoungbe@gmail.com', role: 'admin' };

if (!localStorage.getItem('jwt_token')) {
    localStorage.setItem('jwt_token', DEV_TOKEN);
    localStorage.setItem('user_info', JSON.stringify(DEV_USER));
}
// ─────────────────────────────────────────────────────────────────────────────

// Guard d'authentification — DÉSACTIVÉ en mode dev (réactiver en production)
function checkAuthGuard() {
    /*
    const token = localStorage.getItem('jwt_token');
    const currentPage = window.location.pathname.split('/').pop();
    if (currentPage !== 'login.html' && !token) {
        window.location.href = 'login.html';
    }
    */
}
checkAuthGuard();

/**
 * Effectue un appel API vers le backend "Goûts du Bénin"
 */
async function apiRequest(method, endpoint, body = null, isFormData = false) {
    const token = localStorage.getItem('jwt_token');
    const headers = {};

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    if (!isFormData) {
        headers['Content-Type'] = 'application/json';
    }

    const options = { method: method.toUpperCase(), headers };

    if (body) {
        options.body = isFormData ? body : JSON.stringify(body);
    }

    toggleLoader(true);

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, options);

        if (response.status === 401) {
            localStorage.removeItem('jwt_token');
            localStorage.removeItem('user_info');
            window.location.href = 'login.html';
            throw new Error("Authentification expirée ou requise.");
        }

        if (response.status === 403) {
            throw new Error("Accès refusé (403). Vérifiez vos permissions côté serveur.");
        }

        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const errorText = await response.text();
            throw new Error(`Réponse non-JSON reçue : ${errorText.substring(0, 200)}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Une erreur est survenue lors de l'appel API.");
        }

        return result.data;
    } catch (error) {
        console.error(`Erreur API [${method} ${endpoint}] :`, error);
        throw error;
    } finally {
        toggleLoader(false);
    }
}

/**
 * Affichage du loader spinner
 */
function toggleLoader(show) {
    const loader = document.getElementById('api-loader');
    if (loader) {
        loader.style.display = show ? 'flex' : 'none';
    }
}

/**
 * Déconnexion
 */
function logout() {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('user_info');
    window.location.href = 'login.html';
}

/**
 * Affiche une alerte Bootstrap 5 temporaire
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;

    const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
    alertContainer.innerHTML = alertHtml;

    setTimeout(() => {
        const active = alertContainer.querySelector('.alert');
        if (active) {
            try { bootstrap.Alert.getOrCreateInstance(active).close(); } catch(e) {}
        }
    }, 5000);
}
