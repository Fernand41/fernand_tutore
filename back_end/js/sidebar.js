/**
 * sidebar.js - Injection dynamique de la barre de navigation AdminLTE
 */

document.addEventListener("DOMContentLoaded", () => {
    const sidebarPlaceholder = document.getElementById("app-sidebar-placeholder");
    if (!sidebarPlaceholder) return;

    // Récupérer le nom de l'utilisateur connecté
    let userName = "Admin";
    const userInfoStr = localStorage.getItem('user_info');
    if (userInfoStr) {
        try {
            const userInfo = JSON.parse(userInfoStr);
            userName = userInfo.nom || "Admin";
        } catch (e) {
            console.error("Erreur de parsing des infos utilisateur :", e);
        }
    }

    // Déterminer la page active pour surbrillance
    const path = window.location.pathname;
    const page = path.split("/").pop() || "index.html";

    const isDashboardActive = (page === "index.html" || page === "") ? "active" : "";
    const isRecettesActive = (page === "recettes.html" || page === "recette-form.html") ? "active" : "";
    const isCommentairesActive = (page === "commentaires.html") ? "active" : "";

    const sidebarHtml = `
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
            <!--begin::Brand Link-->
            <a href="./index.html" class="brand-link">
                <!--begin::Brand Image-->
                <img
                    src="./assets/img/AdminLTELogo.png"
                    alt="Logo"
                    class="brand-image opacity-75 shadow"
                />
                <!--end::Brand Image-->
                <!--begin::Brand Text-->
                <span class="brand-text fw-bold">Goûts du Bénin</span>
                <!--end::Brand Text-->
            </a>
            <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->

        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
            <!-- Info Utilisateur Connecté -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex border-bottom border-secondary align-items-center px-3">
                <div class="image">
                    <img src="./assets/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image" style="width: 2.1rem; height: 2.1rem;">
                </div>
                <div class="info ps-3">
                    <span class="d-block text-white fw-bold">${userName}</span>
                    <span class="badge bg-success text-white p-1" style="font-size: 0.7rem;">Administrateur</span>
                </div>
            </div>

            <nav class="mt-2" aria-label="Main navigation">
                <!--begin::Sidebar Menu-->
                <ul
                    class="nav sidebar-menu flex-column"
                    data-lte-toggle="treeview"
                    data-accordion="false"
                    role="menu"
                >
                    <li class="nav-item">
                        <a href="./index.html" class="nav-link ${isDashboardActive}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./recettes.html" class="nav-link ${isRecettesActive}">
                            <i class="nav-icon bi bi-journal-text"></i>
                            <p>Recettes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./commentaires.html" class="nav-link ${isCommentairesActive}">
                            <i class="nav-icon bi bi-chat-left-text-fill"></i>
                            <p>Commentaires</p>
                        </a>
                    </li>
                    
                    <li class="nav-header border-top border-secondary mt-3 pt-2">SESSION</li>
                    <li class="nav-item">
                        <a href="#" onclick="logout(); return false;" class="nav-link text-danger">
                            <i class="nav-icon bi bi-box-arrow-right"></i>
                            <p>Déconnexion</p>
                        </a>
                    </li>
                </ul>
                <!--end::Sidebar Menu-->
            </nav>
        </div>
        <!--end::Sidebar Wrapper-->
    `;

    sidebarPlaceholder.innerHTML = sidebarHtml;
});
