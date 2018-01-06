<?php
class zoho_crm
{
	

	function post_submission_and_get_id($xml_in)
	{
		$ch = curl_init('https://crm.zoho.com/crm/private/xml/CustomModule2/insertRecords?');
		curl_setopt($ch, CURLOPT_VERBOSE, 1); //standard i/o streams
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Turn off the server and peer verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set to return data to string ($response)
		//  curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'rsa_rc4_128_sha');
		curl_setopt($ch, CURLOPT_POST, 1); //Regular post

		$authtoken = "6400a60b24d2fcdeeb656468e80e780b";
		$xmlData = $xml_in;
		$xmlData = urlencode($xmlData);
		
		$query = "newFormat=1&wfTrigger=true&authtoken={$authtoken}&scope=crmapi&xmlData={$xmlData}";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.
		$response = curl_exec($ch);
		curl_close($ch);
		
		//print_r($response);
		$simple = $response;
		
		$p = xml_parser_create();
		xml_parse_into_struct($p, $simple, $vals, $index);
		xml_parser_free($p);
		
	//	echo "Index array\n";
	////	print_r($index);
	//	echo "\nVals array\n";
	//	print_r($vals);
		
		if (!isset($vals[4]['value']))
		{
			$GLOBALS["global_error_message"] = sprintf("Could not create CONTRIBUTOR  %s in zoho, please chack if it is in zoho.\n Error = %s", $provider, $GLOBALS["product"], $response);
			exit;
		};
		
		//printf("%s\n", $vals[4]['value']);
		return ($vals[4]['value']);
	}
}