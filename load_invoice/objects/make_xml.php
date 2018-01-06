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
      
  function create_invoice_xml( $invoice, $lineitems, $potential_id, $potential_account_id, $order ) {

    printf("\n\nMAKE_XML:INVOICE\n---------\n\n%s\n", print_r($invoice,1));
    printf("\n\nMAKE_XML:ORDER\n---------\n\n%s\n", print_r($order,1));

    $xmlstr = "<Invoices></Invoices>";

    // create the SimpleXMLElement object with an empty <book> element
    $xml = new SimpleXMLElement($xmlstr);

    // add some child nodes

    $row = $xml->addChild("row");
    $row->addAttribute("no", "1");

//    <FL val="Subject">26468140</FL>
    

//
// Line items start.
// 

// 
//             [orderid] => 26468252
//             [username] => freeflowfun
//             [email_address] => freeflowfun@hotmail.com
//             [order_total] => 250.00
//             [delivery_fee] => 0.00
//             [license_subtotal] => 250.00
//             [clipcount_discount] => 0
//             [special_instructions] => test
//             [additional_notes] => 
//             [payment_type] => Check

//             [ship_to_name] => Donna Linda
//             [ship_to_company] => NF
//             [ship_to_street1] => 810 Cannery Row
//             [ship_to_street2] => 
//             [ship_to_city] => Monterey
//             [ship_to_state] => CA
//             [ship_to_postal_code] => 93940
//             [ship_to_country] => USA
//             [ship_to_phone] => 831.375.2313

//             [ftp_username] => 
//             [ftp_password] => 
//             [production_title] => test
//             [production_description] => test
//             [license_use_rm] => Distribution of News in all media, excluding Feature Film; World In Perpetuity.
//             [restrictions] => No Advertising or Promotion.
//             [license_category] => Television
//             [license_use_rf] => 
//             [imported_to_filemaker] => No
// 

// 
// <Invoices>
//   <row no="1">
//     <FL val="Subject">26468140</FL>

    $FL = $row->addChild("FL", "". $invoice['orderid'] ."");
    $FL->addAttribute("val", "Subject");


//     <FL val="License Notes">Clips may be used for a single edit in a single Production. If additional seconds are used then Footage Search must be notified of, and give ap
// proval for, the final declaration prior to airing or other use of the additional seconds.</FL>
     $FL = $row->addChild("FL", "".  htmlentities($invoice['additional_notes'])."");
     $FL->addAttribute("val", "License Notes");

//     <FL val="Billing City">Minneapolis</FL>
//             [bill_to_city] => Monterey
    $FL = $row->addChild("FL", "". $invoice['bill_to_city']."");
    $FL->addAttribute("val", "Billing City");


//     <FL val="Billing Company">BBDO Minneapolis</FL>
//             [bill_to_company] => NF
    $FL = $row->addChild("FL", "". $invoice['bill_to_company']."");
    $FL->addAttribute("val", "Billing Company");

//     <FL val="Billing Country">United States</FL>
//             [bill_to_country] => USA
    $FL = $row->addChild("FL", "". $invoice['bill_to_country']."");
    $FL->addAttribute("val", "Billing Country");

//     <FL val="Billing Name">Charlynn Arends</FL>
//             [bill_to_name] => Donna Linda
    $FL = $row->addChild("FL", "". $invoice['bill_to_name']."");
    $FL->addAttribute("val", "Billing Name");


//     <FL val="Billing Code">55402</FL>
//             [bill_to_postal_code] => 93940
    $FL = $row->addChild("FL", "". $invoice['bill_to_postal_code']."");
    $FL->addAttribute("val", "Billing Code");

//     <FL val="Billing State">MN</FL>
//             [bill_to_state] => CA
    $FL = $row->addChild("FL", "". $invoice['bill_to_state']."");
    $FL->addAttribute("val", "Billing State");

//     <FL val="Billing Street">150 South 5th Street</FL>
//             [bill_to_street1] => 810 Cannery Row
    $FL = $row->addChild("FL", "". $invoice['bill_to_street1']."");
    $FL->addAttribute("val", "Billing Street");


//     <FL val="Billing Street 2">Suite 3500</FL>
//             [bill_to_street2] => 

    $FL = $row->addChild("FL", "". $invoice['bill_to_street2']."");
    $FL->addAttribute("val", "Billing Street 2");


// Needed??
//             [bill_to_phone] => 831.375.2313   


//     <FL val="License Fee Subtotal">1130</FL>
     $FL = $row->addChild("FL", "". $invoice['license_subtotal']."");
    $FL->addAttribute("val", "License Fee Subtotal");

//     <FL val="Invoice Date">2014-12-09</FL>

      $dt = strtotime($invoice['invoice_date']);
      $newformat = date('Y-m-d',$dt);

      printf("Converted time %s to %s\n", $invoice['invoice_date'],  $newformat );

     $FL = $row->addChild("FL", "". $newformat."");
     $FL->addAttribute("val", "Invoice Date");


//     <FL val="Invoice Status"></FL>
//     $FL = $row->addChild("FL", "". $invoice['']."");
//    $FL->addAttribute("val", "");

//     <FL val="Invoice Type"></FL>
     $FL = $row->addChild("FL", "". 'Master'."");
    $FL->addAttribute("val", "Invoice Type");

//     <FL val="Service Fee"></FL>
     $FL = $row->addChild("FL", "". $invoice['delivery_fee']."");
    $FL->addAttribute("val", "Service Fee");


//     <FL val="License RM">Distribution of an Advertisement through the Internet and Mobile devices; Small Audience (Less than 5 Million Ad Views) 1 Year.</FL>
    $FL = $row->addChild("FL", "". $invoice['license_use_rm']."");
    $FL->addAttribute("val", "License RM");

//
     $FL = $row->addChild("FL", "". htmlentities($invoice['additional_notes'])."");
     $FL->addAttribute("val", "License Notes");


//     <FL val="License RF"></FL>
     $FL = $row->addChild("FL", "". $invoice['license_use_rf']."");
    $FL->addAttribute("val", "License RF");

    //     <FL val="License RM NFlix"></FL>
    $FL = $row->addChild("FL", "". $invoice['license_use_rm_nflix']."");
    $FL->addAttribute("val", "License RM NFlix");

//     <FL val="License RM Category">Advertising</FL>
       
     $FL = $row->addChild("FL", "". $invoice['license_category']."");
    $FL->addAttribute("val", "License RM Category");



//     <FL val="Licensee Company">BBDO Minneapolis</FL>
//             [licensee_company] => NF
    $FL = $row->addChild("FL", "". $invoice['licensee_company']."");
    $FL->addAttribute("val", "Licensee Company");


//     <FL val="Licensee Country">United States</FL>
//             [licensee_country] => USA
     $FL = $row->addChild("FL", "". $invoice['licensee_country']."");
     $FL->addAttribute("val", "Licensee Country");


//     <FL val="Licensee Name">Charlynn Arends</FL>
//             [licensee_name] => Donna Linda
     $FL = $row->addChild("FL", "". $invoice['licensee_name']."");
     $FL->addAttribute("val", "Licensee Name");


//     <FL val="Licensee State">MN</FL>
//             [licensee_state] => CA
     $FL = $row->addChild("FL", "". $invoice['licensee_state']."");
     $FL->addAttribute("val", "Licensee State");


//     <FL val="Licensee Street1">150 South 5th Street</FL>
//             [licensee_street1] => 810 Cannery Row
     $FL = $row->addChild("FL", "". $invoice['licensee_street1']."");
     $FL->addAttribute("val", "Licensee Street1");


//     <FL val="Licensee Street2">Suite 3500</FL>
//             [licensee_street2] => 
     $FL = $row->addChild("FL", "". $invoice['licensee_street2']."");
     $FL->addAttribute("val", "Licensee Street2");


	$FL = $row->addChild("FL", "". $invoice['licensee_city']."");
	$FL->addAttribute("val", "Licensee City");


//     <FL val="Licensee City">Minneapolis</FL>
//     $FL = $row->addChild("FL", "". $invoice['']."");
//    $FL->addAttribute("val", "");


//             [licensee_city] => Monterey
//             [licensee_postal_code] => 93940
//             [licensee_phone] => 831.375.2313


//     <FL val="License Agreement">Online Accepted</FL>
     $FL = $row->addChild("FL", "". "Online Accepted"."");
     $FL->addAttribute("val", "License Agreement");


//     <FL val="Production Description">Purchase on behalf of Hormel Foods Corporation, Compleates for use in online spots for one year.Job#:  PHOCOMI55011PO #:  PendingClip:
//     $FL = $row->addChild("FL", "". $order['production_description']."");
     $FL = $row->addChild("FL", "". htmlentities($invoice['production_description'])."");
     $FL->addAttribute("val", "Production Description");

     printf("make_xml.php: Adding Production Description description %s\n", $invoice['production_description']);


//     <FL val="Production Title">Top of The Food Chain</FL>
     $FL = $row->addChild("FL", "". htmlentities($invoice['production_title'])."");
     $FL->addAttribute("val", "Production Title");

//     <FL val="Production URL"></FL>
//     $FL = $row->addChild("FL", "". $invoice['']."");
//    $FL->addAttribute("val", "");


//     <FL val="License Restrictions"></FL>
//     $FL = $row->addChild("FL", "". $invoice['restrictions']."");
     $FL = $row->addChild("FL", "". $order['restrictions']."");
    $FL->addAttribute("val", "License Restrictions");

     printf("make_xml.php: Adding License Restrictions :  %s\n", $order['restrictions']);


//     <FL val="SFR Potential ID">2014-12-Chipmunk-Penguins</FL>
    $FL = $row->addChild("FL", "". $potential_id."");
    $FL->addAttribute("val", "SFR Potential ID_ID");
//SFR Potential ID

//     <FL val="Special Instructions"></FL>// 
     $FL = $row->addChild("FL", "". $invoice['special_instructions']."");
    $FL->addAttribute("val", "Special Instructions");


////    <FL val="Account Name">BBDO Minneapolis</FL>
//     $FL = $row->addChild("FL", "". $invoice['bill_to_company']."");
//    $FL->addAttribute("val", "Account Name");

//    <FL val="ACCOUNTID">1279151000011002037</FL>
      $FL = $row->addChild("FL", "". $potential_account_id."");
      $FL->addAttribute("val", "ACCOUNTID");

//     <FL val="Contact Name">Charlynn Arends</FL>
     $FL = $row->addChild("FL", "". $invoice{'licensee_name'}."");
     $FL->addAttribute("val", "Contact Name");

//     <FL val="Username">Charlynn14</FL>
     $FL = $row->addChild("FL", "". $invoice['username']."");
     $FL->addAttribute("val", "Username");


//     <FL val="Adjustment"></FL>
     $FL = $row->addChild("FL", "". $invoice['delivery_fee']."");
     $FL->addAttribute("val", "Adjustment");

//    <FL val="Licensee Code">55402</FL>
     $FL = $row->addChild("FL", "". $invoice['licensee_postal_code']."");
     $FL->addAttribute("val", "Licensee Code");

//     <FL val="Sub Total">1130</FL>
     $FL = $row->addChild("FL", "". $invoice['license_subtotal']."");
    $FL->addAttribute("val", "Sub Total");


//     <FL val="Grand Total">1130</FL>
     $FL = $row->addChild("FL", "". $invoice['order_total']."");
    $FL->addAttribute("val", "Grand Total");


//    <FL val="Product Details">
//      <product no="1">
//        <FL val="Product Id">1279151000007774005</FL>
//        <FL val="List Price">690</FL>
//        <FL val="Product Name">26468140_GG003_0017</FL>
//        <FL val="Quantity">1</FL>
//        <FL val="Net Total">690.00</FL>
//        <FL val="Total">690</FL>
//        <FL val="Discount">0</FL>
//        <FL val="Product Description"></FL>
//
//
//      <product no="2">
//        <FL val="Product Id">1279151000007783001</FL>

    $FL = $row->addChild("FL");
    $FL->addAttribute("val", "Product Details");


    $line_counter = 0;
    foreach($lineitems as $lineitem ) {

        printf("%s\n", print_r($lineitem));

        $line_counter += 1;

        $line_item = $FL->addChild("product");
	$line_item->addAttribute("no", $line_counter);

	$FL2 = $line_item->addChild("FL", "". $lineitem['Product Id']."");
	$FL2->addAttribute("val", "Product Id");

	$FL2 = $line_item->addChild("FL", "". $lineitem["license_cost"]."");
	$FL2->addAttribute("val", "List Price");

	$FL2 = $line_item->addChild("FL", "". $lineitem['Product Name']."");
	$FL2->addAttribute("val", "Product Name");

	$FL2 = $line_item->addChild("FL", "". '1' ."");
	$FL2->addAttribute("val", "Quantity");

//	$FL2 = $line_item->addChild("FL", "". $lineitem['discounted_license_fee']."");
//	$FL2->addAttribute("val", "Net Total");

// license_cost (This is the Amount field) 
	$FL2 = $line_item->addChild("FL", "". $lineitem["license_cost"] ."");
	$FL2->addAttribute("val", "Total");


	$FL2 = $line_item->addChild("FL", "". $lineitem['discounted_license_fee']."");
	$FL2->addAttribute("val", "Net Total");

	$discounted_amount = $lineitem["license_cost"]  - $lineitem['discounted_license_fee'];
	$FL2 = $line_item->addChild("FL", "". $discounted_amount."");
	$FL2->addAttribute("val", "Discount");

	$FL2 = $line_item->addChild("FL", "".  $lineitem['discounted_license_fee']."");

	$FL2->addAttribute("val", "Total After Discount");

	$FL2 = $line_item->addChild("FL", "". $lineitem['clip_description']."");
	$FL2->addAttribute("val", "Product Description");


    }


//
// Line items END
// 

    // add some more child nodes
    $xml_out =  $xml->asXML();

    $remove_string = '<?xml version="1.0"?>'. "\n";
    $xml_out = str_replace($remove_string, "", $xml_out);

    return($xml_out);

  
  }

   function create_line_xml($lineitem)
   {


// 
// <Products>
//   <row no="1">
//     <FL val="Product Name">26468241_LMI004_0306</FL>
//     <FL val="Quantity">1.0</FL>
//     <FL val="Unit Price">200</FL>
//     <FL val="Royalty Due"></FL>
//     <FL val="License Fee After Discount">200</FL>
//     <FL val="Product Code">LMI004_0306</FL>
//     <FL val="Contributor Credits">Michael Lance Milbrand / Footage Search</FL>
//     <FL val="License Discount">0</FL>
//     <FL val="Invoice ID_ID">26468241</FL>
//     <FL val="Invoice ID">26468241</FL>
//     <FL val="Invoice Numer">26468241</FL>
//     <FL val="Vendor Name">LMI</FL>
//     <FL val="Royalty Tax Percentage"></FL>
//     <FL val="Product Deliverable">ProRes422HQ (Cross Platform) 1080p30</FL>
//     <FL val="Royalty Date Paid"></FL>
//     <FL val="License Product Duration">5</FL>
//     <FL val="VENDORID">1279151000003727970</FL>
//     <FL val="Description">Sandhill Crane, Migrating, Visits Nature Park, Spreads Wings</FL>
//     <FL val="Royalty Paid Check"></FL>
//     <FL val="Commission Percent">0</FL>
//     <FL val="Video Image URL">http://s3.footagesearch.com/stills/LMI004_0306.jpg</FL>
//     <FL val="License Category">Rights Managed</FL>
//   </row>
// </Products>
// 

//  [product] => BF79_033
//    [clip_description] => Time Lapse Daisy Opening, Blooming
//    [orderid] => 26468250
//    [total_clip_cost] => 250
//    [license_type] => Rights Managed
//    [delivery_cost] => 0.00
//    [delivery_option] => 14
//    [delivery_format_text] => ProRes4444 (Cross Platform)  23.98 FPS Progressive
//    [delivery_method] => 
//    [delivery_frame_rate] => 
//    [digital_file_format] => ProRes4444 (Cross Platform)
//    [source_format] => 35mm Film
//    [master_format] => 
//    [purchase_duration] => 5
//    [provider_credits] => BlackLight Films
//    [provider_prefix] => BF
//    [discount] => 0.00
//    [license_cost] => 50
//    [discounted_license_fee] => 50

   
    $xmlstr = "<Products></Products>";

    // create the SimpleXMLElement object with an empty <book> element
    $xml = new SimpleXMLElement($xmlstr);

    // add some child nodes

    $row = $xml->addChild("row");
    $row->addAttribute("no", "1");

    $lineitem[""] = "";


//     <FL val="Product Name">26468241_LMI004_0306</FL>
    $FL = $row->addChild("FL", "".  $lineitem["Product Name"]."");
    $FL->addAttribute("val", "Product Name");

//     <FL val="Quantity">1.0</FL>
    $FL = $row->addChild("FL", "". "1.0"."");
    $FL->addAttribute("val", "Quantity");

//     <FL val="Unit Price">200</FL>
//    $FL = $row->addChild("FL", "". $lineitem["total_clip_cost"] ."");
    $FL = $row->addChild("FL", "". $lineitem["license_cost"]."" );
    $FL->addAttribute("val", "Unit Price");

//     <FL val="Royalty Due"></FL>
    $FL = $row->addChild("FL", "". $lineitem["product"]."");
    $FL->addAttribute("val", "Royalty Due");

//     <FL val="License Fee After Discount">200</FL>
//    $FL = $row->addChild("FL", "". $lineitem["discounted_license_fee"] ."");
//    $FL->addAttribute("val", "License Fee After Discount");

//     <FL val="Product Code">LMI004_0306</FL>
    $FL = $row->addChild("FL", "". $lineitem["product"]."");
    $FL->addAttribute("val", "Product Code");


//     <FL val="Contributor Credits">Michael Lance Milbrand / Footage Search</FL>
    $FL = $row->addChild("FL", "". $lineitem["provider_credits"] ."");
    $FL->addAttribute("val", "Contributor Credits");

//     <FL val="License Discount">0</FL>
    $FL = $row->addChild("FL", "". $lineitem['discount']."" );
    $FL->addAttribute("val", "License Discount");


//     <FL val="Invoice ID_ID">26468241</FL>
    $FL = $row->addChild("FL", "".  $lineitem["orderid"]."" );
    $FL->addAttribute("val", "Invoice ID_ID");


//     <FL val="Invoice ID">26468241</FL>
    $FL = $row->addChild("FL", "".  $lineitem["orderid"] ."" );
    $FL->addAttribute("val", "Invoice ID");


//     <FL val="Invoice Numer">26468241</FL>
    $FL = $row->addChild("FL", "".   $lineitem["orderid"] ."");
    $FL->addAttribute("val", "Invoice Numer");


//     <FL val="Vendor Name">LMI</FL>
    $FL = $row->addChild("FL", "". $lineitem["provider_credits"] ."");
    $FL->addAttribute("val", "Vendor Name");


//     <FL val="Royalty Tax Percentage"></FL>
    $FL = $row->addChild("FL", "". $lineitem[""] ."");
    $FL->addAttribute("val", "Royalty Tax Percentage");

//     <FL val="Product Deliverable">ProRes422HQ (Cross Platform) 1080p30</FL>
    $FL = $row->addChild("FL", "". $lineitem["delivery_format_text"] ."");
    $FL->addAttribute("val", "Product Deliverable");

//     <FL val="Royalty Date Paid"></FL>
    $FL = $row->addChild("FL", "". $lineitem[""] ."");
    $FL->addAttribute("val", "Royalty Date Paid");

//     <FL val="License Product Duration">5</FL>
    $FL = $row->addChild("FL", "". sprintf("%d", $lineitem["purchase_duration"])."" );
    $FL->addAttribute("val", "License Product Duration");

//     <FL val="VENDORID">1279151000003727970</FL>
    $FL = $row->addChild("FL", "". $lineitem["VENDORID"]."" );
    $FL->addAttribute("val", "VENDORID");

//     <FL val="Description">Sandhill Crane, Migrating, Visits Nature Park, Spreads Wings</FL>
    $FL = $row->addChild("FL", "". $lineitem["clip_description"] ."");
    $FL->addAttribute("val", "Description");

//     <FL val="Royalty Paid Check"></FL>
    $FL = $row->addChild("FL", "". $lineitem[""] ."");
    $FL->addAttribute("val", "Royalty Paid Check");

//     <FL val="Commission Percent">0</FL>
    $FL = $row->addChild("FL", "". $lineitem[""] ."");
    $FL->addAttribute("val", "Commission Percent");

//     <FL val="Video Image URL">http://s3.footagesearch.com/stills/LMI004_0306.jpg</FL>
//    $FL = $row->addChild("FL", "". sprintf("http://s3.footagesearch.com/stills/%s.jpg", $lineitem["product"] )."");
    $FL = $row->addChild("FL", "".  $lineitem["Video_URL"]."");
    $FL->addAttribute("val", "Video Image URL");

//     <FL val="License Category">Rights Managed</FL>
    $FL = $row->addChild("FL", "". $lineitem["license_type"] ."");
    $FL->addAttribute("val", "License Category");


    // add some more child nodes
    $xml_out =  $xml->asXML();

    $remove_string = '<?xml version="1.0"?>'. "\n";
    $xml_out = str_replace($remove_string, "", $xml_out);

    return($xml_out);
   }


} // end of class wp_db