<?php
// Osiguravamo da je uloga definisana pre ispisa
$currentRole = $_SESSION['role'] ?? 'Gost';
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

<div class="container text-center mt-5">
  <h3 class="fs-2 mb-2">Sve usluge</h3>
  <hr class="bg-primary border-2 w-25 mx-auto" />
  
  <?php if ($currentRole === 'Radnik'): ?>
    <div class="d-flex justify-content-end my-4">
      <a href="manage_service.php" class="btn btn-success px-4 py-2 shadow-sm fw-semibold">
        <i class="fa-solid fa-circle-plus me-2"></i> Dodaj Novu Uslugu
      </a>
    </div>
  <?php endif; ?>
</div>

<section class="container-fluid text-center p-5 jobCards--full">
  </section>