<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
  "http://www.w3.org/TR/html4/loose.dtd">
<HTML lang='en'>
<HEAD>
        <TITLE> Naturefootage Pay Invoice </TITLE>
</HEAD>
<BODY>

<B>NatureFootage: Credit Card Payment</B>

<?php

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

// $test = "true";
  $test = "false";

$amount = 0;
if (isset($_GET["amount"] )) {
   $amount = $_GET["amount"];
}

$invoice = 0;
if (isset($_GET["invoice"] )) {
   $invoice = $_GET["invoice"];
}

	$fields = array (
	                  array( "display" => "",                    "type" => "hidden","name" => "posted",      "x_name" => '',                 "value" => "1" ),

	                  array( "display" => "Amount",              "type" => "text", "name" => "amount",       "x_name" => 'x_amount',         "value" => $amount ),
//	                  array( "display" => "Invoice",             "type" => "text", "name" => "description",  "x_name" => 'x_description',    "value" => $invoice ),
	                  array( "display" => "Invoice",             "type" => "text", "name" => "invoice_num",  "x_name" => 'x_invoice_num',    "value" => $invoice ),

                          array( "display" => "Company",             "type" => "text", "name" => "company",      "x_name" => 'x_company',        "value" => "" ),
                          array( "display" => "First Name",          "type" => "text", "name" => "first_name",   "x_name" => 'x_first_name',     "value" => "" ),
	                  array( "display" => "Last Name",           "type" => "text", "name" => "last_name",    "x_name" => 'x_last_name',      "value" => "" ),
	                  array( "display" => "Address",             "type" => "text", "name" => "address",      "x_name" => 'x_address',        "value" => "" ),
	                  array( "display" => "City",                "type" => "text", "name" => "city" ,        "x_name" => 'x_city',           "value" => "" ),
	                  array( "display" => "State",               "type" => "text", "name" => "state",        "x_name" => 'x_state',          "value" => "" ),
	                  array( "display" => "Country",             "type" => "text", "name" => "country",      "x_name" => 'x_country',        "value" => "" ),
	                  array( "display" => "Zip",                 "type" => "text", "name" => "zip",          "x_name" => 'x_zip',            "value" => "" ),
	                  array( "display" => "Phone",               "type" => "text", "name" => "phone",        "x_name" => 'x_phone',          "value" => "" ),

	                  array( "display" => "Card Number",         "type" => "text", "name" => "card_num",     "x_name" => 'x_card_num',       "value" => "" ),
	                  array( "display" => "Exp Date  ie) 0115 ", "type" => "text", "name" => "exp_date",     "x_name" => 'x_exp_date',       "value" => "" ),
	                  array( "display" => "Card Code(CVC)",      "type" => "text", "name" => "card_code",    "x_name" => 'x_card_code',      "value" => "" ),

			  );


