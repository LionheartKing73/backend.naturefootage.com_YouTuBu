<script type="text/javascript">
  var delete_confirm = "<?=$this->lang->line('delete_confirm')?>";
</script>

<form name="delivery" action="<?=$lang?>/delivery/view" method="post">
  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <div class="btn-group toolbar-item">
<?if ($this->permissions['delivery-edit']):?>
    <a class="btn" href="<?=$lang?>/delivery/edit.html"><?=$this->lang->line('add')?></a>
<?endif?>
<?if ($this->permissions['delivery-ord']):?>
    <a class="btn" href="javascript: change_action(document.delivery,'<?=$lang?>/delivery/ord');"><?=$this->lang->line('save_ord')?></a>
<?endif?>
<?if ($this->permissions['delivery-delete']):?>
    <a class="btn" href="javascript: if (check_selected(document.delivery, 'id[]')) change_action(document.delivery,'<?=$lang?>/delivery/delete');"><?=$this->lang->line('delete')?></a>
<?endif?>
  </div>

<br class="clr">
<br>

<table class="table table-striped">
  <tr>
    <th>
      <input type="checkbox" name="sample" onclick="javascript:select_all(document.delivery);">
    </th>
    <th><?=$this->lang->line('title')?></th>
    <!--<th>Cost</th>-->
    <th><?=$this->lang->line('order')?></th>
    <th class="col-action"><?=$this->lang->line('action')?></th>
  </tr>

  <?foreach($delivery as $item):?>
  <tr>
    <td>
      <input type="checkbox" name="id[]" value="<?=$item['id']?>">
    </td>
    <td><a href="delivery/edit/<?=$item['id']?>.html"><?=$item['name']?></a></td>
    <!--<td><?=number_format($item['cost'], 2, '.', '')?></td>-->
    <td>
      <input type="text" class="field" name="ord[<?=$item['id']?>]" style="text-align:right;width:30px"
        value="<?=$item['ord']?>">
    </td>
    <td>
      <div class="btn-group">
        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
          Action <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li><a href="<?=$lang?>/delivery/edit/<?=$item['id']?>.html">Edit</a></li>
          <li><a href="<?=$lang?>/delivery/delete/<?=$item['id']?>.html"
            onclick="return confirm('The item will be deleted.')">Delete</a></li>
        </ul>
      </div>
    </td>
  </tr>
  <?endforeach?>
</table>
</form>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>