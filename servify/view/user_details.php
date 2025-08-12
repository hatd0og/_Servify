<?php
session_start();
include '../controls/connection.php';

if (!isset($_SESSION['email'], $_SESSION['password'])) {
    header("Location: signup.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $fb_link = $_POST['fb_link'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];
    $date_created = date("Y-m-d H:i:s");

    $role = 'laborer';
    $credit_score = 100;
    $is_verified = 0;

    $sql = "INSERT INTO users (email, password, firstname, middlename, lastname, fb_link, location, contact, date_created, role, credit_score, is_verified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssii", $email, $password, $firstname, $middlename, $lastname, $fb_link, $location, $contact, $date_created, $role, $credit_score, $is_verified);
    
    if ($stmt->execute()) {
        unset($_SESSION['email'], $_SESSION['password']); // Clear session after successful sign-up
        header("Location: index.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Full Page Centering */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f9f9f9;
        }

        /* Frame Container */
        .frame {
            text-align: center;
            padding: 40px;
            border: 2px solid black;
            border-radius: 10px;
            width: 300px;
            box-shadow: 5px 5px 0px black;
        }

        /* Title */
        .frame label {
            display: block;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Input Fields */
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 2px solid black;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Button */
        button {
            font-size: 16px;
            padding: 10px 20px;
            border: 2px solid black;
            background: none;
            cursor: pointer;
            transition: 0.3s;
            border-radius: 5px;
            width: 100%;
        }

        button:hover {
            background: black;
            color: white;
        }
    </style>
</head>
<body>
<!--     <div class="frame">
        <label>Complete Your Profile</label>
        <form action="" method="POST">
            <input  name="firstname" placeholder="First Name" required><br>
            <input  name="middlename" placeholder="Middle Name"><br>
            <input  name="lastname" placeholder="Last Name" required><br>
            <input  name="fb_link" placeholder="Facebook Link"><br>
            <input  name="location" placeholder="Location" required><br>
            <input  name="contact" placeholder="Contact Number" required><br><br>
            <button type="submit">Submit</button>
        </form>
    </div> -->

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

<div class="signup-container text-center">
<h3>Complete Your Profile</h3>
<form action="" method="POST">
  <div class="mb-3">
      <input class="form-control" name="firstname" placeholder="First Name" required>
  </div>
  <div class="mb-3">
      <input class="form-control" name="middlename" placeholder="Middle Name">
  </div>
  <div class="mb-3">
      <input class="form-control" name="lastname" placeholder="Last Name" required>
  </div>
  <div class="mb-3">
      <input class="form-control" name="fb_link" placeholder="Facebook Link">    
  </div>
  <div class="mb-3">
      <input class="form-control" name="location" placeholder="Location" required>
  </div>
  <div class="mb-3">
      <input class="form-control" name="contact" placeholder="Contact Number" required><br>
  </div>
  <button type="submit" class="btn btn-primary w-100">Submit</button>
</form> 

</div>
</body>
</html>
