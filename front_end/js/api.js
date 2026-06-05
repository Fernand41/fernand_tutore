/**
 * api.js - Service central d'appels API REST pour le frontend public
 */

const API_BASE_URL = 'http://localhost/gouts_benin/api';

/**
 * Effectue une requête HTTP asynchrone vers le backend Goûts du Bénin
 * 
 * @param {string} method GET, POST, PUT, DELETE
 * @param {string} endpoint Point d'accès de l'API (ex: '/recettes')
 * @param {object|FormData|null} body Corps de la requête
 * @param {boolean} isFormData Indique si le corps est un objet FormData (pour upload d'images)
 * @returns {Promise<any>} Données retournées sous la clé 'data' si succès
 * @throws {Error} Si l'API retourne success: false ou en cas d'erreur réseau
 */
async function apiRequest(method, endpoint, body = null, isFormData = false) {
    const token = localStorage.getItem('jwt_token');
    const headers = {};

    // Injecter l'en-tête d'authentification Bearer
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    // Définir le type de contenu si ce n'est pas un formulaire binaire (FormData est géré par le navigateur)
    if (!isFormData) {
        headers['Content-Type'] = 'application/json';
    }

    const options = {
        method: method.toUpperCase(),
        headers: headers
    };

    if (body) {
        options.body = isFormData ? body : JSON.stringify(body);
    }

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, options);

        // Gérer l'expiration de session (401 Unauthorized)
        if (response.status === 401) {
            localStorage.removeItem('jwt_token');
            localStorage.removeItem('user_pseudo');
            localStorage.removeItem('user_email');
            
            // Éviter une boucle infinie de redirection sur login.php
            if (!window.location.pathname.endsWith('login.php')) {
                window.location.href = 'login.php';
            }
            throw new Error("Session expirée. Veuillez vous reconnecter.");
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Une erreur inconnue est survenue.");
        }

        return result.data;
    } catch (error) {
        console.error(`Erreur d'appel API [${method} ${endpoint}] :`, error.message);
        throw error;
    }
}
