<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['email'] = $_POST['email'];
    $_SESSION['password'] = $_POST['password'];
    header("Location: user_details.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="../styles/signup.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Log in</title>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="../view/index.php">Servify</a>

    <!-- Search Bar -->
    <div class="search-container">
      <form class="d-flex align-items-center" role="search">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
          <input class="form-control" type="search" placeholder="Search" aria-label="Search">
        </div>
      </form>
    </div>

    <!-- Burger Menu -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="../view/signup.php">Sign Up</a>
        </li>
        <li class="nav-item">
          <a class="nav-link">|</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../view/login.php">Login</a>
        </li>
      </ul>
    </div>
  </div>
  </a>
</nav>

<!-- SIGNUP -->
<div class="signup-container text-center">
    <h3>Sign Up</h3>
    <form action="" method="POST">
    <form action="" method="POST">
      <div class="mb-3">
          <input class="form-control" name="email" placeholder="email">
      </div>
      <div class="mb-3">
          <input class="form-control" name="password" placeholder="password">
      </div>
      <button type="submit" class="btn btn-primary w-100">Next</button>
    </form>

    <p class="small-text mt-3">Already have an account? <a href="../view/login.php">Login</a></p>
</div>

<!-- 
    <div class="frame">
        <label>Sign Up</label>
        <form action="" method="POST">
            <input  name="email" placeholder="Email" required><br>
            <input  name="password" placeholder="Password" required><br><br>
            <a href="login.php">Have an account already? Log In</a><br><br>
            <button type="submit">Next</button>
        </form>
    </div> -->
</body>
</html>

