<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="boyustyle.css" rel="stylesheet" type="text/css">
<link rel="icon" type="image/png" href="icon.png" size="16x16">
<?php
	if(isset($_POST["boyuUpdate"]))
		{
		require_once 'db_config.php';
			
			// create database connection
		$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			//check connection
		if($mysqli->connect_errno)
		{
			die("Failed to connect to MySQL: ".$mysqli->connect_errno);
		}
		
		// update patrol car status
		
		$sql = "UPDATE patrolcar SET patrolcarStatusId = ? WHERE patrolcarId = ? ";
		
		if(!($stmt = $mysqli->prepare($sql)))
		{
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('ss', $_POST['patrolCarStatus'], $_POST['patrolCarId']))
		{
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->execute())
		{
			die("Update patrolcar table failed: ". $stmt->errno);
		}
		
		// if patrol car status is Arrived (4) then capture the time of arrival
		
		if($_POST["patrolCarStatus"] == '4')
		{
			$sql = "UPDATE dispatch SET timeArrived = NOW() WHERE timeArrived is NULL AND patrolcarId = ?";
			
			if(!($stmt=$mysqli->prepare($sql)))
			{
				die("Prepare failed: ".$mysqli->errno);
			}
			if(!$stmt->bind_param('s', $_POST['patrolCarId']))
			{
				die("Binding parameter failed: ".$stmt->errno);
			}
			if(!$stmt->execute())
			{
				die("Update dispatch table failed: ".$stmt->errno);
			}
			
		} else if($_POST["patrolCarStatus"] == '3'){ //else if patrol car status is free (3) then capture the time of completion
		
			//First, retreive the incident ID from dispatch table handled by that patrol car
			$sql = "SELECT incidentId FROM dispatch WHERE timeCompleted IS NULL AND patrolcarId = ?";
			
			if (!($stmt = $mysqli->prepare($sql)))
			{
				die("Prepare failed: ".$mysqli->errno);
			}
			
			if(!$stmt->bind_param('s' , $_POST['patrolCarId']))
			{
				die("Binding parameters failed: ".$stmt->errno);	
			}
			
			if(!$stmt->execute())
			{
				die("Execute failed failed: ".$stmt->errno);
			}
			
			if(!($resultset = $stmt->get_result()))
			{
				die("Getting result set failed: ".$stmt->errno);
			}
			
			$incidentId;
			
			while ($row = $resultset->fetch_assoc())
			{
				$incidentId = $row['incidentId']; //here
			}
			
			//next update dispatch table
			$sql = "UPDATE dispatch SET timeCompleted = NOW()
						WHERE timeCompleted is NULL AND patrolcarId = ?";
			
			if(!($stmt = $mysqli->prepare($sql)))
			{
				die("Prepare failed: ".$mysqli->errno);
			}
			
			if(!$stmt->bind_param('s', $_POST['patrolCarId']))
			   {
				  die("Binding parameters failed: ".$stmt->errno); 
			   }
			
			if(!$stmt->execute())
			{
				die("Update dispatch table failed: ".$stmt->errno);
			}
			
			//last but not least, update incident table to completed (3) all patrol car attended to it are free now
			$sql = "UPDATE incident SET incidentStatusId = '3' WHERE incidentId = '$incidentId'
					AND NOT EXISTS (SELECT * FROM dispatch WHERE timeCompleted IS NULL AND incidentId = '$incidentId')";
			
			if(!($stmt = $mysqli->prepare($sql)))
			{
				die("Prepare failed 11: ".$mysqli->errno);
			}
			
			if(!$stmt->execute())
			{
				die("Update dispatch table failed: ".$stmt->errno);
			}
		
		$resultset->close();
			
		}
		
		$stmt->close();
		
		$mysqli->close();
		
		?>

		<script>window.location="logcall.php";</script>
	<?php }?>
</head>

<body style="background-color:#00a8ff">
	<?php $page = "update";?>
	<div class="cheeboyu">
	<?php require_once 'nav.php';?>
	</div>
	<?php 
	if (!isset($_POST["boyuSearch"])) { ?>	
	
	<fieldset><br>
<legend>Update Car Status</legend>	
<!-- create a form to search for patrol car based on id -->
<form name="formupdate" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
	<table width="50%" border="2" align="center" cellpadding="4"  cellspacing="4">
	<tr></tr>
	<tr>
		<td width="20%" align="center">Patrol Car ID :</td>
		<td width="50%" align="center"><input type="text" name="patrolCarId" id="patrolCarId" placeholder="Please enter the patrol car id" size="25"></td>
		<td align="center"><input type="submit" name="boyuSearch" id="boyuSearch" value="Search" class="boyubutton"></td>
	</tr>
   </table>
</form>
	
<?php }
	
