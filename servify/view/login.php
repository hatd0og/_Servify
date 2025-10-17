<?php
session_start();

// If the user is already logged in, redirect them to the profile page
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit();  // Always call exit after header redirect
}
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- <link rel="stylesheet" type="text/css" href="../styles/login.css"> -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="../styles/login.css?v=<?php echo time(); ?>">
  <title>Log in</title>
</head>
<body>

<!-- NAVIGATION BAR -->
<header>
  <div class="header-content">
    <div class="brand"><a href="../view/index.php">Servify</a></div>
    <div class="menu-container">
      <nav class="wrapper-2" id="menu">
        <p><a href="../view/browse.php">Services</a></p>
        <p><a href="#">Become a laborer</a></p>
        <p class="divider">|</p>

        <?php if ($is_logged_in): ?>
          <p class="profile-wrapper">
            <span class="profile-icon" onclick="toggleProfileMenu()">
              <img src="../image/man.png" alt="Profile" class="icon">
            </span>
            <div id="profile-menu" class="profile-menu d-none">
              <div class="user-info">
                <a href="../view/profile.php">
                  <span>name</span>
                </a>
                <i class="bi bi-pencil-square"></i>
              </div>
              <a href="../view/messages.php"><i class="bi bi-chat-dots"></i> Inbox</a>
              <a href="#"><i class="bi bi-bell"></i> Notifications</a>
              <a href="#"><i class="bi bi-grid"></i> Dashboard</a>
              <a href="../controls/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
          </p>
        <?php else: ?>
          <p class="login"><a href="../view/login.php"><i class="bi bi-box-arrow-in-right"></i> Login / Signup</a></p>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>

<!-- Bottom Navigation (Mobile/Tablet Only) -->
<div class="bottom-nav mobile-only">
  <div class="nav-item active" onclick="goToHome()">
    <i class="bi bi-house"></i>
    <span>Home</span>
  </div>
  <a href="../view/browse.php">
  <div class="nav-item" onclick="goToServices()">
    <i class="bi bi-search"></i>
    <span>Services</span>
  </div>
  </a>
  <div class="nav-item" onclick="toggleMoreMenu()">
    <i class="bi bi-three-dots"></i>
    <span>More</span>
  </div>
</div>


<!-- Fullscreen More Menu -->
<div id="more-menu" class="fullscreen-menu d-none">
  <div class="menu-panel">
    <div class="menu-header">
      <h1 class="menu-title">SERVIFY</h1>
      <span class="close-btn" onclick="toggleMoreMenu()">✕</span>
    </div>

    <?php if ($is_logged_in): ?>
      <!-- Logged-in User Menu -->
      <div class="user-section">
        <div class="profile-info" onclick="toggleProfileMenu()">
          <a href="../view/profile.php" class="profile-link">
            <img src="../image/man.png" alt="Profile" class="icon">
            <h3 class="user-name">Name...</h3>
          </a>
        </div>
        <i class="bi bi-pencil-square edit-icon"></i>
      </div>

      <div class="section-divider"></div>

      <div class="menu-options">
        <a href="../view/messages.php"><i class="bi bi-chat-dots"></i> Inbox</a>
        <a href="#"><i class="bi bi-bell"></i> Notifications</a>
        <a href="#"><i class="bi bi-grid"></i> Dashboard</a>
        <a href="../controls/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </div>

    <?php else: ?>
      <!-- Non-User Menu -->
      <div class="menu-options">
        <a href="../view/become-laborer.php"><i class="bi bi-person-workspace"></i> Become a laborer</a>
        <a href="../view/login.php"><i class="bi bi-person-circle"></i> Signin / Signup</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- LOGIN WRAPPER -->

  <div class="login-wrapper">
    <div class="login-container text-center">
      <h3>Log in</h3>
      <form action="../controls/login_validation.php" method="POST">
        <div class="mb-3">
          <input name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="mb-3">
          <input name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <div class="form-check text-start">
            <input type="checkbox" class="form-check-input">
            <label class="form-check-label">Remember password</label>
          </div>
          <a href="#" class="small-text">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100">LOGIN</button>
      </form>
      <p class="small-text mt-3">Don't have an account? <a href="#">Sign up</a></p>
    </div>
  </div>


        
<!--     <div class="frame">
        <form action="../controls/login_validation.php" method="POST">
            <label>Log in</label>
            <input  name="email" placeholder="Email" required><br>
            <input  name="password" placeholder="Password" required><br><br>
            <a href="signup.php">Don't have an account yet? Sign Up</a><br><br>
            <button type="submit">Login</button>
        </form>
    </div>
 -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  function toggleMenu() {
    const menu = document.getElementById('menu');
    menu.classList.toggle('active');
  }
</script>

<script>
  // Toggle profile dropdown
  function toggleProfileMenu() {
    const menu = document.getElementById('profile-menu');
    menu.classList.toggle('d-none');
  }

  // Toggle fullscreen "More" menu
  function toggleMoreMenu() {
    const menu = document.getElementById('more-menu');
    menu.classList.toggle('d-none');
  }

  // Navigation actions
  function goToHome() {
    window.location.href = '../view/home.php';
  }

  function goToServices() {
    window.location.href = '../view/services.php';
  }
</script>

</body>
</html>
</body>
</html>
