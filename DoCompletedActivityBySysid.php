<?php
	/*
		This page will generate a completion letter for the entered sysid, and then remove that user from the active database
		1. make sure that the given sysid exists in the system
		2. find date of last timelog
		3. tokenize the completion letter template. wherever there is a $ sign, look for a keyword and replace it with the corresponding value. Here are the keywords:
			- letterdate
			- fname
			-	lname
			- minitial
			- hoursearned
			- completiondate
		4. create a entry in timelogs for the completion letter - insert into timelogs (sysid, in24, out24, activityno, datein, hrsearned, visitnumber) values ('$sysid', 'Completion', 'Letter Given', ($activityno + 1), '$date', 0, $visitnumber);
		5. write out a table of all activities for the given sysid - select activityno, datein, in24, out24, hrsearned, visitnumber from timelogs where sysid = '$sysid' order by activtyno;
		6. copy these timelogs into the repeats table - insert into repeats (datein, sysid, in24, out24, activityno, visitnumber, hrsearned) values ('$datein', '$sysid', '$in24', '$out24', '$activityno', '$visitnumber', '$hoursearned');
		7. delete the timelogs from the timelogs database - delete from timelogs where sysid = '$sysid';
		8. increment the visit number in the profiles database - update profiles set visitnumber = ($visitnumber + 1) where sysid = '$sysid';
		9. set hrsearned back to 0 - update profiles set hrsearned = 0 where sysid = '$sysid';
		NOTE: might have to look more into what's going on here. repeats seems to have all old timelogs from users that have already finished their community service, but i don't see any mention of 'deactivating' these profiles. 
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
?>