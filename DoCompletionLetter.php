<?php
	/*
		This page will generate a completion letter for the entered sysid, and then remove that user from the active database
		1. make sure that the given sysid exists in the system
		2. Insure that the profiles hrsearned field is correct.
		3. find date of last timelog
		4. tokenize the completion letter template. wherever there are brackets, look for a keyword and replace it with the corresponding value. Here are the keywords:
			- letterdate
			- fname
			-	lname
			- hoursearned
			- completiondate
		5. create a entry in timelogs for the completion letter - insert into timelogs (sysid, in24, out24, activityno, datein, hrsearned, visitnumber) values ('$sysid', 'Completion', 'Letter Given', ($activityno + 1), '$date', 0, $visitnumber);
		write out a table of all activities for the given sysid - select activityno, datein, in24, out24, hrsearned, visitnumber from timelogs where sysid = '$sysid' order by activtyno;
		6. copy these timelogs into the repeats table - insert into repeats (datein, sysid, in24, out24, activityno, visitnumber, hrsearned) values ('$datein', '$sysid', '$in24', '$out24', '$activityno', '$visitnumber', '$hoursearned');
		7. delete the timelogs from the timelogs database - delete from timelogs where sysid = '$sysid';
		8. increment the visit number in the profiles database - update profiles set visitnumber = ($visitnumber + 1) where sysid = '$sysid';
		9. set hrsearned back to 0 - update profiles set hrsearned = 0 where sysid = '$sysid';
		NOTE: might have to look more into what's going on here. repeats seems to have all old timelogs from users that have already finished their community service, but i don't see any mention of 'deactivating' these profiles. 
	*/
	//load fpdf (for creating pdfs)
	require("./assets/fpdf/fpdf.php");
	
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
	
	$query="SELECT * FROM locations";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	//get dose form varaibles :D
	$letterdate = $_REQUEST["letterdate"];
	$sysid = strtolower($_REQUEST["sysid"]);
	$county = strtolower($_REQUEST["county"]);
	
	if(strlen($letterdate)<8)
	{
		header("Location: CompletionLetter.php");
		$_SESSION['msgtext'] = "Error: Completion Letter Could Not Be Generated: The letter date was not submitted in the correct format";
		$_SESSION['msgtype'] = "error";
		exit;
	}

	
