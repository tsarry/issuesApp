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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_id'])) {
    $comment_id = $_POST['comment_id'];
    $short_comment = $_POST['short_comment'];
    $long_comment = $_POST['long_comment'];
    $posted_date = $_POST['posted_date'];
    $per_id = $_POST['per_id'];
    $iss_id = $_POST['iss_id'];

    $pdo = Database::connect();
    $sql = "UPDATE iss_comments SET short_comment=?, long_comment=?, posted_date=?, per_id=?, iss_id=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_comment, $long_comment, $posted_date, $per_id, $iss_id, $comment_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: comments_list.php");
    exit();
}

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_comment_id'])) {
    $delete_comment_id = $_POST['delete_comment_id'];

    $pdo = Database::connect();
    $sql = "DELETE FROM iss_comments WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_comment_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: comments_list.php");
    exit();
}

// Handle Add New Comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['short_comment'])) {
    $short_comment = $_POST['short_comment'];
    $long_comment = $_POST['long_comment'];
    $posted_date = $_POST['posted_date'];
    $per_id = $_POST['per_id'];
    $iss_id = $_POST['iss_id'];

    $pdo = Database::connect();
    $sql = 'INSERT INTO iss_comments (short_comment, long_comment, posted_date, per_id, iss_id) 
            VALUES (?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_comment, $long_comment, $posted_date, $per_id, $iss_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: comments_list.php");
    exit();
}

