<?php
/* This code handles all community service logins/logouts for the TAS system

Tasks for logging in ->
1. determine that the sysid exists in the profiles database and is active
2. determine that the user does not currently have an open timelog in the database
3. 
	- count the number of timelogs in the given sysid
		- if greater than 0,
			- use that to get most recent closed timelog activityno
			- add 1 to it, store as activityno
		- if 0,
			- get most recent repeat activityno
	- get a whole bunch of current time and date information
	- culminate with: insert into timelogs (activityno, sysid, datein, in24, inhr, inmin, timestampin, inseconds, status, loginspot, visitnumber)
			values ($activityno, $sysid, datein, in24, target1, target2, timestampin, inseconds, 'open', $locationcode, $visitnumber)
	- display that firstname, lastname has logged in at timestamp
	-display, you currently have a total of x hours

	
Tasks for logging out ->
1. determine that the sysid exists in the profiles database and is active
2. determine that the user currently has an open timelog in the database
3. get some information
	- login date
	- current date
	- activitynumber
4. make sure that the logout request is for the current day - if not, show error message (contact supervisor)
5. get more information
	- hrsearned from profiles
	- current hour
	- current minute
	- total seconds in day
	- current time (H:i:s)
	- hoursearned from current timelog
6. update timelog data for out24, outhr, outmin, outseconds, hrsearned, status.
7. update profiles data with new hoursearned
8. display "fname lname has logged out at timestamp. x hours earned in this session for a total of y hours"
*/

session_start();

//if directly accessed, redirect
if(!isset($_SESSION['location']) || !isset($_REQUEST['action']))
{
	header("Location : PickLoginSite.php");
	exit;
}

	include_once "C:\TASDocs\LoginCreds.inc";
	$tbl_name = "timelogs";

//store the current time at page load so all sql fields remain constant
$currentTime = time();

