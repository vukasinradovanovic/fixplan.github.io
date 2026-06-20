<?php
require_once dirname(__DIR__, 2) . '/models/functions/services.php';
require_once dirname(__DIR__, 2) . '/models/service/service.php';

$dashboardServicesData = getServicesLogic(1, 200, null, 'date_desc'); 
$servicesList = $dashboardServicesData['items'] ?? [];
$allCategories = getAllCategoriesFromDB(); 
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
            <i class="fa-solid fa-square-poll-horizontal me-2 text-primary"></i> Brzo Izmenjivanje Usluga u Redu
        </h5>
        <a href="manage_service.php" class="btn btn-success btn-sm fw-semibold shadow-sm px-3">
            <i class="fa-solid fa-plus me-1"></i> Dodaj Novu Uslugu
        </a>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="ps-4" style="width: 15%;">Slika (Nova)</th>
                        <th style="width: 23%;">Naziv Usluge / Identifikator</th>
                        <th style="width: 32%;">Opis Usluge</th>
                        <th style="width: 18%;">Kategorija</th>
                        <th class="pe-4 text-end" style="width: 12%;">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servicesList)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Trenutno nema unetih građevinskih usluga.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($servicesList as $service): ?>
                            <tr>
                                <td colspan="5" class="p-0 border-0">
                                    <form action="models/service/inline-edit.php?id=<?= $service['id']; ?>" method="POST" enctype="multipart/form-data" class="m-0">
                                        <table class="table align-middle m-0" style="table-layout: fixed; width: 100%;">
                                            <tr>
                                                <td class="ps-4" style="width: 15%;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="public/img/thumbnails/<?= htmlspecialchars($service['bgi']); ?>" 
                                                             alt="Preview" class="rounded border object-fit-cover shadow-sm" style="width: 50px; height: 40px; min-width: 50px;">
                                                        <input type="file" name="image" class="form-control form-control-sm" style="font-size: 11px;" accept="image/*">
                                                    </div>
                                                </td>

                                                <td style="width: 23%;">
                                                    <input type="text" name="name" value="<?= $service['label']; ?>" class="form-control form-control-sm fw-bold text-dark mb-1" required>
                                                    <div class="text-truncate"><small class="text-muted small">Trenutni slug: <code><?= $service['value']; ?></code></small></div>
                                                </td>

                                                <td style="width: 32%;">
                                                    <textarea name="description" class="form-control form-control-sm text-secondary small" rows="2" style="resize: vertical; min-height: 42px; font-size: 13px;"><?= $service['desc']; ?></textarea>
                                                </td>

                                                <td style="width: 18%;">
                                                    <select name="category_id" class="form-select form-select-sm text-dark fw-medium" required>
                                                        <?php foreach ($allCategories as $cat): ?>
                                                            <?php 
                                                            $catId = $cat['id'] ?? 0;
                                                            $catName = $cat['name'] ?? '';
                                                            $isSelected = ($service['category'] === $catName) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $catId; ?>" <?= $isSelected; ?>>
                                                                <?= htmlspecialchars($catName); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>

                                                <td class="pe-4 text-end" style="width: 12%;">
                                                    <div class="d-inline-flex gap-1">
                                                        <button type="submit" class="btn btn-sm btn-primary px-2 shadow-sm" title="Sačuvaj izmene">
                                                            <i class="fa-solid fa-floppy-disk me-1"></i> Sačuvaj
                                                        </button>
                                                        
                                                        <a href="models/service/delete.php?id=<?= $service['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger px-2" 
                                                           title="Obriši uslugu"
                                                           onclick="return confirm('Da li ste sigurni da želite obrisati uslugu: <?= addslashes($service['label']); ?>?');">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
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