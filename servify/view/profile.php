<?php
session_start();
include '../controls/connection.php';
include '../controls/hire_functions.php'; // Include hire functions

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_logged_in = isset($_SESSION['user_id']);

$sql = "SELECT firstname, middlename, lastname, fb_link, email, location, date_created, contact, 
               COALESCE(is_verified, 0) AS is_verified, profile_picture, role
        FROM users 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "User details not found.";
    exit();
}
$stmt->close();

// Fetch posted jobs (only relevant for laborers)
$job_sql = "SELECT jobs.job_id, jobs.job_name, user_jobs.job_description, user_jobs.job_image
            FROM jobs
            INNER JOIN user_jobs ON jobs.job_id = user_jobs.job_id
            WHERE user_jobs.user_id = ?";
$job_stmt = $conn->prepare($job_sql);
$job_stmt->bind_param("i", $user_id);
$job_stmt->execute();
$job_result = $job_stmt->get_result();
$job_stmt->close();

// Fetch hire requests for this laborer
$hire_requests = getHiresForUser($conn, $user_id, 'laborer');

// Handle Accept/Decline action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond_hire'])) {
    $hire_id = intval($_POST['hire_id']);
    $action = $_POST['action'] === 'accepted' ? 'accepted' : 'declined';
    $response_msg = respondToHire($conn, $hire_id, $action);
    echo "<script>alert('".htmlspecialchars($response_msg)."'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
}

