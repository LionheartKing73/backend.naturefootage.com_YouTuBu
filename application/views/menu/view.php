<script type="text/javascript">
  delete_confirm = "<?=$this->lang->line('delete_confirm')?>";
</script>

<form name="menus" action="<?=$lang?>/menu/view" method="post">

  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">

  <div class="btn-group toolbar-item">
   <? if ($this->permissions['menu-edit']) : ?>
     <a class="btn" href="<?=$lang?>/menu/edit"><?=$this->lang->line('add')?></a>
   <? endif; ?>
   <? if ($this->permissions['menu-visible']) : ?>
     <a class="btn" href="javascript: if (check_selected(document.menus, 'id[]')) change_action(document.menus,'<?=$lang?>/menu/visible');"><?=$this->lang->line('visible')?></a>
   <? endif; ?>
   <? if ($this->permissions['menu-ord']) : ?>
     <a class="btn" href="javascript: change_action(document.menus,'<?=$lang?>/menu/ord');"><?=$this->lang->line('save_ord')?></a>
   <? endif; ?>
   <? if ($this->permissions['menu-delete']) : ?>
     <a class="btn" href="javascript: if (check_selected(document.menus, 'id[]')) change_action(document.menus,'<?=$lang?>/menu/delete');"><?=$this->lang->line('delete')?></a>
   <? endif; ?>
  </div>

  <div class="toolbar-item">
    <label forid="type"><?=$this->lang->line('filter')?>:</label>
    <select name="type" id="type" onchange="javascript: change_action(document.menus,'')" style="width: auto">
      <option value="0" <? if(!$filter['type']) echo 'selected'?>><?=$this->lang->line('top')?>
      <option value="1" <? if($filter['type']==1) echo 'selected'?>><?=$this->lang->line('bottom')?>
    </select>
  </div>

  <br class="clr">

<table class="table table-striped">
  <tr>
    <th><input type="checkbox" name="sample" onclick="javascript:select_all(document.menus)"></td>
    <th><?=$this->lang->line('title')?></th>
    <th><?=$this->lang->line('link')?></th>
    <th><?=$this->lang->line('target')?></th>
    <th><?=$this->lang->line('order')?></th>
    <th><?=$this->lang->line('status')?></th>
    <th><?=$this->lang->line('date')?></th>
    <th class="col-action"><?=$this->lang->line('action')?></th>
  </tr>
<?if($menus){?>
  <?foreach($menus as $menu){?>
  <tr>
    <td><input type="checkbox" name="id[]" value="<?=$menu['id']?>"></td>
    <td<?if ($menu['parent_id']){?> class="subitem"<?}?>>
      <?if(!$menu['parent_id'] && $menu['child']){?>
        <img src="data/img/admin/arrow.gif">
      <?}?>
      <?=$menu['title']?>
    </td>
    <td><?=$menu['link']?></td>
    <td><?if($menu['target']) echo '_blank'; else echo '_self'?></td>
    <td><input type="text" name="ord[<?=$menu['id']?>]" style="width:30px" value="<?=$menu['ord']?>"></td>
    <td><?if($menu['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
    <td><?=$menu['ctime']?></td>
    <td>
    <?
      get_actions(array(
        array('display' => $this->permissions['menu-edit'], 'url' => $lang.'/menu/edit/'.$menu['id'], 'name' => $this->lang->line('edit')),
        array('display' => $this->permissions['menu-delete'], 'url' => $lang.'/menu/delete/'.$menu['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>   
    </td>
  <?}?>
<?}else{?>
  <tr><td colspan="8" class="empty-list"><?=$this->lang->line('empty_list')?></td></tr>
<?}?>
</table>
  
</form>

<?if($menus){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>