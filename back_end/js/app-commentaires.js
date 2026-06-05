/**
 * app-commentaires.js - Gestion de la modération des commentaires
 */

document.addEventListener("DOMContentLoaded", async () => {


    fetchPendingComments();
});

/**
 * Récupère tous les commentaires en attente et les affiche
 */
async function fetchPendingComments() {
    const tableBody = document.getElementById("comments-table-body");
    if (!tableBody) return;

    try {
        const comments = await apiRequest('GET', '/commentaires/en-attente');

        if (!comments || comments.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted p-4">
                        <i class="bi bi-chat-left-check me-2" style="font-size: 1.5rem;"></i>
                        Aucun commentaire en attente de modération.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        comments.forEach(comment => {
            const dateStr = comment.date_creation || comment.created_at || '';
            const formattedDate = dateStr ? new Date(dateStr).toLocaleString('fr-FR') : '-';

            html += `
                <tr>
                    <td>
                        <strong class="text-success">${comment.titre}</strong>
                    </td>
                    <td>
                        <div class="fw-bold">${comment.pseudo || 'Utilisateur anonyme'}</div>
                        <small class="text-muted">ID: ${comment.id_utilisateur}</small>
                    </td>
                    <td class="text-wrap" style="max-width: 300px;">
                        ${escapeHtml(comment.contenu)}
                    </td>
                    <td>${formattedDate}</td>
                    <td>
                        <span class="badge bg-warning text-dark">En attente</span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success" onclick="moderateComment(${comment.id}, 'approuve')" title="Approuver">
                                <i class="bi bi-check-lg me-1"></i> Approuver
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="moderateComment(${comment.id}, 'rejete')" title="Rejeter">
                                <i class="bi bi-x-lg me-1"></i> Rejeter
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;

    } catch (e) {
        console.error("Erreur lors du chargement des commentaires :", e);
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger p-4">
                    Erreur lors de la récupération des commentaires en attente.
                </td>
            </tr>
        `;
    }
}

/**
 * Modère un commentaire en l'approuvant ou le rejetant
 * 
 * @param {number} id ID du commentaire
 * @param {string} status 'approuve' ou 'rejete'
 */
async function moderateComment(id, status) {
    const actionText = status === 'approuve' ? "approuvé" : "rejeté";
    
    try {
        await apiRequest('PUT', `/commentaires/${id}/moderer`, { statut: status });
        showAlert(`Le commentaire a été ${actionText} avec succès.`, "success");
        // Recharger le tableau
        fetchPendingComments();
    } catch (e) {
        showAlert(e.message || "Erreur lors de la modération.", "danger");
    }
}

/**
 * Protège contre les injections XSS dans le tableau
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
