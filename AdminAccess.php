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
			<!-- Tab Labels -->
			<ul id="myTab" class="nav nav-tabs">
				<li class="active">
					<a href="#registration" data-toggle="tab">Registration</a>
				</li>
				<li class>
					<a href="#viewsystem" data-toggle="tab">View System</a>
				</li>
				<li class>
					<a href="#viewuser" data-toggle="tab">View User</a>
				</li>
				<li class>
					<a href="#edit" data-toggle="tab">Edit</a>
				</li>
				<li class>
					<a href="#administratorsettings" data-toggle="tab">Administrator Settings</a>
				</li>
				<a href="DoAdminLogout.php" class="btn btn-danger" style="float: right;"><i class="icon-remove icon-white"></i> Log Out</a>
			</ul>
			
			<!-- Tab Content -->
			<div id="myTabContent" class="tab-content">
				<div class="tab-pane fade active in" id="registration">
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="RegisterNewUser.php" class="btn btn-large btn-primary">Register New User</a>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="viewsystem">
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="ActiveLogins.php" class="btn btn-large btn-primary">Active Logins</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="CompletedActivities.php" class="btn btn-large btn-primary">Completed Activities</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="CompletedActivitiesByDay.php" class="btn btn-large btn-primary">Activities for a Selected Day</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="PickLocation.php" class="btn btn-large btn-primary">Activities for a Location on a Selected Day</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="ShowPasswords.php" class="btn btn-large btn-primary">Show Passwords</a>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="viewuser">
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="CompletionLetter.php" class="btn btn-large btn-primary">Completion Letter</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="DisplayRecord.php" class="btn btn-large btn-primary">Display Record</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="LookUpByName.php" class="btn btn-large btn-primary">Look Up User By Name</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="EditCompletionLetter.php" class="btn btn-large btn-warning">Edit/View Completion Letter</a>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="edit">
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="EditHoursEarned.php" class="btn btn-large btn-primary">Edit Hours Earned</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="CompleteFailedLogout.php" class="btn btn-large btn-primary">Complete Failed Logout</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="ReactivateUser.php" class="btn btn-large btn-success">Reactivate User</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="DeactivateUser.php" class="btn btn-large btn-warning">Deactivate User</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="DeleteUser.php" class="btn btn-large btn-danger">Permanently Delete User</a>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="administratorsettings">
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="ChangeAdminPassword.php" class="btn btn-large btn-warning">Change Administrator Password</a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="span10" style="text-align: center;">
							<a href="PurgeDatabaseRecords.php" class="btn btn-large btn-danger">Purge Database Records</a>
						</div>
					</div>
				</div>
			</div>
			
			
			
			<!-- add main page buttons and such here-->
				
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
		<script type="text/javascript">
			//automatically show modal
			$(window).load(function(){
				$('#myModal').modal('show');
				
				//automatically show the last tab viewed
				var lastTab = localStorage.getItem('lastTab');
				if(lastTab) {
					$('a[href=' + lastTab + ']').tab('show');
				}
			});
					
			//activate tabs
			$('#myTab a').click(function (e) {
				$(this).tab('show');
				localStorage.setItem('lastTab', $(e.target).attr('href'));
			})
		</script>

  </body>
</html>
