<?php
// Protection Layer: block malicious direct absolute filename query path hits outside routing index context
if (!isset($currentPage)) {
    header("Location: ../../admin-dashboard.php");
    exit();
}
?>

<?php if (!empty($_SESSION['form_error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 border-0 shadow-sm" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $_SESSION['form_error_message']; unset($_SESSION['form_error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container-fluid px-4 mt-4">
    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-circle-plus me-2 text-success"></i> Ddodavanje novog FAQ pitanja
                    </h5>
                    <a href="admin-dashboard.php?page=faqs" class="btn btn-outline-secondary btn-sm shadow-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> Povratak na FAQ listu
                    </a>
                </div>
                
                <div class="card-body p-4 border-top">
                    <form action="models/faq/insert-faq.php" method="POST">
                        
                        <div class="mb-4">
                            <label for="faq_question" class="form-label fw-bold text-secondary">Pitanje <span class="text-danger">*</span></label>
                            <input type="text" id="faq_question" name="question" class="form-control" placeholder="e.g., What are your standard delivery turnaround timelines?" required autocomplete="off">
                            <div class="form-text text-muted">Pitanje treba da bude jasno i sažeto.</div>
                        </div>

                        <div class="mb-4">
                            <label for="faq_answer" class="form-label fw-bold text-secondary">Odgovor <span class="text-danger">*</span></label>
                            <textarea id="faq_answer" name="answer" class="form-control text-secondary" rows="5" placeholder="Odgovor na pitanje..." required></textarea>
                        </div>

                        <div class="mb-4 style-container" style="max-width: 240px;">
                            <label for="faq_order" class="form-label fw-bold text-secondary">Redni redosled prikaza</label>
                            <input type="number" id="faq_order" name="display_order" class="form-control fw-bold" placeholder="Auto-generates if empty" min="0">
                            <div class="form-text text-muted">Niži broj indeksa ima viši prioritet (npr., 0 se prikazuje pre 5).</div>
                        </div>

                        <hr class="text-muted my-4">

                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <button type="reset" class="btn btn-light border px-4 btn-sm fw-medium">Očisti polja</button>
                            <button type="submit" class="btn btn-success px-4 btn-sm fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Sačuvaj FAQ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>