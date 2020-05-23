<?php
	/*
		This page will edit the given sysid's hours for a given activityno
		1. make sure that the user actually exists
		2. make sure that the specified activityno exists
		3. update the correct timelog - update timelogs set hrsearned = (hrsearned + $addedtime) where sysid = '$sysid' and activityno = $activityno;
		4. update the correct profile - update profiles set hrsearned = (hrsearned + $addedtime) where sysid = '$sysid';
		5. display info message about added time, activityno, total hours, sysid, and fname/lname. 
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
	
	// store locations into an easily accessable array
	$query="SELECT * FROM $tbl_name";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	while ($obj = $result->fetch_object())
	{
		$locations[$obj->code] = $obj->name;
	}
	
	//get variables from the form
	$sysid = strtolower($_REQUEST["sysid"]);
	$addedtime = $_REQUEST["addedtime"];
	//$activityno = $_REQUEST["activityno"];
	
	if(!is_numeric($addedtime))
	{
		$addedtime = "0";
	}
	
	//make sure that sysid exists
	$query = "select * from profiles where sysid = '$sysid' and status=1";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: EditHoursEarned.php");
		$_SESSION['msgtext'] = "Record Not Found: The sysid does not exist or is inactive.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else if ($result->num_rows > 1)
	{
		header("Location: EditHoursEarned.php");
		$_SESSION['msgtext'] = "Multiple Records Found: More than one record was found for the given sysid. Please contact IT staff.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	//Determine that the user does not currently have an open timelog in the database
	$query="SELECT * FROM timelogs WHERE status = 'open' and sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if ($result->num_rows > 0)
	{
		$_SESSION['msgtext'] = "Transaction Failed: A log-in exists that has not been closed out.  Have the client log out of the system or use the Complete Failed Logout Function to close out open record.";
		$_SESSION['msgtype'] = "error";
		header("Location: EditHoursEarned.php");
		exit;
	}
	

	//get firstname and lastname
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	
	/* if activityno field is blank, use most recent activityno
		if($activityno == "")
		{
	*/	
		$query = "select MAX(activityno) from timelogs where sysid = '$sysid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		$activityno = $result->fetch_row()[0];
		
		$query = "select visitnumber from timelogs where sysid = '$sysid' and activityno = '$activityno'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		$visitnumber = $result->fetch_row()[0];
		
		$query = "select loginspot from timelogs where sysid = '$sysid' and activityno = '$activityno'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		$location = $result->fetch_row()[0];
		
		$currentTime = time();
		$today = date("n/j/Y", $currentTime);
								
		//print("Activityno = ".$activityno."<br>");
		//print("Date = ".$today."<br>");
		//exit;
		
		//print("Repeats ".$result3->num_rows."<br>");
		
		
		
	/*
		}
	 else
	{		
		//make sure that activityno exists
		$query = "select * from timelogs where sysid = '$sysid' and activityno = $activityno";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		
		if($result->num_rows < 1)
		{
			header("Location: EditHoursEarned.php");
			$_SESSION['msgtext'] = "Timelog Not Found: There is no record of the timelog with the given activity number";
			$_SESSION['msgtype'] = "error";
			exit;
		}
	}
	*/
	
	/*3. update the correct timelog - update timelogs set hrsearned = (hrsearned + $addedtime) where sysid = '$sysid' and activityno = $activityno;
	$query = "update timelogs set hrsearned = (hrsearned + $addedtime) where sysid = '$sysid' and activityno = $activityno";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	*/
	
	//3. Go ahead and add a timelog record for corrected hours of credit.
	$query="insert into timelogs (sysid, in24, out24, activityno, visitnumber, hrsearned, datein, loginspot) values ('$sysid', 'Corrected Hrs', 'Administrator', $activityno + 1 , '$visitnumber', '$addedtime', '$today', '$location')";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//4. update the correct profile - update profiles set hrsearned = (hrsearned + $addedtime) where sysid = '$sysid';
	$query = "update profiles set hrsearned = (hrsearned + $addedtime) where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		
	
	//5. display info message about added time, activityno, total hours, sysid, and fname/lname.
	$query = "select hrsearned from profiles where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$obj = $result->fetch_object();
	$hrsearned = $obj->hrsearned;
	
	//get total hours from profiles database
	header("Location: EditHoursEarned.php");
	$_SESSION['msgtext'] = "Time Added Successfully: <strong>$fname $lname</strong> has had <strong>$addedtime</strong> hours added to timelog record. Total hours: <strong>$hrsearned</strong>";
	$_SESSION['msgtype'] = "success";
	exit;
?>
