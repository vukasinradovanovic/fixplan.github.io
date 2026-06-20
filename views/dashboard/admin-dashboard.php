<div class="container-fluid">
    <div class="row min-vh-100">

        <?php include_once __DIR__ . '/layout/nav-dashboard.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <?php 
            // 1. Determine which control subview to render based on URL parameter
            $currentPage = isset($_GET['page']) ? trim($_GET['page']) : 'dashboard';

            if ($currentPage === 'services'): 
                // ==========================================
                // VIEW LAYER: SERVICES MANAGEMENT PANEL
                // ==========================================
                include_once __DIR__ . '/services-control.php';

            else: 
                // ==========================================
                // VIEW LAYER: DEFAULT ANALYTICS DASHBOARD
                // ==========================================
            ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Kontrolna Tabla</h1>
                    <div class="text-secondary small">
                        <i class="fa-solid fa-calendar me-1"></i> Danas: <?= date('d.m.Y.'); ?>
                    </div>
                </div>

                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body bg-white rounded p-4 d-flex align-items-center">
                        <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3">
                            <i class="fa-solid fa-user-shield fa-2xl"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Dobrodošli nazad, <?= htmlspecialchars($adminName ?? 'Administrator'); ?>!</h4>
                            <p class="text-muted mb-0">Ulogovani ste sa email adresom: <strong><?= htmlspecialchars($adminEmail ?? ''); ?></strong></p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted text-uppercase small mb-1">Ukupno pregleda strana</h6>
                                    <h3 class="mb-0 fw-bold"><?= (int)($logData['total_hits'] ?? 0); ?></h3>
                                </div>
                                <div class="text-success fs-1"><i class="fa-solid fa-eye"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa-solid fa-ranking-star me-2 text-warning"></i> Statistika posete stranica (Sortirano po popularnosti)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 40%">Naziv Stranice</th>
                                        <th style="width: 20%">Broj Pregleda</th>
                                        <th class="pe-4" style="width: 40%">Procenat Posete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logData['pages'])): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">Nema podataka u access_log.txt datoteci.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logData['pages'] as $pageStat): ?>
                                            <tr>
                                                <td class="ps-4 fw-semibold text-secondary">
                                                    <i class="fa-regular fa-file-code me-2 text-primary"></i><?= htmlspecialchars($pageStat['page']); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded"><?= (int)$pageStat['hits']; ?> pregleda</span>
                                                </td>
                                                <td class="pe-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar bg-primary rounded" role="progressbar" style="width: <?= (float)$pageStat['percentage']; ?>%"></div>
                                                        </div>
                                                        <span class="fw-bold text-dark small" style="min-width: 45px; text-align: right;"><?= (float)$pageStat['percentage']; ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>