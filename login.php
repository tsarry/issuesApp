<?php
session_start(); // Start a session to store user data if login is successful

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // If already logged in, redirect to issues list page or any other page
    header("Location: issues_list.php");
    exit();
}

require '../database/database.php'; // Include the database connection file
$pdo = Database::connect(); // Assuming you have a Database class to handle the connection

// Define error message variable
$errorMessage = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user's email and password from POST
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input
    if (!empty($email) && !empty($password)) {
        try {
            // Prepare SQL query to fetch user details by email
            $sql = "SELECT * FROM iss_persons WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // Check if a user exists with the provided email
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Get the stored password hash and salt
                $id = $user['id'];
                $fname = $user['fname'];
                $lname = $user['lname'];
                $storedHash = $user['pwd_hash'];
                $storedSalt = $user['pwd_salt'];
                $admin = $user['admin'];

                // Hash the entered password with the stored salt
                $hashedPassword = md5($password . $storedSalt);

                // Compare the hashed password with the stored hash
                if ($hashedPassword === $storedHash) {
                    // Login successful, start session and store user data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_fname'] = $user['fname'];
                    $_SESSION['user_lname'] = $user['lname'];
                    $_SESSION['admin'] = $user['admin'];  // Store user level (admin or not)

                    // Redirect to issues list page
                    header("Location: issues_list.php");
                    exit();
                } else {
                    // Incorrect password
                    $errorMessage = "Invalid email or password.";
                    session_destroy();
                }
            } else {
                // User not found
                $errorMessage = "Invalid email or password.";
                session_destroy();
            }
        } catch (PDOException $e) {
            // Handle any PDO errors (e.g., database issues)
            $errorMessage = "Database error: " . $e->getMessage();
            session_destroy();
        }
    } else {
        // Missing email or password
        $errorMessage = "Please enter both email and password.";
        session_destroy();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Tracking System - Login</title>

    <!-- Bootstrap CSS (Add from a CDN) -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Issue Tracking System - Login</h2>

        <?php
        // Display error message if any
        if (!empty($errorMessage)) {
            echo "<div class='alert alert-danger'>$errorMessage</div>";
        }
        ?>

        <form method="POST" action="login.php" class="mt-4">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <!-- "Join" Button to go to the registration page -->
        <p class="mt-3">Don't have an account? <a href="register.php">Join here</a></p>
    </div>

    <!-- Bootstrap JS (Add from a CDN) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
