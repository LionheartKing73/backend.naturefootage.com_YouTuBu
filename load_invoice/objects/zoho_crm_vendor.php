<?php
class zoho_crm_vendor

{
	var $vendor_ids;

	 var $vendor_file = "/var/www/fsearch/public_html/vendors.json";

	//var $vendor_file = "vendors.json";
	function load_vendors_from_disk()
	{
		$vendor_json = file_get_contents($this->vendor_file);
		$this->vendor_ids = json_decode($vendor_json, true);
	}

	function save_vendors_to_disk()
	{
		$vendor_json = json_encode($this->vendor_ids, true);
		file_put_contents($this->vendor_file, $vendor_json);
	}

	function get_vendor_id($provider)
	{
		$this->load_vendors_from_disk();
		if (isset($this->vendor_ids[$provider]))
		{
			return ($this->vendor_ids[$provider]);
		}

		$vendor_id = $this->get_vendor_id_from_zoho($provider);
		
		$this->vendor_ids[$provider] = $vendor_id;
		$this->save_vendors_to_disk();
		return ($vendor_id);
	}

	function get_vendor_id_from_zoho($provider)
	{
		$vendor = $provider;
		$ch = curl_init('https://crm.zoho.com/crm/private/xml/Vendors/searchRecords?');
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
		$url = 'https://crm.zoho.com/crm/private/xml/Vendors/searchRecords?';
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.
		$response = curl_exec($ch);

	// Check for errors and display the error message

		if ($errno = curl_errno($ch))
		{
			$error_message = curl_strerror($errno);
			echo "cURL error ({$errno}):\n {$error_message}";
		}

		curl_close($ch);
		//printf("Response:\n%s\n", htmlentities($response));
		//$simple = $response;
		//print_r($response);
		
		$arr = convertXmltoArray($response,1,"attribute");
		echo "<pre>";
		print_r($arr);

		$array_vendor = array();
		$numCounter = 0;
		
		$error= "";
		if(isset($arr["response"]["error"]))
		{
			echo $error= $arr["response"]["error"]["message"]["value"];	
		}
		
		else if(isset($arr["response"]["nodata"]))
		{
			echo $error= "There is no data to show";	
		}
		else
		{
			//Single records output
			if(isset($arr['response']['result']['Vendors']['row']['FL']))
			{
				foreach ($arr['response']['result']['Vendors']['row']['FL'] as $k=>$row) 
				{
							$array_vendor[$numCounter][$row['attr']['val']] = trim($row['value']);
				}
					$numCounter++;
			}
			else
			{
				//multiple records found
				foreach ($arr['response']['result']['Vendors']['row'] as $detail) 
				{
					foreach($detail["FL"] as  $k=> $row)
					{
							$array_vendor[$numCounter][$row['attr']['val']] = trim($row['value']);
					}
					//if($array_vendor[$numCounter]["Vendor Name"] == $vendor) echo $array_vendor[$numCounter]["VENDORID"];
					
					$numCounter++;
				}
				
			}
			echo $error;
			
			if($error == "")
			{
				foreach($array_vendor as $vendor_line)
				{
					if($vendor_line["Vendor Name"] == $vendor) 
					{
						return $vendor_line["VENDORID"];
					}
				}
			}
			else
			{
				$GLOBALS["global_error_message"] = sprintf("Could not get the vendor ID for vendor '%s' on product %s, please chack if it is in zoho.\n Error = %s", $provider, $GLOBALS["product"], $error);
			//	print_r($GLOBALS);
				return 0;
				exit;
			}
		}
	}
}

