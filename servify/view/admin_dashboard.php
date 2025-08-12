<?php 
include '../controls/connection.php';

/*session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}*/

if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $delete_query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['confirm'])) {
    $report_id = $_GET['confirm'];
    $user_id = $_GET['user_id'];
    $report_reason = isset($_GET['reason']) ? $_GET['reason'] : '';

    if (!empty($report_reason)) {
        $deduction = 0;

        switch ($report_reason) {
            case 'false_information':
                $deduction = 50;
                break;
            case 'nudity':
                $deduction = 50;
                break;
            case 'harassment':
                $deduction = 80;
                break;
            case 'spam':
                $deduction = 20;
                break;
            case 'hate_speech':
                $deduction = 30;
                break;
            case 'scam':
                $deduction = 80;
                break;
            case 'other':
                $deduction = 10;
                break;
        }

        error_log("Deduction for report ID " . $report_id . ": " . $deduction);

        $update_query = "UPDATE users SET credit_score = credit_score - ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $deduction, $user_id);

        if ($stmt->execute()) {
            error_log("Credit score updated successfully for user_id: " . $user_id);

            $update_report_query = "UPDATE reports SET status = 'confirmed' WHERE report_id = ?";
            $stmt = $conn->prepare($update_report_query);
            $stmt->bind_param("i", $report_id);
            if ($stmt->execute()) {
                error_log("Report ID " . $report_id . " marked as confirmed.");

                $stmt->close();
                header("Location: admin_dashboard.php");
                exit();
            } else {
                error_log("Error updating report status for report_id: " . $report_id);
            }
        } else {
            error_log("Error updating credit score for user_id: " . $user_id);
        }

        $stmt->close();
    } else {
        error_log("No report reason selected.");
    }
}

