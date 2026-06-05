/**
 * recette.js - Gestion de la fiche détail d'une recette
 */

$(document).ready(async function() {
    // 1. Récupérer l'ID ou le slug depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const recipeId = urlParams.get('id');
    const recipeSlug = urlParams.get('slug');
    const identifier = recipeId || recipeSlug;

    if (!identifier) {
        // Rediriger vers recettes.php si aucun paramètre
        window.location.href = 'recettes.php';
        return;
    }

    let recipeData = null;
    const isConnected = !!localStorage.getItem('jwt_token');

    // Éléments DOM
    const headerBgImage = $('#headerBgImage');
    const recipeCategoryName = $('#recipeCategoryName');
    const recipeDifficultyName = $('#recipeDifficultyName');
    const recipeTitle = $('#recipeTitle');
    const recipeDescription = $('#recipeDescription');
    const recipePrepTime = $('#recipePrepTime');
    const recipeCookTime = $('#recipeCookTime');
    const recipePortions = $('#recipePortions');
    const recipeRating = $('#recipeRating');
    const recipeImage = $('#recipeImage');
    const ingredientsList = $('#ingredientsList');
    const stepsContainer = $('#stepsContainer');
    const authorAvatar = $('#authorAvatar');
    const authorName = $('#authorName');
    const authorRole = $('#authorRole');
    const suggestionsContainer = $('#suggestionsContainer');
    const commentsContainer = $('#commentsContainer');
    const commentsCount = $('#commentsCount');
    const favBtn = $('#favBtn');

    // Initialisation de l'affichage de connexion pour les avis
    if (isConnected) {
        $('#notConnectedAlert').addClass('d-none');
        $('#commentForm').removeClass('d-none');
    } else {
        $('#notConnectedAlert').removeClass('d-none');
        $('#commentForm').addClass('d-none');
    }

    // Fonction pour générer l'avatar à partir des initiales
    function getInitials(name) {
        if (!name) return 'U';
        return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    }

    // Charger les détails de la recette
    async function loadRecipeDetail() {
        try {
            const res = await apiRequest('GET', `/recettes/${identifier}`);
            recipeData = res;

            // Remplir les informations
            document.title = `${recipeData.titre} - GoûtsBénin`;
            recipeTitle.text(recipeData.titre);
            recipeDescription.text(recipeData.description);
            recipeCategoryName.text(recipeData.nom_categorie || 'Recette');
            recipeDifficultyName.text(recipeData.difficulte || 'moyen');
            
            // Modifier la classe de difficulté
            recipeDifficultyName.removeClass().addClass('badge px-3 py-2 text-uppercase mb-2 ms-2');
            if (recipeData.difficulte === 'facile') recipeDifficultyName.addClass('bg-success');
            else if (recipeData.difficulte === 'moyen') recipeDifficultyName.addClass('bg-warning text-dark');
            else recipeDifficultyName.addClass('bg-danger');

            recipePrepTime.text(`${recipeData.temps_prep || '--'} min`);
            recipeCookTime.text(`${recipeData.temps_cuisson || '--'} min`);
            recipePortions.text(`${recipeData.portion || '--'} pers.`);
            
            const avgRating = recipeData.note_moyenne ? parseFloat(recipeData.note_moyenne).toFixed(1) : 'Aucun';
            const nbNotesText = recipeData.nb_notes > 0 ? ` (${recipeData.nb_notes} note${recipeData.nb_notes > 1 ? 's' : ''})` : '';
            recipeRating.text(`${avgRating} / 5${nbNotesText}`);

            const imgPath = recipeData.image ? `http://localhost/gouts_benin/${recipeData.image}` : 'img/menu/1.jpg';
            recipeImage.attr('src', imgPath);
            headerBgImage.css('background-image', `url('${imgPath}')`);

            // Auteur
            authorName.text(recipeData.nom_auteur || 'Membre');
            authorAvatar.text(getInitials(recipeData.nom_auteur || 'Membre'));
            if (recipeData.role_auteur === 'admin') {
                authorRole.text('Administrateur').removeClass('bg-dark').addClass('bg-danger');
            } else {
                authorRole.text('Membre').removeClass('bg-danger').addClass('bg-dark');
            }

            // Ingrédients
            ingredientsList.empty();
            if (recipeData.ingredients) {
                // Split par saut de ligne ou point-virgule
                const ingredients = recipeData.ingredients.split(/\r?\n/);
                ingredients.forEach(ing => {
                    const cleanIng = ing.trim().replace(/^-\s*/, ''); // Retire les tirets éventuels au début
                    if (cleanIng) {
                        ingredientsList.append(`<li>${cleanIng}</li>`);
                    }
                });
            } else {
                ingredientsList.html('<li class="text-muted">Aucun ingrédient spécifié.</li>');
            }

            // Étapes
            stepsContainer.empty();
            if (recipeData.etapes) {
                const steps = recipeData.etapes.split(/\r?\n/);
                let stepNum = 1;
                steps.forEach(step => {
                    const cleanStep = step.trim().replace(/^\d+[\.\)\-]\s*/, ''); // Retire les numéros éventuels au début
                    if (cleanStep) {
                        stepsContainer.append(`
                            <div class="step-item">
                                <div class="step-number">${stepNum}</div>
                                <div class="step-content">
                                    <h5>Étape ${stepNum}</h5>
                                    <p>${cleanStep}</p>
                                </div>
                            </div>
                        `);
                        stepNum++;
                    }
                });
            } else {
                stepsContainer.html('<p class="text-muted">Aucune étape de préparation renseignée.</p>');
            }

            // Vérifier si cette recette est dans les favoris
            if (isConnected) {
                try {
                    const favoris = await apiRequest('GET', '/favoris');
                    const isFav = favoris.some(f => parseInt(f.id) === parseInt(recipeData.id));
                    if (isFav) {
                        favBtn.addClass('active').html('<i class="fas fa-heart text-white"></i> <span>Retirer des Favoris</span>');
                    }
                } catch (e) {
                    console.warn("Erreur chargement favoris", e);
                }
            }

            // Charger les commentaires
            await loadComments(recipeData.id);

            // Charger les suggestions
            await loadSuggestions();

            // Refresh AOS
            AOS.refresh();

        } catch (error) {
            console.error("Erreur de chargement de la recette:", error);
            $('#recipeTitle').text("Recette introuvable");
            $('#recipeDescription').text("La recette demandée n'existe pas ou n'est plus publiée.");
        }
    }

    // Charger les commentaires de la recette
    async function loadComments(idRecette) {
        try {
            const comments = await apiRequest('GET', `/commentaires/${idRecette}`);
            commentsContainer.empty();
            commentsCount.text(comments.length);

            if (comments.length === 0) {
                commentsContainer.html('<p class="text-muted text-center py-3">Aucun commentaire pour le moment. Soyez le premier à donner votre avis !</p>');
                return;
            }

            comments.forEach(c => {
                // Formater la date
                let commentDate = 'Récemment';
                if (c.created_at) {
                    const d = new Date(c.created_at);
                    if (!isNaN(d.getTime())) {
                        commentDate = d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
                    }
                }

                commentsContainer.append(`
                    <div class="comment-item">
                        <div class="d-flex gap-3">
                            <div class="comment-avatar">${getInitials(c.nom_utilisateur || 'Utilisateur')}</div>
                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0">${c.nom_utilisateur || 'Anonyme'}</h6>
                                    <span class="text-muted small">${commentDate}</span>
                                </div>
                                <p class="mb-0 text-secondary" style="font-size: 0.95rem; line-height: 1.5;">${c.contenu}</p>
                            </div>
                        </div>
                    </div>
                `);
            });
        } catch (err) {
            console.error("Erreur de récupération des commentaires", err);
            commentsContainer.html('<p class="text-danger small">Impossible de charger les commentaires.</p>');
        }
    }

    // Charger les suggestions de recettes
    async function loadSuggestions() {
        try {
            const topRecipes = await apiRequest('GET', '/recettes/top?limit=4');
            suggestionsContainer.empty();

            if (topRecipes.length === 0) {
                suggestionsContainer.html('<p class="text-muted small">Aucune suggestion.</p>');
                return;
            }

            // Exclure la recette courante de la suggestion si possible
            const filteredSuggestions = topRecipes.filter(r => parseInt(r.id) !== parseInt(recipeData?.id)).slice(0, 3);

            filteredSuggestions.forEach(r => {
                const imgPath = r.image ? `http://localhost/gouts_benin/${r.image}` : 'img/menu/1.jpg';
                const starsCount = r.note_moyenne ? Math.round(r.note_moyenne) : 0;
                
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= starsCount) {
                        starsHtml += '<i class="fas fa-star text-warning"></i>';
                    } else {
                        starsHtml += '<i class="far fa-star text-muted"></i>';
                    }
                }

                suggestionsContainer.append(`
                    <div class="suggested-recipe-item">
                        <img src="${imgPath}" onerror="this.src='img/menu/1.jpg'" class="suggested-recipe-img" alt="${r.titre}"/>
                        <div>
                            <a href="recette.php?id=${r.id}" class="suggested-recipe-title">${r.titre}</a>
                            <div class="suggested-recipe-stars">${starsHtml} <span class="text-muted small">(${r.nb_notes || 0})</span></div>
                        </div>
                    </div>
                `);
            });
        } catch (e) {
            console.error("Erreur de chargement des suggestions", e);
        }
    }

    // 4. Gestion du clic sur les ingrédients (effet to-do list)
    $(document).on('click', '.ingredients-list li', function() {
        $(this).toggleClass('checked');
    });

    // 5. Clic sur le bouton Favoris
    favBtn.on('click', async function(e) {
        e.preventDefault();
        if (!isConnected) {
            window.location.href = 'login.php';
            return;
        }

        try {
            const res = await apiRequest('POST', '/favoris', { id_recette: parseInt(recipeData.id) });
            if (res.status === 'added') {
                favBtn.addClass('active').html('<i class="fas fa-heart text-white"></i> <span>Retirer des Favoris</span>');
            } else {
                favBtn.removeClass('active').html('<i class="far fa-heart"></i> <span>Ajouter aux Favoris</span>');
            }
        } catch (err) {
            console.error("Erreur toggle favori:", err);
        }
    });

    // 6. Gestion du sélecteur de note (étoiles)
    const ratingStars = $('#ratingInput i');
    const selectedNoteInput = $('#selectedNote');

    ratingStars.on('mouseenter', function() {
        const val = parseInt($(this).attr('data-val'));
        ratingStars.each(function() {
            const starVal = parseInt($(this).attr('data-val'));
            if (starVal <= val) {
                $(this).removeClass('far').addClass('fas active');
            } else {
                $(this).removeClass('fas active').addClass('far');
            }
        });
    });

    $('#ratingInput').on('mouseleave', function() {
        const currentVal = parseInt(selectedNoteInput.val());
        ratingStars.each(function() {
            const starVal = parseInt($(this).attr('data-val'));
            if (starVal <= currentVal) {
                $(this).removeClass('far').addClass('fas active');
            } else {
                $(this).removeClass('fas active').addClass('far');
            }
        });
    });

    ratingStars.on('click', function() {
        const val = parseInt($(this).attr('data-val'));
        selectedNoteInput.val(val);
    });

    // 7. Soumission du commentaire + note
    $('#commentForm').on('submit', async function(e) {
        e.preventDefault();

        const note = parseInt(selectedNoteInput.val());
        const content = $('#commentContent').val().trim();
        const msgDiv = $('#submitMessage');

        if (note === 0) {
            msgDiv.html('<div class="alert alert-warning py-2 small">Veuillez sélectionner une note de 1 à 5 étoiles.</div>');
            return;
        }

        if (!content) {
            msgDiv.html('<div class="alert alert-warning py-2 small">Veuillez rédiger un commentaire.</div>');
            return;
        }

        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi...');

        try {
            // Étape A: Envoyer la note (Upsert)
            await apiRequest('POST', '/notes', {
                id_recette: parseInt(recipeData.id),
                note: note
            });

            // Étape B: Envoyer le commentaire
            await apiRequest('POST', '/commentaires', {
                id_recette: parseInt(recipeData.id),
                contenu: content
            });

            // Message de succès
            msgDiv.html('<div class="alert alert-success py-2 small"><i class="fas fa-check-circle me-1"></i> Avis et note enregistrés ! Votre commentaire apparaîtra après modération.</div>');
            
            // Réinitialiser le formulaire
            $('#commentContent').val('');
            selectedNoteInput.val(0);
            ratingStars.removeClass('fas active').addClass('far');

            // Rafraîchir les détails pour mettre à jour la note moyenne
            setTimeout(async () => {
                await loadRecipeDetail();
                msgDiv.empty();
            }, 3000);

        } catch (error) {
            console.error("Erreur de soumission de l'avis:", error);
            msgDiv.html(`<div class="alert alert-danger py-2 small">Erreur : ${error.message || "Une erreur est survenue lors de l'envoi."}</div>`);
        } finally {
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Soumettre');
        }
    });

    // Initialisation
    await loadRecipeDetail();
});
