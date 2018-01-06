<?php
$skip_clip_id = 0;
$skip_on_die = 0;
define ("TESTMODE","NO"); // YES/NO
$global_error_message = '';
$product = '';


function onDie()
{
	if (!$GLOBALS["skip_on_die"])
	{
		$message = ob_get_contents();
		ob_end_clean();
		printf("<html><head><style>  body {background-color:rgb(255,200,200);}   </style></head><body>   Failed to post Invoice: %s <br /> Debug information below: <br /> %s </body></html>", $GLOBALS["global_error_message"], $message);
		$invoice_num = 0;
		if (isset($_POST["invoice_num"]))
		{
			$invoice_num = $_POST["invoice_num"];;
		};
		
		//$log_filename = sprintf("logs/load_invoice.%s.%s.txt", $invoice_num, time());
		$log_filename = sprintf("/var/www/fsearch/public_html/load_invoice/logs/load_invoice.%s.%s.txt", $invoice_num, time());
		$log_fh = fopen($log_filename, 'w');
		fprintf($log_fh, "%s\n%s", $GLOBALS["global_error_message"], $message);
		fclose($log_fh);
	}
	else
	{
		if (isset($_POST["invoice_num"]))
		{
			$invoice_num = $_POST["invoice_num"];
			$message = ob_get_contents();
			ob_end_clean();
			printf("<html><head><style>  body {background-color:rgb(200,255,200)}   </style></head><body>  Posted Invoice Succesfully!<br /> Zoho #%s  view at https://crm.zoho.com/crm/EntityInfo.do?module=Invoices&id=%s </body></html>", $GLOBALS["skip_clip_id"], $GLOBALS["skip_clip_id"]);
			
			//$log_filename = sprintf("logs/load_invoice.%s.%s.txt", $invoice_num, time());
			$log_filename = sprintf("/var/www/fsearch/public_html/load_invoice/logs/load_invoice.%s.%s.txt", $invoice_num, time());
			$log_fh = fopen($log_filename, 'w');
			fprintf($log_fh, "%s\n%s", $GLOBALS["global_error_message"], $message);
			fclose($log_fh);
		}
	}
};
register_shutdown_function('onDie');

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


