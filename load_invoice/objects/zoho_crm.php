<?php
class zoho_crm
{
	/*
	function get_vendor_id($provider)
	{
		$vendor = $provider;
		$ch = curl_init('https://crm.zoho.com/crm/private/xml/Vendors/SearchRecords?');
		if (!$ch)
		{

			// Check for errors and display the error message
			if ($errno = curl_errno($ch))
			{
				$error_message = curl_strerror($errno);
				echo "cURL error ({$errno}):\n {$error_message}";
			}
		}

		curl_setopt($ch, CURLOPT_VERBOSE, 1); //standard i/o streams
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//  curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'rsa_rc4_128_sha');
		//  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);// Turn off the server and peer verification
		//  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set to return data to string ($response)
		curl_setopt($ch, CURLOPT_POST, 1); //Regular post
		$authtoken = "6400a60b24d2fcdeeb656468e80e780b";
		$search_condition = sprintf("(Vendor Name:%s)", $vendor);
		$query = "newFormat=2&authtoken={$authtoken}&scope=crmapi&selectColumns=All&criteria={$search_condition}&wfTrigger=true";

		// Check for errors and display the error message

		if ($errno = curl_errno($ch))
		{
			$error_message = curl_strerror($errno);
			echo "cURL error ({$errno}):\n {$error_message}";
		}

		printf("Query:\n%s\n", $query);
		$url = 'https://crm.zoho.com/crm/private/xml/Vendors/SearchRecords?';
		printf("%s%s\n", $url, $query);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.
		$response = curl_exec($ch);

		// Check for errors and display the error message
		if ($errno = curl_errno($ch))
		{
			$error_message = curl_strerror($errno);
			echo "cURL error ({$errno}):\n {$error_message}";
		}

		curl_close($ch);
		printf("Response:\n%s\n", htmlentities($response));
		$simple = $response;
		$p = xml_parser_create();
		xml_parse_into_struct($p, $simple, $vals, $index);
		xml_parser_free($p);
		
		echo "Index array\n";
		print_r($index);
		echo "\nVals array\n";
		print_r($vals);
		
		
		if (0)
		{
			$filename_to = str_replace("invoice_files_new", "invoice_files_sent", $filename);
			printf("moving file %s to %s\n", $filename, $filename_to);
			rename($filename, $filename_to);
			sleep(5);
		}
		
		// ob_end_clean();

		printf("%s\n", $vals[4]['value']);
		if (!isset($vals[4]['value']))
		{
			$GLOBALS["global_error_message"] = sprintf("Could not get the vendor ID for vendor '%s' on product %s, please chack if it is in zoho.\n Error = %s", $provider, $GLOBALS["product"], $response);
			exit;
		}

		return ($vals[4]['value']);
	}
*/
//
//
//
	function post_product_and_get_product_id($xml_in)
	{
		printf("post_product_and_get_product_id:\n%s\n", htmlentities($xml_in));
		$ch = curl_init('https://crm.zoho.com/crm/private/xml/Products/insertRecords?');
		curl_setopt($ch, CURLOPT_VERBOSE, 1); //standard i/o streams
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Turn off the server and peer verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set to return data to string ($response)
		//  curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'rsa_rc4_128_sha');
		curl_setopt($ch, CURLOPT_POST, 1); //Regular post

		$authtoken = "6400a60b24d2fcdeeb656468e80e780b";
		$xmlData = $xml_in;
		$xmlData = urlencode($xmlData);
		printf("Writing file\n----\n%s\n----\n", $xmlData);
		$query = "newFormat=1&wfTrigger=true&authtoken={$authtoken}&scope=crmapi&xmlData={$xmlData}";
		printf("Query:\n%s\n", $query);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.
		$response = curl_exec($ch);
		curl_close($ch);
		printf("Response:\n%s\n", htmlentities($response));
		$simple = $response;
		$p = xml_parser_create();
		xml_parse_into_struct($p, $simple, $vals, $index);
		xml_parser_free($p);
		
		echo "Index array\n";
		print_r($index);
		echo "\nVals array\n";
		print_r($vals);
		
		if (!isset($vals[4]['value']))
		{
			$GLOBALS["global_error_message"] = sprintf("Could not create product  %s in zoho, please chack if it is in zoho.\n Error = %s", $provider, $GLOBALS["product"], $response);
			exit;
		};
		
		printf("%s\n", $vals[4]['value']);
		return ($vals[4]['value']);
	}


//
//
//
//
	function update_product_with_invoice_ID_and_invoice($productid, $invoice_id, $invoice)
	{
		printf("update_product_with_invoice_ID_and_invoice:\n%s\n%s\n%s\n", $productid, $invoice_id, $invoice);
		$ch = curl_init('https://crm.zoho.com/crm/private/xml/Products/updateRecords?');
		curl_setopt($ch, CURLOPT_VERBOSE, 1); //standard i/o streams
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Turn off the server and peer verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set to return data to string ($response)
		//  curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'rsa_rc4_128_sha');
		curl_setopt($ch, CURLOPT_POST, 1); //Regular post
		$authtoken = "6400a60b24d2fcdeeb656468e80e780b";
		$xmlData = sprintf('<Products><row no="1"><FL val="Invoice ID">%s</FL><FL val="Invoice">%s</FL></row></Products>', $invoice_id, $invoice);
		$xmlData = urlencode($xmlData);
		printf("Writing file\n----\n%s\n----\n", $xmlData);
		$query = "newFormat=1&wfTrigger=true&authtoken={$authtoken}&scope=crmapi&id={$productid}&xmlData={$xmlData}";
		printf("Query:\n%s\n", $query);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.
		$response = curl_exec($ch);
		curl_close($ch);
		printf("Response:\n%s\n", htmlentities($response));
		$simple = $response;
		$p = xml_parser_create();
		xml_parse_into_struct($p, $simple, $vals, $index);
		xml_parser_free($p);
		echo "Index array\n";

		print_r($index);
		echo "\nVals array\n";
		print_r($vals);

		if (!isset($vals[4]['value']))
		{
			$GLOBALS["global_error_message"] = sprintf("Could not create product  %s in zoho, please chack if it is in zoho.\n Error = %s", $productid, $GLOBALS["product"], $response);
			exit;
		};
		printf("%s\n", $vals[4]['value']);
		return ($vals[4]['value']);
	}

//
//
//
//
	function post_invoice($xml_in)
	{
		$ch = curl_init('https://crm.zoho.com/crm/private/xml/Invoices/insertRecords?');
		curl_setopt($ch, CURLOPT_VERBOSE, 1); //standard i/o streams
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Turn off the server and peer verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set to return data to string ($response)
		//  curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'rsa_rc4_128_sha');
		curl_setopt($ch, CURLOPT_POST, 1); //Regular post
		$authtoken = "6400a60b24d2fcdeeb656468e80e780b";
		$xmlData = $xml_in;
		$xmlData = urlencode($xmlData);
		printf("Writing file\n----\n%s\n----\n", $xmlData);
		$query = "newFormat=1&wfTrigger=true&authtoken={$authtoken}&scope=crmapi&xmlData={$xmlData}";
		printf("Query:\n%s\n", $query);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.
		$response = curl_exec($ch);
		curl_close($ch);
		
		printf("Response:\n%s\n", htmlentities($response));
		$simple = $response;
		$p = xml_parser_create();
		xml_parse_into_struct($p, $simple, $vals, $index);
		xml_parser_free($p);

		echo "Index array\n";
		print_r($index);
		echo "\nVals array\n";
		print_r($vals);

		if (!isset($vals[4]['value']))
		{
			$GLOBALS["global_error_message"] = sprintf("Could not create invoice in zoho, please chack if it is in zoho.<br /> Error = %s <br />", $provider, $response);
			exit;
		}

		printf("%s\n", $vals[4]['value']);
		return ($vals[4]['value']);
	}
}