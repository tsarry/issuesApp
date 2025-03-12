<?php 
require '../database/database.php';

if ( !empty($_POST)) { // if not first time through

	// initialize user input validation variables
    $shortDescriptionError = null;
    $longDescriptionError = null;
    $openDateError = null;
    $closeDateError = null;
    $priorityError = null;
	
	// initialize $_POST variables
    $shortDescription = $_POST['short_description'];
    $longDescription = $_POST['long_description'];
    $openDate = $_POST['open_date'];
    $closeDate = $_POST['close_date'];
    $priority = $_POST['priority'];
	
	// validate user input
	$valid = true;
	if (empty($shortDescription)) {
		$shortDescriptionError = 'Please enter Short Description';
		$valid = false;
	}
	if (empty($longDescription)) {
		$longDescriptionError = 'Please enter Long Description';
		$valid = false;
	} 		
	if (empty($openDate)) {
		$openDateError = 'Please enter Open Date';
		$valid = false;
	}		
	if (empty($closeDate)) {
		$closeDateError = 'Please enter Close Date';
		$valid = false;
	}
    if (empty($priority)) {
		$priorityError = 'Please enter Priority Rank';
		$valid = false;
	}


	// insert data
	if ($valid) {
		$pdo = Database::connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "INSERT INTO iss_issues (short_description, long_description, open_date, close_date, priority) values(?, ?, ?, ?, ?)";
		$q = $pdo->prepare($sql);
		$q->execute(array($shortDescription,$longDescription,$openDate,$closeDate, $priority));
		Database::disconnect();
		header("Location: iss_issues.php");
	}
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
				<h3>Add New Issue</h3>
			</div>
	
			<form class="form-horizontal" action="iss_issue_create.php" method="post">
			
				<div class="control-group <?php echo !empty($shortDescriptionError)?'error':'';?>">
					<label class="control-label">Short Description</label>
					<div class="controls">
						<input name="short_description" type="text" placeholder="Short Description" value="<?php echo !empty($shortDescription)?$shortDescription:'';?>">
						<?php if (!empty($shortDescriptionError)): ?>
							<span class="help-inline"><?php echo $shortDescriptionError;?></span>
						<?php endif; ?>
					</div>
				</div>
			  
				<div class="control-group <?php echo !empty($longDescriptionError)?'error':'';?>">
					<label class="control-label">Long Description</label>
					<div class="controls">
						<input name="long_description" type="text" placeholder="Long Description" value="<?php echo !empty($longDescription)?$longDescription:'';?>">
						<?php if (!empty($longDescriptionError)): ?>
							<span class="help-inline"><?php echo $longDescriptionError;?></span>
						<?php endif;?>
					</div>
				</div>
				
				<div class="control-group <?php echo !empty($openDateError)?'error':'';?>">
					<label class="control-label">Open Date</label>
					<div class="controls">
						<input name="open_date" type="date" placeholder="Open Date" value="<?php echo !empty($openDate)?$openDate:'';?>">
						<?php if (!empty($openDateError)): ?>
							<span class="help-inline"><?php echo $openDateError;?></span>
						<?php endif;?>
					</div>
				</div>
				
				<div class="control-group <?php echo !empty($closeDateError)?'error':'';?>">
					<label class="control-label">Close Date</label>
					<div class="controls">
						<input name="close_date" type="date" placeholder="Close Date" value="<?php echo !empty($closeDate)?$closeDate:'';?>">
						<?php if (!empty($closeDateError)): ?>
							<span class="help-inline"><?php echo $closeDateError;?></span>
						<?php endif;?>
					</div>
				</div>

                <div class="control-group <?php echo !empty($priorityError)?'error':'';?>">
					<label class="control-label">Priority</label>
					<div class="controls">
						<input name="priority" type="text" placeholder="Priority" value="<?php echo !empty($priority)?$priority:'';?>">
						<?php if (!empty($priorityError)): ?>
							<span class="help-inline"><?php echo $priorityError;?></span>
						<?php endif;?>
					</div>
				</div>
				
				<div class="form-actions">
					<button type="submit" class="btn btn-success">Create</button>
					<a class="btn" href="iss_issues.php">Back</a>
				</div>
				
			</form>
			
		</div> <!-- div: class="container" -->
				
    </div> <!-- div: class="container" -->
	
</body>
</html>