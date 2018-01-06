<html>
<style>
  body, table {font-family:tahoma,verdana; font-size: 8pt;}
</style>
<body>

Hello, <?= $toname ?>!<br><br>
<?= $fromname ?> has sent you a clip bin from the <?=$this->config->item('vendor_name')?> Library.
To view the clip(s), please click on the following link:<br><br>
<a href="<?= $link ?>"><?= $link ?></a>
<br><br>
<? if ($message) { ?>
Additional comments:<br><br>"<?= $message ?>"
<? } ?>

<br><br>
Kind regards,<br><br>
<?=$this->config->item('vendor_name')?> Library team<br>
<a href="mailto:<?=$this->config->item('vendor_email')?>"><?=$this->config->item('vendor_email')?></a>

</body>
</html>