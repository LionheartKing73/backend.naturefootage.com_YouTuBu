<?php

# -- Invoices 
#
#
# 26468251
# 26468250
# 26468249
# 26468248
#
#



class make_xml {


   function create_lead_xml($lineitem)
   {


// 
// <Leads>
// <row no="1">
// <FL val="Lead Source">Web Download</FL>
// <FL val="Company">Your Company</FL>
// <FL val="First Name">Hannah</FL>
// <FL val="Last Name">Smith</FL>
// <FL val="Email">testing@testing.com</FL>
// <FL val="Title">Manager</FL>
// <FL val="Phone">1234567890</FL>
// <FL val="Home Phone">0987654321</FL>
// <FL val="Other Phone">1212211212</FL>
// <FL val="Fax">02927272626</FL>
// <FL val="Mobile">292827622</FL>
// </row>
// </Leads>
// 
// 

// User:
//
//    [id] => 1006345
//    [group_id] => 4
//    [provider_id] => 354
//    [fname] => Mow
//    [lname] => Cow
//    [site] => 
//    [email] => mowcow2@naturefootage.com
//    [login] => mowcow2
//    [active] => 1
//    [ctime] => 2015-01-03 22:22:51
//    [last_login] => 2015-01-03 22:24:48
//    [token] => 6a0605362d7ed7b0f6836bd9ff070b67
//    [activation_key] => 
//    [views] => 0
//    [prefix] => 
//    [exclusive] => 0
//    [dir] => 
//    [register_frontend] => 46
//    [provider_credits] => 
//    [storage_account] => 0
//    [storage_account_changed] => 0
//    [imported] => 0
//    [provider_imported] => 0
//    [provider_intent] => 0
//    [sync] => 1
//    [company_name] => Mow Cow Inc
//    [phone] => 999-999-9999
//    [country] => Peruloo
//    [primary_interest] => Purchase Stock Footage
//    [referral] => Search Engine or Directory
//    [email_updates] => 1
//

    $xmlstr = '<?xml version="1.0" encoding="UTF-8"?><Leads></Leads>';

    // create the SimpleXMLElement object with an empty <book> element
    $xml = new SimpleXMLElement($xmlstr);

    // add some child nodes

    $row = $xml->addChild("row");
    $row->addAttribute("no", "1");

    $lineitem[""] = "";

// <FL val="Lead Source">Web Download</FL>
    $FL = $row->addChild("FL", $lineitem["referral"]);
    $FL->addAttribute("val", "Lead Source");

    $xml_out =  $xml->asXML();

// <FL val="Company">Your Company</FL>
    $FL = $row->addChild("FL", $lineitem["company_name"]);
    $FL->addAttribute("val", "Company");

// <FL val="First Name">Hannah</FL>
//    $FL = $row->addChild("FL", urlencode($lineitem["fname"]));
    $FL = $row->addChild("FL", $lineitem["fname"]);
    $FL->addAttribute("val", "First Name");

    $xml_out =  $xml->asXML();

// <FL val="Last Name">Smith</FL>
    $FL = $row->addChild("FL",  $lineitem["lname"]  );
    $FL->addAttribute("val", "Last Name");

    $xml_out =  $xml->asXML();

// <FL val="Email">testing@testing.com</FL>
    $FL = $row->addChild("FL", $lineitem["email"]);
    $FL->addAttribute("val", "Email");

// <FL val="Title">Manager</FL>
    $FL = $row->addChild("FL", $lineitem[""]);
    $FL->addAttribute("val", "Title");

// <FL val="Phone">1234567890</FL>
    $FL = $row->addChild("FL", $lineitem["phone"]);
    $FL->addAttribute("val", "Phone");

// <FL val="Home Phone">0987654321</FL>
    $FL = $row->addChild("FL", $lineitem[""]);
    $FL->addAttribute("val", "Home Phone");

    $xml_out =  $xml->asXML();

// <FL val="Login Password">asdfasdf</FL>
    $FL = $row->addChild("FL", $lineitem["password"]);
    $FL->addAttribute("val", "Login Password");

// <FL val="Login Password">asdfasdf</FL>
    $FL = $row->addChild("FL", $lineitem["login"]);
    $FL->addAttribute("val", "Login Username");

    $xml_out =  $xml->asXML();

// <FL val="Other Phone">1212211212</FL>
    $FL = $row->addChild("FL", $lineitem[""]);
    $FL->addAttribute("val", "Other Phone");

// <FL val="Fax">02927272626</FL>
    $FL = $row->addChild("FL", $lineitem[""]);
    $FL->addAttribute("val", "Fax");

// <FL val="Mobile">292827622</FL>
    $FL = $row->addChild("FL", $lineitem[""]);
    $FL->addAttribute("val", "Mobile");

    $xml_out =  $xml->asXML();

    if ( $lineitem["email_updates"] ) {
      $FL = $row->addChild("FL", 'False');
      $FL->addAttribute("val", "EMC Opt Out");    
    } else { 
      $FL = $row->addChild("FL", 'True');
      $FL->addAttribute("val", "EMC Opt Out");    
    }

    $xml_out =  $xml->asXML();

    $FL = $row->addChild("FL", $lineitem["country"]);
    $FL->addAttribute("val", "Country");

    $xml_out =  $xml->asXML();

    $FL = $row->addChild("FL", $lineitem["primary_interest"]);
    $FL->addAttribute("val", "Category Interest");

    $xml_out =  $xml->asXML();

    //
    //
    //
    // Generate actual XML
    //
    //
    //

    $xml_out =  $xml->asXML();

    $remove_string = '<?xml version="1.0"?>'. "\n";
    $xml_out = str_replace($remove_string, "", $xml_out);

    return($xml_out);
   }

      



} // end of class wp_db