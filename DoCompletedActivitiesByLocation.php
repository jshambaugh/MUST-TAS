<?php
	/*
		This page displays the completed activities for the selected day
		1. get selected date from previous page
		2. run error checking to ensure the date is valid, and check to see if anything in the profiles or repeats table. return error if not.
			-SQL select count(*) from timelogs where datein = '$date';
			-SQL select count(*) from repeats where datein = '$date';
		3. display results of this statement for active users - select timelogs.datein, profiles.fname, profiles.lname, timelogs.sysid, timelogs.in24, timelogs.visitnumber, timelogs.out24, timelogs.hrsearned from profiles, timelogs where timelogs.datein = '$date' and timelogs.sysid = profiles.sysid order by timelogs.visitnumber, profiles.lname, timelogs.in24;
		4. display results of this statement for inactive users - select repeats.datein, profiles.fname, profiles.lname, repeats.sysid, repeats.in24, prepeats.visitnumber, repeats.out24, repeats.hrsearned from profiles, repeats where repeats.datein = '$date' and repeats.sysid = profiles.sysid order by repeats.visitnumber, profiles.lname, repeats.in24;
		5. if there are no results, display that there are none. 
		6. at the end, mention what time/date the report was generated. 
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
	$query="SELECT * FROM locations";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//1. get selected date from previous page
	$location = $_SESSION['location'];
	$date = $_REQUEST['date'];
	
	if(strlen($date)<8)
	{
		header("Location: CompletedActivitiesByLocation.php");
		$_SESSION['msgtext'] = "Error: The date was not submitted in the correct format";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//printf($date." \n");
	//printf($location." \n");
	//exit;
	
	/*2. run error checking to ensure the date is valid, and check to see if anything in the profiles or repeats table. return error if not.
			-SQL select count(*) from timelogs where datein = '$date';
			-SQL select count(*) from repeats where datein = '$date';
			*/
	try
	{
		$count = 0;
	
		$query="select count(*) from timelogs where datein = '$date'";
		$result=$mysqli->query($query);
		$count = $result->fetch_row()[0];
		
		$query="select count(*) from repeats where datein = '$date'";
		$result=$mysqli->query($query);
		$count += $result->fetch_row()[0];
	}
	catch(Exception $e)
	{
		header("Location: CompletedActivitiesByLocation.php");
		$_SESSION['msgtext'] = "Error: The date was not submitted in the correct format";
		$_SESSION['msgtype'] = "error";
		exit;
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
	  <?php
					//look up location codes from database, store into an array
					$query="SELECT * FROM locations";
					$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
					
					while ($obj = $result->fetch_object())
					{
						$locations[$obj->code] = $obj->name;
					}
		?>
	  
			<a href="AdminAccess.php" class="btn btn-info" style="float: left;"><i class="icon-home icon-white"></i> Admin Access</a>
			<a href="DoAdminLogout.php" class="btn btn-danger" style="float: right;"><i class="icon-remove icon-white"></i> Log Out</a>
			<hr>
			<h1> Activities for <?php echo $date; ?> at <?php echo $locations[$location]; ?> </h1>
			
			<br>
			
			<table class="table table-hover">
			
				<tr>
					<td><strong>User</strong></td>
					<td><strong>Sysid</strong></td>
					<td><strong>Login Time</strong></td>
					<td><strong>Logout Time</strong></td>
					<td><strong>Date</strong></td>
					<td><strong>Hours Earned</strong></td>
					<td><strong>Visit Number</strong></td>
					<td><strong>Status</strong></td>
					<td><strong>Location</strong></td>
				</tr>
			
				<?php
					//find today's date and time, store in database format
					$today = date("n/j/Y");
					$time = date("H:i");
					
					//look up location codes from database, store into an array
					$query="SELECT * FROM locations";
					$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
					
					while ($obj = $result->fetch_object())
					{
						$locations[$obj->code] = $obj->name;
					}
				
					/* 3. display results of this statement for active users - select timelogs.datein, profiles.fname, profiles.lname, timelogs.sysid, timelogs.in24, timelogs.visitnumber, timelogs.out24, timelogs.hrsearned, timelogs.loginspot from profiles, timelogs where timelogs.datein = '$date' and timelogs.sysid = profiles.sysid and timelogs.loginspot = '$location' order by timelogs.visitnumber, profiles.lname, timelogs.in24; */
					$query="select timelogs.datein, profiles.fname, profiles.lname,profiles.status, timelogs.sysid, timelogs.in24, timelogs.visitnumber, timelogs.out24, timelogs.hrsearned, timelogs.loginspot from profiles, timelogs where timelogs.datein = '$date' and timelogs.sysid = profiles.sysid and timelogs.loginspot = '$location' order by timelogs.visitnumber, profiles.lname, timelogs.in24";
					$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
					
					//show if there are no active logins
					 if($count < 1)
					 {
						?> <tr class="error">
										<td>There are no open activites for this date</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr> <?php	
					 }
					 
					$totalhrs = 0;
					
					//now, systematically display all relevant information
					while($obj = $result->fetch_object())
					{
						?> <tr class="success" onclick="location.href='DoDisplayRecord.php?sysid=<?php echo $obj->sysid ?>'" style="cursor:pointer;">
								<td><?php echo $obj->lname ?>, <?php echo $obj->fname ?></td>
								<td><?php echo $obj->sysid ?></td>
								<td><?php echo $obj->in24 ?></td>
								<td><?php echo $obj->out24 ?></td>
								<td><?php echo $obj->datein ?></td>
								<td><?php echo $obj->hrsearned ?></td>
								<td><?php echo $obj->visitnumber ?></td>
								<td><?php echo $obj->status ?><td>
								<td><?php echo $locations[$obj->loginspot] ?></td>
									<?php $totalhrs = $totalhrs + $obj->hrsearned?>
						   </tr> <?php
					}
		
				
					/*4. display results of this statement for inactive users - select repeats.datein, profiles.fname, profiles.lname, profiles.status repeats.sysid, repeats.in24, repeats.visitnumber, repeats.out24, repeats.hrsearned from profiles, repeats where repeats.datein = '$date' and repeats.sysid = profiles.sysid and profiles.site = '$location' order by repeats.visitnumber, profiles.lname, repeats.in24;*/
					$query="select repeats.datein, profiles.site, profiles.fname, profiles.lname, profiles.status, repeats.sysid, repeats.in24, repeats.visitnumber, repeats.out24, repeats.hrsearned from profiles, repeats where repeats.datein = '$date' and repeats.sysid = profiles.sysid and profiles.site = '$location' order by profiles.site, repeats.visitnumber, profiles.lname, repeats.in24";
					$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
					
					//show if there are no completed activities
					 if($count < 1)
					 {
						?> <tr class="error">
										<td>There are no completed activites for this date</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr> <?php	
					 }

					//now, systematically display all relevant information
					while($obj = $result->fetch_object())
					{
						?> <tr class="error" onclick="location.href='DoDisplayRecord.php?sysid=<?php echo $obj->sysid ?>'" style="cursor:pointer;">
								<td><?php echo $obj->lname ?>, <?php echo $obj->fname ?></td>
								<td><?php echo $obj->sysid ?></td>
								<td><?php echo $obj->in24 ?></td>
								<td><?php echo $obj->out24 ?></td>
								<td><?php echo $obj->datein ?></td>
								<td><?php echo $obj->hrsearned ?></td>
								<td><?php echo $obj->visitnumber ?></td>
								<td><?php echo $obj->status ?><td>
								<td><?php echo $locations[$obj->site] ?></td>
									<?php $totalhrs = $totalhrs + $obj->hrsearned?>
							</tr> <?php
					}
				?>
			
			</table>
			
			<p><strong>Total Community Service Hours Worked at  <?php echo $locations[$location]; ?> on <?php echo $date; ?>: <?php echo $totalhrs?></strong></p>

			<p>Report generated at <strong><?php echo $time ?></strong> on <strong><?php echo $today ?></strong></p>
			
			<?php unset($_SESSION['location']); ?>
		
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