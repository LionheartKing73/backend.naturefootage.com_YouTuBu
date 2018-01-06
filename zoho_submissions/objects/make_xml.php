<?php



class make_xml {
      
 
   function create_line_xml($lineitem)
   {
 
    $xmlstr = "<CustomModule2></CustomModule2>";

    // create the SimpleXMLElement object with an empty <CustomModule2> element
    $xml = new SimpleXMLElement($xmlstr);

    // add some child nodes

    $row = $xml->addChild("row");
    $row->addAttribute("no", "1");
  

    $FL = $row->addChild("FL", "".  $lineitem["Contributor Name"]."");
    $FL->addAttribute("val", "Contributor Name");

   
    $FL = $row->addChild("FL", "". $lineitem["Submission Name"]."" );
    $FL->addAttribute("val", "CustomModule2 Name");

    $FL = $row->addChild("FL", "". $lineitem["Date Submission"]."");
    $FL->addAttribute("val", "Date Submission");
	
	$FL = $row->addChild("FL", "". $lineitem["Date Submission"]."");
    $FL->addAttribute("val", "Date Online");

    $FL = $row->addChild("FL", "". $lineitem["Drive Submission"]."");
    $FL->addAttribute("val", "Drive Submission");


    $FL = $row->addChild("FL", "". $lineitem["Status"] ."");
    $FL->addAttribute("val", "Status");
	
	 $FL = $row->addChild("FL", "". $lineitem["Format Category"] ."");
    $FL->addAttribute("val", "Format Category");
	
	 $FL = $row->addChild("FL", "". $lineitem["Submission Minutes"] ."");
    $FL->addAttribute("val", "Submission Minutes");
	
	 $FL = $row->addChild("FL", "". $lineitem["Submission Number Clips"] ."");
    $FL->addAttribute("val", "Submission Number Clips");


if(	$lineitem["vendor_id"] != "")
{
	 $FL = $row->addChild("FL", "". $lineitem["vendor_id"] ."");
    $FL->addAttribute("val", "Contributor Name_ID");
}

    // add some more child nodes
    $xml_out =  $xml->asXML();

    $remove_string = '<?xml version="1.0"?>'. "\n";
    $xml_out = str_replace($remove_string, "", $xml_out);

    return($xml_out);
   }


} // end of class wp_db