function get_potential_from_zoho($sfrcode)
	{
		$ch = curl_init('https://crm.zoho.com/crm/private/xml/Potentials/searchRecords?');
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
		$search_condition = sprintf("(SFR ID Auto:%s)", $sfrcode);
		
		$query = "newFormat=2&authtoken={$authtoken}&scope=crmapi&selectColumns=All&criteria={$search_condition}&wfTrigger=true";

		// Check for errors and display the error message
		if ($errno = curl_errno($ch))
		{
			$error_message = curl_strerror($errno);
			echo "cURL error ({$errno}):\n {$error_message}";
		}
		$url = 'https://crm.zoho.com/crm/private/xml/Potentials/searchRecords?';
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.
		$response = curl_exec($ch);

	// Check for errors and display the error message

		if ($errno = curl_errno($ch))
		{
			$error_message = curl_strerror($errno);
			echo "cURL error ({$errno}):\n {$error_message}";
		}

		curl_close($ch);
		//printf("Response:\n%s\n", htmlentities($response));
		//$simple = $response;
		//print_r($response);
		
		$arr = convertXmltoArray($response,1,"attribute");
		//echo "<pre>";
		//print_r($arr);
		//echo "</pre>";

		$array_potential = array();
		$numCounter = 0;
		
		$error= "";
		if(isset($arr["response"]["error"]))
		{
			echo $error= $arr["response"]["error"]["message"]["value"];	
			return 0;
		}
		
		else if(isset($arr["response"]["nodata"]))
		{
			echo $error= "There is no data to show";	
			return 0;
		}
		else
		{
			//Single records output
			if(isset($arr['response']['result']['Potentials']['row']['FL']))
			{
				foreach ($arr['response']['result']['Potentials']['row']['FL'] as $k=>$row) 
				{
							$array_potential[$numCounter][$row['attr']['val']] = trim($row['value']);
				}
					$numCounter++;
			}
			
			//echo $error;
			
			if($error == "")
			{
			    return $array_potential[0];
			}
			else
			{
				$GLOBALS["global_error_message"] = sprintf("Could not get the vendor ID for SFR ID '%s' , please chack if it is in zoho.\n Error = %s", $sfrcode, $error);
			//	print_r($GLOBALS);
				return 0;
				exit;
			}
		}
	}

function convertXmlToArray($contents, $get_attributes=0, $priority = 'tag') { 
    if(!$contents) return array(); 

    if(!function_exists('xml_parser_create')) { 
        //print "'xml_parser_create()' function not found!"; 
        return array(); 
    } 

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create(''); 
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss 
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
    xml_parse_into_struct($parser, trim($contents), $xml_values); 
    xml_parser_free($parser); 

    if(!$xml_values) return;//Hmm... 

    //Initializations 
    $xml_array = array(); 
    $parents = array(); 
    $opened_tags = array(); 
    $arr = array(); 

    $current = &$xml_array; //Refference 

    //Go through the tags. 
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) { 
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope 
        // tag(string), type(string), level(int), attributes(array). 
        extract($data);//We could use the array by itself, but this cooler. 

        $result = array(); 
        $attributes_data = array(); 
         
        if(isset($value)) { 
            if($priority == 'tag') $result = $value; 
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        } 

        //Set the attributes too. 
        if(isset($attributes) and $get_attributes) { 
            foreach($attributes as $attr => $val) { 
                if($priority == 'tag') $attributes_data[$attr] = $val; 
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
            } 
        } 

        //See tag status and do the needed. 
        if($type == "open") {//The starting of the tag '<tag>' 
            $parent[$level-1] = &$current; 
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result; 
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 

                $current = &$current[$tag]; 

            } else { //There was another element with the same tag name 

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                    $repeated_tag_index[$tag.'_'.$level]++; 
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2; 
                     
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                        unset($current[$tag.'_attr']); 
                    } 

                } 
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
                $current = &$current[$tag][$last_item_index]; 
            } 

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
            //See if the key is already taken. 
            if(!isset($current[$tag])) { //New Key 
                $current[$tag] = $result; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array) 
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 

                    // ...push the new element into that array. 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                     
                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; 

                } else { //If it is not an array... 
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1; 
                    if($priority == 'tag' and $get_attributes) { 
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                             
                            $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                            unset($current[$tag.'_attr']); 
                        } 
                         
                        if($attributes_data) { 
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                        } 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                } 
            } 

        } elseif($type == 'close') { //End of tag '</tag>' 
            $current = &$parent[$level-1]; 
        } 
    } 
     
    return ($xml_array); 
	} 
?>