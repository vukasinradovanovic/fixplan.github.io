<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card p-4 shadow-sm border-0">
                <div class="card-body">
                    <h2 class="mb-4">Verifikacija Naloga</h2>
                    <div class="alert <?= $statusClass; ?>" role="alert">
                        <?= htmlspecialchars($message); ?>
                    </div>
                    <a href="login.php" class="btn btn-primary mt-3">Idi na stranicu za prijavu</a>
                </div>
            </div>
        </div>
    </div>
</div>