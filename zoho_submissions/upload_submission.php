<?php

if (getenv("environment") !== "production") {
	die ("This should be run only on production!" . PHP_EOL);
}

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
define ("TESTMODE", 'NO'); // YES/NO
define ("ZOHO_KEY",  "6400a60b24d2fcdeeb656468e80e780b");

/*
define ("HOST", 'localhost');
define ("DBNAME",  "nature_footage");
define ("DBPORT",  "3306");
define ("DBUSER",  "root");
define ("DBPASS",  "");
*/


define ("HOST", 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com');
define ("DBNAME",  "fsmaster-production");
define ("DBPORT",  "3306");
define ("DBUSER",  "fsmaster");
define ("DBPASS",  "FSdbm6512");

	
$con=mysqli_connect(HOST,DBUSER,DBPASS,DBNAME);
// Check connection
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	die();
}
	include "objects/wp_db.php";
	include "objects/zoho_crm.php";
	include "objects/zoho_crm_submissions.php";
	include "objects/make_xml.php";

	$wpdb = new wp_db();
	$wpdb->connect();
	
$yesterday = date('Y-m-d',strtotime("-1 days"));

$sql="SELECT * FROM lib_submissions where zoho_id = '' and date = '" . $yesterday  . "'";

if ($result=mysqli_query($con,$sql))
  {
	  
  	$crm = new zoho_crm();
	$mxml = new make_xml();
	$crm_vendor = new zoho_crm_submission();
  // Fetch one and one row
  while ($row_s=mysqli_fetch_assoc($result))
  {
	if(TESTMODE == "YES") 
	{
		echo "<pre>";
		print_r($row_s);
		echo "</pre>";
	}
	$sub_detail = $wpdb->get_lib_submission($row_s["id"]);
	$pp = $sub_detail["Contributor Name"];
	
	if(TESTMODE == "YES") 
	{
		echo "<pre>";
		print_r($sub_detail);
		echo "</pre>";
		//die;
	}
	
//	if ($pp == "")
//	{
//		printf("PP was blank , replace non-alphas\n", $pp);
//		$pp = $invoice_line['product'];
//		$pp = preg_replace("/[0-9_]/", "", $pp);
//	}

	// Fix AAU to DI
//	if ($pp == "AAU")
//	{
//		$pp = 'DI';
//	}

	// get vendors id from zoho for each product
	if($pp != "")
	{
		$vendor_id = $crm_vendor->get_vendor_id_from_zoho($pp);
		$sub_detail["vendor_id"] = $vendor_id;
	}
	else
	{
		$sub_detail["vendor_id"] = "";
	}
	$xml = $mxml->create_line_xml($sub_detail);

if(TESTMODE == "YES") 
{
	echo "<h1> XML </h1>";
	echo $xml;
	echo "<br>";

}
	$zoho_id = $crm->post_submission_and_get_id($xml);
	
	//update the record in lib_submission
	
$sql_update="update lib_submissions set zoho_id ='". $zoho_id . "' where id ='". $row_s["id"] . "'";
 
if(TESTMODE == "YES") 
{
	echo $sql_update;
}

	$result_update=mysqli_query($con,$sql_update) or die(mysqli_error($con));;
	echo  "<br>" . $row_s["id"] . " - " . $row_s["code"] . " - ZOHO ID : ". $zoho_id . "<br>";

    }
  // Free result set
  mysqli_free_result($result);
}
mysqli_close($con);
echo "Process completed";
?>