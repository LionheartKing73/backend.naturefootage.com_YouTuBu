<form name="groups" action="<?=$lang?>/groups/view" method="post">
  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">

  <div class="btn-group toolbar-item">
  <? if ($this->permissions['groups-edit']) : ?>
    <a class="btn" href="<?=$lang?>/groups/edit"><?=$this->lang->line('add')?></a>
  <? endif; ?>
  <? if ($this->permissions['groups-visible']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.groups, 'id[]')) change_action(document.groups,'<?=$lang?>/groups/visible');"><?=$this->lang->line('visible')?></a>
  <? endif; ?>
  <? if ($this->permissions['groups-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.groups, 'id[]')) change_action(document.groups,'<?=$lang?>/groups/delete');"><?=$this->lang->line('delete')?></a>
  <? endif; ?>
  </div>

  <div class="toolbar-item">
    <label for="active"><?=$this->lang->line('filter')?>:</label>
    <select name="active" id="active" onchange="javascript: change_action(document.groups,'')" style="width: auto">
      <option value="0" <? if(!$filter['active']) echo 'selected'?>><?=$this->lang->line('all')?>
      <option value="1" <? if($filter['active']==1) echo 'selected'?>><?=$this->lang->line('nothidden')?>
      <option value="2" <? if($filter['active']==2) echo 'selected'?>><?=$this->lang->line('hidden')?>
    </select>
  </div>
  
<br class="clr">

<table class="table table-striped">
  <tr>
    <th><input type="checkbox" name="sample" onclick="javascript:select_all(document.groups)"></th>
    <th><a href="<?=$uri?>/sort/title" class="title"><?=$this->lang->line('title')?></a></th>
    <th><a href="<?=$uri?>/sort/users" class="title"><?=$this->lang->line('users')?></a></th>
    <th><a href="<?=$uri?>/sort/active" class="title"><?=$this->lang->line('status')?></a></th>
    <th><a href="<?=$uri?>/sort/ctime" class="title"><?=$this->lang->line('date')?></a></th>
    <th class="col-action"><?=$this->lang->line('action')?></th>
  </tr>

<?if($groups): foreach($groups as $group):?>
  <tr>
    <td>
      <? if(!$group['is_admin'] && !$group['is_default'] && !$group['is_client']): ?>
        <input type="checkbox" name="id[]" value="<?=$group['id']?>">
      <? else: ?>
        <input type="checkbox" name="id[]" value="" disabled>
      <? endif?>
    </td>
    <td><?=$group['title']?></td>
    <td><? echo $group['users'] ?></td>
    <td><? if($group['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
    <td><?=$group['ctime']?></td>
    <td align="center">
    <?
      get_actions(array(
        array('display' => $this->permissions['groups-edit'], 'url' => $lang.'/groups/edit/'.$group['id'], 'name' => $this->lang->line('edit')),
        array('display' => ($this->permissions['groups-permissions'] && !$group['is_admin'] && !$group['is_default']), 'url' => $lang.'/groups/permissions/'.$group['id'], 'name' => $this->lang->line('permissions')),
        array('display' => ($this->permissions['groups-delete'] && !$group['is_admin'] && !$group['is_default'] && !$group['is_client']), 'url' => $lang.'/groups/delete/'.$group['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>
    </td>
  </tr>
<? endforeach; else: ?>
  <tr>
    <td colspan="6" class="empty-list"><?=$this->lang->line('empty_list')?></td>
  </tr>
<? endif ?>
  
</table>
</form>

<?if($groups){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>