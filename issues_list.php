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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issue_id'])) {

  if($_FILES['pdf_attachment']['size'] > 0) {
    $fileTmpPath = $_FILES['pdf_attachment']['tmp_name'];
    $fileName = $_FILES['pdf_attachment']['name'];
    $fileSize = $_FILES['pdf_attachment']['size'];
    $fileType = $_FILES['pdf_attachment']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    if($fileExtension !== 'pdf') {
      die("Only pdf files allowed");
    }

    if($fileSize > 2 * 1024 * 1024) {
      die("File size exceeds 2MB limit");
    }

    $newFileName = MD5(time() . $fileName) . '.' . $fileExtension;
    $uploadFileDir = './uploads/';
    $dest_path = $uploadFileDir . $newFileName;

    // if uploads directory does not exist, create it
    if (!is_dir($uploadFileDir)) {
      mkdir($uploadFileDir, 0755, true);
    }

    if(move_uploaded_file($fileTmpPath, $dest_path)) {
      $attachmentPath = $dest_path;
    } else {
      die("error moving file");
    }
  } // end pdf attachment

    if ( !($_SESSION['admin'] == "1" || $_SESSION['user_id'] == $_POST['per_id']) ) {
      header("Location: issues_list.php");
      exit();
    }

    $issue_id = $_POST['issue_id'];
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $open_date = $_POST['open_date'];
    $close_date = $_POST['close_date'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $per_id = $_POST['per_id'];

    // $newFileName is PDF attachment
    // $attachmentPath is the entire path

    $pdo = Database::connect();
    $sql = "UPDATE iss_issues SET short_description=?, long_description=?, open_date=?, close_date=?, priority=?, org=?, project=?, pdf_attachment=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $newFileName, $issue_id,]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: issues_list.php");
    exit();
}

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_issue_id'])) {
  if ( !($_SESSION['admin'] == "1" || $_SESSION['user_id'] == $_POST['per_id']) ) {
    header("Location: issues_list.php");
    exit();
  }

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

  if($_FILES['pdf_attachment']['size'] > 0) {
    $fileTmpPath = $_FILES['pdf_attachment']['tmp_name'];
    $fileName = $_FILES['pdf_attachment']['name'];
    $fileSize = $_FILES['pdf_attachment']['size'];
    $fileType = $_FILES['pdf_attachment']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    if($fileExtension !== 'pdf') {
      die("Only pdf files allowed");
    }

    if($fileSize > 2 * 1024 * 1024) {
      die("File size exceeds 2MB limit");
    }

    $newFileName = MD5(time() . $fileName) . '.' . $fileExtension;
    $uploadFileDir = './uploads/';
    $dest_path = $uploadFileDir . $newFileName;

    // if uploads directory does not exist, create it
    if (!is_dir($uploadFileDir)) {
      mkdir($uploadFileDir, 0755, true);
    }

    if(move_uploaded_file($fileTmpPath, $dest_path)) {
      $attachmentPath = $dest_path;
    } else {
      die("error moving file");
    }
  } // end pdf attachment

    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $open_date = $_POST['open_date'];
    $close_date = $_POST['close_date'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $per_id = $_POST['per_id'];

    // $newFileName is PDF attachment
    // $attachmentPath is the entire path

    $pdo = Database::connect();
    $sql = 'INSERT INTO iss_issues (short_description, long_description, open_date, close_date, priority, org, project, per_id, pdf_attachment) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $per_id, $newFileName]);
    Database::disconnect();

    // Reload the page to reflect the changes
    header("Location: issues_list.php");
    exit();
}

// Fetch issues from the database to display in the table
$pdo = Database::connect();

// Fetch issues from the database to display in the table
$pdo = Database::connect();

$show = $_GET['show'] ?? 'open'; // Default to "open"

if ($show === 'all') {
    $sql = 'SELECT * FROM iss_issues';
} else {
    $sql = 'SELECT * FROM iss_issues 
            WHERE close_date IS NULL 
            OR close_date = "0000-00-00"
            OR close_date >= CURDATE()';
}

