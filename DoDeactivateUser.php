<?php
	/*
		This page will deactivate an active user.
		1. make sure that the user actually exists and is currently active
		2. deactivate the user
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
	
		
	//make sure that sysid exists and is active
	$query = "select * from profiles where sysid = '$sysid' and status = 1";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: DeactivateUser.php");
		$_SESSION['msgtext'] = "Record Not Found: The sysid entered either could not be found in the system or is not active.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else if ($result->num_rows > 1)
	{
		header("Location: DeactivateUser.php");
		$_SESSION['msgtext'] = "Multiple Records Found: More than one record was found for the given sysid. Please contact IT staff.";
		$_SESSION['msgtype'] = "error";
		exit;
	}

	//get firstname and lastname
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	
	//find date of last timelog
	$query="SELECT max(activityno) FROM timelogs WHERE sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$activityno = $result->fetch_row()[0];
	
	$query="SELECT visitnumber FROM timelogs WHERE activityno='$activityno' AND sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$visitnumber = $result->fetch_row()[0];
	
	//make sure that the user is not logged in
	$query="SELECT status FROM timelogs WHERE activityno='$activityno' AND sysid='$sysid' AND status = 2";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result->num_rows < 1)
	{
		header("Location: DeactivateUser.php");
		$_SESSION['msgtext'] = "Unable to deactivate: The sysid entered is currently logged in.";
		$_SESSION['msgtype'] = "error";
		exit;
	}

		//find today's date, store in database format
		$currentdate = date("n/j/Y");
		
		$newactivity = $activityno + 1; 

	// the following print functions are for debugging only.
	/*	printf("$currentdate<br>");
		printf("$activityno<br>");
		printf("$newactivity<br>");
		printf("$visitnumber<br>");
		exit();*/

	// create a entry in timelogs for the completion letter - insert into timelogs (sysid, in24, out24, activityno, datein, status, hrsearned, visitnumber) values ('$sysid', 'User', 'Deactivated', ($activityno + 1), '$date', 'closed', 0, $visitnumber);
		$query="insert into timelogs (sysid, in24, out24, activityno, datein, status, hrsearned, visitnumber) values ('$sysid', 'User', 'Deactivated', ($activityno + 1), '$currentdate', 'closed', 0, $visitnumber)";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	// copy these timelogs into the repeats table - insert into repeats (datein, sysid, in24, out24, activityno, visitnumber, hrsearned) values ('$datein', '$sysid', '$in24', '$out24', '$activityno', '$visitnumber', '$hoursearned');
		$query="insert into repeats (datein, sysid, in24, out24, activityno, visitnumber, hrsearned) select datein, sysid, in24, out24, activityno, visitnumber, hrsearned from timelogs where sysid='$sysid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
	// delete the timelogs from the timelogs database - delete from timelogs where sysid = '$sysid';
		$query="delete from timelogs where sysid = '$sysid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
	// increment the visit number in the profiles database - update profiles set visitnumber = ($visitnumber + 1) where sysid = '$sysid';
		$query="update profiles set visitnumber = ($visitnumber + 1) where sysid = '$sysid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
	// set hrsearned back to 0 - update profiles set hrsearned = 0 where sysid = '$sysid';
		$query="update profiles set hrsearned = 0 where sysid = '$sysid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
	// make the user inactive
		$query="update profiles set status = 2 where sysid = '$sysid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	


	header("Location: DeactivateUser.php");
	$_SESSION['msgtext'] = "User Deactivated: <strong>$fname $lname</strong> has been successfully deactivated.";
	$_SESSION['msgtype'] = "success";
	

?>