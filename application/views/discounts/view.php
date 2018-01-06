<div class="action_title">
  <b><?=$this->lang->line('action')?>:</b>
  &nbsp;
<?if ($this->permissions['discounts-edit']){?>
  <a class="action" href="<?=$lang?>/discounts/edit"><?=$this->lang->line('add')?></a>
<?}?>
</div>

<table cellpadding="3" cellspacing="1" border="0" class="listview">
  <tr>
    <th>Items count</th>
    <th>Discount, %</th>
    <th>Action</th>
  </tr>

  <? if($discounts) { foreach($discounts as $discount){?>
  <tr>
    <td align="right">
      <?=$discount->item_count?>
    </td>
    <td align="right">
      <?=$discount->discount?>
    </td>
    <td align="center">
      <a href="<?=$lang?>/discounts/edit/<?=$discount->id?>" class="action">Edit</a>
      |
      <a href="<?=$lang?>/discounts/delete/<?=$discount->id?>" class="mand"
        onclick="return confirm('The item will be deleted.')">Delete</a>
    </td>
  </tr>
  <?}} else {?>
  <tr><td colspan="3" align="center">Empty</td></tr>
  <?}?>
</table>