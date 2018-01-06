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


      // Pass by value so will 

   function set_product_id(&$lineitem) {

   $xml =  $this->create_product_xml($lineitem);
   printf(" invoice_in for read: %s\n", $xml );
   printf(" xml for read: %s\n", $lineitem );

   }


   // http://php.net/manual/en/class.simplexmlelement.php
   function create_line_xml(&$lineitem)
   {
   
    $xmlstr = "<?xml version='1.0' ?>\n".
              // optionally you can specify a xml-stylesheet for presenting the results. just uncoment the following line and change the stylesheet name.
              /* "<?xml-stylesheet type='text/xsl' href='xml_style.xsl' ?>\n". */
              "<book></book>";

    // create the SimpleXMLElement object with an empty <book> element
    $xml = new SimpleXMLElement($xmlstr);

    // add some child nodes
    $xml->addChild("title", "Title of my book");
    $xml->addChild("abstract", "My book is about learning to work with SimpleXMLElement");

    // add some more child nodes
    $chapter1 = $xml->addChild("chapter_1");
    $xml_out =  $xml->asXML();

    return($xml_out);
   

   }


} // end of class wp_db