$conn->close();
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile</title>
  <link rel="stylesheet" href="../styles/view_profile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { padding-top: 10px; }
    a {text-decoration: none;color: #fff;}
header {width: 100%;height: 4rem;background-image: linear-gradient(to left, #027d8d, #035a68);box-shadow: 0 4px 8px rgba(0,0,0,0.2), 0 6px 20px rgba(0,0,0,0.19);display: flex;align-items: center;justify-content: center;padding: 0 1rem;  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000; /* ensures it stays above other content */}
    .header-content {width: 100%;max-width: 1200px;display: flex;justify-content: space-between;align-items: center;}
    .brand {font-size: 1.5rem;font-weight: bold;color: #fff;}
    .menu-container {position: relative;}
    .wrapper-2 {display: flex;align-items: center;gap: 1rem;color: #fff;}
    .wrapper-2 p {margin: 0;padding: 0.5rem 1rem;}
    .wrapper-2 .login {border: 1px solid #fff;border-radius: 10px;}
    .wrapper-2 .login:hover {background-color: #fff;color: #000 !important;}
    .wrapper-2 .login:hover a {color: #000 !important;}
    .burger {display: none;font-size: 1.8rem;color: white;cursor: pointer;}

    /* RESPONSIVE */
    @media only screen and (max-width: 780px){.burger {display: block;} .wrapper-2 {display: none;position: absolute;top: 4rem;right: 1rem;background-color: #fff;box-shadow: 0 4px 8px rgba(0,0,0,0.2);padding: 1rem 2rem;flex-direction: column;gap: 1rem;align-items: flex-start;z-index: 100;border-radius: 10px;} .wrapper-2.active {display: flex;} .wrapper-2 p, .wrapper-2 a {color: #000 !important;font-size: 0.95rem;} .wrapper-2 .login {border: 1px solid #000;background-color: transparent;} .wrapper-2 .login:hover {background-color: #000;color: #fff !important;} .wrapper-2 .login:hover a {color: #fff !important;}.wrapper-2 .divider {display: none;}}

html, body {
  margin: 0;
  padding: 0;
}

body{
   padding-top: 4rem;
}

/* Hide burger by default */
.burger {
  display: none;
}

/* Responsive visibility */
@media (max-width: 991px) {
  .burger {
    display: inline-block;
  }
}

/* Nav layout */
.wrapper-2 {
  display: flex;
  align-items: center;
  gap: 15px;
}

.wrapper-2 p {
  margin: 0;
  vertical-align: middle;
}

/* Profile icon and dropdown */
.profile-wrapper {
  position: relative;
  display: inline-block;
}

.profile-icon .icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  vertical-align: middle;
}

.profile-menu {
  position: absolute;
  right: 0;
  top: 40px;
  background-color: white;
  color: black;
  border-radius: 5px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  padding: 10px;
  min-width: 200px;
  z-index: 1000;
}

.profile-menu a {
  display: block;
  padding: 8px 10px;
  text-decoration: none;
  color: black;
}

.profile-menu a:hover {
  background-color: #f1f1f1;
}

.user-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 8px;
  border-bottom: 1px solid #ccc;
}

/* Hide top nav items on mobile/tablet */
@media (max-width: 991px) {
  .wrapper-2 p:not(.divider):not(.login) {
    display: none;
  }
}
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: white;
  display: flex;
  justify-content: space-around; /* evenly spaced items */
  align-items: center;
  padding: 8px 0;
  z-index: 999;
  box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
  border-top: 1px solid #ccc;
}

.bottom-nav .nav-item {
  text-align: center;
  cursor: pointer;
  color: #333;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  min-width: 60px; /* ensures tap target size */
}

.bottom-nav .nav-item i {
  font-size: 20px;
  color: inherit;
}

.bottom-nav .nav-item span {
  font-size: 12px;
  color: inherit;
}

.bottom-nav .nav-item.active {
  color: teal;
  font-weight: bold;
}


/* More popup menu */
.more-popup {
  position: fixed;
  bottom: 60px;
  right: 10px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  padding: 10px;
  z-index: 1000;
}

.more-popup a {
  display: block;
  padding: 8px 10px;
  color: black;
  text-decoration: none;
  font-size: 14px;
}

.more-popup a:hover {
  background-color: #f1f1f1;
}

/* Responsive visibility */
.mobile-only {
  display: none;
}

@media (max-width: 991px) {
  .mobile-only {
    display: flex;
  }
}

@media (max-width: 991px) {
  .wrapper-2 {
    display: none;
  }
}

/*FULL SCREEN TOGGLE MENU*/
.fullscreen-menu {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: white; /* solid white to fully cover */
  z-index: 10000;
  padding: 20px;
  overflow-y: auto;
}

/* Optional: if you want a centered card-style panel */
.menu-panel {
  max-width: 400px;
  margin: 0 auto;
}

/* OR: if you want full width layout */
.menu-panel {
  width: 100%;
}

/* Keep the rest of your styles */
.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.menu-title {
  font-size: 20px;
  color: teal;
  margin: 0;
}

.close-btn {
  font-size: 20px;
  cursor: pointer;
  color: black;
  background-color: #e0e0e0; /* light gray */
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}


.user-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 16px;
  margin-bottom: 20px;
}

.edit-icon {
  font-size: 18px;
  cursor: pointer;
  color: gray;
}

.menu-options {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.menu-options a {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
  text-decoration: none;
  color: black;
}

.menu-options a:hover {
  background-color: #f1f1f1;
  padding: 8px;
  border-radius: 6px;
}

/*PROFILE IN MENU TOGGLE*/
.user-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.profile-info {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.menu-title {
  font-size: 2rem; /* roughly 32px */
  font-weight: bold;
  padding-bottom: 10px;
  font-style: italic;
}

.icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-name {
  margin: 0;
  font-size: 18px;
  color: #333;
}

.edit-icon {
  font-size: 20px;
  color: gray;
  cursor: pointer;
}

.profile-link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
}


/*DIVIDER*/
.section-divider {
  height: 1px;
  background-color: #ccc;
  margin: 15px 0;
}


/*FOR NON USERS*/
.fullscreen-menu {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: white;
  z-index: 10000;
  padding: 20px;
  overflow-y: auto;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.menu-title {
  font-size: 28px;
  color: teal;
  margin: 0;
}

.close-btn {
  font-size: 20px;
  cursor: pointer;
  color: black;
  background-color: #e0e0e0;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.menu-options {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.menu-options a {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
  text-decoration: none;
  color: black;
}

.menu-options a:hover {
  background-color: #f1f1f1;
  padding: 8px;
  border-radius: 6px;
}


  </style>
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
  <a href="../view/index.php">
    <div class="nav-item active" onclick="goToHome()">
      <i class="bi bi-house"></i>
      <span>Home</span>
    </div>
  </a>
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


  <div class="container mt-2 pt-2">
    <!-- Profile Info -->
    <div class="row align-items-center mb-4 mt-5">
      <div class="col-md-3 text-center">
        <img src="../<?php echo htmlspecialchars($row['profile_picture']) ?: 'uploads/profile_pics/default.jpg'; ?>" alt="Profile Picture" class="img-fluid" style="width: 200px; height: 200px; object-fit: cover;">
      </div>
      <div class="col-md-9">
        <h3 class="d-flex align-items-center gap-2">
          <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']); ?>
          <?php if ($row['is_verified']): ?>
            <span class="badge bg-success">✅ Verified</span>
          <?php else: ?>
            <span class="badge bg-danger">❌ Not Verified</span>
          <?php endif; ?>
        </h3>
        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></p>
        <p><strong>Email: </strong> <?php echo htmlspecialchars($row['email']); ?></p>
        <p><strong>Contact: </strong> <?php echo htmlspecialchars($row['contact']); ?></p>
        <p><strong>Facebook: </strong> <a href="<?php echo htmlspecialchars($row['fb_link']); ?>" target="_blank"><?php echo htmlspecialchars($row['fb_link']);?></a></p>
        <p><strong>Member since: </strong> <?php echo date('F j, Y', strtotime($row['date_created'])); ?></p>
        <button onclick="toggleEditForm()" class="btn btn-primary mt-2">Edit Details</button>
      </div>
    </div>

    <!-- Hire Requests Section -->
    <?php if ($row['role'] === 'laborer'): ?>
      <hr>
      <div class="hire-requests mb-5">
        <h4>Hire Requests</h4>
        <?php if ($hire_requests->num_rows > 0): ?>
          <ul class="list-group">
            <?php while ($hire = $hire_requests->fetch_assoc()): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center flex-column flex-md-row">
                <div>
                  <strong>From: <?php echo htmlspecialchars($hire['employer_firstname'] . ' ' . $hire['employer_middlename'] . ' ' . $hire['employer_lastname']); ?></strong><br>
                  <strong>Message:</strong> <?php echo htmlspecialchars($hire['message']); ?><br>
                  <strong>Location:</strong> <?php echo htmlspecialchars($hire['meeting_location']); ?><br>
                  <strong>Status:</strong> <?php echo ucfirst($hire['status']); ?>
                </div>
                <?php if ($hire['status'] === 'pending'): ?>
                  <div class="mt-2 mt-md-0">
                    <form action="" method="POST" class="d-inline">
                      <input type="hidden" name="hire_id" value="<?php echo $hire['id']; ?>">
                      <input type="hidden" name="action" value="accepted">
                      <button type="submit" name="respond_hire" class="btn btn-success btn-sm">Accept</button>
                    </form>
                    <form action="" method="POST" class="d-inline">
                      <input type="hidden" name="hire_id" value="<?php echo $hire['id']; ?>">
                      <input type="hidden" name="action" value="declined">
                      <button type="submit" name="respond_hire" class="btn btn-danger btn-sm">Decline</button>
                    </form>
                  </div>
                <?php endif; ?>
              </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <p>No hire requests at the moment.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>


    <!-- Edit Form -->
    <div id="editDetailsForm" class="card card-body mb-4" style="display: none;">
      <form action="../controls/user/update_profile.php" method="POST" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>First Name:</label>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($row['firstname']); ?>" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label>Middle Name:</label>
            <input type="text" name="middlename" value="<?php echo htmlspecialchars($row['middlename']); ?>" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($row['lastname']); ?>" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label>Contact:</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($row['contact']); ?>" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label>Facebook Link:</label>
            <input type="text" name="fb_link" value="<?php echo htmlspecialchars($row['fb_link']); ?>" class="form-control">
          </div>
          <div class="col-md-12 mb-3">
            <label>Location:</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($row['location']); ?>" class="form-control" required>
          </div>
          <div class="col-md-12 mb-3">
            <label>Profile Picture:</label>
            <input type="file" name="profile_pic" class="form-control">
          </div>
        </div>
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <button type="submit" class="btn btn-success">Save Changes</button>
      </form>
    </div>

    <!-- Show Job List ONLY if user is laborer -->
    <?php if ($row['role'] === 'laborer'): ?>
      <hr>
      <div class="job-container mb-4">
        <h2>My Posted Jobs</h2>
        <?php if ($job_result->num_rows > 0): ?>
          <ul class="list-group">
            <?php while ($job = $job_result->fetch_assoc()): ?>
              <li class="list-group-item d-flex justify-content-between align-items-start flex-column flex-md-row">
                <div>
                  <strong><?php echo htmlspecialchars($job['job_name']); ?></strong>
                  <p class="mb-1"><?php echo htmlspecialchars($job['job_description']); ?></p>
                  <?php if (!empty($job['job_image'])): ?>
                    <img src="http://localhost/servify/uploads/<?php echo htmlspecialchars($job['job_image']); ?>" alt="Job Image" class="img-fluid" style="width: 100px; height: 100px; object-fit: cover;">
                  <?php else: ?>
                    <p>No job image available.</p>
                  <?php endif; ?>
                </div>
                <div>
                  <form action="edit_labor.php" method="POST" class="d-inline">
                    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                    <button type="submit" class="btn btn-warning btn-sm">Edit</button>
                  </form>
                  <form action="delete_labor.php" method="POST" class="d-inline">
                    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this job?');">Delete</button>
                  </form>
                </div>
              </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <p>No jobs posted yet.</p>
        <?php endif; ?>
      </div>

      <!-- Add Labor -->
      <div class="text-center mb-4">
        <form action="add_labor.php" method="POST">
          <button type="submit" class="btn btn-primary">+ Add Labor</button>
        </form>
      </div>
    <?php endif; ?>

    <hr>

    <!-- Account Verification Section -->
    <div class="verification-container mb-5">
      <h4>Account Verification</h4>
      <div class="alert alert-info">
        <strong>Verification Status:</strong> 
        <?php echo ($row['is_verified'] == 1) ? '✅ Verified' : '❌ Not Verified'; ?>
      </div>

      <?php if ($row['is_verified'] == 0): ?>
        <p>Your account has not been verified yet. Please upload the required documents for verification.</p>
        <form action="../controls/user/upload_verification.php" method="POST" enctype="multipart/form-data">
          <label for="id_proof">Primary ID (Barangay ID if laborer):</label>
          <input type="file" name="id_proof" id="id_proof" class="form-control mb-2" required>
          <label for="supporting_doc">Supporting document (e.g. Birth Certificate, other Government Issued ID, etc.)</label>
          <input type="file" name="supporting_doc" id="supporting_doc" class="form-control mb-2" required>
          <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
          <button type="submit" class="btn btn-success">Upload Documents</button>
        </form>

      <?php endif; ?>
    </div>
  </div>

<script>
  function toggleEditForm() {
    const form = document.getElementById('editDetailsForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
  }
</script>

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>