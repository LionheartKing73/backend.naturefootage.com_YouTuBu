<form method="post">
<table cellpadding="2" cellspacing="2" border="0" class="itemview">

<tr class="table_title">
  <td colspan="2">DISCOUNT DETAILS</td>
</tr>

<tr>
  <th width="100">Items count <span class="mand">*</span>:</th>
  <td><input type="text" name="item_count" value="<?=$discount->item_count?>" class="field"></td>
</tr>

<tr>
  <th>Discount, % <span class="mand">*</span>:</th>
  <td><input type="text" name="discount" value="<?=$discount->discount?>" class="field"></td>
</tr>

<tr>
  <td colspan="2" align="center">
    <input type="submit" name="save" value="Save" class="sub">
  </td>
</tr>

</table>
</form>