<html> 
<style>
  body, table {font-family:tahoma,verdana; font-size: 8pt;}
</style>
<body>
<b>Corporate Account Application</b><br><br>
<table border=0 cellpadding=0 cellspacing=0>
 <tr><td valign=top height=20 width=100>Client name:</td><td valign=top><?=$fname.' '.$lname?></td></tr>
 <tr><td valign=top height=20>Client email:</td><td valign=top><?=$email?></td></tr>
</table>
<br><br>
Kind regards,<br>
<?=$this->config->item('vendor_name')?> Library team
</body>
</html>