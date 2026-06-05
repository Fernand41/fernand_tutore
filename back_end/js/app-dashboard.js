/**
 * app-dashboard.js - Logique du Tableau de bord Admin
 */

document.addEventListener("DOMContentLoaded", async () => {
    // S'assurer de la présence du token (le guard s'exécute déjà dans app-auth.js)


    try {
        // 1. Récupérer les informations de profil pour valider la session
        const profile = await apiRequest('GET', '/auth/profil');
        
        // Mettre à jour le header navbar avec le nom
        const headerUserName = document.getElementById("header-user-name");
        if (headerUserName) {
            headerUserName.textContent = profile.nom;
        }

        // 2. Charger les statistiques en parallèle
        loadStats();

        // 3. Charger le tableau des top recettes
        loadTopRecipes();

    } catch (error) {
        console.error("Erreur d'initialisation du dashboard :", error);
    }
});

/**
 * Charge les statistiques globales de l'API
 */
async function loadStats() {
    try {
        // A. Recettes en attente (nous utilisons l'API list avec filtres)
        // Astuce : nous demandons limit=1, et l'API renvoie le total global correspondant dans la pagination
        const recettesPendingData = await apiRequest('GET', '/recettes?admin=true&statut=en_attente&limit=1');
        const countPendingRecettes = recettesPendingData.pagination ? recettesPendingData.pagination.total : 0;
        document.getElementById("count-pending-recipes").textContent = countPendingRecettes;

        // B. Commentaires en attente (nouvel endpoint)
        const commentairesPendingData = await apiRequest('GET', '/commentaires/en-attente');
        const countPendingComments = Array.isArray(commentairesPendingData) ? commentairesPendingData.length : 0;
        document.getElementById("count-pending-comments").textContent = countPendingComments;

        // C. Catégories totales
        const categoriesData = await apiRequest('GET', '/categories');
        const countCategories = Array.isArray(categoriesData) ? categoriesData.length : 0;
        document.getElementById("count-categories").textContent = countCategories;

    } catch (error) {
        console.error("Erreur lors du chargement des statistiques :", error);
        // Fallback en cas d'erreur de connexion BDD (mariadb éteinte)
        document.getElementById("count-pending-recipes").textContent = "0";
        document.getElementById("count-pending-comments").textContent = "0";
        document.getElementById("count-categories").textContent = "0";
    }
}

/**
 * Charge les recettes populaires
 */
async function loadTopRecipes() {
    const tableBody = document.getElementById("top-recipes-table-body");
    if (!tableBody) return;

    try {
        const topRecipes = await apiRequest('GET', '/recettes/top?limit=5');
        
        if (!topRecipes || topRecipes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">Aucune recette populaire trouvée.</td>
                </tr>
            `;
            return;
        }

        let html = '';
        topRecipes.forEach((recipe, index) => {
            const imagePath = recipe.image 
? `http://localhost/gout_benin/back_end/gouts_benin/uploads/${recipe.image}`
                : './assets/img/default-150x150.png';

            html += `
                <tr>
                    <td><span class="badge bg-secondary">${index + 1}</span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${imagePath}" class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <span class="fw-bold">${recipe.titre}</span>
                        </div>
                    </td>
                    <td>
                        <div class="text-warning">
                            <i class="bi bi-star-fill"></i> <strong class="text-dark">${parseFloat(recipe.note_moyenne).toFixed(1)}</strong>
                        </div>
                    </td>
                    <td><span class="text-secondary">${recipe.nb_notes} notes</span></td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;

    } catch (error) {
        console.error("Erreur lors du chargement des top recettes :", error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger">Impossible de charger les recettes populaires.</td>
            </tr>
        `;
    }
}
