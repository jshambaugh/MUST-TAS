<?php
	session_start();

	//if not logged in, don't allow access
	if(!isset($_SESSION['admin']))
	{
		header("Location: AdminAccessLogin.php");
		$_SESSION['msgtext'] = "Access Denied: You must log in as an administrator to view this page";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	if($_SESSION['admin'] != true)
	{
		header("Location: AdminAccessLogin.php");
		$_SESSION['msgtext'] = "Access Denied: You must log in as an administrator to view this page";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//get the locations from the database
	
	include_once "C:\TASDocs\LoginCreds.inc";
	$tbl_name = "locations";
	
	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	$query="SELECT * FROM $tbl_name";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>MUST &middot; Time Accounting System</title>
		<link rel="shortcut icon" href="./assets/img/timesheet.png" type="image/png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="./assets/css/bootstrap.css" rel="stylesheet">
		<link href="./assets/css/datepicker.css" rel="stylesheet">
		<link href="./assets/css/bootstrap-timepicker.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
        padding-bottom: 60px;
      }

      /* Custom container */
      .container {
        margin: 0 auto;
        max-width: 1000px;
      }
      .container > hr {
        margin: 60px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 80px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 100px;
        line-height: 1;
      }
      .jumbotron .lead {
        font-size: 24px;
        line-height: 1.25;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }


      /* Customize the navbar links to be fill the entire space of the .navbar */
      .navbar .navbar-inner {
        padding: 0;
      }
      .navbar .nav {
        margin: 0;
        display: table;
        width: 100%;
      }
      .navbar .nav li {
        display: table-cell;
        width: 1%;
        float: none;
      }
      .navbar .nav li a {
        font-weight: bold;
        text-align: center;
        border-left: 1px solid rgba(255,255,255,.75);
        border-right: 1px solid rgba(0,0,0,.1);
      }
      .navbar .nav li:first-child a {
        border-left: 0;
        border-radius: 3px 0 0 3px;
      }
      .navbar .nav li:last-child a {
        border-right: 0;
        border-radius: 0 3px 3px 0;
      }
    </style>
    <link href="./assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="./assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="./assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="./assets/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="./assets/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="./assets/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="./assets/ico/favicon.png">
  </head>

  <body>

    <div class="container">

      <div class="masthead">
				<table style="width: 100%;">
					<tr>
						<td style="width: 50%;"><h3>TAS Admin Access</h3></td>
						<td style="width: 50%;"><img src="./assets/img/MUST_logo.jpg" style="float: right;"></td>
					</tr>
				</table>
				<hr>
			</div>
        

      <!-- Main Section -->
			<div class="row">
				<div class="offset3">
					<br>
					<br>
					<form class="form-horizontal" action="LogInSheet.php" method="post">
						<div class="control-group">
							<label class="control-label" for="fname">First Name:</label>
							<div class="controls">
								<input type="text" name="fname"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="lname">Last Name:</label>
							<div class="controls">
								<input type="text" name="lname"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="minitial">Middle Ininitial:</label>
							<div class="controls">
								<input type="text" name="minitial"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="rdate">Initial Registration Date:</label>
							<div class="controls">
								<input type="text" data-date-format="m/d/yyyy" id="rdate" name="rdate"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="odate">Orientation Date:</label>
							<div class="controls">
								<input type="text" data-date-format="m/d/yyyy" id="odate" name="odate"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="otime">Orientation Time:</label>
							<div class="controls">
								<input type="text" class="input-small" id="timepicker1" name="otime"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="sysid">System ID:</label>
							<div class="controls">
								<input type="text" name="sysid"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="location">Location:</label>
							<div class="controls">
								<select name="location">
									<?php while ($obj = $result->fetch_object()) {									
										echo "<option value=\"$obj->code\">$obj->name</option>";
									} ?>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="social">SSAN:</label>
							<div class="controls">
								<input type="text" name="social"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="age">Age:</label>
							<div class="controls">
								<input type="text" name="age"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="street">Street Address:</label>
							<div class="controls">
								<input type="text" name="street"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="city">City:</label>
							<div class="controls">
								<input type="text" name="city"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="state">State:</label>
							<div class="controls">
								<input type="text" name="state"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="zip">Zip Code:</label>
							<div class="controls">
								<input type="text" name="zip"></input>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="county">County:</label>
							<div class="controls">
								<input type="text" name="county"></input>
							</div>
						</div>
						<div class="control-group">
							<div class="controls">		
								<button type="submit" class="btn btn-primary">Register User</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		
				
				<?php //This adds an information pop-up when logging in incorrectly
				if(isset($_SESSION['msgtype'])) {
					$msgtype = $_SESSION['msgtype'];
					$msgtext = explode(":",$_SESSION['msgtext'],2); ?>
					
					<div class="modal hide fade" id="myModal">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 class="text-<?php echo $msgtype;?>"><?php echo $msgtext[0]; ?></h3>
						</div>
						<div class="modal-body">
							<p class="text-<?php echo $msgtype;?>"><?php echo $msgtext[1]; ?></p>
						</div>
						<div class="modal-footer">
							<a class="btn btn-primary" data-dismiss="modal">Close</a>
						</div>
					</div>
					
				<?php
						unset($_SESSION['msgtype']);
					} ?>

      <hr>

      <div class="footer">
        <p>&copy; MUST Ministries 2019</p>
      </div>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
		<script src="./assets/js/jquery.min.js"></script>
		<script src="./assets/js/bootstrap.min.js"></script>
		<script src="./assets/js/bootstrap-datepicker.js"></script>
		<script src="./assets/js/bootstrap-timepicker.min.js"></script>
		<script type="text/javascript">
			//automatically show modal
			$(window).load(function(){
				$('#myModal').modal('show');
				//start datepicker
				$('#rdate').datepicker();
				$('#odate').datepicker();
				$('#timepicker1').timepicker();
			});
		</script>

  </body>
</html>