<?php 
require '../database/database.php';

$id = $_GET['id'];

if ( !empty($_POST)) { // if user clicks "yes" (sure to delete), delete record

	$id = $_POST['id'];
	
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "DELETE FROM iss_persons WHERE id = ?";
	$q = $pdo->prepare($sql);
	$q->execute(array($id));
	Database::disconnect();
	header("Location: iss_persons.php");
	
} 
else { // otherwise, pre-populate fields to show data to be deleted
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM iss_persons where id = ?";
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
		<div class="row">
			<h3>Delete Person</h3>
		</div>
		
		<form class="form-horizontal" action="iss_per_delete.php" method="post">
			<input type="hidden" name="id" value="<?php echo $id;?>"/>
			<p class="alert alert-error">Are you sure you want to delete ?</p>
			<div class="form-actions">
				<button type="submit" class="btn btn-danger">Yes</button>
				<a class="btn" href="iss_persons.php">No</a>
			</div>
		</form>
		
		<!-- Display same information as in file: iss_per_read.php -->
		
		<div class="form-horizontal" >
				
			<div class="control-group col-md-6">
			
				<label class="control-label">First Name</label>
				<div class="controls ">
					<label class="checkbox">
						<?php echo $data['fname'];?> 
					</label>
				</div>
				
				<label class="control-label">Last Name</label>
				<div class="controls ">
					<label class="checkbox">
						<?php echo $data['lname'];?> 
					</label>
				</div>
				
			</div>
			
				<div class="row">
					<h4>Issues where this person has commented</h4>
				</div>
				
				<?php
					$pdo = Database::connect();
					$sql = "SELECT * FROM iss_comments, iss_issues WHERE iss_id = iss_issues.id AND per_id = " . $id . " ORDER BY open_date ASC";
					foreach ($pdo->query($sql) as $row) {
                        echo $row['short_description'] . ' -- ' . $row['long_description'] . ' -- Opened on ' . $row['open_date'];
					}
				?>
				
		</div>  <!-- end div: class="form-horizontal" -->

    </div> <!-- end div: class="container" -->
	
</body>
</html>