<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm border-0 p-4">
        <div class="card-body">
          <h2 class="card-title text-center mb-4 font-weight-bold nav_logoText" style="font-size: 2.5rem; color: #212529;">FixPlan</h2>
          <h5 class="text-center text-muted mb-4">Kreirajte novi korisnički nalog</h5>
          
          <?php if (!empty($message)): ?>
            <div class="alert <?php echo $messageClass; ?> py-2" role="alert">
              <i class="fa-solid <?php echo $messageClass === 'alert-success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i> 
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>

          <form action="register.php" method="POST">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="firstName" class="form-label text-secondary small">Ime</label>
                <input type="text" class="form-control" id="firstName" name="firstName" required placeholder="Vukašin" value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="lastName" class="form-label text-secondary small">Prezime</label>
                <input type="text" class="form-control" id="lastName" name="lastName" required placeholder="Radovanović" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" />
              </div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label text-secondary small">Email adresa</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="ime@primer.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
              </div>
            </div>
            
            <div class="mb-4">
              <label for="password" class="form-label text-secondary small">Lozinka</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="Najmanje 6 karaktera" />
              </div>
            </div>

            <button type="submit" name="btnRegister" class="btn primaryButton w-100 py-2 mb-3 fs-5">
              <i class="fa-solid fa-user-plus me-2"></i> Registruj se
            </button>
          </form>
          
          <div class="text-center mt-3 small">
            <span class="text-muted">Već imate nalog?</span> 
            <a href="login.php" class="text-decoration-none ms-1">Prijavite se ovde</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>