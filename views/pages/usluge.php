<?php
require_once dirname(__DIR__, 2) . '/config/connection.php'; 
require_once dirname(__DIR__, 2) . '/models/functions/services.php';
require_once dirname(__DIR__, 2) . '/models/service/service.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentRole = $_SESSION['role'] ?? 'Gost';

$categories  = getAllCategoriesFromDB();

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$testData    = getServicesLogic(1, 100, null, 'name_asc');
?>

<section class="container-fluid banner--main position-relative">
    <div class="banner_overlay"></div>
    <div class="row w-50 mx-auto">
        <div class="col-12 d-flex flex-column justify-content-center align-items-center text-center">
            <h2 class="banner_title">FixPlan</h2>
            <p class="banner_text">
                Vaš pouzdan partner za sve popravke i održavanje.
            </p>
        </div>
    </div>
</section>

<div class="container mt-5">
    <div class="row align-items-center justify-content-between g-3">
        <div class="col-12 <?= ($currentRole === 'Radnik') ? 'col-md-8 text-md-start text-center' : 'text-center' ?>">
            <h3 class="fs-2 mb-2">Sve usluge</h3>
            <hr class="bg-primary border-2 w-25 <?= ($currentRole === 'Radnik') ? 'ms-md-0 mx-auto' : 'mx-auto' ?>" />
        </div>
        
        <?php if ($currentRole === 'Radnik'): ?>
            <div class="col-12 col-md-4 text-md-end text-center">
                <a href="manage_service.php" class="btn btn-success px-4 py-2 shadow-sm fw-semibold">
                    <i class="fa-solid fa-circle-plus me-2"></i> Dodaj Novu Uslugu
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container my-4">
    <div class="row g-4">
        
        <aside class="col-12 col-md-4 col-lg-3">
            <div class="card shadow-sm border p-3 bg-light position-sticky" style="top: 20px;">
                
                <div class="mb-4">
                    <label for="searchServices" class="form-label fw-bold text-secondary">Pretraga</label>
                    <div class="input-group">
                        <input type="text" id="searchServices" class="form-control" placeholder="Pronađi uslugu..." value="<?= htmlspecialchars($searchQuery) ?>">
                        <button class="btn btn-primary" type="button" id="btnSearchSubmit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
                
                <hr class="my-3 text-muted" />

                <div class="mb-3">
                    <label for="filterCategory" class="form-label fw-bold text-secondary">Kategorija</label>
                    <select id="filterCategory" class="form-select">
                        <option value="">Sve kategorije</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="sortServices" class="form-label fw-bold text-secondary">Sortiranje</label>
                    <select id="sortServices" class="form-select">
                        <option value="name_asc">Naziv (A-Z)</option>
                        <option value="name_desc">Naziv (Z-A)</option>
                        <option value="date_desc">Najnovije</option>
                        <option value="date_asc">Najstarije</option>
                    </select>
                </div>
            </div>
        </aside>

        <main class="col-12 col-md-8 col-lg-9">
            <section class="container-fluid text-center p-0 jobCards--full"></section>
        </main>

    </div>
</div>

 <!-- 3. Test Zone: Raw Business Logic Data Dump -->
<!-- <div class="container my-5 p-4 bg-light border rounded">
    <h4 class="text-danger border-bottom pb-2">Test Zone: Raw Business Logic Data Dump</h4>
    <p class="text-start text-monospace text-break" style="font-family: monospace; font-size: 13px;">
        <?= json_encode($testData, JSON_UNESCAPED_UNICODE) ?>
    </p>
</div> -->