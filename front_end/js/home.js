/**
 * home.js - Logique dynamique pour la page d'accueil
 */

$(document).ready(async function() {
    const recipesContainer = $('#mgrid');
    const categoriesContainer = $('#category .row');
    const filterButtonsContainer = $('#menu .text-center.mb-4');
    
    // Afficher des spinners de chargement
    recipesContainer.html(`
        <div class="col-12 text-center my-5">
            <div class="spinner-border text-danger" role="status">
                <span class="visually-hidden">Chargement des recettes...</span>
            </div>
        </div>
    `);
    
    categoriesContainer.html(`
        <div class="col-12 text-center my-3">
            <div class="spinner-border text-danger" role="status">
                <span class="visually-hidden">Chargement des catégories...</span>
            </div>
        </div>
    `);

    // Dictionnaire d'images locales correspondantes aux catégories
    function getCategoryImage(slug) {
        const maps = {
            'plats': 'img/category/2.jpg',
            'plats-principaux': 'img/category/2.jpg',
            'soupes': 'img/category/3.webp',
            'soupes-sauces': 'img/category/3.webp',
            'entrees': 'img/category/4.png',
            'boissons': 'img/category/5.jpg',
            'desserts': 'img/category/6.jpg'
        };
        return maps[slug] || 'img/menu/1.jpg';
    }

    try {
        // 1. Charger les catégories
        const categories = await apiRequest('GET', '/categories');
        
        // Vider et injecter "Toutes"
        categoriesContainer.empty();
        categoriesContainer.append(`
            <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="0">
                <div class="catcard active" data-filter="all">
                    <img class="catimg" src="img/category/1.jpeg" alt="Toutes les recettes"/>
                    <div class="catnm">Toutes</div>
                    <div class="catct">Toutes les recettes</div>
                </div>
            </div>
        `);

        // Boutons de filtre sous le menu
        filterButtonsContainer.empty();
        filterButtonsContainer.append(`<button class="filtbtn active" data-f="all">Toutes</button>`);

        categories.forEach((cat, index) => {
            const delay = (index + 1) * 70;
            const imgPath = getCategoryImage(cat.slug);
            const countText = cat.nb_recettes > 1 ? `${cat.nb_recettes} recettes` : `${cat.nb_recettes} recette`;
            
            categoriesContainer.append(`
                <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="${delay}">
                    <div class="catcard" data-filter="${cat.slug}">
                        <img class="catimg" src="${imgPath}" alt="${cat.nom}"/>
                        <div class="catnm">${cat.nom}</div>
                        <div class="catct">${countText}</div>
                    </div>
                </div>
            `);

            filterButtonsContainer.append(`
                <button class="filtbtn" data-f="${cat.slug}">${cat.nom}</button>
            `);
        });

        // 2. Charger les favoris si connecté
        const favoriteIds = new Set();
        if (localStorage.getItem('jwt_token')) {
            try {
                const favs = await apiRequest('GET', '/favoris');
                if (Array.isArray(favs)) {
                    favs.forEach(f => favoriteIds.add(parseInt(f.id)));
                }
            } catch (err) {
                console.warn("Impossible de récupérer les favoris", err);
            }
        }

        // 3. Charger les 6 dernières recettes
        const data = await apiRequest('GET', '/recettes?limit=6');
        const recipes = data.recettes || [];
        
        recipesContainer.empty();
        if (recipes.length === 0) {
            recipesContainer.html('<div class="col-12 text-center text-muted">Aucune recette publiée pour le moment.</div>');
        } else {
            recipes.forEach((rec, index) => {
                const delay = index * 80;
                const isFav = favoriteIds.has(parseInt(rec.id));
                const heartClass = isFav ? 'fas fa-heart' : 'far fa-heart';
                const heartStyle = isFav ? 'color: var(--primary);' : 'color: #ccc;';
                const imagePath = rec.image ? `http://localhost/gouts_benin/${rec.image}` : 'img/menu/1.jpg';
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
                const reviewsText = rec.nb_notes > 1 ? `(${rec.nb_notes} avis)` : `(${rec.nb_notes} avis)`;

                // Formater la difficulté pour le badge
                let difficultyBadgeClass = 'hot';
                if (rec.difficulte === 'facile') difficultyBadgeClass = 'new';
                else if (rec.difficulte === 'moyen') difficultyBadgeClass = 'popular';

                recipesContainer.append(`
                    <div class="col-sm-6 col-lg-4 mwrap" data-c="${rec.slug_categorie}" data-aos="fade-up" data-aos-delay="${delay}">
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
        }

        // Rafraîchir les animations AOS
        AOS.refresh();

    } catch (error) {
        console.error("Erreur lors de l'initialisation de l'accueil:", error);
        recipesContainer.html('<div class="col-12 text-center text-danger">Erreur lors de la récupération des données de la page d\'accueil.</div>');
    }

    // Gestion du clic sur les cœurs (Favoris)
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
            console.error("Erreur toggle favori:", err);
        }
    });

    // Filtres dynamiques sur la page d'accueil (locale)
    function filterMenu(cat) {
        // sync filter buttons
        $('.filtbtn').each(function() {
            $(this).toggleClass('active', $(this).attr('data-f') === cat);
        });
        // sync category cards
        $('.catcard').each(function() {
            $(this).toggleClass('active', $(this).attr('data-filter') === cat);
        });
        // show/hide menu cards
        $('.mwrap').each(function() {
            var c = $(this).attr('data-c');
            if (cat === 'all' || c === cat) {
                $(this).removeClass('gone');
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(16px)',
                    'display': ''
                });
                setTimeout(() => {
                    $(this).css({
                        'transition': 'opacity .38s, transform .38s',
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });
                }, 60);
            } else {
                $(this).addClass('gone').css('display', 'none');
            }
        });
    }

    // Clic sur les boutons de filtre
    $(document).on('click', '.filtbtn', function() {
        filterMenu($(this).attr('data-f'));
    });

    // Clic sur les cartes de catégorie
    $(document).on('click', '.catcard', function() {
        var f = $(this).attr('data-filter');
        $('html, body').animate({
            scrollTop: $('#menu').offset().top - 80
        }, 300);
        setTimeout(function() {
            filterMenu(f);
        }, 350);
    });

    // Overlay de recherche - redirection vers recettes.php
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            const q = $(this).val().trim();
            if (q) {
                window.location.href = `recettes.php?q=${encodeURIComponent(q)}`;
            }
        }
    });

    $('.sovinput button').on('click', function(e) {
        e.preventDefault();
        const q = $('#searchInput').val().trim();
        if (q) {
            window.location.href = `recettes.php?q=${encodeURIComponent(q)}`;
        }
    });

    // Clic sur les catégories dans le search overlay
    $(document).on('click', '.sovcat', function() {
        const cat = $(this).attr('data-cat');
        if (cat === 'all') {
            window.location.href = 'recettes.php';
        } else {
            window.location.href = `recettes.php?categorie=${encodeURIComponent(cat)}`;
        }
    });
});
