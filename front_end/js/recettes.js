/**
 * recettes.js - Gestion du listing des recettes avec recherche, filtres et pagination
 */

$(document).ready(async function() {
    const recipesContainer = $('#mgrid');
    const paginationContainer = $('#recettes-pagination');
    const categorySelect = $('#recipesCategorySelect');
    const difficultySelect = $('#recipesDifficultySelect');
    const searchInput = $('#recipesSearchInput');

    let currentPage = 1;
    const itemsPerPage = 9; // Grid 3x3

    // Parse URL params for initial values
    const urlParams = new URLSearchParams(window.location.search);
    const initialQuery = urlParams.get('q') || '';
    const initialCategory = urlParams.get('categorie') || 'all';
    
    // Set initial input states
    searchInput.val(initialQuery);

    // Dictionnaire d'images de remplacement si besoin
    function getRecipeImage(image) {
        return image ? `http://localhost/gouts_benin/${image}` : 'img/menu/1.jpg';
    }

    // 1. Charger les catégories pour le select
    async function loadCategories() {
        try {
            const categories = await apiRequest('GET', '/categories');
            categories.forEach(cat => {
                const selected = cat.slug === initialCategory ? 'selected' : '';
                categorySelect.append(`<option value="${cat.slug}" ${selected}>${cat.nom}</option>`);
            });
        } catch (error) {
            console.error("Erreur de récupération des catégories:", error);
        }
    }

    // 2. Charger les recettes avec filtres
    async function loadRecipes() {
        recipesContainer.html(`
            <div class="col-12 text-center my-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Chargement des recettes...</span>
                </div>
            </div>
        `);
        paginationContainer.empty();

        const query = searchInput.val().trim();
        const category = categorySelect.val();
        const difficulty = difficultySelect.val();

        // Build query string
        let endpoint = `/recettes?page=${currentPage}&limit=${itemsPerPage}`;
        if (query) endpoint += `&q=${encodeURIComponent(query)}`;
        if (category && category !== 'all') endpoint += `&categorie=${encodeURIComponent(category)}`;
        if (difficulty) endpoint += `&difficulte=${encodeURIComponent(difficulty)}`;

        try {
            // Récupérer les favoris si l'utilisateur est connecté
            const favoriteIds = new Set();
            if (localStorage.getItem('jwt_token')) {
                try {
                    const favs = await apiRequest('GET', '/favoris');
                    if (Array.isArray(favs)) {
                        favs.forEach(f => favoriteIds.add(parseInt(f.id)));
                    }
                } catch (e) {
                    console.warn("Impossible de charger les favoris", e);
                }
            }

            const data = await apiRequest('GET', endpoint);
            const recipes = data.recettes || [];
            const pagination = data.pagination || {};

            recipesContainer.empty();

            if (recipes.length === 0) {
                recipesContainer.html('<div class="col-12 text-center text-muted py-5">Aucune recette ne correspond à votre recherche.</div>');
                return;
            }

            recipes.forEach((rec, index) => {
                const delay = (index % 3) * 80;
                const isFav = favoriteIds.has(parseInt(rec.id));
                const heartClass = isFav ? 'fas fa-heart' : 'far fa-heart';
                const heartStyle = isFav ? 'color: var(--primary);' : 'color: #ccc;';
                const imagePath = getRecipeImage(rec.image);
                const starsCount = rec.note_moyenne ? Math.round(rec.note_moyenne) : 0;
                
                // Formater les étoiles
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= starsCount) {
                        starsHtml += '<i class="fas fa-star" style="color: var(--secondary);"></i>';
                    } else {
                        starsHtml += '<i class="far fa-star" style="color: #ccc;"></i>';
                    }
                }
                const reviewsText = `(${rec.nb_notes} avis)`;

                let difficultyBadgeClass = 'hot';
                if (rec.difficulte === 'facile') difficultyBadgeClass = 'new';
                else if (rec.difficulte === 'moyen') difficultyBadgeClass = 'popular';

                recipesContainer.append(`
                    <div class="col-sm-6 col-lg-4 mwrap" data-aos="fade-up" data-aos-delay="${delay}">
                        <div class="mcard" data-id="${rec.id}">
                            <div class="mimg">
                                <a href="recette.php?id=${rec.id}"><img src="${imagePath}" onerror="this.src='img/menu/1.jpg'" alt="${rec.titre}"/></a>
                                <div class="mbdg ${difficultyBadgeClass}">
                                    <i class="fas fa-signal"></i> ${rec.difficulte.charAt(0).toUpperCase() + rec.difficulte.slice(1)}
                                </div>
                                <div class="mhrt" data-id="${rec.id}" style="${heartStyle}"><i class="${heartClass}"></i></div>
                            </div>
                            <div class="mbody">
                                <div class="mcat">${rec.nom_categorie}</div>
                                <a href="recette.php?id=${rec.id}" class="mtit" style="text-decoration: none; color: inherit;">${rec.titre}</a>
                                <div class="mdesc">${rec.description.substring(0, 80)}${rec.description.length > 80 ? '...' : ''}</div>
                                <div class="mfoot">
                                    <div>
                                        <div class="mprice">${rec.temps_prep} min • ${rec.portion || 4} pers.</div>
                                        <div class="mstars">${starsHtml} <span style="color:#bbb;font-size:.7rem;">${reviewsText}</span></div>
                                    </div>
                                    <a href="recette.php?id=${rec.id}" class="madd" title="Voir détails" style="text-decoration: none; color: inherit;"><i class="fas fa-plus"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });

            // Afficher la pagination
            renderPagination(pagination);

            // Relancer les animations
            AOS.refresh();

        } catch (error) {
            console.error("Erreur de chargement des recettes:", error);
            recipesContainer.html('<div class="col-12 text-center text-danger">Erreur lors de la récupération des recettes.</div>');
        }
    }

    // 3. Dessiner la pagination
    function renderPagination(pagination) {
        if (!pagination || pagination.last_page <= 1) return;

        const current = pagination.current_page;
        const total = pagination.last_page;

        // Bouton Précédent
        const prevDisabled = current === 1 ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${current - 1}" aria-label="Précédent">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `);

        // Pages
        for (let i = 1; i <= total; i++) {
            const activeClass = i === current ? 'active' : '';
            paginationContainer.append(`
                <li class="page-item ${activeClass}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Bouton Suivant
        const nextDisabled = current === total ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${current + 1}" aria-label="Suivant">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `);
    }

    // Initialisation
    await loadCategories();
    await loadRecipes();

    // Event listener pour la pagination
    $(document).on('click', '#recettes-pagination .page-link', function(e) {
        e.preventDefault();
        const selectedPage = parseInt($(this).attr('data-page'));
        if (selectedPage && selectedPage !== currentPage) {
            currentPage = selectedPage;
            loadRecipes();
            $('html, body').animate({
                scrollTop: $('#menu').offset().top - 80
            }, 300);
        }
    });

    // Event listeners pour les filtres
    let searchTimeout;
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadRecipes();
        }, 400); // Debounce
    });

    categorySelect.on('change', function() {
        currentPage = 1;
        loadRecipes();
    });

    difficultySelect.on('change', function() {
        currentPage = 1;
        loadRecipes();
    });

    // Gestion des favoris
    $(document).on('click', '.mhrt', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!localStorage.getItem('jwt_token')) {
            window.location.href = 'login.php';
            return;
        }

        const id_recette = $(this).attr('data-id');
        const heartIcon = $(this).find('i');
        const heartWrapper = $(this);

        try {
            const res = await apiRequest('POST', '/favoris', { id_recette: parseInt(id_recette) });
            if (res.status === 'added') {
                heartIcon.removeClass('far').addClass('fas');
                heartWrapper.css('color', 'var(--primary)');
            } else {
                heartIcon.removeClass('fas').addClass('far');
                heartWrapper.css('color', '#ccc');
            }
        } catch (err) {
            console.error("Erreur favori:", err);
        }
    });
});
