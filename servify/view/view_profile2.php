<?php
session_start();
include '../controls/connection.php';

// Get user_id from URL or set it to 0 if not present
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$is_logged_in = isset($_SESSION['user_id']);

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0; // Retrieve job_id from URL

if ($user_id === 0) {
    header("Location: 404.php");
    exit();
}

// Fetch user details from the database
$sql = "SELECT firstname, middlename, lastname, fb_link, email, location, date_created, contact, is_verified, rating, profile_picture FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: 404.php");
    exit();
}

$profile_picture = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png';
// Fetch services offered by the user
if ($job_id === 0) {
    // If job_id is 0 or not set, show all jobs of the user
    $services_sql = "SELECT jobs.job_name, user_jobs.job_description, user_jobs.job_image 
                     FROM jobs
                     INNER JOIN user_jobs ON jobs.job_id = user_jobs.job_id
                     WHERE user_jobs.user_id = ?";
    $services_stmt = $conn->prepare($services_sql);
    $services_stmt->bind_param("i", $user_id);
} else {
    // Show specific job
    $services_sql = "SELECT jobs.job_name, user_jobs.job_description, user_jobs.job_image 
                     FROM jobs
                     INNER JOIN user_jobs ON jobs.job_id = user_jobs.job_id
                     WHERE jobs.job_id = ? AND user_jobs.user_id = ?";
    $services_stmt = $conn->prepare($services_sql);
    $services_stmt->bind_param("ii", $job_id, $user_id);
}

$services_stmt->execute();
$services_result = $services_stmt->get_result();
$services_stmt->close();

// Report functionality
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $report_reasons = isset($_POST['report_reason']) ? $_POST['report_reason'] : [];
    $additional_details = isset($_POST['additional_details']) ? $_POST['additional_details'] : "";

    if (empty($report_reasons)) {
        $error_message = "No report reasons selected.";
    } else {
        $status = 'pending';

        $stmt = $conn->prepare("INSERT INTO reports (user_id, reason, additional_details, status, report_date) VALUES (?, ?, ?, ?, NOW())");

        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("isss", $user_id, $reason, $additional_details, $status);

        foreach ($report_reasons as $reason) {
            $reason = $conn->real_escape_string($reason);
            if (!$stmt->execute()) {
                $error_message = "There was an issue submitting your report. Please try again.";
                break;
            }
        }

        if (!isset($error_message)) {
            $success_message = "Your report has been submitted successfully and is awaiting admin review.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../styles/view_profile.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Profile</title>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="../view/index.php">Servify</a>

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

<!-- PROFILE -->
<div class="container mt-4">
  <div class="profile-section d-flex justify-content-between align-items-center">
      <div class="profile-pic-container">
          <!-- Check if profile_picture exists and display the image -->
          <img src="../<?php echo htmlspecialchars($user['profile_picture']) ?: 'uploads/profile_pics/default.jpg'; ?>" alt="Profile Picture" class="profile-pic">

      </div>
      <div>
          <div class="name-rating">
              <h3><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']); ?></h3>
              <div class="profile-detail">
                  <?php echo $user['is_verified'] ? '<span class="verified">✅ Verified</span>' : '<span class="not-verified">❌ Not Verified</span>'; ?>
              </div>  
          </div>
          <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['location']); ?></p>
          <p><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($user['contact']); ?></p>
          <div class="social-icons">
              <?php if (!empty($user['fb_link'])): ?>
                  <a href="<?php echo htmlspecialchars($user['fb_link']); ?>" target="_blank" title="Facebook" class="social-icon">
                      <i class="fa-brands fa-facebook"></i>
                  </a>
              <?php endif; ?>
              <?php if (!empty($user['email'])): ?>
                  <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" title="Email" class="social-icon">
                      <i class="fa-solid fa-envelope"></i>
                  </a>
              <?php endif; ?>
          </div>
      </div>
      <!-- Report Button -->
      <div class="report-btn">
          <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#reportModal">Report</button>
      </div>
  </div>
  <hr>
  <div class="row">
      <div class="col-md-12">
        <div id="tab-content">
          <div class="tab-pane active" id="about">
            <h6 class="mt-3">Services</h6>
            <div class="services-list">
    <?php while ($service = $services_result->fetch_assoc()): ?>
        <div class="service-item">
            <h5><?php echo htmlspecialchars($service['job_name']); ?></h5>
            <p><?php echo htmlspecialchars($service['job_description']); ?></p>
            <!-- Display job image if it exists -->
            <?php if (!empty($service['job_image'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($service['job_image']); ?>" alt="Service Image" class="service-image" style="width: 100%; max-width: 300px; max-height: 300px; height: auto; border-radius: 10px; margin-top: 10px;">
            <?php else: ?>
                <!-- Display a placeholder image or a default image if no job_image is available -->
                <p>This laborer has not yet added a photo for this job.</p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<?php if ($services_result->num_rows === 0): ?>
    <p>No services available.</p>
<?php endif; ?>

          </div>
        </div>
      </div>
  </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reportModalLabel">Report User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Select a reason for reporting this user:</p>
        <form method="POST">
          <select class="form-select" name="report_reason[]">
            <option value="false_information">False Information</option>
            <option value="nudity">Nudity</option>
            <option value="harassment">Harassment</option>
            <option value="spam">Spam</option>
            <option value="hate_speech">Hate Speech</option>
            <option value="scam">Scam</option>
            <option value="other">Other</option>
          </select>
          <textarea class="form-control mt-2" name="additional_details" placeholder="Additional details (optional)"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger" id="submitReport">Submit Report</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById("submitReport").addEventListener("click", function () {
    const reason = document.getElementById("reportReason").value;
    const details = document.getElementById("reportDetails").value;
    alert("Report submitted: " + reason + (details ? "\nDetails: " + details : ""));
    document.querySelector(".btn-close").click(); // Close modal after submitting
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

</body>
</html>
