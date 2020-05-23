<?php
	/*
		This page will update the administrator password
		1. make sure that the old password is correct
		2. make sure that the new password matches the confirmation password
		3. store the new password in the database
		4. log the administrator out
		5. prompt the successful password change
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

	//get variables
	$oldpw = $_REQUEST['oldpw'];
	$newpw = $_REQUEST['newpw'];
	$confirmpw = $_REQUEST['confirmpw'];
	
	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	//1. make sure that the old password is correct
	$query="SELECT password FROM admin";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	$dbpword = $result->fetch_row()[0];
	
	if($dbpword != $oldpw)
	{
		$_SESSION['msgtext'] = "Change Administrator Password Failed: The old administrator password is incorrect";
		$_SESSION['msgtype'] = "error";
		header("Location: ChangeAdminPassword.php");
		exit;
	}
	
	//2. make sure that the new password matches the confirmation password
	if($newpw != $confirmpw)
	{
		$_SESSION['msgtext'] = "Change Administrator Password Failed: The new password does not match the confirmation password";
		$_SESSION['msgtype'] = "error";
		header("Location: ChangeAdminPassword.php");
		exit;
	}
	
	//3. store the new password in the database
	$query="update admin set password = '$newpw';";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//4. log the administrator out
	$_SESSION['admin'] = false;
	
	//5. prompt the successful password change
	$_SESSION['msgtext'] = "Change Administrator Password Successful: You have successfully changed the administrator password. Please log in with the new password to continue";
	$_SESSION['msgtype'] = "info";
	header("Location: AdminAccessLogin.php");
	exit;
?>