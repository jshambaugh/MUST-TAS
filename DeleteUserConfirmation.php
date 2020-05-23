<?php
	/*
		1. if no sysid found in database, return to previous page
		2. Display record of sysid mentioned earlier
		3. if delete button pressed, delete user.
		4. if cancel button pressed, don't delete user.
		5. no matter what, prompt the user of the results
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
	
	//get variables from form
	$sysid = strtolower($_REQUEST["sysid"]);
	
	//1. if no sysid found in database, return to previous page
	$query="SELECT * FROM profiles where sysid='$sysid';";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: DeleteUser.php");
		$_SESSION['msgtext'] = "Delete User Error: The sysid entered could not be found in the database";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//2. Display record of sysid mentioned earlier
	
	// store locations into an easily accessable array
	$query="SELECT * FROM $tbl_name";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	while ($obj = $result->fetch_object())
	{
		$locations[$obj->code] = $obj->name;
	}
	
	//now get the right record's profile and timelogs
	$sysid = strtolower($_REQUEST["sysid"]);
	$query = "select * from profiles where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$query2 = "select activityno, visitnumber, datein, in24, out24, hrsearned from timelogs where sysid = '$sysid'";
	$result2=$mysqli->query($query2) or die($mysqli->error.__LINE__);
	$query3 = "select activityno, visitnumber, datein, in24, out24, hrsearned from repeats where sysid = '$sysid'";
	$result3=$mysqli->query($query3) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: DisplayRecord.php");
		$_SESSION['msgtext'] = "Record Not Found: The sysid entered could not be found in the system";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else if ($result->num_rows > 1)
	{
		header("Location: DisplayRecord.php");
		$_SESSION['msgtext'] = "Multiple Records Found: More than one record was found for the given sysid. Please contact IT staff.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else
	{ 
	
		$obj = $result->fetch_object();
		$obj2 = $result2->fetch_object();
		$obj3 = $result3->fetch_object();
		
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
					
					<h1>Permanently Delete User</h1>
					<br>
					<table>
						<tr>
							<td colspan="2">
								<div class="alert alert-danger">Warning: This action is permanent and can not be undone! Are you sure that you want to permanently delete this user?</div>
							</td>
						</tr>
						<tr>
							<td style="width: 50%; text-align: center;">
								<a href="DoDeleteUser.php?sysid=<?php echo $sysid ?>" class="btn btn-large btn-danger">Delete</a>
							</td>
							<td style="width: 50%; text-align: center;">
								<a href="DeleteUser.php" class="btn btn-large btn-info">Cancel</a>
							</td>
						</tr>
					</table>
					
					<hr>
					
					<!-- Tab Labels -->
					<ul id="myTab" class="nav nav-tabs">
						<li class="active">
							<a href="#profile" data-toggle="tab">Profile</a>
						</li>
						<li class>
							<a href="#timelogs" data-toggle="tab">Timelogs</a>
						</li>
					</ul>
					
					<!-- Tab Content -->
					<div id="myTabContent" class="tab-content">
						<div class="tab-pane fade active in" id="profile">
					
							<br>
							
							<h1>Profile: <?php echo $obj->fname ?> <?php echo $obj->lname ?></h1>
							
							<br>
							
							<!--<div class="row">-->
									<br>
									<br>
									<table class="table">
										<tr>
											<td><strong>First Name:</strong></td>
											<td><?php echo $obj->fname ?></td>
										</tr>
										<tr>
											<td><strong>Middle Ininitial:</strong></td>
											<td><?php echo $obj->middle ?></td>
										</tr>
										<tr>
											<td><strong>Last Name:</strong></td>
											<td><?php echo $obj->lname ?></td>
										</tr>
										<tr>
											<td><strong>System ID:</strong></td>
											<td><?php echo $obj->sysid ?></td>
										</tr>
										<tr class="<?php  if($obj->status == "active")
																				echo success;
																			else
																				echo error; ?>">
											<td><strong>Status:</strong></td>
											<td><?php echo $obj->status ?></td>
										</tr>
										<tr>
											<td><strong>Hours Earned:</strong></td>
											<td><?php echo $obj->hrsearned ?></td>
										</tr>
										<tr>
											<td><strong>Location:</strong></td>
											<td><?php if(array_key_exists($obj->site, $locations))
																	echo $locations[$obj->site];
																else
																	echo $obj->site." (Old Format)";
														?></td>
										</tr>
										<tr>
											<td><strong>SSAN:</strong></td>
											<td><?php echo $obj->ssan ?></td>
										</tr>
										<tr>
											<td><strong>Age:</strong></td>
											<td><?php echo $obj->age ?></td>
										</tr>
										<tr>
											<td><strong>Street Address:</strong></td>
											<td><?php echo $obj->street ?></td>
										</tr>
										<tr>
											<td><strong>City:</strong></td>
											<td><?php echo $obj->city ?></td>
										</tr>
										<tr>
											<td><strong>State:</strong></td>
											<td><?php echo $obj->state ?></td>
										</tr>
										<tr>
											<td><strong>Zip Code:</strong></td>
											<td><?php echo $obj->zip ?></td>
										</tr>
										<tr">
											<td><strong>County:</strong></td>
											<td><?php echo $obj->county ?></td>
										</tr>
									</table>
							<!--</div>-->
							
						</div>
						<div class="tab-pane fade" id="timelogs">
							
							<br>
						
							<h1>Timelogs: <?php echo $obj->fname ?> <?php echo $obj->lname ?></h1>
							
							<br>
							
							<table class="table table-striped">
								<tr>
									<td><strong>Activity #</strong></td>
									<td><strong>Visit #</strong></td>
									<td><strong>Date</strong></td>
									<td><strong>Start Time</strong></td>
									<td><strong>End Time</strong></td>
									<td><strong>Hours Earned</strong></td>
								</tr>
								
								<?php
									//find today's date, store in database format
									$date = date("n/j/Y");
									$time = date("H:i");
									
									//show if there are no timelogs
									if($result2->num_rows < 1 && $result3->num_rows < 1)
									{
										?> <tr class="error">
														<td colspan=7>There are currently no timelogs for this user</td>
													</tr> <?php	
									}
									
									//now, systematically display all relevant information
									while($obj = $result2->fetch_object())
									{
										?> <tr class="success">
												<td><?php echo $obj->activityno ?></td>
												<td><?php echo $obj->visitnumber ?></td>
												<td><?php echo $obj->datein ?></td>
												<td><?php echo $obj->in24 ?></td>
												<td><?php echo $obj->out24 ?></td>
												<td><?php echo $obj->hrsearned ?></td>
											 </tr> <?php
									}
									while($obj = $result3->fetch_object())
									{
										?> <tr class="error">
												<td><?php echo $obj->activityno ?></td>
												<td><?php echo $obj->visitnumber ?></td>
												<td><?php echo $obj->datein ?></td>
												<td><?php echo $obj->in24 ?></td>
												<td><?php echo $obj->out24 ?></td>
												<td><?php echo $obj->hrsearned ?></td>
											 </tr> <?php
									}
								?>
							
							</table>
						
						</div>
					</div>
					
					<p>Report generated at <strong><?php echo $time ?></strong> on <strong><?php echo $date ?></strong></p>
					
					<hr>

					<div class="footer">
						<p>&copy; MUST Ministries 2018</p>
					</div>

				</div> <!-- /container -->

				<!-- Le javascript
				================================================== -->
				<!-- Placed at the end of the document so the pages load faster -->
				<script src="./assets/js/jquery.min.js"></script>
				<script src="./assets/js/bootstrap.min.js"></script>
				<script type="text/javascript">
					//activate tabs
					$('#myTab a').click(function (e) {
						$(this).tab('show');
					})
				</script>
			</body>
		</html>
		
	<?php } ?>
