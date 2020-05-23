<?php
	/*
		This page will reactivate a deactivated user.
		1. make sure that the user actually exists and is currently inactive
		2. reactivate the user
		3. prompt the results
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
	
	//make sure that sysid exists and is inactive
	$query = "select * from profiles where sysid = '$sysid' and status = 2";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: ReactivateUser.php");
		$_SESSION['msgtext'] = "Record Not Found: The sysid entered either could not be found in the system or is already active.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else if ($result->num_rows > 1)
	{
		header("Location: ReactivateUser.php");
		$_SESSION['msgtext'] = "Multiple Records Found: More than one record was found for the given sysid. Please contact IT staff.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//get firstname and lastname
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	$currentdate = date("n/j/Y", time());
	$visitnumber = $obj->visitnumber;
	
	//get stuff from activity number
	$query="SELECT max(activityno) FROM repeats WHERE sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);	
	$activityno = $result->fetch_row()[0] + 1;
	
	//get location from profiles
	$query="SELECT site FROM profiles WHERE sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);	
	$location = $result->fetch_row()[0];
	
	//reactivate user
	$query = "update profiles set status = 1 where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//Debug only
	//echo "Location = '$location'<br>";
	//echo "Activity Number = '$activityno'";
	//exit;
		
	//create empty timelog that records reactivation
	$query="insert into timelogs (sysid, in24, out24, activityno, datein, hrsearned, visitnumber, loginspot) values ('$sysid', 'User', 'Reactivated', $activityno, '$currentdate', 0, $visitnumber, '$location')";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	header("Location: ReactivateUser.php");
	$_SESSION['msgtext'] = "User Reactivated: <strong>$fname $lname</strong> has been successfully reactivated.";
	$_SESSION['msgtype'] = "success";
	exit;
?>