// Fetch comments from the database to display in the table
$pdo = Database::connect();
$sql = 'SELECT * FROM iss_comments';
$stmt = $pdo->query($sql);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comments List</title>

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
    <!-- "+" Button to Open the Add Comment Modal -->
    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addCommentModal">
      <i class="fa fa-plus"></i> Add Comment
    </button>

    <!-- Add New Comment Modal -->
    <div class="modal fade" id="addCommentModal" tabindex="-1" role="dialog" aria-labelledby="addCommentModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCommentModalLabel">Add New Comment</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Form to Add a New Comment -->
            <form action="comments_list.php" method="POST">
              <div class="form-group">
                <label for="short_comment">Short Comment</label>
                <input type="text" class="form-control" name="short_comment" required>
              </div>
              <div class="form-group">
                <label for="long_comment">Long Comment</label>
                <textarea class="form-control" name="long_comment" rows="4" required></textarea>
              </div>
              <div class="form-group">
                <label for="posted_date">Posted Date</label>
                <input type="date" class="form-control" name="posted_date" required>
              </div>
              <div class="form-group">
                <label for="per_id">Person</label>
                <select class="form-control" name="per_id" required>
                  <?php
                    $pdo = Database::connect();
                    $sql = 'SELECT id, fname, lname FROM iss_persons';
                    $stmt = $pdo->query($sql);
                    while ($row = $stmt->fetch()) {
                      echo "<option value='{$row['id']}'>{$row['fname']} {$row['lname']}</option>";
                    }
                    Database::disconnect();
                  ?>
                </select>
              </div>
              <div class="form-group">
                <label for="iss_id">Issue</label>
                <select class="form-control" name="iss_id" required>
                  <?php
                    $pdo = Database::connect();
                    $sql = 'SELECT id, short_description FROM iss_issues';
                    $stmt = $pdo->query($sql);
                    while ($row = $stmt->fetch()) {
                      echo "<option value='{$row['id']}'>{$row['short_description']}</option>";
                    }
                    Database::disconnect();
                  ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Add Comment</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Comments List Table -->
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Short Comment</th>
          <th>Long Comment</th>
          <th>Posted Date</th>
          <th>Person</th>
          <th>Issue</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($comments as $comment): ?>
          <tr>
            <td><?php echo $comment['id']; ?></td>
            <td><?php echo $comment['short_comment']; ?></td>
            <td><?php echo $comment['long_comment']; ?></td>
            <td><?php echo $comment['posted_date']; ?></td>
            <td>
              <?php
                $pdo = Database::connect();
                $sql = 'SELECT fname, lname FROM iss_persons WHERE id = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$comment['per_id']]);
                $person = $stmt->fetch();
                Database::disconnect();
                echo $person['fname'] . ' ' . $person['lname'];
              ?>
            </td>
            <td>
              <?php
                $pdo = Database::connect();
                $sql = 'SELECT short_description FROM iss_issues WHERE id = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$comment['iss_id']]);
                $issue = $stmt->fetch();
                Database::disconnect();
                echo $issue['short_description'];
              ?>
            </td>
            <td>
              <!-- Read Button (R) -->
              <button class="btn btn-info" data-toggle="modal" data-target="#readCommentModal<?php echo $comment['id']; ?>">R</button>

              <!-- Update Button (U) -->
              <button class="btn btn-warning" data-toggle="modal" data-target="#updateCommentModal<?php echo $comment['id']; ?>">U</button>

              <!-- Delete Button (D) -->
              <button class="btn btn-danger" data-toggle="modal" data-target="#deleteCommentModal<?php echo $comment['id']; ?>">D</button>
            </td>
          </tr>

          <!-- Read Comment Modal (R) -->
          <div class="modal fade" id="readCommentModal<?php echo $comment['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="readCommentModalLabel<?php echo $comment['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="readCommentModalLabel<?php echo $comment['id']; ?>">Read Comment</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p><strong>ID:</strong> <?= htmlspecialchars($comment['id']); ?></p>
                  <p><strong>Short Comment:</strong> <?= htmlspecialchars($comment['short_comment']); ?></p>
                  <p><strong>Long Comment:</strong> <?= htmlspecialchars($comment['long_comment']); ?></p>
                  <p><strong>Posted Date:</strong> <?= htmlspecialchars($comment['posted_date']); ?></p>
                  <p><strong>Person ID:</strong> <?= htmlspecialchars($comment['per_id']); ?></p>
                  <p><strong>Issue ID:</strong> <?= htmlspecialchars($comment['iss_id']); ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Update Comment Modal (U) -->
          <div class="modal fade" id="updateCommentModal<?php echo $comment['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateCommentModalLabel<?php echo $comment['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="updateCommentModalLabel<?php echo $comment['id']; ?>">Update Comment</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form action="comments_list.php" method="POST">
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <div class="form-group">
                      <label>Short Comment</label>
                      <input type="text" class="form-control" name="short_comment" value="<?php echo $comment['short_comment']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Long Comment</label>
                      <textarea class="form-control" name="long_comment" rows="4" required><?php echo $comment['long_comment']; ?></textarea>
                    </div>
                    <div class="form-group">
                      <label>Posted Date</label>
                      <input type="date" class="form-control" name="posted_date" value="<?php echo $comment['posted_date']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Person</label>
                      <select class="form-control" name="per_id" required>
                        <?php
                          $pdo = Database::connect();
                          $sql = 'SELECT id, fname, lname FROM iss_persons';
                          $stmt = $pdo->query($sql);
                          while ($row = $stmt->fetch()) {
                            echo "<option value='{$row['id']}'" . ($row['id'] == $comment['per_id'] ? ' selected' : '') . ">{$row['fname']} {$row['lname']}</option>";
                          }
                          Database::disconnect();
                        ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Issue</label>
                      <select class="form-control" name="iss_id" required>
                        <?php
                          $pdo = Database::connect();
                          $sql = 'SELECT id, short_description FROM iss_issues';
                          $stmt = $pdo->query($sql);
                          while ($row = $stmt->fetch()) {
                            echo "<option value='{$row['id']}'" . ($row['id'] == $comment['iss_id'] ? ' selected' : '') . ">{$row['short_description']}</option>";
                          }
                          Database::disconnect();
                        ?>
                      </select>
                    </div>
                    <button type="submit" class="btn btn-warning">Update Comment</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Delete Comment Modal (D) -->
          <div class="modal fade" id="deleteCommentModal<?php echo $comment['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteCommentModalLabel<?php echo $comment['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteCommentModalLabel<?php echo $comment['id']; ?>">Delete Comment</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p>Are you sure you want to delete this comment?</p>
                  <form action="comments_list.php" method="POST">
                    <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
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
