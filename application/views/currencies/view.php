<form name="curs" action="<?=$lang?>/currencies/view" method="post">
           
  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">


  <div class="btn-group toolbar-item">
    <? if ($this->permissions['currencies-edit']) : ?>
      <a class="btn" href="<?=$lang?>/currencies/edit"><?=$this->lang->line('add')?></a>
    <? endif; ?>
    <? if ($this->permissions['currencies-visible']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.curs, 'id[]')) change_action(document.curs,'<?=$lang?>/currencies/visible');"><?=$this->lang->line('visible')?></a>
    <? endif; ?>
    <? if ($this->permissions['currencies-delete']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.curs, 'id[]')) change_action(document.curs,'<?=$lang?>/currencies/delete');"><?=$this->lang->line('delete')?></a>
    <? endif; ?>
  </div>

  <div class="toolbar-item">
    <label for="active"><?=$this->lang->line('filter')?>:</label>
    <select name="active" id="active" onchange="change_action(document.curs,'')" style="width: auto">
      <option value="0" <? if(!$filter['active']) echo 'selected'?>><?=$this->lang->line('all')?>
      <option value="1" <? if($filter['active']==1) echo 'selected'?>><?=$this->lang->line('nothidden')?>
      <option value="2" <? if($filter['active']==2) echo 'selected'?>><?=$this->lang->line('hidden')?>
    </select>
  </div>
  <br class="clr">
     
<table class="table">
  <tr>
    <td><input type="checkbox" name="sample" onclick="javascript:select_all(document.curs);"></td>
    <th><?=$this->lang->line('title')?></th> 
    <th><?=$this->lang->line('code')?></th>
    <th><?=$this->lang->line('rate')?></th>
    <th><?=$this->lang->line('status')?></th>
    <th class="col-action"><?=$this->lang->line('action')?></th>
  </tr>

<?if($currencies) {
  foreach($currencies as $k=>$item) {?>
  <tr<?if($item['is_default']){?> class="row-highlighted"<?}?>>
    <td><input type="checkbox" name="id[]" value="<?=$item['id']?>"></td>
    <td<?if($item['is_default']){?> style="font-weight: bold"<?}?>><?=$item['title']?></td>
    <td><?=$item['code']?></td>
    <td><?=$item['rate']?></td>
    <td><? if($item['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
    <td align="center">
    <?
      get_actions(array(
        array('display' => $this->permissions['currencies-edit'], 'url' => $lang.'/currencies/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
        array('display' => $this->permissions['currencies-delete'], 'url' => $lang.'/currencies/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>
    </td>
</tr>
  <?}
} else {?>
<tr><td colspan="6" class="empty-list"><?=$this->lang->line('empty_list')?></td></tr>
<?}?>
</table>

</form>

<?if($currencies){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>