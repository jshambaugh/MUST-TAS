<?php
	/*
		This page displays the completed activities for the current day
		1. get current date and time in correct format
		2. display results of this statement - select profiles.sysid, fname, lname, in24, out24, activityno, timelogs.hrsearned,
		datein from profiles, timelogs where profiles.sysid = timelogs.sysid and timelogs.status = 'closed and
		datein = '$todaysdate' order by lname, activityno;
		3. if there are no results, display that there are none. 
		4. at the end, mention what time/date the report was generated. 
	*/
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
	
	include_once "C:\TASDocs\LoginCreds.inc";

	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
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
			<a href="AdminAccess.php" class="btn btn-info" style="float: left;"><i class="icon-home icon-white"></i> Admin Access</a>
			<a href="DoAdminLogout.php" class="btn btn-danger" style="float: right;"><i class="icon-remove icon-white"></i> Log Out</a>
			<hr>
			
			<h1> Completed Activities </h1>
			
			<br>
			
			<table class="table table-hover">
			
				<tr>
					<td><strong>User</strong></td>
					<td><strong>Sysid</strong></td>
					<td><strong>Login Time</strong></td>
					<td><strong>Logout Time</strong></td>
					<td><strong>Date</strong></td>
					<td><strong>Hours Earned</strong></td>
					<td><strong>Location</strong></td>
				</tr>
			
				<?php
					//find today's date, store in database format
					$date = date("n/j/Y");
					$time = date("H:i");
					
					//look up location codes from database, store into an array
					$query="SELECT * FROM locations";
					$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
					
					while ($obj = $result->fetch_object())
					{
						$locations[$obj->code] = $obj->name;
					}
				
					//execute huge query to find users that have completed activities today
					$query="select profiles.sysid, fname, lname, in24, out24, activityno, timelogs.hrsearned, loginspot, datein from profiles, timelogs where profiles.sysid = timelogs.sysid and timelogs.status = 'closed' and datein = '$date' order by loginspot, lname, activityno";
					$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
					
					//show if there are no activities completed today
					if($result->num_rows < 1)
					{
						?> <tr class="error">
										<td>There are no completed activites at this time</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr> <?php	
					}
					
					//now, systematically display all relevant information
					
					$totalhrs = 0;

					while($obj = $result->fetch_object())
					{
						?> <tr onclick="location.href='DoDisplayRecord.php?sysid=<?php echo $obj->sysid ?>'" style="cursor:pointer;">
								<td><?php echo $obj->lname ?>, <?php echo $obj->fname ?></td>
								<td><?php echo $obj->sysid ?></td>
								<td><?php echo $obj->in24 ?></td>
								<td><?php echo $obj->out24 ?></td>
								<td><?php echo $obj->datein ?></td>
								<td><?php echo $obj->hrsearned ?></td>
								<td><?php echo $locations[$obj->loginspot] ?></td>
								<?php $totalhrs = $totalhrs + $obj->hrsearned?>
						   </tr> <?php
					}
					
				?>
			
			</table>
			
			<p><strong>Total Community Service Hours Worked: <?php echo $totalhrs?></strong></p>

		
			<p>Report generated at <strong><?php echo $time ?></strong> on <strong><?php echo $date ?></strong></p>
		
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
  </body>
</html>