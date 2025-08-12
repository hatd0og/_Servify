<?php
session_start();
include '../controls/connection.php';

$sql = "SELECT job_id, job_name, job_description FROM jobs";
$result = $conn->query($sql);

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Servify - Home</title>
  <link rel="stylesheet" type="text/css" href="../styles/landing_page.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .modal {
      display: flex;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-content {
      background: white;
      padding: 30px;
      width: 50%;
      text-align: center;
      border-radius: 8px;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .modal-content h3 {
      margin-top: 20px;
      margin-bottom: 10px;
    }

    .modal-content ul {
      text-align: left;
      margin: 10px 0;
      padding-left: 20px;
    }

    .hidden { display: none; }

    .btn {
      padding: 10px 15px;
      margin: 15px 10px 0;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }

    .accept-btn { background: green; color: white; }
    .decline-btn { background: red; color: white; }

    .button.active {
      background-color: #0d6efd;
      color: white;
    }

    /* Profile Image Styling */
    .profile-img {
      height: 150px; /* Fixed height */
      width: auto; /* Keep aspect ratio */
      border-radius: 50%; /* Make it circular */
      object-fit: cover; /* Ensure the image covers the area without distortion */
    }
    .filters-section {
  display: flex;
  justify-content: flex-end; /* Aligns content to the right */
  gap: 10px; /* Adds space between elements */
  margin-right: 210px; /* Adds right margin */
  align-items: center; /* Vertically centers items */
}

  </style>
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
          <input class="form-control" type="search" aria-label="Search" id="search-input" placeholder="Search users by name, job, location..." onkeyup="filterUsers()">
        </div>
      </form>
    </div>

    <!-- Burger Menu -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <?php if ($is_logged_in): ?>
          <!-- If user is logged in, show profile icon -->
          <li class="nav-item">
            <a class="nav-link" href="../view/profile.php">
              <i class="bi bi-person-circle"></i> Profile
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../controls/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
          </li>
        <?php else: ?>
          <!-- If user is not logged in, show Sign Up and Login links -->
          <li class="nav-item"><a class="nav-link" href="../view/signup.php">Sign Up</a></li>
          <li class="nav-item"><a class="nav-link">|</a></li>
          <li class="nav-item"><a class="nav-link" href="../view/login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

  <!-- CAROUSEL -->
  <div class="container mt-5 mb-4">
    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="../image/bg2.png" class="d-block" style="width: 100%; height: 350px; object-fit: cover;" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h1 class="display-5" style="color: #fff;">Welcome to Servify</h1>
            <p class="lead" style="color: #fff">Connecting you with the right laborer, creating opportunities and maximizing potential earnings.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="../image/electrician.jpg" class="d-block" style="width: 100%; height: 350px; object-fit: cover;" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Find Electricians</h5>
            <p>Get electrical services for your needs.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="../image/plumber.png" class="d-block" style="width: 100%; height: 350px; object-fit: cover;" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Hire Plumbers</h5>
            <p>Reliable plumbing solutions for your home and business.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="../image/catering.jpeg" class="d-block" style="width: 100%; height: 350px; object-fit: cover;" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Book Caterers</h5>
            <p>Delicious catering services for all your events.</p>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>

 <!-- CATEGORIES -->
<div class="container text-center mt-5">
  <h4 class="fw-bold">Browse Categories</h4>
  <p class="text-muted">Find the right laborer for your needs</p>
</div>

<div class="categories-wrapper">
  <button class="scroll-left">&lt;</button>
  <div class="buttons-container">
    <div class="buttons">
      <button class="button active" data-job-id="all">All</button>
      <?php
        // Dynamically load job categories from the database
        $sql = "SELECT * FROM jobs"; // assuming jobs table is available
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<button class="button" data-job-id="' . $row["job_id"] . '">' . $row["job_name"] . '</button>';
          }
        }
      ?>
    </div>
  </div>
  <button class="scroll-right">&gt;</button>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <label for="filter_by_select">Filter By</label>
    <select id="filter_by_select">
        <option value="labor">Labor Type</option>
        <option value="location">Location</option>
        <option value="name">Name</option>
    </select>
    <label for="sort_order_select">Sort Order</label>
    <select id="sort_order_select">
        <option value="ASC">Ascending</option>
        <option value="DESC">Descending</option>
    </select>
</div>

<!-- Existing Laborers Container -->
<div id="workers-container" class="container p-4">
    <!-- Laborers will be displayed here -->
</div>

<!-- DISCLAIMER MODAL -->
<div id="disclaimerModal" class="modal">
  <div class="modal-content">
    <h2>Welcome to Servify!</h2>
    <p>Servify connects users with laborers for various services. We are not responsible for disputes, service quality, or misconduct.</p>
    <h3>Terms of Use</h3>
    <p>By using Servify, you agree to our Terms and Conditions.</p>
    <p><strong>Key Terms:</strong></p>
    <ul>
      <li>Servify is a platform connecting users and laborers but does not employ them.</li>
      <li>We do not guarantee service quality or mediate disputes.</li>
      <li>Users must conduct their own due diligence before hiring laborers.</li>
      <li>Servify is not liable for any damages, misconduct, or losses from transactions outside the platform.</li>
    </ul>
    <h3>Privacy Policy</h3>
    <p>We collect minimal personal information to improve our service. Your data is not shared without consent, except when required by law.</p>
    <button class="btn accept-btn" onclick="acceptTerms()">Accept & Continue</button>
    <button class="btn decline-btn" onclick="declineTerms()">Decline & Exit</button>
  </div>
</div>

<script>
function acceptTerms() {
  localStorage.setItem('acceptedTerms', 'true');
  document.getElementById('disclaimerModal').style.display = 'none';
}

function declineTerms() {
  alert("You must accept the terms to use this website.");
  window.location.href = "https://www.google.com";
}

window.onload = function() {
  if (!localStorage.getItem('acceptedTerms')) {
    document.getElementById('disclaimerModal').style.display = 'flex';
  }
};

// Fetch workers based on selected job, filter, and sort order
document.addEventListener("DOMContentLoaded", function() {
  const categoryButtons = document.querySelectorAll('.button');
  const filterBySelect = document.getElementById('filter_by_select');
  const sortOrderSelect = document.getElementById('sort_order_select');
  const workersContainer = document.getElementById('workers-container');

  // Function to fetch laborers based on selected job category and filters
  function fetchLaborers(job_id) {
    const filterBy = filterBySelect.value;
    const sortOrder = sortOrderSelect.value;

    const params = new URLSearchParams();
    params.append('job_id', job_id);
    params.append('filter_by', filterBy);
    params.append('sort_order', sortOrder);

    fetch('fetch_workers.php', {
      method: 'POST',
      body: params
    })
    .then(response => response.text())
    .then(data => {
      workersContainer.innerHTML = data;
    })
    .catch(error => console.error('Error fetching workers:', error));
  }

  // Trigger fetchLaborers when a category button is clicked
  categoryButtons.forEach(button => {
    button.addEventListener('click', function() {
      categoryButtons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
      const job_id = this.getAttribute('data-job-id');
      fetchLaborers(job_id);
    });
  });

  // Trigger fetch when filter or sort order changes
  filterBySelect.addEventListener('change', function() {
    const job_id = document.querySelector('.button.active').getAttribute('data-job-id');
    fetchLaborers(job_id);
  });

  sortOrderSelect.addEventListener('change', function() {
    const job_id = document.querySelector('.button.active').getAttribute('data-job-id');
    fetchLaborers(job_id);
  });

  // Load "All" laborers by default when the page is loaded
  fetchLaborers('all');
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
