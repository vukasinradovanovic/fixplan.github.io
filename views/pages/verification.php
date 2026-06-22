<div class="container mt-5" style="min-height: 65vh;">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card p-4 shadow-sm border-0">
                <div class="card-body">
                    <h2 class="mb-4 fw-bold text-dark">
                        <i class="fa-solid fa-user-check text-primary me-2"></i>Verifikacija Naloga
                    </h2>
                    
                    <div class="alert <?= $statusClass; ?> border-0 shadow-sm d-flex align-items-center justify-content-center py-3" role="alert">
                        <i class="fa-solid <?= $autoRedirect ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger'; ?> fs-5 me-2"></i>
                        <span><?= htmlspecialchars($message); ?></span>
                    </div>
                    
                    <?php if ($autoRedirect): ?>
                        <a href="index.php" class="btn btn-success mt-3 px-4 shadow-sm fw-medium">
                            <i class="fa-solid fa-gauge-high me-2"></i>Početna stranica
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary mt-3 px-4 shadow-sm fw-medium">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Idi na stranicu za prijavu
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>