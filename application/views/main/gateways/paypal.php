<form method="POST" action="<?=$pp['site']?>" name="cart">
<table cellspacing="0" cellpadding="0">
<tr><td><img src="data/img/paypal_logo.gif" width="80" height="36"></td>
<td width="10">
  <input type="hidden" name="cmd" value="_xclick">
  <input type="hidden" name="business" value="<?=$pp['vendor']?>">
  <input type="hidden" name="item_name" value="<?=$pp['description']?>">
  <input type="hidden" name="item_number" value="<?=$pp['order_id']?>">
  <input type="hidden" name="amount" value="<?=$pp['order_amount']?>">
  <input type="hidden" name="no_shipping" value="1">
  <input type="hidden" name="return" value="<?=$pp['success']?>">
  <input type="hidden" name="notify_url" value="<?=$pp['notify']?>">
  <input type="hidden" name="rm" value="2">
  <input type="hidden" name="cancel_return" value="<?=$pp['cancel']?>">
  <input type="hidden" name="currency_code" value="<?=$pp['currency']?>">
</td>
<td align="center"><input type="submit" value="Proceed to PayPal" name="pay" class="sub" style="width:150px"></td></tr>
</table>
</form>
<br>
<hr size="1">
