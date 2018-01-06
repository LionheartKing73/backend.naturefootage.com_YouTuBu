<!--
<? foreach ($resArray as $key=>$value) {?>
  <?=$key?>: <?=$value?>

<? } ?>
-->

<form action="/cart/pay" method="POST">
  You are about to pay <?=$resArray['CURRENCYCODE'] . $resArray['AMT']?>
  for <?=$this->config->item('title')?> shopping cart
  via your PayPal account
  <br /><br />
  <input type="submit" value="Pay" />
</form>