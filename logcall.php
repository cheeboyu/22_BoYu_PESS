<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="boyustyle.css" rel="stylesheet" type="text/css">
<link rel="icon" type="image/png" href="icon.png" size="16x16">
</head>
<script>
//validation
function validation()
{
	var number = document.forms["formLoginCall"]["boyunumber"].value;
	var location = document.forms["formLoginCall"]["boyulocation"].value;
	var incident = document.forms["formLoginCall"]["incidentType"].value;
	var description = document.forms["formLoginCall"]['boyudescription'].value;
	
	if (number == "")
	{
		alert("Please enter your number!");
		return false;
	}
	
	if (location == "")
	{
		alert("Please enter your location!");
		return false;
	}
	
	if (incident == "")
	{
		alert("Please select your incident type!");
		return false;
	}
	
	if(description == "") {
			alert("Description Must Be Filled Out!");
			return false;
		}
		return true;
	}
	</script>
		
<body style="background-color:#00a8ff">
<?php $page = "logcall";?>
<?php require 'nav.php';?> <!--menu bar -->
<?php require 'db_config.php'; //database details

//create database connection
$mysqli = mysqli_connect(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
//check connection
if ($mysqli->connect_errno)
	{
	die("Unable to connect to MySQL: ".$mysqli->connect_errno);
	}
	
$sql = "SELECT * FROM incidenttype";
//Test the sql command in $sql, if got error display error message and exit.
if (!($stmt = $mysqli->prepare($sql)))
	{
	die("The command have failed: ".$mysqli->errno);
	}
//Checking command
if (!$stmt->execute())
	{
	die("Cannot run database(mysql) command: ".$stmt->errno);
	}
//Check any data in resultset
if (!($resultset = $stmt->get_result()))
	{
	die("There is no data in resultset: ".$stmt->errno);
	}
	
$incidentType; //an array variable
	
while ($row =$resultset->fetch_assoc())
	{
	//Create an assoicative array of $incidentType {incident_type_id, incident_type_desc}
	$incidentType[$row['incidentTypeId']] = $row['incidentTypeDesc'];
	}

$stmt->close();

$resultset->close();

$mysqli->close();
?>
<fieldset>
<legend>Log in Call</legend>
<form name="formLoginCall" method="post" action="dispatch.php" onSubmit="return validation();">
	<table width="45%" border="2" align="center" cellpadding="5" cellspacing="5">
	<tr>
	<td width="20%" align="center">Name of Caller:</td>
	<td width="50%"><input type="text" name="boyucaller" id="boyucaller" placeholder="Please enter the caller name" pattern="[A-Za-z\s]{0,}"title="Letters Only!" size="25"></td>
	</tr>
	<tr>
	<td width="20%" align="center">Contact Number:<span class="required">*</span></td>
	<td width="50%"><input type="text" name="boyunumber" id="boyunumber" placeholder="Please enter the caller number" pattern="[0-9]{8}"title="8-Digt Only!" size="25"></td>
	</tr>
	<tr>
	<td width="20%" align="center">Location:<span class="required">*</span></td>
	<td width="50%"><input type="text" name="boyulocation" id="boyulocation" placeholder="Please enter the location" size="25"></td>
	</tr>
	<tr>
	<td width="20%" align="center">Incident Type:<span class="required">*</span></td>
	
	<td width="50%"><select name="incidentType" id="incidentType">
		<option disabled selected value="">--Select an option--
		</option>
		<?php foreach($incidentType as $key=> $value) {?>
		<option value="<?php echo $key ?> " >
		<?php echo $value ?> </option>
		<?php } ?>
	</select>
	</td>
	</tr>
	<tr>
	<td width="20%" align="center">Description:<span class="required">*</span></td>
	<td width="50%"><textarea name="boyudescription" id="boyudescription" cols="60" rows="6" placeholder="Please enter the description of the incident"></textarea></td>
	</tr>
	<tr>
	<table width="40%" border="0" align="center" cellpadding="5" cellspacing="5">
	<td align="center"><input type="reset" name="resetProcess" id="resetProcess" value="Reset" class="boyubutton"</td>	
	<td align="center"><input type='submit' name="btnProcess" id="btnProcess" value="Process Call" class="boyubutton"</td>
	</tr>
	</table>
	</table>
</form>
</fieldset>
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
            </p>
            </p>
          </div>
        </div>
      </div>
      </div>
</footer>
</body>
</html>
