<?php
// Include the database connection
require '../database/database.php';
$pdo = Database::connect();

$errorMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($fname) && !empty($lname) && !empty($mobile) && !empty($email) && !empty($password)) {
        try {
            // Check if email already exists
            $sql = "SELECT * FROM iss_persons WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                // Email doesn't exist, so create new user
                $salt = uniqid('', true); // Unique salt
                $pwd_hash = md5($password . $salt);

                // Insert the new user into the database
                $sql = "INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt, admin) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fname, $lname, $mobile, $email, $pwd_hash, $salt, 'No']); // Default user level as 'No' (not admin)

                // Redirect to login page after successful registration
                header("Location: login.php");
                exit();
            } else {
                $errorMessage = "Email is already registered.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill out all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Department Status Report (DSR)</title>
</head>
<body>
    <h2>Register for Department Status Report (DSR)</h2>

    <?php
    // Display error message if any
    if (!empty($errorMessage)) {
        echo "<p style='color: red;'>$errorMessage</p>";
    }
    ?>

    <form method="POST" action="register.php">
        <label for="fname">First Name:</label>
        <input type="text" name="fname" id="fname" required><br><br>

        <label for="lname">Last Name:</label>
        <input type="text" name="lname" id="lname" required><br><br>

        <label for="mobile">Mobile:</label>
        <input type="text" name="mobile" id="mobile" required><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>
