/**
 * app-recettes.js - Gestion du CRUD Recettes culinaires
 */

document.addEventListener("DOMContentLoaded", async () => {


    const currentPage = window.location.pathname.split('/').pop();

    if (currentPage === 'recettes.html') {
        // Initialiser l'écran de liste
        await initRecettesList();
    } else if (currentPage === 'recette-form.html') {
        // Initialiser l'écran de formulaire
        await initRecetteForm();
    }
});

/* ==========================================
   PAGE LISTE DES RECETTES (recettes.html)
   ========================================== */

let currentRecipesPage = 1;
const recipesLimit = 10;

async function initRecettesList() {
    // 1. Charger les catégories pour le filtre
    await loadCategoriesFilter();

    // 2. Écouter les filtres
    document.getElementById("filterForm").addEventListener("submit", (e) => {
        e.preventDefault();
        currentRecipesPage = 1;
        fetchRecettes(currentRecipesPage);
    });

    document.getElementById("btn-clear-filters").addEventListener("click", () => {
        document.getElementById("filterForm").reset();
        currentRecipesPage = 1;
        fetchRecettes(currentRecipesPage);
    });

    // 3. Charger le premier jeu de données
    // Récupérer le filtre par statut depuis l'URL s'il est spécifié (ex: depuis le widget dashboard)
    const urlParams = new URLSearchParams(window.location.search);
    const statutFilter = urlParams.get('statut');
    if (statutFilter) {
        document.getElementById("filter-statut").value = statutFilter;
    }

    fetchRecettes(currentRecipesPage);
}

async function loadCategoriesFilter() {
    const select = document.getElementById("filter-category");
    if (!select) return;

    try {
        const categories = await apiRequest('GET', '/categories');
        let html = '<option value="">Toutes les catégories</option>';
        categories.forEach(cat => {
            html += `<option value="${cat.id}">${cat.nom}</option>`;
        });
        select.innerHTML = html;
    } catch (e) {
        console.error("Erreur chargement catégories filtres :", e);
    }
}

