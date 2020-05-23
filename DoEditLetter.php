<?php
	/*
		This page will save the letter format
		1. check the letter for misformatted variables
		2. save the letter to the database
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
	
	include_once "C:\TASDocs\LoginCreds.inc";

	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	//get variables from the form
	$contents = $_REQUEST["letter"];
	$county = $_SESSION[county];
	
	//TODO: check to make sure all of the variables are valid.
	for ($i=0; $i<strlen($contents); $i++)
	{
		//find an opening bracket
		if(substr($contents, $i, 1) == "[")
		{
			//get variable name
			$variablename = explode("]",substr($contents, $i + 1), 2)[0];
			
			//make sure variable name matches a real variable name
			if($variablename == "fname" || $variablename == "lname" || $variablename == "letterdate" || $variablename == "completiondate" || $variablename == "hours")
			{
				//all is well, continue
			}
			else
			{
				//don't save the results and prompt the reason for the mistake
				header("Location: EditLetter.php");
				$_SESSION['msgtext'] = "Variable Mistake: The variable <strong>$variablename</strong> is not a valid variable. The completion letter could not be saved.";
				$_SESSION['msgtype'] = "error";
	exit;
			}
		}
	}
	
	//save the contents to the database
	$query = "update letters set contents='$contents' where name = '$county'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//prompt the results
	header("Location: AdminAccess.php");
	$_SESSION['msgtext'] = "Letter Saved: The completion letter format has been saved";
	$_SESSION['msgtype'] = "success";
	exit;
?>