if (isset($_POST["posted"] )) {


?>


<!--
This sample code is designed to connect to Authorize.net using the AIM method.
For API documentation or additional sample code, please visit:
http://developer.authorize.net
-->

<?PHP

//   $log_filename = sprintf("/root/load_invoice_logs/processing.%s.txt", time());
   $log_filename = sprintf("/var/www/fsearch/public_html/load_invoice/logs/processing.%s.txt", time());

   $log_fh = fopen($log_filename, 'w');

//   printf("log_fn = %s\nlog_fh = %s\n", $log_filename, print_r($log_fh));

   $rval = fprintf($log_fh, "Starting: at %s\n", date('r'));

//   printf("fprintf rval = %s\n", $rval);
   

// By default, this sample code is designed to post to our test server for
// developer accounts: https://test.authorize.net/gateway/transact.dll
// for real accounts (even in test mode), please make sure that you are
// posting to: https://secure.authorize.net/gateway/transact.dll
// $post_url = "https://test.authorize.net/gateway/transact.dll";
$post_url = "https://secure.authorize.net/gateway/transact.dll";

$post_values = array(
        
        // the API Login ID and Transaction Key must be replaced with valid values
        "x_login"                       => "dvfoot323",
        "x_tran_key"                    => "6Se4Ah3ws7YgNyUz",

	"x_test_request"                =>  $test,

        "x_version"                     => "3.1",

        "x_delim_data"          => "TRUE",
        "x_delim_char"          => "|",
        "x_relay_response"      => "FALSE",

        "x_type"                        => "AUTH_CAPTURE",
        "x_method"                      => "CC",



        // Additional fields can be added here as outlined in the AIM integration
        // guide at: http://developer.authorize.net
);


  // printf("<pre>GET :\n%s\n</pre>", print_r($_POST,1));

  fprintf($log_fh, "POST:\n%s\n",print_r($_POST,1));

  foreach ($fields as $field ) {
     if (  $field['x_name'] != "" ) {
        $post_values[  $field['x_name'] ] = $_POST[  $field['name'] ];
     }
   }

 // This section takes the input fields and converts them to the proper format
 // for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
 $post_string = "";
 foreach( $post_values as $key => $value )
        { $post_string .= "$key=" . urlencode( $value ) . "&"; }
 $post_string = rtrim( $post_string, "& " );

// The following section provides an example of how to add line item details to
// the post string.  Because line items may consist of multiple values with the
// same key/name, they cannot be simply added into the above array.
//
// This section is commented out by default.

if ( 0 ) {
$line_items = array(
        "item1<|>golf balls<|><|>2<|>18.95<|>Y",
        "item2<|>golf bag<|>Wilson golf carry bag, red<|>1<|>39.99<|>Y",
        "item3<|>book<|>Golf for Dummies<|>1<|>21.99<|>Y");
        
foreach( $line_items as $value )
        { $post_string .= "&x_line_item=" . urlencode( $value ); }
}


//printf("<pre>");
//print_r($post_values);
//print_r($post_string);
//printf("</pre>");

fprintf($log_fh, "POST_VLAUES:\n%s\n",print_r($post_values,1));

// This sample code uses the CURL library for php to establish a connection,
// submit the post, and record the response.
// If you receive an error, you may want to ensure that you have the curl
// library enabled in your php configuration
$request = curl_init($post_url); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
        $post_response = curl_exec($request); // execute curl post and store results in $post_response
        // additional options may be required depending upon your server configuration
        // you can find documentation on curl options at http://www.php.net/curl_setopt
curl_close ($request); // close curl object

fprintf($log_fh, "POST_RESPONSE:\n%s\n",print_r($post_response,1));

// This line takes the response and breaks it into an array using the specified delimiting character
$response_array = explode($post_values["x_delim_char"],$post_response);

// 
// 1 This transaction has been approved.
// 2 This transaction has been declined.
// 3 There has been an error processing this transaction.
// 4 This transaction is being held for review
// 


fprintf($log_fh, "RESPONSE:\n%s\n",print_r($response_array,1));

if ( $response_array[0] == "^1^" ) {
  printf("Transaction Successfull Transaction #%s\n", $response_array[6]);
} else {
  printf("Transaction Failed Reason code: %s\n", $response_array[3]);
}

// The results are output to the screen in the form of an html numbered list.
//
//echo "<OL>\n";
//foreach ($response_array as $value) {
//        echo "<LI>" . $value . "&nbsp;</LI>\n";
//}
//echo "</OL>\n";
// individual elements of the array could be accessed to read certain response
// fields.  For example, response_array[0] would return the Response Code,
// response_array[2] would return the Response Reason Code.
// for a list of response fields, please review the AIM Implementation Guide

fclose($log_fh);

?>


<?php

} else {


if ( $test == "true" ) {

?>


<pre>

TESTMODE

#TEST CARD NUMBERS
#
#CARD TYPE        
#
#American Express   370000000000002
#Discover           6011000000000012
#MasterCard         5424000000000015
#Visa               4007000000027
#
# ERROR Test cards : 
#  Test Mode and submit a transaction with the card number 4222222222222.
#  And amount of the error number desired, ie 27.00 for error code 27
#


</pre>

<?php
   }
?>

<form method="POST">

<?php


//   printf("<pre>");
//   print_r($fields);

       foreach ($fields as $field ) {
//   print_r($field);

   printf('%s<br><input type="%s" name="%s" value="%s" size="50"><br>',
          $field['display'], $field['type'], $field['name'], $field['value']);
   }

//   printf("</pre>");

?>

<input type="submit" value="Submit">
</form> 

<?php

   }; 


?>


</BODY>
</HTML>
