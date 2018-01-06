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


// class to connect to the wordpress database and get an invoice for processing.

class wp_db {


    var $host = 'master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com';
    var $db_name = "fsmaster-production";
    var $db_port = 3306;
    var $username = "fsmaster";
    var $password = "FSdbm6512";
/*

//sanjay local
    var $host = 'localhost';
    var $db_name = "nature_footage";
    var $db_port = 3306;
    var $username = "root";
    var $password = "";

*/
    var $dbh = 0;

   // http://php.net/manual/en/mysqli.quickstart.connections.php

   function connect()
   {
       $this->dbh =  new mysqli($this->host, $this->username, $this->password, $this->db_name, $this->db_port);

       if ($this->dbh->connect_errno) {
           echo "Failed to connect to MySQL: (" . $this->dbh->connect_errno . ") " . $this->dbh->connect_error;
       }
   }


   // http://php.net/manual/en/mysqli.quickstart.prepared-statements.php
   //  http://php.net/manual/en/mysqli.quickstart.stored-procedures.php

   function get_invoice($invoice_num)
   {

   printf("1\n");

     /* Prepared statement, stage 1: prepare use version 2 */
     if (!($stmt = $this->dbh->prepare("call filemaker_order_information3(?)"))) {
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
         return;
     }

   printf("2\n");

     if (!$stmt->bind_param("i", $invoice_num)) {
         echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
         return;
     }

   printf("3\n");

     if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         return;
     }

     $stmt->store_result();

   printf("4\n");


    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field())
    {
        $params[] = &$row[$field->name];
    }

   call_user_func_array(array($stmt, 'bind_result'), $params);

    while ($stmt->fetch()) {
        foreach($row as $key => $val)
        {
            $c[$key] = $val;
        }
        $result[] = $c;

    }


   printf("5\n");

  $stmt->free_result();
 $stmt->close();

   printf("5\n");

     return($result);
   }



   // http://php.net/manual/en/mysqli.quickstart.prepared-statements.php
   //  http://php.net/manual/en/mysqli.quickstart.stored-procedures.php

   function get_invoice_lines($invoice_num)
   {

   printf("1\n");

     /* Prepared statement, stage 1: prepare */
     if (!($stmt = $this->dbh->prepare("call zoho_order_lineitems(?)"))) {
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
	 return;
     }

   printf("2\n");

     if (!$stmt->bind_param("i", $invoice_num)) {
         echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	 return;
     }

   printf("3\n");

     if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

   printf("4\n");


    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field())
    {
        $params[] = &$row[$field->name];
    }

   call_user_func_array(array($stmt, 'bind_result'), $params);

    while ($stmt->fetch()) {
        foreach($row as $key => $val)
        {
            $c[$key] = $val;
        }
        $result[] = $c;
    }


   printf("5\n");

  $stmt->free_result();
   $stmt->close();

   printf("6\n");

   return($result);
   }





   // http://php.net/manual/en/mysqli.quickstart.prepared-statements.php
   //  http://php.net/manual/en/mysqli.quickstart.stored-procedures.php

   function get_lib_order($invoice_num)
   {

   printf("1\n");

     /* Prepared statement, stage 1: prepare */
     if (!($stmt = $this->dbh->prepare("select * from lib_orders where id = ?"))) {
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
         exit;
     }

   printf("2\n");

     if (!$stmt->bind_param("i", $invoice_num)) {
         echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

   printf("3\n");

     if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

   printf("4\n");


    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field())
    {
        $params[] = &$row[$field->name];
    }

   call_user_func_array(array($stmt, 'bind_result'), $params);

    while ($stmt->fetch()) {
        foreach($row as $key => $val)
        {
            $c[$key] = $val;
        }
        $result[] = $c;
    }


   printf("5\n");

  $stmt->free_result();
   $stmt->close();

   printf("6\n");

   return($result);
   }


  function get_lib_clips_res($prod_code)
   {


  // printf("1\n");

     /* Prepared statement, stage 1: prepare */
	 
     if (!($stmt = $this->dbh->prepare("SELECT * FROM lib_clips_res WHERE resource = 'jpg' and clip_id in (SELECT id FROM lib_clips WHERE CODE = '$prod_code')"))) {
         echo "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
         exit;
     }

  /*
   printf("2\n");

     if (!$stmt->bind_param("i", $prod_code)) {
         echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }
*/
  // printf("3\n");

     if (!$stmt->execute()) {
         echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
         exit;
     }

  // printf("4\n");


    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field())
    {
        $params[] = &$row[$field->name];
    }

   call_user_func_array(array($stmt, 'bind_result'), $params);

    while ($stmt->fetch()) {
        foreach($row as $key => $val)
        {
            $c[$key] = $val;
        }
        $result[] = $c;
    }


   printf("5\n");

  $stmt->free_result();
   $stmt->close();

   printf("6\n");

   return($result);
   }

} // end of class wp_db