async function fetchRecettes(page = 1) {
    currentRecipesPage = page;
    const tableBody = document.getElementById("recipes-table-body");
    if (!tableBody) return;

    // Récupérer les filtres
    const category = document.getElementById("filter-category").value;
    const difficulty = document.getElementById("filter-difficulty").value;
    const status = document.getElementById("filter-statut").value;
    const q = document.getElementById("filter-search").value.trim();

    // Construire l'URL avec les filtres
    let queryParams = `admin=true&page=${page}&limit=${recipesLimit}`;
    if (category) queryParams += `&categorie=${category}`;
    if (difficulty) queryParams += `&difficulte=${difficulty}`;
    if (status) queryParams += `&statut=${status}`;
    if (q) queryParams += `&q=${encodeURIComponent(q)}`;

    try {
        const data = await apiRequest('GET', `/recettes?${queryParams}`);
        const recipes = data.recettes || [];
        const pagination = data.pagination;

        if (recipes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted p-4">Aucune recette ne correspond à votre recherche.</td>
                </tr>
            `;
            renderPagination(0, 1);
            return;
        }

        let html = '';
        recipes.forEach(recipe => {
            // Déterminer les boutons d'action
            let actionButtons = '';
            
            if (recipe.statut === 'en_attente') {
                actionButtons += `
                    <button class="btn btn-sm btn-success me-1" onclick="updateRecipeStatus(${recipe.id}, 'publie')" title="Publier">
                        <i class="bi bi-check-lg"></i> Publier
                    </button>
                    <button class="btn btn-sm btn-danger me-1" onclick="updateRecipeStatus(${recipe.id}, 'rejete')" title="Rejeter">
                        <i class="bi bi-x-lg"></i> Rejeter
                    </button>
                `;
            }

            actionButtons += `
                <a href="./recette-form.html?id=${recipe.id}" class="btn btn-sm btn-primary me-1" title="Modifier">
                    <i class="bi bi-pencil"></i>
                </a>
                <button class="btn btn-sm btn-secondary" onclick="deleteRecipe(${recipe.id})" title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            `;

            // Statut Badge
            let statusBadge = '';
            if (recipe.statut === 'publie') {
                statusBadge = '<span class="badge bg-success">Publiée</span>';
            } else if (recipe.statut === 'en_attente') {
                statusBadge = '<span class="badge bg-warning text-dark">En attente</span>';
            } else {
                statusBadge = '<span class="badge bg-danger">Rejetée</span>';
            }

            // Note moyenne
            const noteAvg = recipe.note_moyenne ? parseFloat(recipe.note_moyenne).toFixed(1) : '-';
            const noteCount = recipe.nb_notes ? `(${recipe.nb_notes})` : '';

            html += `
                <tr>
                    <td>
                        <div class="fw-bold">${recipe.titre}</div>
                        <small class="text-muted">Slug: ${recipe.slug}</small>
                    </td>
                    <td>${recipe.nom_categorie || 'Catégorie ID: ' + recipe.id_categorie}</td>
                    <td>${recipe.nom_auteur || 'ID Auteur: ' + recipe.id_auteur}</td>
                    <td><span class="badge bg-light text-dark text-capitalize">${recipe.difficulte}</span></td>
                    <td>${statusBadge}</td>
                    <td>
                        <span class="text-warning"><i class="bi bi-star-fill"></i> ${noteAvg}</span> ${noteCount}
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            ${actionButtons}
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;
        renderPagination(pagination.total_pages, pagination.page);

    } catch (e) {
        console.error("Erreur de chargement des recettes :", e);
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger p-4">Erreur lors de la récupération des recettes.</td>
            </tr>
        `;
    }
}

function renderPagination(totalPages, currentPage) {
    const container = document.getElementById("recipes-pagination");
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="fetchRecettes(${currentPage - 1}); return false;" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    `;

    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="fetchRecettes(${i}); return false;">${i}</a>
            </li>
        `;
    }

    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="fetchRecettes(${currentPage + 1}); return false;" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    `;

    container.innerHTML = html;
}

// Changer le statut d'une recette (Publier/Rejeter)
async function updateRecipeStatus(id, newStatus) {
    try {
        await apiRequest('PUT', `/recettes/${id}`, { statut: newStatus });
        showAlert(`La recette a été modifiée avec succès (${newStatus}).`, "success");
        fetchRecettes(currentRecipesPage);
    } catch (e) {
        showAlert(e.message || "Erreur lors de la modification du statut.", "danger");
    }
}

// Supprimer une recette
async function deleteRecipe(id) {
    if (!confirm("Voulez-vous vraiment supprimer définitivement cette recette ?\nCette action est irréversible et effacera également l'image associée.")) {
        return;
    }

    try {
        await apiRequest('DELETE', `/recettes/${id}`);
        showAlert("Recette supprimée avec succès.", "success");
        fetchRecettes(currentRecipesPage);
    } catch (e) {
        showAlert(e.message || "Erreur lors de la suppression.", "danger");
    }
}


/* ==========================================
   PAGE FORMULAIRE RECETTE (recette-form.html)
   ========================================== */

let isEditMode = false;
let currentRecipeId = null;

async function initRecetteForm() {
    // 1. Charger la liste des catégories pour le select
    await loadCategoriesSelect();

    // 2. Détecter si on est en mode édition
    const urlParams = new URLSearchParams(window.location.search);
    currentRecipeId = urlParams.get('id');

    if (currentRecipeId) {
        isEditMode = true;
        document.getElementById("form-page-title").textContent = "Modifier la Recette";
        document.getElementById("breadcrumb-current-page").textContent = "Modifier";
        await loadRecipeData(currentRecipeId);
    }

    // 3. Gérer la soumission du formulaire
    document.getElementById("recetteForm").addEventListener("submit", handleFormSubmit);
}

async function loadCategoriesSelect() {
    const select = document.getElementById("recette-category");
    if (!select) return;

    try {
        const categories = await apiRequest('GET', '/categories');
        let html = '<option value="" disabled selected>Sélectionnez une catégorie</option>';
        categories.forEach(cat => {
            html += `<option value="${cat.id}">${cat.nom}</option>`;
        });
        select.innerHTML = html;
    } catch (e) {
        console.error("Erreur chargement catégories select :", e);
    }
}

async function loadRecipeData(id) {
    try {
        // Rappel: Notre backend modifié supporte l'ID en paramètre à la place du slug
        const recipe = await apiRequest('GET', `/recettes/${id}`);

        document.getElementById("recette-title").value = recipe.titre;
        document.getElementById("recette-description").value = recipe.description;
        document.getElementById("recette-ingredients").value = recipe.ingredients;
        document.getElementById("recette-etapes").value = recipe.etapes;
        document.getElementById("recette-prep").value = recipe.temps_prep;
        document.getElementById("recette-cuisson").value = recipe.temps_cuisson;
        document.getElementById("recette-portions").value = recipe.portion;
        document.getElementById("recette-difficulty").value = recipe.difficulte;
        document.getElementById("recette-category").value = recipe.id_categorie;

        // Si une image existe déjà, l'afficher
        if (recipe.image) {
            const previewContainer = document.getElementById("image-preview-container");
            if (previewContainer) {
                previewContainer.innerHTML = `
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1">Image actuelle :</small>
<img src="http://localhost/gout_benin/back_end/gouts_benin/uploads/${recipe.image}"
                    </div>
                `;
            }
        }
    } catch (e) {
        showAlert("Erreur lors de la récupération des détails de la recette : " + e.message, "danger");
    }
}

async function handleFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData();

    // Récupérer les valeurs des éléments du formulaire
    const titre = document.getElementById("recette-title").value.trim();
    const description = document.getElementById("recette-description").value.trim();
    const ingredients = document.getElementById("recette-ingredients").value.trim();
    const etapes = document.getElementById("recette-etapes").value.trim();
    const temps_prep = document.getElementById("recette-prep").value;
    const temps_cuisson = document.getElementById("recette-cuisson").value;
    const portion = document.getElementById("recette-portions").value;
    const difficulte = document.getElementById("recette-difficulty").value;
    const id_categorie = document.getElementById("recette-category").value;
    const imageInput = document.getElementById("recette-image");

    if (!titre || !description || !ingredients || !etapes || !id_categorie) {
        showAlert("Veuillez remplir tous les champs obligatoires (*).", "warning");
        return;
    }

    // Remplir le FormData
    formData.append("titre", titre);
    formData.append("description", description);
    formData.append("ingredients", ingredients);
    formData.append("etapes", etapes);
    formData.append("temps_prep", temps_prep);
    formData.append("temps_cuisson", temps_cuisson);
    formData.append("portion", portion); // Nom API pour personnes
    formData.append("difficulte", difficulte);
    formData.append("id_categorie", id_categorie);

    // Ajouter l'image si sélectionnée
    if (imageInput.files.length > 0) {
        formData.append("image", imageInput.files[0]);
    }

    try {
        let result;
        if (isEditMode) {
            // Surcharge PUT requise pour gérer l'upload avec fichier
            formData.append("_method", "PUT");
            result = await apiRequest('POST', `/recettes/${currentRecipeId}`, formData, true);
            showAlert("Recette mise à jour avec succès et renvoyée pour modération !", "success");
        } else {
            result = await apiRequest('POST', '/recettes', formData, true);
            showAlert("Recette créée avec succès et en attente de modération !", "success");
        }

        // Rediriger après 2 secondes vers la liste
        setTimeout(() => {
            window.location.href = 'recettes.html';
        }, 2000);

    } catch (e) {
        showAlert(e.message || "Erreur lors de la sauvegarde.", "danger");
    }
}
