<?php
require_once dirname(__DIR__, 2) . '/models/auth.php';

$usersList = getAllUsersFromDB();
?>

<?php if (!empty($_SESSION['form_success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show mx-4 mt-3 border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= $_SESSION['form_success_message']; unset($_SESSION['form_success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['form_error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $_SESSION['form_error_message']; unset($_SESSION['form_error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mx-4 mt-4">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold text-dark">
            <i class="fa-solid fa-users me-2 text-primary"></i> Upravljanje Korisnicima
        </h5>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="ps-4" style="width: 20%;">Ime</th>
                        <th style="width: 20%;">Prezime</th>
                        <th style="width: 25%;">Email Adresa</th>
                        <th style="width: 13%;">Status Verifikacije</th>
                        <th style="width: 12%;">Nalog Zaključan</th>
                        <th class="pe-4 text-end" style="width: 10%;">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usersList)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Trenutno nema registrovanih korisnika.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usersList as $user): ?>
                            <tr>
                                <td colspan="6" class="p-0 border-0">
                                    <form action="models/user/inline-edit.php?id=<?= $user['id']; ?>" method="POST" class="m-0">
                                        <table class="table align-middle m-0" style="table-layout: fixed; width: 100%;">
                                            <tr>
                                                <td class="ps-4" style="width: 20%;">
                                                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>" class="form-control form-control-sm fw-bold text-dark" required>
                                                </td>

                                                <td style="width: 20%;">
                                                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']); ?>" class="form-control form-control-sm fw-bold text-dark" required>
                                                </td>

                                                <td style="width: 25%;">
                                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" class="form-control form-control-sm text-secondary" required>
                                                </td>

                                                <td style="width: 13%;">
                                                    <select name="is_verified" class="form-select form-select-sm text-dark fw-medium" required>
                                                        <option value="1" <?= (int)$user['is_verified'] === 1 ? 'selected' : ''; ?>>Verifikovan</option>
                                                        <option value="0" <?= (int)$user['is_verified'] === 0 ? 'selected' : ''; ?>>Nije Verifikovan</option>
                                                    </select>
                                                </td>

                                                <td style="width: 12%;">
                                                    <select name="is_locked" class="form-select form-select-sm text-dark fw-medium" required>
                                                        <option value="0" <?= (int)$user['is_locked'] === 0 ? 'selected' : ''; ?>>Ne (Aktivan)</option>
                                                        <option value="1" <?= (int)$user['is_locked'] === 1 ? 'selected' : ''; ?>>Da (Zaključan)</option>
                                                    </select>
                                                </td>

                                                <td class="pe-4 text-end" style="width: 10%;">
                                                    <div class="d-inline-flex gap-1">
                                                        <button type="submit" class="btn btn-sm btn-primary px-2 shadow-sm" title="Sačuvaj izmene">
                                                            <i class="fa-solid fa-floppy-disk"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>