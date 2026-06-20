<?php
require_once dirname(__DIR__, 2) . '/config/connection.php';

try {
    global $conn;
    $query = "SELECT id, question, answer, display_order FROM faqs ORDER BY display_order ASC, id DESC";
    $faqList = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database view load error in faq-control.php: " . $e->getMessage());
    $faqList = [];
}
?>

<?php if (!empty($_SESSION['form_success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show mx-4 mt-3 border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= $_SESSION['form_success_message'];
                                                        unset($_SESSION['form_success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['form_error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $_SESSION['form_error_message'];
                                                                unset($_SESSION['form_error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mx-4 mt-4">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold text-dark">
            <i class="fa-solid fa-circle-question me-2 text-primary"></i> FAQ Management Panel
        </h5>
        <a href="admin-dashboard.php?page=add-faq" class="btn btn-success btn-sm fw-semibold shadow-sm px-3">
            <i class="fa-solid fa-plus me-1"></i> Dodaj novo FAQ pitanje
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th class="ps-4" style="width: 8%;">Redni index</th>
                        <th style="width: 32%;">Pitanje</th>
                        <th style="width: 48%;">Odgovor</th>
                        <th class="pe-4 text-end" style="width: 12%;">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faqList)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Nema dostupnih česta pitanja.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($faqList as $faq): ?>
                            <tr>
                                <td colspan="4" class="p-0 border-0">
                                    <form action="models/faq/inline-edit-faq.php?id=<?= $faq['id']; ?>" method="POST" class="m-0">
                                        <table class="table align-middle m-0" style="table-layout: fixed; width: 100%;">
                                            <tr>
                                                <td class="ps-4" style="width: 8%;">
                                                    <input type="number" name="display_order" value="<?= (int)$faq['display_order']; ?>" class="form-control form-control-sm text-center fw-bold" min="0" required>
                                                </td>

                                                <td style="width: 32%;">
                                                    <textarea name="question" class="form-control form-control-sm text-dark fw-semibold" rows="2" style="resize: vertical; min-height: 44px;" required><?= htmlspecialchars($faq['question']); ?></textarea>
                                                </td>

                                                <td style="width: 48%;">
                                                    <textarea name="answer" class="form-control form-control-sm text-secondary small" rows="2" style="resize: vertical; min-height: 44px;" required><?= htmlspecialchars($faq['answer']); ?></textarea>
                                                </td>

                                                <td class="pe-4 text-end" style="width: 12%;">
                                                    <div class="d-inline-flex gap-1">
                                                        <button type="submit" class="btn btn-sm btn-primary px-2 shadow-sm" title="Save this row">
                                                            <i class="fa-solid fa-floppy-disk me-1"></i> Save
                                                        </button>

                                                        <a href="models/faq/delete-faq.php?id=<?= $faq['id']; ?>"
                                                            class="btn btn-sm btn-outline-danger px-2"
                                                            title="Delete FAQ item"
                                                            >
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