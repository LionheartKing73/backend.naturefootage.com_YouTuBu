<html> 
<style>
  body, table {font-family:tahoma,verdana; font-size: 8pt;}
</style>
<body>

Dear friend!<br>
<?=$fromname?> has sent you a bin from the <?=$this->config->item('vendor_name')?> Library website. To view the image(s), please 
click on the following link:<br><br>

<?if($links):?>

  <?foreach($links as $link):?>
  <a href="<?=$link['url']?>"><?=$link['title'].' ('.$link['code'].')'?></a><br>
  <?endforeach;?>

<?endif;?>

<br> 
<?if($message):?>Additional comments: <?=$message?><?endif;?>

<br><br>
Kind regards,<br>
<?=$this->config->item('vendor_name')?> Library team
</body>
</html>