if (isset($_POST["invoice_num"]))
{
	ob_start();
	
	include "objects/wp_db.php";
	include "objects/zoho_crm.php";
	include "objects/zoho_crm_vendor.php";
	include "objects/make_xml.php";

	printf("Starting\n");
	$num = $_POST["invoice_num"];
	//$potential_id = $_POST["contact_name"];
	
	echo "INVOICE from call : " . $num . "<br>";
	echo"Contact Name SFR# from call : " . $_POST["contact_name"] . "<br>";
	
	$potential = get_potential_from_zoho($_POST["contact_name"]);
	$potential_id = $potential['POTENTIALID'];
    $potential_account_id = $potential['ACCOUNTID'];
	if($potential_id == 0)
	{
		echo "Error invalid SFR#";
		die;
	}
	
	if(TESTMODE == "YES")  echo "sanjay - get_invoice started" . "<br />";
	
	// get  the invoice detail from db //mysql procedure filemaker_order_information2
	$wpdb = new wp_db();
	$wpdb->connect();
	$invoice = $wpdb->get_invoice($num);
	if(TESTMODE == "YES")
	{
		 echo "<pre>";
		 print_r($invoice);
		 echo "</pre>";
		  echo "sanjay - get_lib_order" . "<br />";
	}

	// get tne order details from table lib_orders
	$wpdb2 = new wp_db();
	$wpdb2->connect();
	$order = $wpdb2->get_lib_order($num);
	$order = $order[0];

	if(TESTMODE == "YES")
	{
		echo "<pre>";
		 print_r($order);
		 echo "</pre>";
		  echo "sanjay - get_invoice_lines" . "<br />";
	}
	// get the invoice lines mysql procedute zoho_order_lineitems
	$wpdb2 = new wp_db();
	$wpdb2->connect();
	$invoice_lines = $wpdb2->get_invoice_lines($num);
	
	if(TESTMODE == "YES")
	{
		echo "<pre>";
		print_r($invoice_lines);
		echo "</pre>";
	}

	// Fix invoice at header and print it out.
	$invoice = $invoice[0];
	$invoice['invoice_date'] = $order['mtime'];
	$invoice['potential_id'] = $potential_id;
	printf("INVOICE from call\n%s\n", print_r($invoice, 1));
	printf("Order from Database:%s\n", print_r($order, 1));

	// Fix invoice at line level
	$invoice_lines_fixed = array();
	$mxml = new make_xml();
	$had_errors = 0;
	$line_count = sizeof($invoice_lines);
	if ($line_count > 100)
	{
		$GLOBALS["global_error_message"] = sprintf("Cannot have more than 100 lines in invoice, this invoice has %d lines.", $line_count);
		exit;
	}

	$wpdb3 = new wp_db();
	$wpdb3->connect();
	foreach($invoice_lines as $invoice_line)
	{
		if (!$had_errors)
		{
			printf("LINE\n%s\n", print_r($invoice_line, 1));
			if (($invoice_line['product'] != ""))
			{
				$crm = new zoho_crm();
				$crm_vendor = new zoho_crm_vendor();
				$pp = $invoice_line['provider_prefix'];
				$GLOBALS["product"] = $invoice_line['product'];
				printf("Lookup the vendor.\n");
				if ($pp == "")
				{
					printf("PP was blank , replace non-alphas\n", $pp);
					$pp = $invoice_line['product'];
					$pp = preg_replace("/[0-9_]/", "", $pp);
				}

				// Fix AAU to DI
				if ($pp == "AAU")
				{
					$pp = 'DI';
				}

				// get vendors id from zoho for each product

				printf("Lookup the vendor:%s\n", $pp);
				
				//sanjay
				$vendor_id = $crm_vendor->get_vendor_id($pp);
				printf("Vender_ID\n%s\n", $vendor_id);

				// get the video url from the database

				$clips_res = $wpdb3->get_lib_clips_res($invoice_line["product"]);
				$invoice_line['Video_URL'] = $clips_res[0]["location"];
				$invoice_line['VENDORID'] = $vendor_id;
				$invoice_line['invoice_date'] = $order['mtime'];
				$invoice_line['Product Name'] = $num . "_" . $invoice_line['product'];
				$invoice_line['Invoice'] = $num;
			
				if(TESTMODE == "YES")
				{
					echo "<h1>Invoice line</h1>";
					echo "<pre>";
					print_r($invoice_line);
					echo "</pre>";
				}
				
				$xml = $mxml->create_line_xml($invoice_line);
				printf("XML\n%s\n", print_r($xml, 1));
				$clip_id = "";

				// post the product to zoho
				if(TESTMODE == "NO")
				{
					$clip_id = $crm->post_product_and_get_product_id($xml);
				}
				else
				{
					$clip_id= "1111111";
				}
				$invoice_line['Product Id'] = $clip_id;
				array_push($invoice_lines_fixed, $invoice_line);
			}
			else
			{
				printf("Product was null, skip this line on the import\n");
			}
		}
	}

	if (!$had_errors)
	{
		printf("\n");
		printf("%s\n", print_r($invoice_lines, 1));
		/*
		echo "<h1>invoice</h1>";
		echo "<pre>";
		print_r($invoice);
		echo "</pre>";
		echo "<h1>invoice_lines_fixed</h1>";
		echo "<pre>";
		print_r($invoice_lines_fixed);
		echo "</pre>";
		echo "<h1>order</h1>";
		echo "<pre>";
		print_r($order);
		echo "</pre>";
		*/
		
		$xml = $mxml->create_invoice_xml($invoice, $invoice_lines_fixed, $potential_id, $potential_account_id, $order);
		printf("XML\n%s\n", print_r(htmlentities($xml) , 1));
		$crm = new zoho_crm();

		// create invoice in zoho
		if(TESTMODE == "NO")
		{
			$clip_id = $crm->post_invoice($xml);
			foreach($invoice_lines_fixed as $invoice_line)
			{
				printf("Update LINE\n%s\n", print_r($invoice_line, 1));
				// update the product in zoho with invoice id and invoice id
				$crm->update_product_with_invoice_ID_and_invoice($invoice_line['Product Id'], $clip_id, $invoice_line['Invoice']);
			}
		}
		printf("Done!\n");
	}

	
	//   $page = ob_get_contents();
	//   ob_end_clean();

	$page_output = "some output here\n";
	if ($had_errors)
	{
		exit;
	}
	else
	{
		printf("<html><head><style>  body {background-color:rgb(200,255,200)}   </style></head><body>  Posted Invoice Succesfully!<br /> Zoho # %s</body></html>", $clip_id);
		$GLOBALS["skip_clip_id"] = $clip_id;
		$GLOBALS["skip_on_die"] = 1;
		exit;
	}
}
else
{
	$GLOBALS["skip_on_die"] = 1;
?>


<pre>
# 26468251  -- 0 lines
# 26468250  -- 1 lines
# 26468249  -- multiple lines
# 26468248
</pre>

<form action="upload_invoice.php" method="post">

Invoice #:<br />
<input type="text" name="invoice_num">
<br />
    SFR#(enter as SFR12345) :<br />
<input type="text" name="contact_name">
<input type="submit" value="Submit">
</form>

<?php
}
?>