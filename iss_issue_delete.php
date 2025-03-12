<?php 
require '../database/database.php';

$id = $_GET['id'];

if ( !empty($_POST)) { // if user clicks "yes" (sure to delete), delete record

	$id = $_POST['id'];
	
	// delete data
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "DELETE FROM iss_issues WHERE id = ?";
	$q = $pdo->prepare($sql);
	$q->execute(array($id));
	Database::disconnect();
	header("Location: iss_issues.php");
	
} 
else { // otherwise, pre-populate fields to show data to be deleted
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM iss_issues where id = ?";
	$q = $pdo->prepare($sql);
	$q->execute(array($id));
	$data = $q->fetch(PDO::FETCH_ASSOC);
	Database::disconnect();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <!-- <link   href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.min.js"></script>
	<link rel="icon" href="cardinal_logo.png" type="image/png" /> -->
</head>

<body>
    <div class="container">

		<div class="span10 offset1">
		
			<div class="row">
				<h3>Delete Issue</h3>
			</div>
			
			<form class="form-horizontal" action="iss_issue_delete.php" method="post">
				<input type="hidden" name="id" value="<?php echo $id;?>"/>
				<p class="alert alert-error">Are you sure you want to delete ?</p>
				<div class="form-actions">
					<button type="submit" class="btn btn-danger">Yes</button>
					<a class="btn" href="iss_issues.php">No</a>
				</div>
			</form>
			
			<!-- Display same information as in file: iss_issue_read.php -->
			
			<div class="form-horizontal" >
			
            <div class="control-group">
					<label class="control-label">Short Description</label>
					<div class="controls">
						<label class="checkbox">
							<?php echo $data['short_description'];?>
						</label>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">Long Description</label>
					<div class="controls">
						<label class="checkbox">
							<?php echo $data['long_description'];?>
						</label>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">Open Date</label>
					<div class="controls">
						<label class="checkbox">
							<?php echo $data['open_date'];?>
						</label>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">Close Date</label>
					<div class="controls">
						<label class="checkbox">
							<?php echo $data['close_date'];?>
						</label>
					</div>
				</div>

                <div class="control-group">
					<label class="control-label">Priority</label>
					<div class="controls">
						<label class="checkbox">
							<?php echo $data['priority'];?>
						</label>
					</div>
				</div>
				
			<div class="row">
				<h4>People Who Commented on This Issue</h4>
			</div>
			
			<?php
				$pdo = Database::connect();
				$sql = "SELECT * FROM iss_comments, iss_persons WHERE per_id = iss_persons.id AND iss_id = " . $data['id'] . ' ORDER BY lname ASC, fname ASC';
				$countrows = 0;
				foreach ($pdo->query($sql) as $row) {
					echo $row['lname'] . ', ' . $row['fname'] . '<br />';
					$countrows++;
				}
				if ($countrows == 0) echo 'none.';
			?>
			
			</div> <!-- end div: class="form-horizontal" -->
			
		</div> <!-- end div: class="span10 offset1" -->
				
    </div> <!-- end div: class="container" -->
  </body>
</html>