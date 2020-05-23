<?php
/*
	This document contains code to store the initial registration information into the must database.
	1. Check to make sure that first name, last name, and sysid are all valid
	2. Make sure that sysid does not already exist in the system
	3. The following does not seem necessary, and will not be re-coded in the updated system
		- get number of records from database, store next record number as recno
		- if no records found, nrecs = 0, otherwise nrecs = 1 (not sure if this is needed)
		-if no records found, insert into profiles (fname, lname, sysid) values ('xx', 'xx', 'xx')"
		- notify that there were initially no records in the profiles table, and to re-enter data
		- if there is only one other record, and it is the inital record, delete it
	4. store information into the database = insert into profiles (visitnumber, recno, sysid, fname, lname, middle, site, status, initialdate, orientationdate, orientationtime, ssan, age, street, city, state, zip, country, hrsearned)
	5. Go ahead and add a timelog for 1 hour of orientation credit.
		- insert into timelogs (sysid, in24, out24, activityno, visitnumber, hrsearned, datein)
		- update profiles set hrsearned = (hrsearned + 1) where sysid = '$sysid'
	6. Create a success message
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
	
	//set up connection to the database
	
	include_once "C:\TASDocs\LoginCreds.inc";

	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	//get all the form data and store it as local variables
	$fname = trim($_REQUEST['fname']);
	$lname = trim($_REQUEST['lname']);
	$minitial = strtoupper(trim($_REQUEST['minitial']));
	$rdate = $_REQUEST['rdate'];
	$odate = $_REQUEST['odate'];
	$otime = $_REQUEST['otime'];
	$sysid = strtolower($_REQUEST['sysid']);
	$location = $_REQUEST['location'];
	$ssan = $_REQUEST['social'];
	$age = $_REQUEST['age'];
	$street = $_REQUEST['street'];
	$city = $_REQUEST['city'];
	$state = $_REQUEST['state'];
	$zip = $_REQUEST['zip'];
	$county = $_REQUEST['county'];
	
	if(strlen($rdate)<8)
	{
		header("Location: RegisterNewUser.php");
		$_SESSION['msgtext'] = "Error: The  registration date was not submitted in the correct format";
		$_SESSION['msgtype'] = "error";
		exit;
	}

	if(strlen($odate)<8)
	{
		header("Location: RegisterNewUser.php");
		$_SESSION['msgtext'] = "Error: The orientation date was not submitted in the correct format";
		$_SESSION['msgtype'] = "error";
		exit;
	}

	
	//1. Check to make sure that first name, last name, age and sysid are all valid
	if($fname == "" || $lname == "" || $sysid == "")
	{
		$_SESSION['msgtext'] = "Registration Error: User <strong>must</strong> have a valid first name, last name, and sysid";
		$_SESSION['msgtype'] = "error";
		header("Location: RegisterNewUser.php");
		exit;
	}
	if(!is_numeric($age))
	{
	/*	$_SESSION['msgtext'] = "Registration Error: Age must be numeric";
		$_SESSION['msgtype'] = "error";
		header("Location: RegisterNewUser.php");
		exit;*/
		$age = 0;
	}
	
	//2. Make sure that sysid does not already exist in the system
	$query="SELECT * FROM profiles where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows > 0)
	{
		$_SESSION['msgtext'] = "Registration Error: The sysid <strong>$sysid</strong> is already in the system. Please try a different system id.";
		$_SESSION['msgtype'] = "error";
		header("Location: RegisterNewUser.php");
		exit;
	}
	
	//4. store information into the database = insert into profiles (visitnumber, recno, sysid, fname, lname, middle, site, status, initialdate, orientationdate, orientationtime, ssan, age, street, city, state, zip, county, hrsearned)
	$query="insert into profiles (visitnumber, recno, sysid, fname, lname, middle, site, status, initialdate, orientationdate, orientationtime, ssan, age, street, city, state, zip, county, hrsearned) values (1, 1, '$sysid', '$fname', '$lname', '$minitial', '$location', 'active', '$rdate', '$odate', '$otime', '$ssan', $age, '$street', '$city', '$state', '$zip', '$county', 1.00)";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//5. Go ahead and add a timelog for 1 hour of orientation credit.
	$query="insert into timelogs (sysid, in24, out24, activityno, visitnumber, hrsearned, datein, loginspot) values ('$sysid', 'Registration', 'Orientation', 1, 1, 1.00, '$odate', '$location')";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//6. Create a success message
	$_SESSION['msgtext'] = "Registration Success: <strong>$fname $minitial $lname</strong> was registered under the sysid <strong>$sysid</strong>. Please note that the system has already credited <strong>$fname</strong> with 1 hour for orientation.";
	$_SESSION['msgtype'] = "success";
	header("Location: RegisterNewUser.php");
	exit;
?>