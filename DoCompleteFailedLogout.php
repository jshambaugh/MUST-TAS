<?php
	/*
		This page will edit the given sysid's logout time for the most recent (should be only) failed log-out
		1. make sure that the user actually exists
		2. make sure that the most recent activity log is still active
		3. make sure that there are no more than 1 active logins
		4. make sure that the time entered is after the log-in time
		5. log the user out in the database with the correct time
		6. prompt the results
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

	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	//get variables from the form
	$sysid = strtolower($_REQUEST["sysid"]);
	$logouttime = $_REQUEST["logouttime"];
	
	//make sure that sysid exists
	$query = "select * from profiles where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: CompleteFailedLogout.php");
		$_SESSION['msgtext'] = "Record Not Found: The sysid entered could not be found in the system";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else if ($result->num_rows > 1)
	{
		header("Location: CompleteFailedLogout.php");
		$_SESSION['msgtext'] = "Multiple Records Found: More than one record was found for the given sysid. Please contact IT staff.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//get firstname and lastname
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	
	//make sure that there is one and only one open timelog
	$query = "select * from timelogs where sysid = '$sysid' and status='open'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: CompleteFailedLogout.php");
		$_SESSION['msgtext'] = "Open Timelog Not Found: There are no open timelogs for the given sysid";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else if ($result->num_rows > 1)
	{
		header("Location: CompleteFailedLogout.php");
		$_SESSION['msgtext'] = "Multiple Open Timelogs: More than one open timelog was found for the given sysid. Please contact IT staff.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//make sure that the most recent timelog is still open
	$query = "select MAX(activityno) from timelogs where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$activityno = $result->fetch_row()[0];
	
	$query = "select * from timelogs where sysid = '$sysid' and activityno = $activityno";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$obj = $result->fetch_object();
	
	if($obj->status == "closed")
	{
		header("Location: CompleteFailedLogout.php");
		$_SESSION['msgtext'] = "Open Timelog Unresolved In The Past: There is an open timelog, but it is not the most recent timelog. Please contact IT staff";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//make sure the closing time is after the opening time
	$query = "select in24 from timelogs where sysid = '$sysid' and status = 'open'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$in24 = $result->fetch_row()[0];
	
	$splitin24 = explode(":", $in24);
	$splitout24 = explode(":", $logouttime);
	
	if($splitout24[0] > $splitin24[0])
	{
		//all is well, do nothing
	}
	else if($splitout24[0] == $splitin24[0] && $splitout24[1] > $splitin24[1])
	{
		//all is well, do nothing
	}
	else
	{
		header("Location: CompleteFailedLogout.php");
		$_SESSION['msgtext'] = "Invalid Time: The time entered is before or equal to the starting time of the login";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//log the user out in the database with the correct time
	$query="select hrsearned FROM profiles WHERE sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$previoushours = $result->fetch_row()[0];
	
	$outhr = $splitout24[0];
	$outmin = $splitout24[1];
	$outseconds = ($outhr * 60 * 60) + ($outmin * 60);
	$out24 = $logouttime . ":00";
	
	$query="select inseconds FROM timelogs WHERE sysid='$sysid' and activityno='$activityno'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$row = $result->fetch_row();
	$newhrsearned = ($outseconds - $row[0])/ 3600;

	//round to 2 decimal places	
	$newhrsearned = round($newhrsearned, 2);
	
	$totalhrs = $previoushours + $newhrsearned;
	
	//update timelog data for out24, outhr, outmin, outseconds, hrsearned, status.
	$query="UPDATE timelogs SET out24='$out24', outhr=$outhr, outmin=$outmin, outseconds=$outseconds, hrsearned=$newhrsearned, status='closed' WHERE sysid='$sysid' and status = 'open'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//update profiles data with new hoursearned
	$query="UPDATE profiles SET hrsearned='$totalhrs' where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//display "fname lname has logged out at timestamp. x hours earned in this session for a total of y hours"
	$query="select * from profiles where sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	
	$_SESSION['msgtext'] = "Logout Success: <strong>$fname $lname</strong> has logged out at <strong>" . $outhr . ":" . $outmin . "</strong>. <strong>$newhrsearned</strong> hours were earned in this session for a total of <strong>$totalhrs</strong> hours.";
	$_SESSION['msgtype'] = "success";
	header("Location: CompleteFailedLogout.php");
	exit;
?>