/*	print("sysid = ".$sysid."<br>");
	print("letterdate = ".$letterdate."<br>"); 
	print("county = ".$county."<br>");
	exit();	*/
	
	$query="SELECT * FROM letters WHERE name ='$county'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
		if ($result->num_rows == 0)
	{
		$_SESSION['msgtext'] = "Completion Letter Could Not Be Generated: The requested county could not be found in the database";
		$_SESSION['msgtype'] = "error";
		header("Location: CompletionLetter.php");
		exit;
	}

	
	//1. make sure that the given sysid exists in the system, and that the user is active
	$query="SELECT * FROM profiles WHERE sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	
	if ($result->num_rows == 0)
	{
		$_SESSION['msgtext'] = "Completion Letter Could Not Be Generated: The requested sysid could not be found in the database";
		$_SESSION['msgtype'] = "error";
		header("Location: CompletionLetter.php");
		exit;
	}
	else if($result->fetch_object()->status != 'active')
	{
		$_SESSION['msgtext'] = "Completion Letter Could Not Be Generated: The requested user is inactive. Please refer to a saved copy in <strong>Display Record</strong>.";
		$_SESSION['msgtype'] = "error";
		header("Location: CompletionLetter.php");
		exit;
	}
	
	//2. Insure that the profiles hrsearned field is correct.
	
	$query4 = "select hrsearned from timelogs where sysid = '$sysid'";
	$result4=$mysqli->query($query4) or die($mysqli->error.__LINE__);

			$totalhrs = 0;
			$data = array();
			$i = 0;
			while($obj4 = $result4->fetch_object())
			{
				$data[$i] = array($obj4->hrsearned);
				$totalhrs = $totalhrs + $obj4->hrsearned;
				$i++;
			}		
	$query5 = "update profiles set hrsearned = $totalhrs where sysid = '$sysid'";
	$result5=$mysqli->query($query5) or die($mysqli->error.__LINE__);	

	//print("Total hours: ".$totalhrs."<br>");
	//exit();
	
	
	//3. find date of last timelog
	$query="SELECT max(activityno) FROM timelogs WHERE sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$activityno = $result->fetch_row()[0];
	
	//make sure that the user is not logged in
	$query="SELECT status FROM timelogs WHERE activityno='$activityno' AND sysid='$sysid' AND status = 2";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	if($result->num_rows < 1)
	{
		header("Location: CompletionLetter.php");
		$_SESSION['msgtext'] = "Unable to generate completion letter: The sysid entered is currently logged in.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	
	$query="SELECT datein FROM timelogs WHERE activityno='$activityno'AND sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);

	$completiondate = $result->fetch_row()[0];


	/* tokenize the completion letter template. wherever there are brackets, look for a keyword and replace it with the corresponding value. Here are the keywords:
			- letterdate
			- fname
			-	lname
			- hoursearned
			- completiondate*/
			
	//get remaining variables
	$query="SELECT * FROM profiles WHERE sysid='$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	

	$obj = $result->fetch_object();
	
	$fname = $obj->fname;
	$lname = $obj->lname;
	$hrsearned = $obj->hrsearned;
	$visitnumber = $obj->visitnumber;
	$status = $obj->status;

	
	//get the letter from the database
	$query="SELECT * FROM letters WHERE name='$county'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		
	
	$contents = $result->fetch_object()->contents;
			
	//replace variable placeholders with values
	$contents = str_replace("[fname]",strtoupper($fname),$contents);
	$contents = str_replace("[lname]",strtoupper($lname),$contents);
	//this has been moved to the top (header) of the letter
	$contents = str_replace("[letterdate]",strtoupper($letterdate),$contents);
	$contents = str_replace("[completiondate]",strtoupper($completiondate),$contents);
	$contents = str_replace("[hours]",strtoupper($hrsearned),$contents);
	
	// write out a table of all activities for the given sysid - select activityno, datein, in24, out24, hrsearned, visitnumber from timelogs where sysid = '$sysid' order by activtyno;
	
	//now get the right record's profile and timelogs
	$sysid = strtolower($_REQUEST["sysid"]);
	
	//print('$sysid = '.$sysid);
	//exit;	
	
	$query = "select * from profiles where sysid = '$sysid'";
	$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
	$query2 = "select activityno, visitnumber, datein, in24, out24, hrsearned from timelogs where sysid = '$sysid'";
	$result2=$mysqli->query($query2) or die($mysqli->error.__LINE__);
	$query3 = "select activityno, visitnumber, datein, in24, out24, hrsearned from repeats where sysid = '$sysid'";
	$result3=$mysqli->query($query3) or die($mysqli->error.__LINE__);
	
	if($result->num_rows < 1)
	{
		header("Location: DisplayRecord.php");
		$_SESSION['msgtext'] = "Record Not Found: The sysid entered could not be found in the system";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else if ($result->num_rows > 1)
	{
		header("Location: DisplayRecord.php");
		$_SESSION['msgtext'] = "Multiple Records Found: More than one record was found for the given sysid. Please contact IT staff.";
		$_SESSION['msgtype'] = "error";
		exit;
	}
	else
	{ 		
		//5. create a entry in timelogs for the completion letter - insert into timelogs (sysid, in24, out24, activityno, datein, status, hrsearned, visitnumber) values ('$sysid', 'Completion', 'Letter Given', ($activityno + 1), '$date', 'closed', 0, $visitnumber);
		$query="insert into timelogs (sysid, in24, out24, activityno, datein, status, hrsearned, visitnumber) values ('$sysid', 'Completion', 'Letter Given', ($activityno + 1), '$letterdate', 'closed', 0, $visitnumber)";
		$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
		//output
		
		//output the results of the saved letter
		$pdf = new FPDF('P','mm','A4');
		$pdf->AddPage();
		$pdf->Image('./assets/img/MUST_logo.jpg',70,6,60);
		//add date to header
		$pdf->SetFont('Times','',16);
		$pdf->Cell(80);
		//$pdf->Cell(30,10,$letterdate,1,0,'C');
		$pdf->Ln(40);
		$pdf->MultiCell(0,5,$contents);
		$pdf->Ln(10);
		//$pdf->MultiCell(0,5,"Sincerely, Annette Lee");
		
		//now, output the timelogs
			// Colors, line width and bold font
			//
			$pdf->AddPage(); 
			$pdf->Image('./assets/img/MUST_logo.jpg',70,6,60);
			$pdf->ln(50);
			$pdf->Multicell(0,5,"Detailed hours of $fname $lname:");
			$pdf->ln(5);
			$pdf->SetFillColor(255,0,0);
			$pdf->SetTextColor(255);
			$pdf->SetDrawColor(128,0,0);
			$pdf->SetLineWidth(.3);
			$pdf->SetFont('','B');
			// Header
			$tblhead = array("Activity #","Visit #","Date","Start Time","End Time","Hours Earned");
			$w = array(28, 28, 32, 34, 34, 40);
			for($i=0;$i<count($tblhead);$i++)
					$pdf->Cell($w[$i],7,$tblhead[$i],1,0,'C',true);
			$pdf->Ln();
			// Color and font restoration
			$pdf->SetFillColor(224,235,255);
			$pdf->SetTextColor(0);
			$pdf->SetFont('');
			// Data
			$fill = false;
			// get dat data
			// if there are no entries...
			if($result2->num_rows < 1 && $result3->num_rows < 1)
			{
				$pdf->Cell(100,6,"There are currently no timelogs for this user",'LR',0,'L',$fill);
			}
			//now, systematically display all relevant information
			$totalhrs = 0;
			$data = array();
			$i = 0;
			while($obj = $result2->fetch_object())
			{
				$data[$i] = array($obj->activityno,$obj->visitnumber,$obj->datein,$obj->in24,$obj->out24,$obj->hrsearned);
				$totalhrs = $totalhrs + $obj->hrsearned;
				$i++;
			}
			foreach($data as $row)
			{
					$pdf->Cell($w[0],6,$row[0],'LR',0,'C',$fill);
					$pdf->Cell($w[1],6,$row[1],'LR',0,'C',$fill);
					$pdf->Cell($w[2],6,$row[2],'LR',0,'C',$fill);
					$pdf->Cell($w[3],6,$row[3],'LR',0,'C',$fill);
					$pdf->Cell($w[4],6,$row[4],'LR',0,'C',$fill);
					$pdf->Cell($w[5],6,$row[5],'LR',0,'C',$fill);
					$pdf->Ln();
					$fill = !$fill;
			}
			//$pdf->echo("Total Hours = ".$totalhrs);
			// Closing line
			$pdf->Cell(array_sum($w),0,'','T');
			$pdf->ln();
			$pdf->Multicell(196,6,"Total Hours Served = $totalhrs     ",'LRB','R',$fill);
			$pdf->ln(5);
			
			// The following print function is for debugging only.
			//printf("$completiondate Total Hours = ".$totalhrs);
			//exit();

			//6. copy these timelogs into the repeats table - insert into repeats (datein, sysid, in24, out24, activityno, visitnumber, hrsearned) values ('$datein', '$sysid', '$in24', '$out24', '$activityno', '$visitnumber', '$hoursearned');
			$query="insert into repeats (datein, sysid, in24, out24, activityno, visitnumber, hrsearned) select datein, sysid, in24, out24, activityno, visitnumber, hrsearned from timelogs where sysid='$sysid'";
			$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
			//7. delete the timelogs from the timelogs database - delete from timelogs where sysid = '$sysid';
			$query="delete from timelogs where sysid = '$sysid'";
			$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
			//8. increment the visit number in the profiles database - update profiles set visitnumber = ($visitnumber + 1) where sysid = '$sysid';
			$query="update profiles set visitnumber = ($visitnumber + 1) where sysid = '$sysid'";
			$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
			//9. set hrsearned back to 0 - update profiles set hrsearned = 0 where sysid = '$sysid';
			$query="update profiles set hrsearned = 0 where sysid = '$sysid'";
			$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			
			//10. make the user inactive
			$query="update profiles set status = 2 where sysid = '$sysid'";
			$result=$mysqli->query($query) or die($mysqli->error.__LINE__);
			/* NOTE: might have to look more into what's going on here. repeats seems to have all old timelogs from users that have already finished their community service, but i don't see any mention of 'deactivating' these profiles. */
	
		//create subdirectory if necessary
		if(!file_exists("TAS Completion Letters\\" . $sysid))
		{
			mkdir("TAS Completion Letters\\" . $sysid);
		}
		
		//save the PDF with naming convention sysid_Fname_Lname_Letterdate
		$pdf->Output("TAS Completion Letters\\" . $sysid . "\\" . $sysid . "_" . $fname . "_" . $lname . "_" . date("n-j-Y_H-i-s", time()) . ".pdf", "F");
		
		$pdf->Output();
	}
?>