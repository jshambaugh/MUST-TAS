

<?php
	/* This code handles all administrator logins to the system

	Tasks for logging in ->
	1. Determine that the entered administrator password matches the database
	2. redirect to admin access page
	*/
	
	session_start();

	//if directly accessed, redirect
	if(!isset($_REQUEST['password']))
	{
		header("Location : AdminAccessLogin.php");
		exit;
	}
	include_once "C:\TASDocs\LoginCreds.inc";

	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("SQL Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	//store password from the form
	$pword=$_REQUEST['password'];
	
	//determine that the sysid exists in the profiles database
	$query="SELECT password FROM admin";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	$dbpword = $result->fetch_row()[0];
	
	if($dbpword == $pword)
	{
		$_SESSION['admin'] = true;
		header("Location: AdminAccess.php");
		exit;
	}
	else
	{
		$_SESSION['msgtext'] = "Log-in Failed: The administrator password is incorrect";
		$_SESSION['msgtype'] = "error";
		header("Location: AdminAccessLogin.php");
		exit;
	}
?>
