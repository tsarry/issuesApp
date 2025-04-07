<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  // If not logged in, redirect to login page
  session_destroy();
  header("Location: login.php");
}

require '../database/database.php';

// Handle Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['person_id'])) {
    $person_id = $_POST['person_id'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $admin = $_POST['admin'];

    $pdo = Database::connect();
    $sql = "UPDATE iss_persons SET fname=?, lname=?, mobile=?, email=?, admin=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fname, $lname, $mobile, $email, $admin, $person_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: persons_list.php");
    exit();
}

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_person_id'])) {
    $delete_person_id = $_POST['delete_person_id'];

    $pdo = Database::connect();
    $sql = "DELETE FROM iss_persons WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_person_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: persons_list.php");
    exit();
}

// Handle Add New Person
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fname'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $pwd_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pwd_salt = bin2hex(random_bytes(16));
    $admin = $_POST['admin'];

    $pdo = Database::connect();
    $sql = 'INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt, admin) 
            VALUES (?, ?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fname, $lname, $mobile, $email, $pwd_hash, $pwd_salt, $admin]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: persons_list.php");
    exit();
}

// Fetch persons from the database to display in the table
$pdo = Database::connect();
$sql = 'SELECT * FROM iss_persons';
$stmt = $pdo->query($sql);
$persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Persons List</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

  <!-- jQuery, Popper.js, and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container mt-4">
    <!-- "+" Button to Open the Add Person Modal -->
    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addPersonModal">
      <i class="fa fa-plus"></i> Add Person
    </button>

    <!-- Add New Person Modal -->
    <div class="modal fade" id="addPersonModal" tabindex="-1" role="dialog" aria-labelledby="addPersonModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addPersonModalLabel">Add New Person</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Form to Add a New Person -->
            <form action="persons_list.php" method="POST">
              <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" class="form-control" name="fname" required>
              </div>
              <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" class="form-control" name="lname" required>
              </div>
              <div class="form-group">
                <label for="mobile">Mobile</label>
                <input type="text" class="form-control" name="mobile" required>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" required>
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" required>
              </div>
              <div class="form-group">
                <label for="admin">Admin</label>
                <select class="form-control" name="admin" required>
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Add Person</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Persons List Table -->
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Email</th>
          <th>Mobile</th>
          <th>Admin</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($persons as $person): ?>
          <tr>
            <td><?php echo $person['id']; ?></td>
            <td><?php echo $person['fname']; ?></td>
            <td><?php echo $person['lname']; ?></td>
            <td><?php echo $person['email']; ?></td>
            <td><?php echo $person['mobile']; ?></td>
            <td><?php echo $person['admin'] == 1 ? 'Yes' : 'No'; ?></td>
            <td>
              <!-- Read Button (R) -->
              <button class="btn btn-info" data-toggle="modal" data-target="#readPersonModal<?php echo $person['id']; ?>">R</button>

              <!-- Update Button (U) -->
              <button class="btn btn-warning" data-toggle="modal" data-target="#updatePersonModal<?php echo $person['id']; ?>">U</button>

              <!-- Delete Button (D) -->
              <button class="btn btn-danger" data-toggle="modal" data-target="#deletePersonModal<?php echo $person['id']; ?>">D</button>
            </td>
          </tr>

          <!-- Read Person Modal (R) -->
          <div class="modal fade" id="readPersonModal<?php echo $person['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="readPersonModalLabel<?php echo $person['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="readPersonModalLabel<?php echo $person['id']; ?>">Read Person</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p><strong>ID:</strong> <?= htmlspecialchars($person['id']); ?></p>
                  <p><strong>First Name:</strong> <?= htmlspecialchars($person['fname']); ?></p>
                  <p><strong>Last Name:</strong> <?= htmlspecialchars($person['lname']); ?></p>
                  <p><strong>Email:</strong> <?= htmlspecialchars($person['email']); ?></p>
                  <p><strong>Mobile:</strong> <?= htmlspecialchars($person['mobile']); ?></p>
                  <p><strong>Admin:</strong> <?= htmlspecialchars($person['admin']) == 1 ? 'Yes' : 'No'; ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Update Person Modal (U) -->
          <div class="modal fade" id="updatePersonModal<?php echo $person['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updatePersonModalLabel<?php echo $person['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="updatePersonModalLabel<?php echo $person['id']; ?>">Update Person</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form action="persons_list.php" method="POST">
                    <input type="hidden" name="person_id" value="<?php echo $person['id']; ?>">
                    <div class="form-group">
                      <label>First Name</label>
                      <input type="text" class="form-control" name="fname" value="<?php echo $person['fname']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Last Name</label>
                      <input type="text" class="form-control" name="lname" value="<?php echo $person['lname']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Mobile</label>
                      <input type="text" class="form-control" name="mobile" value="<?php echo $person['mobile']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Email</label>
                      <input type="email" class="form-control" name="email" value="<?php echo $person['email']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Admin</label>
                      <select class="form-control" name="admin" required>
                        <option value="0" <?php echo ($person['admin'] == '0') ? 'selected' : ''; ?>>No</option>
                        <option value="1" <?php echo ($person['admin'] == '1') ? 'selected' : ''; ?>>Yes</option>
                      </select>
                    </div>
                    <button type="submit" class="btn btn-warning">Update Person</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Delete Person Modal (D) -->
          <div class="modal fade" id="deletePersonModal<?php echo $person['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deletePersonModalLabel<?php echo $person['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deletePersonModalLabel<?php echo $person['id']; ?>">Delete Person</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p>Are you sure you want to delete this person?</p>
                  <form action="persons_list.php" method="POST">
                    <input type="hidden" name="delete_person_id" value="<?php echo $person['id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
