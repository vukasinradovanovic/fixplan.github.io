<!-- <?php
      // Inject the logging engine automatically across all pages
      require_once __DIR__ . '/../../../models/logs.php';
      writePageAccessLog();
      ?> -->
<div id="mainContent">
  <header class="container-fluid sticky-top p-0 m-0 navHolder">
    <nav class="navbar navbar-expand-lg shadow-sm">
      <div class="container">
        <a class="navbar-brand nav_logoText" href="index.php">FixPlan</a>
        <button
          class="navbar-toggler bg-white"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav navigation me-auto">
          </ul>

          <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
            <?php if (isset($_SESSION['user_id'])): ?>
              <span class="text-light me-2 d-none d-sm-inline">
                <i class="fa-solid fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['first_name']); ?>
              </span>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="admin-dashboard.php" class="btn btn-light btn-sm px-3">
                <i class="fa-solid fa-sign-out-alt me-1"></i> Admin Panel
              </a>
              <?php endif; ?>
              <a href="logout.php" class="btn btn-danger btn-sm px-3">
                <i class="fa-solid fa-sign-out-alt me-1"></i> Odjavi se
              </a>
            <?php else: ?>
              <a href="login.php" class="btn btn-outline-light btn-sm px-3">
                <i class="fa-solid fa-sign-in-alt me-1"></i> Prijava
              </a>
              <a href="register.php" class="btn btn-light btn-sm px-3">
                <i class="fa-solid fa-user-plus me-1"></i> Registracija
              </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="nav_socialIcons d-none d-lg-block ms-3">
          <ul class="nav_socialIconsHolder d-flex justify-content-between align-items-center p-0 m-0">
          </ul>
        </div>
      </div>
    </nav>
  </header>
  <main>