<div class="container formContainer">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm border-0 p-4">
        <div class="card-body">
          <h2 class="card-title text-center mb-5 font-weight-bold nav_logoText" style="font-size: 2.5rem; color: #212529;">FixPlan</h2>
          <h5 class="text-center text-muted mb-5">Prijavite se na Vaš nalog</h5>
          
          <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger py-2" role="alert">
              <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label text-secondary small">Email adresa</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="ime@primer.com" />
              </div>
            </div>
            
            <div class="mb-4">
              <label for="password" class="form-label text-secondary small">Lozinka</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="Unesite lozinku" />
              </div>
            </div>

            <button type="submit" name="btnLogin" class="btn primaryButton w-100 py-2 mb-3 fs-5">
              <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Prijavi se
            </button>
          </form>
          
          <div class="text-center mt-3 small">
            <span class="text-muted">Nemate nalog?</span> 
            <a href="register.php" class="text-decoration-none ms-1">Registrujte se ovde</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>