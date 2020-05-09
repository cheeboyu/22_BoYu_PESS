<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="boyustyle.css" rel="stylesheet" type="text/css">
<link rel="icon" type="image/png" href="icon.png" size="16x16">
</head>
<body style="background-color:#00a8ff">
	<div class="cheeboyu">
	<?php require 'nav.php';
	?>
	</div>
	<?php //if post back
	if (isset($_POST["btnDispatch"]))
	{
		require_once 'db_config.php';
		
		//create database connection
		$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
		//check connection
		if ($mysqli->connect_errno)
		{
			die("Unable to connect to MySql: ".$mysqli->connect_errno);
		}
		
		$patrolcarDispatched = $_POST["chkPatrolcar"]; // array of patrolcar being dispatched from post back
		$numOfPatrolcarDispatched = count($patrolcarDispatched);
		
		//insert new incident
		$incidentStatus;
		if ($numOfPatrolcarDispatched > 0)
		{
			$incidentStatus='2'; //incident status to be set as Dispatched
		}
		else
		{
			$incidentStatus='1'; //incident status to be set as Pending
		}
		
		$sql = "INSERT INTO incident (callerName, phoneNumber, incidentTypeId, incidentLocation, incidentDesc, incidentStatusId)
		VALUES (?, ?, ?, ?, ?, ?)";
		
		if(!($stmt = $mysqli->prepare($sql)))
		{
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('ssssss', $_POST['boyucaller'],
							 			$_POST['boyunumber'],
							  			$_POST['incidentType'],
							  			$_POST['boyulocation'],
							  			$_POST['boyudescription'],
							 			$incidentStatus))
		
		{
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if (!$stmt->execute())
		{
			die("Insert incident table failed: ".$stmt->errno);
		}
		
		// retrieve incident_id for the newly inserted incident
		$incidentId=mysqli_insert_id($mysqli);
		
		//update patrolcar status table and add into dispatch table
		for($i=0; $i < $numOfPatrolcarDispatched; $i++)
			
	{
		// update patrol car status
		$sql = "UPDATE patrolcar SET patrolcarStatusId ='1' WHERE patrolcarId = ?";
		
		if (!($stmt = $mysqli->prepare($sql)))
		{
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if (!$stmt->bind_param('s', $patrolcarDispatched[$i]))
		{
			die("Binding parameters failed: ".$stmt->errno);
		}
			
		if (!$stmt->execute())
		{
			die("Update patrolcar_status table failed: ".$stmt->errno);
		}
			
		//insert dispatch data
		$sql = "INSERT INTO dispatch (incidentId, patrolcarId, timeDispatched) VALUES (?, ?, NOW())";
		
		if (!($stmt = $mysqli->prepare($sql)))
		{
			die("Prepare failed: ".$mysqli->errno);
		}
			
		if (!$stmt->bind_param('ss', $incidentId,
							  		$patrolcarDispatched[$i]))
		{
			die("Binding parameters failed: ".$stmt->errno);
		}
			
		if(!$stmt->execute())
		{
			die("Insert dispatch table failed: ".$stmt->errno);
		}
	}
		
		$stmt->close();
		
		$mysqli->close();
	} ?>
	
<fieldset>
<legend>Dispatch Patrol Cars</legend>
<!-- display the incident information passed from logcall.php -->
<form name="formdispatch" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
<div class="tabledispatch">
<table width="40%" border="1" align="center" cellpadding="4" cellspacing="4">
	<tr>
		<td colspan="2"><strong><center>Incident Detail</center></strong></td>
		<hr>
	</tr>
	<tr>
	<td>Name of Caller:</td>
	<td><?php echo $_POST['boyucaller']?>
	<input type="hidden" name="boyucaller" id="boyucaller"
	value="<?php echo $_POST['boyucaller']?>"></td>
	</tr>

	<tr>
	<td>Contact Number:</td>
	<td><?php echo $_POST['boyunumber']?>
	<input type="hidden" name="boyunumber" id="boyunumber"
	value="<?php echo $_POST['boyunumber']?>"></td>
	</tr>
	
	<tr>
	<td>Location:</td>
	<td><?php echo $_POST['boyulocation']?>
	<input type="hidden" name="boyulocation" id="boyulocation"
	value="<?php echo $_POST['boyulocation']?>"></td>
	</tr>

	<tr>
	<td>Incident Type:</td>
	<td><?php echo $_POST['incidentType']?>
	<input type="hidden" name="incidentType" id="incidentType"
	value="<?php echo $_POST['incidentType']?>"></td>
	</tr>
	
	<tr>
	 <td>Description :</td>	
<td><textarea name="boyudescription" cols="45"
rows="5" readonly id="boyudescription"><?php echo $_POST['boyudescription'] ?></textarea>
<input name="boyudescription" type="hidden" id="boyudescription" value="<?php echo $_POST['boyudescription']?>"></td>
	</tr>
</table>
	<?php 
// connect to a database
require_once'db_config.php';
	
// create database connection
$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
// check connection
if($mysqli->connect_errno) 
{
	die("Failed to connect to MySQL: ".$mysqli->connect_errno);
}

// retrieve from patrolcar table those patrol cars that are 2:Patrol or 3:Free
$sql = "SELECT patrolcarId, statusDesc FROM patrolcar JOIN patrolcar_status
ON patrolcar.patrolcarStatusId=patrolcar_status.StatusId
WHERE patrolcar.patrolcarStatusId='2' OR patrolcar.patrolcarStatusId='3'";

	if (!($stmt = $mysqli->prepare($sql)))
	{
		die("Prepare failed: ".$mysqli->errno);
	}
	if (!$stmt->execute())
	{
		die("Cannot run SQL command: ".$stmt->errno);
	}
	if(!($resultset = $stmt->get_result()))
	{
		die("No data in resultset: ".$stmt->errno);
	}
	
	$patrolcarArray; // an array variable
	
	while  ($row = $resultset->fetch_assoc()) 
	{
		$patrolcarArray[$row['patrolcarId']] = $row['statusDesc'];
	}
	
	$stmt->close();
	$resultset->close();
	$mysqli->close();
	?>
	
	<!--populate table with patrol car data -->
	<br><br>
        <table border="1" align="center" width="100%"> 
            <tr> 
				<td colspan="3"><center><strong>Dispatch Patrolcar Panel</strong></center>
    </tr> 
            
        <?php 
            foreach($patrolcarArray as $key=>$value){ 
?> 
    <tr> 
    <td align="center"><input type="checkbox" name="chkPatrolcar[]" value="<?php echo $key?>">
    </td> 
    <td align="center"><?php echo $key ?></td>
    <td align="center"><?php echo $value ?></td>
        
    </tr> <?php } ?> 
    <tr>
    <td align="center"><input type="reset" name="btnCancel" id="btnCancel" value="Reset" class="boyubutton"> </td>
    <td colspan="2" align="center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnDispatch" value="Dispatch" class="boyubutton">
</td>
        </tr>
                                                        
        </table>
</div>
</form>
	</fieldset>
<br>
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