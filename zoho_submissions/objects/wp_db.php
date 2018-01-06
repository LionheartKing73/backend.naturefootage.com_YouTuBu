<?php

class wp_db {

    var $dbh = 0;

   // http://php.net/manual/en/mysqli.quickstart.connections.php

   function connect()
   {
       $this->dbh =  new mysqli(HOST, DBUSER, DBPASS, DBNAME, DBPORT);

       if ($this->dbh->connect_errno) {
           echo "Failed to connect to MySQL: (" . $this->dbh->connect_errno . ") " . $this->dbh->connect_error;
       }
   }


  function get_lib_submission($prod_code)
   {

$arr_zoho_data = array();

$sql1 = "SELECT *, lib_submissions.id as sub_id FROM `lib_submissions`, `lib_users` WHERE `lib_submissions`.provider_id =  `lib_users`.id  and  `lib_submissions`.id=" . $prod_code;

if(TESTMODE == "YES") 
{
	echo "<h1>lib_submissions</h1>";
	echo $sql1;
}

	if (!($stmt = $this->dbh->prepare($sql1))) {
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
         exit;
     }
	 
	 if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

	$res = $stmt->get_result();
	$row_submission = $res->fetch_assoc();
	$row_submission["id"] = $row_submission["sub_id"];

if(TESTMODE == "YES") 
{
	echo "<pre>";
	   print_r($row_submission);
	echo "</pre>";
}

$arr_zoho_data["Contributor Name"] = $row_submission["prefix"];
$arr_zoho_data["Submission Name"] = $row_submission["code"];
$arr_zoho_data["Date Submission"] = $row_submission["date"];
$arr_zoho_data["Drive Submission"] = $row_submission["location"];
$arr_zoho_data["Status"] = "Logging: Ready to Log";


//pricing_category

$sql2 = "SELECT distinct(pricing_category) FROM  `lib_clips` WHERE  `lib_clips`.submission_id =" . $row_submission["id"];

if(TESTMODE == "YES") 
{
	echo "<h1>pricing_category</h1>";
	echo $sql2;
}

if (!($stmt = $this->dbh->prepare($sql2))) {
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
         exit;
     }
	 
	 if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

	$res = $stmt->get_result();
	
	$pricing_category = array();
	while ($row = $res->fetch_assoc()) {
	  $pricing_category[] = $row;
	}

	//$pricing_category = $res->fetch_all();

if(TESTMODE == "YES") 
{
	echo "<pre>";
	print_r($pricing_category);
	echo "</pre>";
}

//IF COUNT =1

if( isset($pricing_category[0]) and  is_array($pricing_category))
{
	if(count($pricing_category[0]) ==1)
	{
		$arr_zoho_data["Format Category"] = $pricing_category["0"]["pricing_category"];
	}
	else
	{
		$arr_zoho_data["Format Category"] = "";
	}
}
else
{
	$arr_zoho_data["Format Category"] = "";
}

if(TESTMODE == "YES") 
{
	echo "<h1>arr_zoho_data</h1>";
	echo "<pre>";
	print_r($arr_zoho_data);
	echo "</pre>";
}
//duration

$sql3 = "SELECT sum(duration ) as duration FROM  `lib_clips` WHERE  `lib_clips`.submission_id =" . $row_submission["id"];

if(TESTMODE == "YES") 
{
	echo "<h1>duration</h1>";
	echo $sql3;
}

	if (!($stmt = $this->dbh->prepare($sql3))) {
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
         exit;
     }
	 
	 if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

	$res = $stmt->get_result();
	$duration = $res->fetch_assoc();

if(TESTMODE == "YES") 
{
	echo "<pre>";
	print_r($duration);
	echo "</pre>";
}

$arr_zoho_data["Submission Minutes"] = round($duration["duration"]/60,0);

//count
$sql4 = "SELECT count(* ) as clips FROM  `lib_clips` WHERE  `lib_clips`.submission_id =" . $row_submission["id"];
	if (!($stmt = $this->dbh->prepare($sql4))) 
	{
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
         exit;
     }
	 
	 if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

	$res = $stmt->get_result();
	$nos_clips = $res->fetch_assoc();

	if(TESTMODE == "YES") 
	{
		echo "<h1>nos_clips</h1>";
		echo $sql4;
		echo "<pre>";
		print_r($nos_clips);
		echo "</pre>";
	}
	
	$arr_zoho_data["Submission Number Clips"] = $nos_clips["clips"];

//echo "<pre>";
//   print_r($arr_zoho_data);
//	echo "</pre>";
	
return $arr_zoho_data;	
   }
} // end of class wp_db
