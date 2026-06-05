/**
 * soumettre.js - Logique de soumission de recette avec upload d'image
 */

$(document).ready(async function() {
    // Rediriger si non connecté
    if (!localStorage.getItem('jwt_token')) {
        window.location.href = 'login.php';
        return;
    }

    const categorySelect = $('#recipeCategorySelect');
    const imageBox = $('#imageBox');
    const imageInput = $('#recipeImageFile');
    const imagePreview = $('#imagePreview');
    const formMessage = $('#formMessage');
    const submitBtn = $('#submitBtn');

    // 1. Charger les catégories dans le select
    async function loadCategories() {
        try {
            const categories = await apiRequest('GET', '/categories');
            categories.forEach(cat => {
                categorySelect.append(`<option value="${cat.id}">${cat.nom}</option>`);
            });
        } catch (error) {
            console.error("Erreur chargement catégories:", error);
            formMessage.html('<div class="alert alert-danger py-2">Impossible de charger les catégories de recettes.</div>');
        }
    }

    // 2. Clic sur la boîte d'image pour déclencher l'input
    imageBox.on('click', function() {
        imageInput.click();
    });

    // 3. Prévisualisation de l'image sélectionnée
    imageInput.on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validation simple de la taille (5 Mo max)
            if (file.size > 5 * 1024 * 1024) {
                formMessage.html('<div class="alert alert-warning py-2">L\'image est trop volumineuse (maximum 5 Mo).</div>');
                imageInput.val(''); // Reset
                imagePreview.hide();
                imageBox.find('i, span').show();
                return;
            }

            // Aperçu
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.attr('src', event.target.result).show();
                imageBox.find('i, span').hide(); // Masquer les textes d'aide
            };
            reader.readAsDataURL(file);
        }
    });

    // Support du drag & drop
    imageBox.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('border-color', 'var(--primary)').css('background', '#fff5f5');
    });

    imageBox.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('border-color', '#ccc').css('background', '#fdfdfd');
    });

    imageBox.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('border-color', '#ccc').css('background', '#fdfdfd');

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            imageInput[0].files = files;
            imageInput.trigger('change');
        }
    });

    // 4. Soumission du formulaire
    $('#submitRecipeForm').on('submit', async function(e) {
        e.preventDefault();

        // Récupérer les données
        const titre = $('#recipeTitleInput').val().trim();
        const categoryId = categorySelect.val();
        const description = $('#recipeDescInput').val().trim();
        const difficulte = $('#recipeDifficultySelect').val();
        const tempsPrep = $('#recipePrepInput').val();
        const tempsCuisson = $('#recipeCookInput').val();
        const portion = $('#recipePortionsInput').val();
        const ingredients = $('#recipeIngredientsInput').val().trim();
        const etapes = $('#recipeStepsInput').val().trim();
        const file = imageInput[0].files[0];

        // Validations
        if (!titre || !categoryId || !description || !ingredients || !etapes) {
            formMessage.html('<div class="alert alert-warning py-2">Veuillez remplir tous les champs obligatoires.</div>');
            return;
        }

        // Créer un FormData pour l'upload multipart
        const formData = new FormData();
        formData.append('titre', titre);
        formData.append('id_categorie', parseInt(categoryId));
        formData.append('description', description);
        formData.append('difficulte', difficulte);
        formData.append('temps_prep', parseInt(tempsPrep));
        formData.append('temps_cuisson', parseInt(tempsCuisson));
        formData.append('portion', parseInt(portion));
        formData.append('ingredients', ingredients);
        formData.append('etapes', etapes);
        
        if (file) {
            formData.append('image', file);
        }

        // Désactiver le bouton pendant l'envoi
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...');
        formMessage.empty();

        try {
            const res = await apiRequest('POST', '/recettes', formData, true);
            
            formMessage.html(`
                <div class="alert alert-success py-3">
                    <h5 class="fw-bold mb-1"><i class="fas fa-check-circle me-1"></i> Recette soumise avec succès !</h5>
                    <p class="mb-0 small">Votre recette "${titre}" a bien été enregistrée et est actuellement en cours de modération. Vous allez être redirigé vers votre espace personnel...</p>
                </div>
            `);

            // Réinitialiser le formulaire
            $('#submitRecipeForm')[0].reset();
            imagePreview.hide();
            imageBox.find('i, span').show();

            // Redirection
            setTimeout(() => {
                window.location.href = 'profil.php';
            }, 3500);

        } catch (error) {
            console.error("Erreur de création de la recette:", error);
            formMessage.html(`<div class="alert alert-danger py-2">Erreur : ${error.message || "Une erreur est survenue lors de l'enregistrement de votre recette."}</div>`);
            submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Soumettre la recette');
        }
    });

    // Initialisation
    await loadCategories();
});
