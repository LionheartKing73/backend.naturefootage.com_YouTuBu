<form name="users" action="<?=$lang?>/users/view" method="post">
  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">

  <div class="btn-group toolbar-item">
    <? if ($this->permissions['users-edit']) : ?>
      <a class="btn" href="<?=$lang?>/users/edit"><?=$this->lang->line('add')?></a>
    <? endif; ?>
    <? if ($this->permissions['users-visible']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.users, 'id[]')) change_action(document.users,'<?=$lang?>/users/visible');"><?=$this->lang->line('visible')?></a>
    <? endif; ?>
    <? if ($this->permissions['users-delete']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.users, 'id[]')) change_action(document.users,'<?=$lang?>/users/delete');"><?=$this->lang->line('delete')?></a>
    <? endif; ?>

      <?//TODO permissions?>
      <? if ($this->permissions['users-delete']) : ?>
          <a class="btn" href="javascript: if (check_selected(document.users, 'id[]')) change_action(document.users,'<?=$lang?>/users/sales_representatives');"><?=$this->lang->line('delete')?></a>
      <? endif; ?>
  </div>
 
  <div class="toolbar-item">
    <label for="words"><?=$this->lang->line('search')?>:</label>
    <input type="text" name="words" id="words" style="width: 100px" value="<?=$filter['words']?>">
    <input type="submit" value="<?=$this->lang->line('find')?>" class="btn find">
  </div>

  <div class="toolbar-item">
    <label for="active"><?=$this->lang->line('filter')?>:</label>
    <select name="active" id="active" onchange="javascript: change_action(document.users,'')"
      style="width: auto">
      <option value="0" <? if(!$filter['active']) echo 'selected'?>><?=$this->lang->line('all')?>
      <option value="1" <? if($filter['active']==1) echo 'selected'?>><?=$this->lang->line('nothidden')?>
      <option value="2" <? if($filter['active']==2) echo 'selected'?>><?=$this->lang->line('hidden')?>
    </select>
  </div>

  <div class="toolbar-item">
    <label for="group"><?=$this->lang->line('groups')?>:</label>
    <select name="group" id="group" onchange="javascript: change_action(document.users,'')"
      style="width: auto">
      <option value="0"><?=$this->lang->line('all')?>
      <?foreach($groups as $group):?>
        <option value="<?=$group['id']?>" <?if($group['id']==$filter['group']) echo 'selected'?>> <?=$group['title']?><br>
      <?endforeach?>
    </select>
  </div>

<br class="clr">

<table class="table table-striped">
<tr>
  <th><input type="checkbox" name="sample" onclick="select_all(document.users)"></th>
  <th><a href="<?=$uri?>/sort/fname"><?=$this->lang->line('fname')?></a></th>
  <th><a href="<?=$uri?>/sort/lname"><?=$this->lang->line('lname')?></a></th>
  <th><a href="<?=$uri?>/sort/groups"><?=$this->lang->line('group')?></a></th>
  <th><a href="<?=$uri?>/sort/email"><?=$this->lang->line('email')?></a></th>
  <th><a href="<?=$uri?>/sort/active"><?=$this->lang->line('status')?></a></th> 
  <th><a href="<?=$uri?>/sort/ctime"><?=$this->lang->line('date')?></a></th>
  <th class="col-action"><?=$this->lang->line('action')?></th>
</tr>

<? if($users) { foreach($users as $user) {?>
<tr class="tdata1">  
  <td><input type="checkbox" name="id[]" value="<?=$user['id']?>"></td>
  <td><?=$user['fname']?></td>
  <td><?=$user['lname']?></td>
  <td><?=$user['groups']?></td>
  <td><?=$user['email']?></td>
  <td><? if($user['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
  <td><?=strftime('%Y-%m-%d', strtotime($user['ctime']))?></td>
  <td align="center">
  <?
    get_actions(array(
      array('display' => $this->permissions['users-edit'], 'url' => $lang.'/users/edit/'.$user['id'], 'name' => $this->lang->line('edit')),
      array('display' => $this->permissions['users-create_frontend'] && $user['is_provider'] && !$user['frontend_id'], 'url' => $lang.'/users/create_frontend/'.$user['id'], 'name' => 'Create frontend'),
      array('display' => $this->permissions['users-logs'], 'url' => $lang.'/users/logs/'.$user['id'], 'name' => $this->lang->line('log')),
      array('display' => $this->permissions['users-delete'], 'url' => $lang.'/users/delete/'.$user['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
    ));
  ?>
  </td>
</tr>
<? }} else {?>
<tr><td colspan="8" class="empty-list"><?=$this->lang->line('empty_list')?></td></tr>
<?}?>
</table>
</form>

<?if($users){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>