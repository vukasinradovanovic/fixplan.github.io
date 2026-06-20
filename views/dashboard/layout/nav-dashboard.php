<nav class="col-md-3 col-lg-2 d-md-block bg-dark text-white p-3 collapse d-md-flex flex-column justify-content-between">
    <div>
        <a href="index.php" class="navbar-brand text-center d-block fs-3 fw-bold border-bottom border-secondary pb-3 mb-4 text-white text-decoration-none">
            FixPlan <span class="fs-6 text-primary d-block">Admin</span>
        </a>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a href="#" class="nav-link active bg-primary text-white">
                    <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="admin-dashboard.php?page=services" class="nav-link text-white-50 hover-link">
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i> Upravljanje Uslugama
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="index.php" class="nav-link text-white-50 hover-link">
                    <i class="fa-solid fa-house me-2"></i> Nazad na sajt
                </a>
            </li>
        </ul>
    </div>
    <div class="border-top border-secondary pt-3 mt-4">
        <div class="small text-white-50 mb-2">Prijavljen kao:</div>
        <div class="fw-bold text-truncate"><?= htmlspecialchars($adminName ?? 'Administrator'); ?></div>
        <a href="logout.php" class="btn btn-outline-danger btn-sm w-100 mt-3">
            <i class="fa-solid fa-sign-out-alt me-1"></i> Odjavi se
        </a>
    </div>
</nav>