else
{ // post back here after clicking the btnSearch button
	require_once 'db_config.php';
	
	// create database connection
	$mysqli  = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if ($mysqli->connect_errno){
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	// retrieve patrol car detail
	$sql = "SELECT * FROM patrolcar WHERE patrolcarId = ?";
	
	if (!($stmt = $mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if (!$stmt->bind_param('s', $_POST['patrolCarId'])){
		die("Binding parameters failed: ".$stmt->errno);
	}
	
	if (!$stmt->execute()) {
		die("Execute failed: ".$stmt->errno);
	}
	
	if (!($resultset = $stmt->get_result())) {
		die("Getting result set failed: ".$stmt->errno);
	}
	
	// if the patrol car does not exist, redirect back to update.php
	if ($resultset->num_rows == 0) {
		?>
	      <script>window.location="update.php";
		alert("Please re-enter the correct Patrol Car Id!");
		</script>		
	    <?php }
	
	// else if the patrol car found
	$patrolCarId;
	$patrolCarStatusId;
	
	while ($row = $resultset->fetch_assoc()) {
		$patrolCarId = $row['patrolcarId'];
		$patrolCarStatusId = $row['patrolcarStatusId'];
	}
	
	
	//retrieve from patrolcar_status table for populating the combo box
	$sql = "SELECT * FROM patrolcar_status";
	if (!($stmt = $mysqli->prepare($sql))) {
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if (!$stmt->execute()) {
		die("Execute failed: ".$stmt->errno);
	}
	
	if (!($resultset = $stmt->get_result())) {
		die("Getting result set failed: ".$stmt->errno);
	}
	
	$patrolCarStatusArray;; // an array variable
	
	while ($row = $resultset->fetch_assoc()) {
		$patrolCarStatusArray[$row['statusId']] = $row['statusDesc'];
	}
	
	$stmt->close();
	
	$resultset->close();
	
	$mysqli->close();
?>

<fieldset><br>
<legend>Update Car Status</legend>	
<!-- display a form for operator to update the status of patrol car -->
<form name="formstatuscar" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
	
	<table width="50%" border="2" align="center" cellpadding="4"  cellspacing="4">
		<tr></tr>
		<tr>
		   <td align="center">ID: </td>
			<td align="center"><?php echo $patrolCarId ?>
			<input type="hidden" name="patrolCarId" id="patrolCarId" 
			value="<?php echo $patrolCarId ?>">
			</td>
		</tr>
		<tr>
		    <td align="center">Status: </td>
			<td align="center"><select name="patrolCarStatus" id="patrolCarStatus">
			<?php foreach( $patrolCarStatusArray as $key => $value){ ?>
			<option value="<?php echo $key ?>"
			<?php if ($key==$patrolCarStatusId) {?> selected="selected"
				<?php }?>
			>
				<?php echo $value ?>
			</option>
			<?php } ?>
			</select></td>
		</tr>
		<tr>
		   <td align="center"><input type="reset" name="boyuCancel" id="boyuCancel" value="Reset" class="boyubutton"></td>
			<td align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="submit" name="boyuUpdate" id="boyuUpdate" value="Update" class="boyubutton">
			</td>
		</tr>
	</table>
</form>
<?php } ?>
	</fieldset>
	
	<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>	
	<!-- Site footer -->
    <footer class="site-footer">
      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <p class="text-justify" align="center"><b>Copyright &copy; 2020 Police Emergency Service System. All rights reserved.</b></p>
          </div>
        <hr>
      </div>
      <div class="container">
        <div class="row">
          <div class="col-md-8 col-sm-6 col-xs-12">
			  <p class="text-justify" align="center"><b>Design by Chee Bo Yu &nbsp;</i></b><br><hr>
				<p class="text-justify" align="center"><strong>Email:</strong> 
					<a href="mailto:boyu.chee@gmail.com">boyu.chee@gmail.com</a> for more enquires. &nbsp;</i>
          </div>
        </div>
      </div>
</footer>
</body>
</html>
