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
	
	//now, log out
	$_SESSION['admin'] = false;
	
	$_SESSION['msgtext'] = "Logout Success: You have logged out as an admin";
	$_SESSION['msgtype'] = "info";
	header("Location: AdminAccessLogin.php");
	exit;
?>