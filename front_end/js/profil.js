/**
 * profil.js - Gestion de l'espace utilisateur personnel et de ses favoris
 */

$(document).ready(async function() {
    // Redirection si non connecté
    if (!localStorage.getItem('jwt_token')) {
        window.location.href = 'login.php';
        return;
    }

    const profileName = $('#profileName');
    const profileEmail = $('#profileEmail');
    const profileRole = $('#profileRole');
    const profileAvatar = $('#profileAvatar');
    const favoritesGrid = $('#favoritesGrid');

    // Fonction pour récupérer les initiales
    function getInitials(name) {
        if (!name) return 'U';
        return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    }

    // Charger les informations du profil
    async function loadUserProfile() {
        try {
            const user = await apiRequest('GET', '/auth/profil');
            
            profileName.text(user.nom);
            profileEmail.text(user.email);
            profileAvatar.text(getInitials(user.nom));

            // Rôle badges
            if (user.role === 'admin') {
                profileRole.text('Administrateur').removeClass('bg-dark').addClass('bg-danger');
                
                // Ajouter un lien vers le tableau de bord Admin
                if ($('#adminLink').length === 0) {
                    $('#profileLogoutBtn').before(`
                        <a href="/gouts_benin/admin/index.html" id="adminLink" class="btn btn-warning py-2 fw-semibold mb-2 text-dark">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard Admin
                        </a>
                    `);
                }
            } else {
                profileRole.text('Membre').removeClass('bg-danger').addClass('bg-dark');
            }
        } catch (error) {
            console.error("Erreur de récupération du profil:", error);
            // Si le token est expiré ou invalide
            localStorage.removeItem('jwt_token');
            localStorage.removeItem('user_info');
            window.location.href = 'login.php';
        }
    }

    // Charger les recettes favorites
    async function loadFavoriteRecipes() {
        favoritesGrid.html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `);

        try {
            const favorites = await apiRequest('GET', '/favoris');
            favoritesGrid.empty();

            if (favorites.length === 0) {
                favoritesGrid.html(`
                    <div class="col-12 text-center py-5">
                        <div class="mb-3 text-muted"><i class="far fa-heart" style="font-size: 3rem;"></i></div>
                        <p class="text-secondary">Vous n'avez pas encore ajouté de recettes favorites.</p>
                        <a href="recettes.php" class="btn btn-sm btn-outline-danger mt-2 px-3">Parcourir les recettes</a>
                    </div>
                `);
                return;
            }

            favorites.forEach((rec, index) => {
                const delay = (index % 3) * 80;
                const imagePath = rec.image ? `http://localhost/gouts_benin/${rec.image}` : 'img/menu/1.jpg';
                const starsCount = rec.note_moyenne ? Math.round(rec.note_moyenne) : 0;

                // Étoiles de la note moyenne
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= starsCount) {
                        starsHtml += '<i class="fas fa-star" style="color: var(--secondary);"></i>';
                    } else {
                        starsHtml += '<i class="far fa-star" style="color: #ccc;"></i>';
                    }
                }
                const reviewsText = `(${rec.nb_notes || 0} avis)`;

                favoritesGrid.append(`
                    <div class="col-sm-6 mwrap" data-aos="fade-up" data-aos-delay="${delay}">
                        <div class="mcard" data-id="${rec.id}">
                            <div class="mimg">
                                <a href="recette.php?id=${rec.id}"><img src="${imagePath}" onerror="this.src='img/menu/1.jpg'" alt="${rec.titre}"/></a>
                                <div class="mhrt" data-id="${rec.id}" style="color: var(--primary);"><i class="fas fa-heart"></i></div>
                            </div>
                            <div class="mbody">
                                <a href="recette.php?id=${rec.id}" class="mtit" style="text-decoration: none; color: inherit;">${rec.titre}</a>
                                <div class="mdesc text-truncate">${rec.description || ''}</div>
                                <div class="mfoot">
                                    <div>
                                        <div class="mstars">${starsHtml} <span style="color:#bbb;font-size:.7rem;">${reviewsText}</span></div>
                                    </div>
                                    <a href="recette.php?id=${rec.id}" class="madd" title="Voir détails" style="text-decoration: none; color: inherit;"><i class="fas fa-plus"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });

            // Relancer les animations
            AOS.refresh();

        } catch (error) {
            console.error("Erreur de récupération des favoris:", error);
            favoritesGrid.html('<div class="col-12 text-center text-danger">Impossible de charger vos favoris pour le moment.</div>');
        }
    }

    // Gestion du clic sur le cœur pour retirer des favoris
    $(document).on('click', '.mhrt', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        const id_recette = $(this).attr('data-id');
        const cardContainer = $(this).closest('.mwrap');

        try {
            const res = await apiRequest('POST', '/favoris', { id_recette: parseInt(id_recette) });
            // Comme on est sur la page de profil, si on clique on s'attend à retirer le favori
            if (res.status === 'removed') {
                // Animation de suppression
                cardContainer.fadeOut(300, function() {
                    $(this).remove();
                    // Si plus de favoris, afficher le message vide
                    if (favoritesGrid.children('.mwrap').length === 0) {
                        favoritesGrid.html(`
                            <div class="col-12 text-center py-5">
                                <div class="mb-3 text-muted"><i class="far fa-heart" style="font-size: 3rem;"></i></div>
                                <p class="text-secondary">Vous n'avez pas encore ajouté de recettes favorites.</p>
                                <a href="recettes.php" class="btn btn-sm btn-outline-danger mt-2 px-3">Parcourir les recettes</a>
                            </div>
                        `);
                    }
                });
            }
        } catch (err) {
            console.error("Erreur toggle favori:", err);
        }
    });

    // Clic Déconnexion
    $('#profileLogoutBtn').on('click', function() {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user_info');
        window.location.href = 'index.php';
    });

    // Initialisation
    await loadUserProfile();
    await loadFavoriteRecipes();
});
