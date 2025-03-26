<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

require '../database/database.php';

// Handle Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issue_id'])) {
    $issue_id = $_POST['issue_id'];
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $open_date = $_POST['open_date'];
    $close_date = $_POST['close_date'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $per_id = $_POST['per_id'];

    $pdo = Database::connect();
    $sql = "UPDATE iss_issues SET short_description=?, long_description=?, open_date=?, close_date=?, priority=?, org=?, project=?, per_id=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $per_id, $issue_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: issues_list.php");
    exit();
}

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_issue_id'])) {
    $delete_issue_id = $_POST['delete_issue_id'];

    $pdo = Database::connect();
    $sql = "DELETE FROM iss_issues WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_issue_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: issues_list.php");
    exit();
}

// Handle Add New Issue
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['short_description'])) {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $open_date = $_POST['open_date'];
    $close_date = $_POST['close_date'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $per_id = $_POST['per_id'];

    $pdo = Database::connect();
    $sql = 'INSERT INTO iss_issues (short_description, long_description, open_date, close_date, priority, org, project, per_id) 
            VALUES (?, ?, NOW(), NULL, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_description, $long_description, $priority, $org, $project, $per_id]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: issues_list.php");
    exit();
}

// Fetch issues from the database to display in the table
$pdo = Database::connect();
$sql = 'SELECT * FROM iss_issues';
$stmt = $pdo->query($sql);
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
Database::disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Issues List</title>
  
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
    <!-- "+" Button to Open the Add Issue Modal -->
    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addIssueModal">
      <i class="fa fa-plus"></i> Add Issue
    </button>

    <!-- Button to redirect to persons_list.php -->
    <a href="persons_list.php" class="btn btn-info mb-3">
      <i class="fa fa-users"></i> View Persons List
    </a>

    <!-- Button to redirect to comments_list.php -->
    <a href="comments_list.php" class="btn btn-info mb-3">
      <i class="fa fa-comments"></i> View Comments List
    </a>

    <!-- Logout Button -->
    <a href="logout.php"><button>Logout</button></a>

    <!-- Add New Issue Modal -->
    <div class="modal fade" id="addIssueModal" tabindex="-1" role="dialog" aria-labelledby="addIssueModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addIssueModalLabel">Add New Issue</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Form to Add a New Issue -->
            <form action="issues_list.php" method="POST">
              <div class="form-group">
                <label for="short_description">Short Description</label>
                <input type="text" class="form-control" name="short_description" required>
              </div>
              <div class="form-group">
                <label for="long_description">Long Description</label>
                <textarea class="form-control" name="long_description" rows="4" required></textarea>
              </div>
              <div class="form-group">
                <label for="open_date">Open Date</label>
                <input type="date" class="form-control" name="open_date" required>
              </div>
              <div class="form-group">
                <label for="close_date">Close Date</label>
                <input type="date" class="form-control" name="close_date">
              </div>
              <div class="form-group">
                <label for="priority">Priority</label>
                <select class="form-control" name="priority" required>
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="C">C</option>
                </select>
              </div>
              <div class="form-group">
                <label for="org">Organization</label>
                <input type="text" class="form-control" name="org" required>
              </div>
              <div class="form-group">
                <label for="project">Project</label>
                <input type="text" class="form-control" name="project" required>
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
              <button type="submit" class="btn btn-primary">Add Issue</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Issues List Table -->
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Short Description</th>
          <th>Open Date</th>
          <th>Close Date</th>
          <th>Priority</th>
          <th>Org</th>
          <th>Project</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($issues as $issue): ?>
          <tr>
            <td><?php echo $issue['id']; ?></td>
            <td><?php echo $issue['short_description']; ?></td>
            <td><?php echo $issue['open_date']; ?></td>
            <td><?php echo $issue['close_date']; ?></td>
            <td><?php echo $issue['priority']; ?></td>
            <td><?php echo $issue['org']; ?></td>
            <td><?php echo $issue['project']; ?></td>
            <td>
              <!-- Read Button (R) -->
              <button class="btn btn-info" data-toggle="modal" data-target="#readIssueModal<?php echo $issue['id']; ?>">R</button>

              <!-- Update Button (U) -->
              <button class="btn btn-warning" data-toggle="modal" data-target="#updateIssueModal<?php echo $issue['id']; ?>">U</button>

              <!-- Delete Button (D) -->
              <button class="btn btn-danger" data-toggle="modal" data-target="#deleteIssueModal<?php echo $issue['id']; ?>">D</button>
            </td>
          </tr>

          <!-- Read Issue Modal (R) -->
          <div class="modal fade" id="readIssueModal<?php echo $issue['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="readIssueModalLabel<?php echo $issue['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="readIssueModalLabel<?php echo $issue['id']; ?>">Read Issue</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p><strong>ID:</strong> <?= htmlspecialchars($issue['id']); ?></p>
                  <p><strong>Short Description:</strong> <?= htmlspecialchars($issue['short_description']); ?></p>
                  <p><strong>Long Description:</strong> <?= htmlspecialchars($issue['long_description']); ?></p>
                  <p><strong>Open Date:</strong> <?= htmlspecialchars($issue['open_date']); ?></p>
                  <p><strong>Close Date:</strong> <?= htmlspecialchars($issue['close_date']); ?></p>
                  <p><strong>Priority:</strong> <?= htmlspecialchars($issue['priority']); ?></p>
                  <p><strong>Organization:</strong> <?= htmlspecialchars($issue['org']); ?></p>
                  <p><strong>Project:</strong> <?= htmlspecialchars($issue['project']); ?></p>
                  <p><strong>Person ID:</strong> <?= htmlspecialchars($issue['per_id']); ?></p>

                  <!-- List of Comments for the Issue -->
                  <h5 class="mt-4">Comments:</h5>
                  <ul class="list-group">
                    <?php
                      // Fetch comments and person names for this issue
                      $pdo = Database::connect();
                      $sql = "
                        SELECT c.short_comment, c.long_comment, c.posted_date, p.fname, p.lname
                        FROM iss_comments c
                        JOIN iss_persons p ON c.per_id = p.id
                        WHERE c.iss_id = ?
                      ";
                      $stmt = $pdo->prepare($sql);
                      $stmt->execute([$issue['id']]);
                      $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      Database::disconnect();

                      // Display the comments
                      if (count($comments) > 0) {
                        foreach ($comments as $comment) {
                          echo "<li class='list-group-item'>";
                          echo "<strong>" . htmlspecialchars($comment['short_comment']) . "</strong><br>";
                          echo "<p>" . nl2br(htmlspecialchars($comment['long_comment'])) . "</p>";
                          echo "<small>Posted by " . htmlspecialchars($comment['fname']) . " " . htmlspecialchars($comment['lname']) . " on " . htmlspecialchars($comment['posted_date']) . "</small>";
                          echo "</li>";
                        }
                      } else {
                        echo "<li class='list-group-item'>No comments for this issue.</li>";
                      }
                    ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Update Issue Modal (U) -->
          <div class="modal fade" id="updateIssueModal<?php echo $issue['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateIssueModalLabel<?php echo $issue['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="updateIssueModalLabel<?php echo $issue['id']; ?>">Update Issue</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form action="issues_list.php" method="POST">
                    <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                    <div class="form-group">
                      <label>Short Description</label>
                      <input type="text" class="form-control" name="short_description" value="<?php echo $issue['short_description']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Long Description</label>
                      <textarea class="form-control" name="long_description" rows="4" required><?php echo $issue['long_description']; ?></textarea>
                    </div>
                    <div class="form-group">
                      <label>Open Date</label>
                      <input type="date" class="form-control" name="open_date" value="<?php echo $issue['open_date']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Close Date</label>
                      <input type="date" class="form-control" name="close_date" value="<?php echo $issue['close_date']; ?>">
                    </div>
                    <div class="form-group">
                      <label>Priority</label>
                      <select class="form-control" name="priority" required>
                        <option value="A" <?php echo ($issue['priority'] == 'A') ? 'selected' : ''; ?>>A</option>
                        <option value="B" <?php echo ($issue['priority'] == 'B') ? 'selected' : ''; ?>>B</option>
                        <option value="C" <?php echo ($issue['priority'] == 'C') ? 'selected' : ''; ?>>C</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Organization</label>
                      <input type="text" class="form-control" name="org" value="<?php echo $issue['org']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Project</label>
                      <input type="text" class="form-control" name="project" value="<?php echo $issue['project']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label>Person</label>
                      <select class="form-control" name="per_id" required>
                        <?php
                          $pdo = Database::connect();
                          $sql = 'SELECT id, fname, lname FROM iss_persons';
                          $stmt = $pdo->query($sql);
                          while ($row = $stmt->fetch()) {
                            echo "<option value='{$row['id']}'" . ($row['id'] == $issue['per_id'] ? ' selected' : '') . ">{$row['fname']} {$row['lname']}</option>";
                          }
                          Database::disconnect();
                        ?>
                      </select>
                    </div>
                    <button type="submit" class="btn btn-warning">Update Issue</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Delete Issue Modal (D) -->
          <div class="modal fade" id="deleteIssueModal<?php echo $issue['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteIssueModalLabel<?php echo $issue['id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteIssueModalLabel<?php echo $issue['id']; ?>">Delete Issue</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p>Are you sure you want to delete this issue?</p>
                  <form action="issues_list.php" method="POST">
                    <input type="hidden" name="delete_issue_id" value="<?php echo $issue['id']; ?>">
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