$stmt = $pdo->query($sql);
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
Database::disconnect();

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
    <a href="logout.php" class="btn btn-warning">Logout</a>

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
            <form action="issues_list.php" method="POST" enctype="multipart/form-data">
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
                <?php if ($_SESSION['admin'] == "1") { ?>
                  <select class="form-control" name="per_id" required>
                    <?php
                      $pdo = Database::connect();
                      $sql = 'SELECT id, fname, lname FROM iss_persons';
                      $stmt = $pdo->query($sql);
                      while ($row = $stmt->fetch()) {
                        echo "<option value='{$row['id']}'" . 
                            ($_SESSION['user_id'] == $row['id'] ? ' selected' : '') . 
                            ">{$row['fname']} {$row['lname']}</option>";
                      }
                      Database::disconnect();
                    ?>
                  </select>
                <?php } else { 
                  // Non-admin: fetch the user's name to display and lock their ID
                  $pdo = Database::connect();
                  $stmt = $pdo->prepare("SELECT fname, lname FROM iss_persons WHERE id = ?");
                  $stmt->execute([$_SESSION['user_id']]);
                  $user = $stmt->fetch();
                  Database::disconnect();
                  ?>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?>" disabled>
                  <input type="hidden" name="per_id" value="<?= $_SESSION['user_id']; ?>">
                <?php } ?>
              </div>
              <div class="form-group">
                <label for="pdf_attachment">PDF</label>
                <input type="file" class="form-control" name="pdf_attachment" accept="application/pdf" />
              </div>
              <button type="submit" class="btn btn-primary">Add Issue</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Buttons -->
    <div class="mb-3">
      <a href="issues_list.php?show=open" class="btn btn-primary <?= (!isset($_GET['show']) || $_GET['show'] === 'open') ? 'active' : '' ?>">Open Issues</a>
      <a href="issues_list.php?show=all" class="btn btn-secondary <?= (isset($_GET['show']) && $_GET['show'] === 'all') ? 'active' : '' ?>">All Issues</a>
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

              <?php if($_SESSION['user_id'] == $issue['per_id'] || $_SESSION['admin'] == "1") { ?>
              <!-- Update Button (U) -->
              <button class="btn btn-warning" data-toggle="modal" data-target="#updateIssueModal<?php echo $issue['id']; ?>">U</button>

              <!-- Delete Button (D) -->
              <button class="btn btn-danger" data-toggle="modal" data-target="#deleteIssueModal<?php echo $issue['id']; ?>">D</button>
              <?php } ?>
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
                        SELECT c.id, c.short_comment, c.long_comment, c.posted_date, p.fname, p.lname
                        FROM iss_comments c
                        JOIN iss_persons p ON c.per_id = p.id
                        WHERE c.iss_id = ?
                        ORDER BY c.posted_date DESC
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
                          echo "<small>Posted by " . htmlspecialchars($comment['fname']) . " " . htmlspecialchars($comment['lname']) . " on " . htmlspecialchars($comment['posted_date']) . "</small>";
                          
                          // Add the Read, Update, and Delete buttons for each comment
                          ?>
                          <!-- Read Button (R) -->
                          <button class="btn btn-info" data-toggle="modal" data-target="#readCommentModal<?php echo $comment['id']; ?>">R</button>

                          <?php if($_SESSION['user_id'] == $issue['per_id'] || $_SESSION['admin'] == "1") { ?>
                          <!-- Update Button (U) -->
                          <button class="btn btn-warning" data-toggle="modal" data-target="#updateCommentModal<?php echo $comment['id']; ?>">U</button>

                          <!-- Delete Button (D) -->
                          <button class="btn btn-danger" data-toggle="modal" data-target="#deleteCommentModal<?php echo $comment['id']; ?>">D</button>
                          <?php } ?>

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
                                  <p><strong>Person:</strong> <?= htmlspecialchars($comment['fname']) . " " . htmlspecialchars($comment['lname']); ?></p>
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
                                      <label for="short_comment">Short Comment</label>
                                      <input type="text" class="form-control" name="short_comment" value="<?php echo $comment['short_comment']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                      <label for="long_comment">Long Comment</label>
                                      <textarea class="form-control" name="long_comment" rows="4" required><?php echo $comment['long_comment']; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                      <label for="posted_date">Posted Date</label>
                                      <input type="date" class="form-control" name="posted_date" value="<?php echo $comment['posted_date']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                      <label for="per_id">Person</label>
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
                                      <label for="iss_id">Issue</label>
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
                          <?php
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
                      <label>PDF</label>
                      <input type="file" class="form-control" name="pdf_attachment" value="<?php echo $issue['pdf_attachment']; ?>" />
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