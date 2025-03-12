<?php
require '../database/database.php';

$id = $_GET['id'];

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "SELECT * FROM iss_persons where id = ?";
$q = $pdo->prepare($sql);
$q->execute(array($id));
$data = $q->fetch(PDO::FETCH_ASSOC);
Database::disconnect();

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
				<h3>View Person Details</h3>
			</div>
			 
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
					
					<div class="form-actions">
						<a class="btn" href="iss_persons.php">Back</a>
					</div>
					
				</div>
				
				<div class="row">
					<h4>Issues where this person has commented</h4>
				</div>
				
				<?php
					$pdo = Database::connect();
					$sql = "SELECT * FROM iss_comments, iss_issues WHERE iss_id = iss_issues.id AND per_id = " . $id . " ORDER BY open_date ASC";
					$countrows = 0;
					foreach ($pdo->query($sql) as $row) {
                        echo $row['short_description'] . ' -- ' . $row['long_description'] . ' -- Opened on ' . $row['open_date'];
						$countrows++;
					}
					if ($countrows == 0) echo 'none.';
				?>
				
			</div>  <!-- end div: class="form-horizontal" -->

		</div> <!-- end div: class="container" -->
		
	</body> 
	
</html>