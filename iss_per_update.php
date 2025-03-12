<?php 
	
require '../database/database.php';

$id = $_GET['id'];

if ( !empty($_POST)) { // if $_POST filled then process the form

	# initialize/validate (same as file: iss_per_create.php)

	// initialize user input validation variables
	$fnameError = null;
	$lnameError = null;
	
	// initialize $_POST variables
	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	
	// validate user input
	$valid = true;
	if (empty($fname)) {
		$fnameError = 'Please enter First Name';
		$valid = false;
	}
	if (empty($lname)) {
		$lnameError = 'Please enter Last Name';
		$valid = false;
	}

	
	// restrict file types for upload
	
	if ($valid) { // if valid user input update the database
		$pdo = Database::connect();
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "UPDATE iss_persons  set fname = ?, lname = ? WHERE id = ?";
			$q = $pdo->prepare($sql);
			$q->execute(array($fname, $lname, $id));
			Database::disconnect();
			header("Location: iss_persons.php");
	}
} else { // if $_POST NOT filled then pre-populate the form
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM iss_persons where id = ?";
	$q = $pdo->prepare($sql);
	$q->execute(array($id));
	$data = $q->fetch(PDO::FETCH_ASSOC);
	$fname = $data['fname'];
	$lname = $data['lname'];
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
				<h3>Update Person Details</h3>
			</div>
	
			<form class="form-horizontal" action="iss_per_update.php?id=<?php echo $id?>" method="post" enctype="multipart/form-data">
			
				<!-- Form elements (same as file: iss_per_create.php) -->

				<div class="control-group <?php echo !empty($fnameError)?'error':'';?>">
					<label class="control-label">First Name</label>
					<div class="controls">
						<input name="fname" type="text"  placeholder="First Name" value="<?php echo !empty($fname)?$fname:'';?>">
						<?php if (!empty($fnameError)): ?>
							<span class="help-inline"><?php echo $fnameError;?></span>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="control-group <?php echo !empty($lnameError)?'error':'';?>">
					<label class="control-label">Last Name</label>
					<div class="controls">
						<input name="lname" type="text"  placeholder="Last Name" value="<?php echo !empty($lname)?$lname:'';?>">
						<?php if (!empty($lnameError)): ?>
							<span class="help-inline"><?php echo $lnameError;?></span>
						<?php endif; ?>
					</div>
				</div>
			  
				<div class="form-actions">
					<button type="submit" class="btn btn-success">Update</button>
					<a class="btn" href="iss_persons.php">Back</a>
				</div>
				
			</form>
				
		</div><!-- end div: class="span10 offset1" -->
		
    </div> <!-- end div: class="container" -->
	
</body>
</html>