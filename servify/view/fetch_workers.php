<?php
include '../controls/connection.php'; // Ensure you're including your DB connection file

// 1. Read inputs (job_id, filter_by, sort_order)
$job_id = $_POST['job_id'] ?? 'all';
$filter_by = $_POST['filter_by'] ?? 'labor'; // Default filter by 'name'
$sort_order = $_POST['sort_order'] ?? 'ASC'; // Default to ascending order

// 2. Validate/sanitize filter_by
$valid_filters = ['name', 'location', 'labor'];
if (!in_array($filter_by, $valid_filters)) {
    $filter_by = 'labor'; // Default to filtering by 'name'
}

// 3. Map filter_by to actual columns
switch ($filter_by) {
    case 'location':
        $order_by = 'users.location';
        break;
    case 'labor':
        $order_by = 'jobs.job_name';
        break;
    case 'name':
    default:
        $order_by = "CONCAT(users.firstname, ' ', users.lastname)";
        break;
}

// 4. Build SQL query
if ($job_id === 'all') {
    $sql = "
      SELECT
        users.user_id,
        users.firstname,
        users.lastname,
        users.location,
        users.is_verified,
        users.rating,
        users.profile_picture,
        jobs.job_id,
        jobs.job_name
      FROM users
      INNER JOIN user_jobs ON users.user_id = user_jobs.user_id
      INNER JOIN jobs ON jobs.job_id = user_jobs.job_id
      WHERE users.role != 'admin'
      ORDER BY $order_by $sort_order
    ";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "
      SELECT
        users.user_id,
        users.firstname,
        users.lastname,
        users.location,
        users.is_verified,
        users.rating,
        users.profile_picture,
        jobs.job_id,
        jobs.job_name
      FROM users
      INNER JOIN user_jobs ON users.user_id = user_jobs.user_id
      INNER JOIN jobs ON jobs.job_id = user_jobs.job_id
      WHERE user_jobs.job_id = ?
      ORDER BY $order_by $sort_order
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
}

// 5. Execute and output
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows > 0) {
    echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6">';
    while ($worker = $res->fetch_assoc()) {
        $status = $worker['is_verified'] ? 'Verified' : 'Not Verified';
        $pic = !empty($worker['profile_picture'])
            ? 'http://localhost/servify/' . $worker['profile_picture']
            : 'http://localhost/servify/uploads/profile_pics/default.jpg';

        echo '<div class="col mb-4 labor-card">
                <a href="../view/view_profile2.php?user_id=' . $worker['user_id'] . '&job_id=' . $worker['job_id'] . '" style="text-decoration:none;color:inherit;">
                  <div class="card mx-auto border-0 shadow-sm" style="width:12rem;cursor:pointer;">
                    <img src="' . htmlspecialchars($pic) . '" alt="Profile Picture" class="profile-pic" style="height:150px;object-fit:cover;">
                    <div class="card-body p-2">
                      <div class="d-flex justify-content-between align-items-center w-100" style="gap:20px;">
                        <h6 class="card-title mb-0 labor-name text-truncate" style="font-size:18px;flex-grow:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                          ' . htmlspecialchars($worker['firstname'] . ' ' . $worker['lastname']) . '
                        </h6>
                        <span class="badge ' . ($worker['is_verified'] ? 'bg-success' : 'bg-danger') . '" style="font-size:12px;white-space:nowrap;">
                          ' . $status . '
                        </span>
                      </div>
                      <h6 class="card-text mt-1 mb-0 text-muted labor-job text-truncate" style="font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ' . htmlspecialchars($worker['job_name']) . '
                      </h6>
                      <p class="card-text mb-1 text-muted labor-location text-truncate" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ' . htmlspecialchars($worker['location']) . '
                      </p>
                    </div>
                  </div>
                </a>
              </div>';
    }
    echo '</div>';
} else {
    echo '<p>No workers available for this category and filter.</p>';
}

$conn->close();
?>
