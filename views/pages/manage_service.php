<?php 
// Check if an error message was passed back through the session
if (!empty($_SESSION['form_error_message'])) {
    $message = $_SESSION['form_error_message'];
    unset($_SESSION['form_error_message']); // Clear it out so it doesn't stay forever
}
?>

<?php if(!empty($message)): ?>
    <div class="alert alert-danger"><?php echo $message; ?></div>
<?php endif; ?>

<div class="container" style="max-width: 600px;">
    <div class="mb-3">
        <a href="usluge.php" class="text-decoration-none text-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Nazad na usluge</a>
    </div>
    
    <div class="mainFormCRUD p-4">
        <h3 class="mb-4 text-center fw-bold text-dark"><?php echo $isEdit ? 'Izmeni Uslugu' : 'Dodaj Novu Uslugu'; ?></h3>
        
        <?php if(!empty($message)): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="models/service/service.php<?php echo $isEdit ? '?id='.$id : ''; ?>" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Izaberite Kategoriju</label>
                <select class="form-select" name="category_id" required>
                    <option value="" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>-- Odaberite kategoriju --</option>
                    <?php if (!empty($allCategories) && is_array($allCategories)): ?>
                        <?php foreach ($allCategories as $cat): ?>
                            <?php 
                                $isArr   = is_array($cat);
                                $catId   = $isArr ? ($cat['id'] ?? 0) : ($cat->id ?? 0);
                                $catName = $isArr ? ($cat['name'] ?? '') : ($cat->name ?? '');
                            ?>
                            <option value="<?php echo $catId; ?>" <?php echo ($currentCategoryId == $catId) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($catName); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Naziv Usluge</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" required placeholder="npr. Krečenje zidova">
            </div>

            <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Jedinstveni identifikator (Slug)</label>
                <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($slug); ?>" required placeholder="npr. krecenije_zidova">
            </div>

            <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Izaberite Sliku sa Uređaja</label>
                <input type="file" class="form-control" name="image" <?php echo $isEdit ? '' : 'required'; ?> accept="image/*">
                <?php if ($isEdit && !empty($bgi)): ?>
                    <div class="form-text text-muted mt-1">Trenutna slika: <code><?php echo htmlspecialchars($bgi); ?></code></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="form-label small text-secondary fw-semibold">Opis Usluge</label>
                <textarea class="form-control" name="description" rows="4" placeholder="Unesite detaljan opis građevinske usluge..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-floppy-disk me-1"></i> Sačuvaj Podatke
            </button>
        </form>
    </div>
</div>