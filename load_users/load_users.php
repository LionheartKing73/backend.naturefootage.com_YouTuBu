<?php

if (getenv("environment") !== "production") {
    die ("This should be run only on production!" . PHP_EOL);
}

// https://crm.zoho.com/crm/private/xml/Leads/getRecords?authtoken=6400a60b24d2fcdeeb656468e80e780b&newFormat=2&selectColumns=All&scope=crmapi&id=1279151000008041005

  ini_set('display_startup_errors',1);
  ini_set('display_errors',1);
  error_reporting(-1);

  include "objects/wp_db.php";
  include "objects/zoho_crm.php";
  include "objects/make_xml.php";


###
### Get a new user from the databsae to import if there are any.
###

  $wpdb = new wp_db();
  $wpdb->connect();

  $new_users = $wpdb->get_users_not_imported();
  
  if ( sizeof($new_users) == 0 ) {
    printf("No new users to import, exit\n");
    exit;
  }

  $new_user = $new_users[0];
  $new_user = $new_user['login'];

  if ( $new_user == '') {
    printf("New user had a blank name, exit\n");
    exit;
  }



###
### Make a database call to get user details.
###

   printf("Loading user %s\n", $new_user);

   $wpdb2 = new wp_db();
   $wpdb2->connect();
   $user = $wpdb2->get_user($new_user);
   $user = $user[0];

   printf("User:\n%s\n", print_r($user,1));	

   $wpdb2 = new wp_db();
   $wpdb2->connect();
   $user_meta  = $wpdb2->get_user_metadata($user['id']);;

   printf("User Meta:\n%s\n", print_r($user_meta,1));	
   foreach($user_meta as $d1) {
     $user[ $d1['meta_key'] ] = $d1['meta_value'];   		      
   }

   printf("User With Meta:\n%s\n", print_r($user,1));	

   $mxml = new make_xml();
   $xml = $mxml->create_lead_xml($user);
   printf("XML\n%s\n", print_r(htmlentities($xml),1));	

   $crm = new zoho_crm();
   $lead_id = $crm->post_lead($xml);
   printf("CRM created:%s\n", $lead_id);



   $wpdb2 = new wp_db();
   $wpdb2->connect();
   $wpdb2->set_user_imported($user['id']);;

   printf("Done!\n");

