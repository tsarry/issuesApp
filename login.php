<?php
session_start(); // Start a session to store user data if login is successful
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

                

                // Hash the entered password with the stored salt
                $hashedPassword = md5($password . $storedSalt);

                // Compare the hashed password with the stored hash
                if ($hashedPassword === $storedHash) {
                    // Login successful, start session and store user data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_fname'] = $user['fname'];
                    $_SESSION['user_lname'] = $user['lname'];
                    $_SESSION['user_level'] = $user['admin'];  // Store user level (admin or not)

                    // Redirect to issues list page
                    header("Location: issues_list.php");
                    exit();
                } else {
                    // Incorrect password
                    $errorMessage = "Invalid email or password.";
                }
            } else {
                // User not found
                $errorMessage = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            // Handle any PDO errors (e.g., database issues)
            $errorMessage = "Database error: " . $e->getMessage();
        }
    } else {
        // Missing email or password
        $errorMessage = "Please enter both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Department Status Report (DSR)</title>
</head>
<body>
    <h2>Login to Department Status Report (DSR)</h2>

    <?php
    // Display error message if any
    if (!empty($errorMessage)) {
        echo "<p style='color: red;'>$errorMessage</p>";
    }
    ?>

    <form method="POST" action="login.php">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
