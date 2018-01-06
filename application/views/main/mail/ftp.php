<html> 
<style>
  body, table {font-family:tahoma,verdana; font-size: 8pt;}
</style>
<body>

<b>FTP details</b><br><br>

Dear <?=$fname.' '.$lname?>. 

<br><br>
To upload your content please use ftp details below:<br>
IP: <?=$this->config->item('ftp_host')?> <br>
username: <?=$login?><br>
pass: <?=$password?>
<br><br>

If you have any queries, please don't hesitate to contact us by e-mail at 
<a href="mailto:<?=$this->config->item('vendor_email')?>"><?=$this->config->item('vendor_email')?></a>,
or by phone on <?=$this->config->item('vendor_phone')?>.
<br><br>
Kind regards,<br>
<?=$this->config->item('vendor_name')?> Library team
</body>
</html>