<?php
require '../database/database.php';

// Handle Update Person
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

// Handle Delete Person
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
    $admin = $_POST['admin'];
    $pwd_salt = uniqid(); // Generate a unique salt
    $pwd_hash = password_hash("default_password", PASSWORD_DEFAULT); // Default password, change as needed

    $pdo = Database::connect();
    $sql = 'INSERT INTO iss_persons (fname, lname, mobile, email, admin, pwd_hash, pwd_salt) VALUES (?, ?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fname, $lname, $mobile, $email, $admin, $pwd_hash, $pwd_salt]);
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
    
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addPersonModal">
            <i class="fa fa-plus"></i> Add Person
        </button>

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

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
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
                        <td><?php echo $person['mobile']; ?></td>
                        <td><?php echo $person['email']; ?></td>
                        <td><?php echo $person['admin']; ?></td>
                        <td>
                            <button class="btn btn-info" data-toggle="modal" data-target="#readPersonModal<?php echo $person['id']; ?>">R</button>

                            <button class="btn btn-warning" data-toggle="modal" data-target="#updatePersonModal<?php echo $person['id']; ?>">U</button>

                            <button class="btn btn-danger" data-toggle="modal" data-target="#deletePersonModal<?php echo $person['id']; ?>">D</button>
                        </td>
                    </tr>

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
                                    <form>
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type="text" class="form-control" value="<?php echo $person['fname']; ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type="text" class="form-control" value="<?php echo $person['lname']; ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Mobile</label>
                                            <input type="text" class="form-control" value="<?php echo $person['mobile']; ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" value="<?php echo $person['email']; ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Admin</label>
                                            <input type="text" class="form-control" value="<?php echo $person['admin']; ?>" readonly>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                                <option value="0" <?php echo ($person['admin'] == 0) ? 'selected' : ''; ?>>No</option>
                                                <option value="1" <?php echo ($person['admin'] == 1) ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-warning">Update Person</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

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