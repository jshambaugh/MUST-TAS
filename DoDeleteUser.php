<?php
	/*
		This page will delete a user.
		1. make sure that the user actually exists
		2. delete the user
		3. delete all timelogs associated with user
		4. prompt the results
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
	
	//make sure that sysid exists
	$query = "select * from profiles where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: DeleteUser.php");
		$_SESSION['msgtext'] = "Record Not Found: The sysid entered could not be found in the system";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	//get firstname and lastname
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	
	//delete user and all timelogs associated with the user
	$query = "delete from profiles where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	$query = "delete from timelogs where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	$query = "delete from repeats where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	header("Location: DeleteUser.php");
	$_SESSION['msgtext'] = "User Deleted: <strong>$fname $lname</strong> has been successfully deleted from the database. This action is permanent and cannot be undone.";
	$_SESSION['msgtype'] = "info";
	exit;
?>