// Handle report rejection
if (isset($_GET['reject'])) {
    $report_id = $_GET['reject'];

    // Mark the report as rejected
    $reject_query = "UPDATE reports SET status = 'rejected' WHERE report_id = ?";
    $stmt = $conn->prepare($reject_query);
    $stmt->bind_param("i", $report_id);

    if ($stmt->execute()) {
        error_log("Report ID " . $report_id . " marked as rejected.");
    } else {
        error_log("Error rejecting report ID: " . $report_id);
    }

    $stmt->close();

    // Redirect back to the admin dashboard
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch total laborers count
$laborers_query = "SELECT COUNT(*) AS total_laborers FROM users WHERE role = 'laborer'";
$laborers_result = $conn->query($laborers_query);
$laborers_count = ($laborers_result && $laborers_result->num_rows > 0) ? $laborers_result->fetch_assoc()['total_laborers'] : 0;

// Fetch total job postings count
$jobs_query = "SELECT COUNT(*) AS total_jobs FROM jobs";
$jobs_result = $conn->query($jobs_query);
$jobs_count = ($jobs_result && $jobs_result->num_rows > 0) ? $jobs_result->fetch_assoc()['total_jobs'] : 0;

// Fetch pending verification requests
$verification_query = "SELECT v.request_id, v.user_id, v.id_proof, v.supporting_doc, v.status, u.firstname, u.lastname 
                       FROM verification_requests v 
                       JOIN users u ON v.user_id = u.user_id
                       WHERE v.status = 'pending'";
$verification_result = $conn->query($verification_query);

// Fetch pending reports
$report_query = "SELECT r.report_id, r.user_id, r.reason, r.additional_details, r.status, u.firstname, u.lastname 
                 FROM reports r 
                 JOIN users u ON r.user_id = u.user_id 
                 WHERE r.status = 'pending'";
$report_result = $conn->query($report_query);

// Fetch all laborers
$sql = "SELECT user_id, firstname, lastname, email, location, contact, rating, credit_score, is_verified FROM users WHERE role = 'laborer'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-blue-900 text-white p-5">
            <h1 class="text-2xl font-bold mb-6">Admin Panel</h1>
            <nav>
                <ul>
                    <li class="mb-4"><a href="admin_dashboard.php" class="block p-2 hover:bg-blue-700 rounded">Dashboard</a></li>
                    <li class="mb-4"><a href="admin_jobs.php" class="block p-2 hover:bg-blue-700 rounded">Jobs</a></li>
                    <li class="mb-4"><a href="admin_users.php" class="block p-2 hover:bg-blue-700 rounded">Users</a></li>
                    <li class="mb-4"><a href="admin_verifications.php" class="block p-2 hover:bg-blue-700 rounded">View Applications</a></li>
                    <li><a href="../controls/logout.php" class="block p-2 hover:bg-blue-700 rounded">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-semibold mb-6">Admin Dashboard</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold">Total Laborers</h3>
                    <p class="text-2xl font-bold"><?php echo $laborers_count; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold">Job Postings</h3>
                    <p class="text-2xl font-bold"><?php echo $jobs_count; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold">Reports</h3>
                    <p class="text-2xl font-bold"><?php echo $report_result->num_rows; ?> Pending</p>
                </div>
            </div>

            <!-- Verification Applications Section -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-2xl font-semibold mb-4">Verification Applications</h3>
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">User ID</th>
                            <th class="border p-2">Name</th>
                            <th class="border p-2">ID Proof</th>
                            <th class="border p-2">Supporting Document</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $verification_result->fetch_assoc()):
                        ?>
                            <tr class="border">
                                <td class="border p-2"><?php echo $row['user_id']; ?></td>
                                <td class="border p-2"><?php echo $row['firstname'] . " " . $row['lastname']; ?></td>
                                <td class="border p-2"><a href="../uploads/<?php echo $row['id_proof']; ?>" target="_blank">View ID</a></td>
                                <td class="border p-2"><a href="../uploads/<?php echo $row['supporting_doc']; ?>" target="_blank">View Document</a></td>
                                <td class="border p-2"><?php echo ucfirst($row['status']); ?></td>
                                <td class="border p-2">
                                    <a href="view_user.php?user_id=<?php echo $row['user_id']; ?>" class="text-blue-500">View Profile</a> | 
                                    <a href="../controls/admin/approve_verification.php?request_id=<?php echo $row['request_id']; ?>" class="text-green-500">Approve</a> | 
                                    <a href="../controls/admin/reject_verification.php?request_id=<?php echo $row['request_id']; ?>" class="text-red-500">Reject</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Reports Section -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-2xl font-semibold mb-4">Pending Reports</h3>
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">Report ID</th>
                            <th class="border p-2">User ID</th>
                            <th class="border p-2">Name</th>
                            <th class="border p-2">Reason</th>
                            <th class="border p-2">Details</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $report_result->fetch_assoc()):
                        ?>
                            <tr class="border">
                                <td class="border p-2"><?php echo $row['report_id']; ?></td>
                                <td class="border p-2"><?php echo $row['user_id']; ?></td>
                                <td class="border p-2"><?php echo $row['firstname'] . " " . $row['lastname']; ?></td>
                                <td class="border p-2"><?php echo ucfirst($row['reason']); ?></td>
                                <td class="border p-2"><?php echo $row['additional_details']; ?></td>
                                <td class="border p-2"><?php echo ucfirst($row['status']); ?></td>
                                <td class="border p-2">
                                <a href="view_user.php?user_id=<?php echo $row['user_id']; ?>" class="text-blue-500">View Profile</a> | 
                                <a href="?confirm=<?php echo $row['report_id']; ?>&user_id=<?php echo $row['user_id']; ?>&reason=<?php echo $row['reason']; ?>" class="text-green-500">Confirm</a> | 
                                <a href="?reject=<?php echo $row['report_id']; ?>" class="text-red-500" onclick="return confirm('Are you sure you want to reject this report?')">Reject</a>
                            </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>    
                </table>
            </div>

            <!-- Laborers Management Section -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold mb-4">Laborers Management</h3>
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">User ID</th>
                            <th class="border p-2">First Name</th>
                            <th class="border p-2">Last Name</th>
                            <th class="border p-2">Email</th>
                            <th class="border p-2">Location</th>
                            <th class="border p-2">Contact</th>
                            <th class="border p-2">Rating</th>
                            <th class="border p-2">Credit Score</th>
                            <th class="border p-2">Verified</th>
                            <th class="border p-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border">
                                <td class="border p-2"><?php echo $row['user_id']; ?></td>
                                <td class="border p-2"><?php echo $row['firstname']; ?></td>
                                <td class="border p-2"><?php echo $row['lastname']; ?></td>
                                <td class="border p-2"><?php echo $row['email']; ?></td>
                                <td class="border p-2"><?php echo $row['location']; ?></td>
                                <td class="border p-2"><?php echo $row['contact']; ?></td>
                                <td class="border p-2"><?php echo $row['rating']; ?></td>
                                <td class="border p-2"><?php echo $row['credit_score']; ?></td>
                                <td class="border p-2"><?php echo ($row['is_verified'] == 1) ? '✅ Yes' : '❌ No'; ?></td>
                                <td class="border p-2">
                                    <a href="?delete=<?php echo $row['user_id']; ?>" class="text-red-500" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

<?php $conn->close(); ?>