if($_REQUEST['action'] == "signin")
{
	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("SQL Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	//store id and location from the form
	$uid=strtolower($_REQUEST['id']);
	/*
	print('$uid = '.$uid);
	exit;
	*/
	$location=$_SESSION['location'];
	
	//protect against SQL injection
	$uid=stripslashes($uid);
	$uid=$mysqli->real_escape_string($uid);
	
	//1. determine that the sysid exists in the profiles database
	$query="SELECT * FROM profiles WHERE sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if ($result->num_rows == 0)
	{
		$_SESSION['msgtext'] = "Log-in Failed: The requested sysid could not be found in the database";
		$_SESSION['msgtype'] = "error";
		header("Location: LogInSheet.php");
		exit;
	}
	
	//make sure user is active
	if ($result->fetch_object()->status != 'active')
	{
		$_SESSION['msgtext'] = "Log-in Failed: The requested sysid is not currently active. Please contact an administrator to get reactivated";
		$_SESSION['msgtype'] = "warning";
		header("Location: LogInSheet.php");
		exit;
	}
	
	//2. determine that the user does not currently have an open timelog in the database
	$query="SELECT * FROM $tbl_name WHERE status = 'open' and sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if ($result->num_rows > 0)
	{
		$_SESSION['msgtext'] = "Log-in Failed: You have a log-in that has not been closed out";
		$_SESSION['msgtype'] = "error";
		header("Location: LogInSheet.php");
		exit;
	}
	
	//3. count the number of timelogs with the given sysid
	$query="SELECT count(sysid) FROM timelogs WHERE sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$count = $result->fetch_row()[0];
	
	if ($count > 0)
	{
		//store number of records for use in success message (later in page)
		$nrecs = $count;
	
		//determine the next activityno to be entered into the database
		$query="SELECT max(activityno) FROM timelogs WHERE status = 'closed' and sysid='$uid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);	
		$activityno = $result->fetch_row()[0] + 1;
	}
	else
	{
		//determine the next activityno to be entered into the database (from repeats)
		$query="SELECT max(activityno) FROM repeats WHERE sysid='$uid'";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);	
		$activityno = $result->fetch_row()[0] + 1;
	}
	
	//get current date in sql format
	$datein = date("n/j/Y", $currentTime);
	
	//get hours earned and visitnumber information
	$query="SELECT hrsearned, visitnumber FROM profiles WHERE sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);	
	$obj = $result->fetch_object();
	$hrsearned = $obj->hrsearned;
	$visitnumber = $obj->visitnumber;
	
	//get current hour
	$inhr = date("H", $currentTime);
	
	//get current minute
	$inmin = date("i", $currentTime);
	
	//get total seconds in day so far
	$inseconds = $currentTime - strtotime('today');
	
	//get time in ordinary format
	$in24 = date("H:i:s", $currentTime);
	
	//get current timestamp
	$timestampin = date("n/j/Y H:i:s", $currentTime);
	
	//get location from previous page
	$dalocation = $_SESSION['location'];
	
	//now, insert all of this data into the database
	$query="insert into timelogs (activityno, sysid, datein, in24, inhr, inmin, timestampin, inseconds, status, loginspot, visitnumber) values ($activityno, '$uid', '$datein', '$in24', $inhr, $inmin, '$timestampin', $inseconds, 'open', '$dalocation', $visitnumber)";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);	
	
	//get information for success message
	$query="select * from profiles, timelogs where timelogs.sysid = '$uid' and timelogs.status = 'open' and profiles.sysid = timelogs.sysid";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	
	$totalhours = 0;
	$query="select sysid, status, hrsearned from timelogs where sysid = '$uid' and status = 'closed'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		
		while ($obj = $result->fetch_object()) 
		{									
			$totalhours = $totalhours + $obj->hrsearned;
		
		}
	$query1="update profiles set hrsearned = $totalhours where sysid = '$uid'";
	$result1=$mysqli->query($query1) or die($mysqli->error.__LINE__);
	
	//if successful, include info message and redirect to 
	$_SESSION['msgtext'] = "Log-in Success: <strong>$fname $lname</strong> logged in at <strong>" . $inhr . ":" . $inmin . "</strong> with <strong>$totalhours hours</strong>";
	$_SESSION['msgtype'] = "success";
	header("Location: LogInSheet.php");
	exit;
}
else if($_REQUEST['action'] == "signout")
{
	//connect to server and select database
	$mysqli = new mysqli("$host", "$username", "$password","$db_name");
	if (mysqli_connect_errno())
	{
		printf("SQL Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	//store id and location from the form
	$uid=strtolower($_REQUEST['id']);
	$location=$_SESSION['location'];
	
	//protect against SQL injection
	$uid=stripslashes($uid);
	$uid=$mysqli->real_escape_string($uid);
	
	//1. determine that the sysid exists in the profiles database
	$query="SELECT * FROM profiles WHERE sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if ($result->num_rows == 0)
	{
		$_SESSION['msgtext'] = "Log-out failed: The requested sysid could not be found in the database";
		$_SESSION['msgtype'] = "error";
		header("Location: LogInSheet.php");
		exit;
	}
	
	//make sure user is active
	if ($result->fetch_object()->status != 'active')
	{
		$_SESSION['msgtext'] = "Log-in Failed: The requested sysid is not currently active. Please contact an administrator to get reactivated";
		$_SESSION['msgtype'] = "warning";
		header("Location: LogInSheet.php");
		exit;
	}
	
	//2. determine that the user currently has one (and only one) open timelog in the database
	$query="SELECT * FROM $tbl_name WHERE status='open' and sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if ($result->num_rows == 0)
	{
		$_SESSION['msgtext'] = "Log-out Failed: You have not logged in yet today";
		$_SESSION['msgtype'] = "error";
		header("Location: LogInSheet.php");
		exit;
	}
	else if($result->num_rows > 1)
	{
		$_SESSION['msgtext'] = "Log-out Failed: You have multiple open log-ins. Please contact a supervisor";
		$_SESSION['msgtype'] = "error";
		header("Location: LogInSheet.php");
		exit;
	}
	
	//3. get some information (login date, current date, activity number)
	$query="select activityno, datein FROM timelogs WHERE sysid='$uid' and status='open'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$row = $result->fetch_row();
	$activityno = $row[0];
	$logdate = $row[1];
	$todaysdate = date("n/j/Y", $currentTime);
	
	//4. make sure that the logout request is for the current day - if not, show error message (contact supervisor)
	if ($todaysdate != $logdate)
	{
		$_SESSION['msgtext'] = "Log-out Failed: You have an open log-in from $logdate. Please contact a supervisor";
		$_SESSION['msgtype'] = "error";
		header("Location: LogInSheet.php");
		exit;
	}
	
	/*6. get more information
		- hrsearned from profiles
		- current hour
		- current minute
		- total seconds in day
		- current time (H:i:s)
		- hoursearned from current timelog
		- totalhours with timelog too*/
		
	$query="select hrsearned FROM profiles WHERE sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$previoushours = $result->fetch_row()[0];
	
	$outhr = date("H", $currentTime);
	$outmin = date("i", $currentTime);
	$outseconds = $currentTime - strtotime('today');
	$out24 = date("H:i:s", $currentTime);
	
	$query="select inseconds FROM timelogs WHERE sysid='$uid' and activityno='$activityno'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$row = $result->fetch_row();
	$newhrsearned = ($outseconds - $row[0])/ 3600;

	//round to 2 decimal places	
	$newhrsearned = round($newhrsearned, 2);
	
	$totalhrs = $previoushours + $newhrsearned;
	
	//update timelog data for out24, outhr, outmin, outseconds, hrsearned, status.
	$query="UPDATE timelogs SET out24='$out24', outhr=$outhr, outmin=$outmin, outseconds=$outseconds, hrsearned=$newhrsearned, status='closed' WHERE sysid='$uid' and status = 'open'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//8. update profiles data with new hoursearned
	$query="UPDATE profiles SET hrsearned='$totalhrs' where sysid = '$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//9. display "fname lname has logged out at timestamp. x hours earned in this session for a total of y hours"
	$query="select * from profiles where sysid='$uid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$obj = $result->fetch_object();
	$fname = $obj->fname;
	$lname = $obj->lname;
	
	$_SESSION['msgtext'] = "Logout Success: <strong>$fname $lname</strong> has logged out at <strong>" . $outhr . ":" . $outmin . "</strong>. <strong>$newhrsearned</strong> hours were earned in this session for a total of <strong>$totalhrs</strong> hours.";
	$_SESSION['msgtype'] = "success";
	header("Location: LogInSheet.php");
	exit;
}
else
{
	header("Location: PickLoginSite.php");
	exit;
}
?>