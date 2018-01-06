<html> 
<style>
  body, table {font-family:tahoma,verdana; font-size: 8pt;}
</style>
<body>

<b>Confirmation message</b><br><br>

Dear <?=$fname.' '.$lname?>. <br>
Thank you for registering on our website.  
<br><br>

<table border=0 cellpadding=0 cellspacing=0>
 <tr><td valign=top height=20 width=120>Username: </td><td valign=top><?=$login?></td></tr>
 <tr><td valign=top height=20>Password:</td><td valign=top><?=$password?><br><br></td></tr>
</table>
 
Now that you are registered, you can create bins, download footage and purchase online. If you have any 
queries, please don't hesitate to contact us by e-mail at 
<a href="mailto:<?=$this->config->item('vendor_email')?>"><?=$this->config->item('vendor_email')?></a>,
or by phone on <?=$this->config->item('vendor_phone')?>.
<br><br>
Kind regards,<br>
<?=$this->config->item('vendor_name')?> Library team
</body>
</html>