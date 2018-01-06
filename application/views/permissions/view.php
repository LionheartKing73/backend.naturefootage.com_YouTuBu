<form name="permissions" action="<?=$lang?>/permissions/view" method="post">
  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">

  <div class="btn-group toolbar-item">
   <? if ($this->permissions['permissions-edit']): ?>
     <a class="btn" href="<?=$lang?>/permissions/edit"><?=$this->lang->line('add');?></a>
   <? endif; ?>
   <? if ($this->permissions['permissions-delete']): ?>
     <a class="btn" href="javascript: if (check_selected(document.permissions, 'id[]')) change_action(document.permissions,'<?=$lang?>/permissions/delete');"><?=$this->lang->line('delete');?></a>
   <? endif; ?>
  </div>

<br class="clr">
<br>
<table class="table">
<? if($permissions){
  foreach($permissions as $permission){?>
<tr<?if(!strstr($permission['code'], '-')){?> class="row-highlighted"<?}?>>
    <td><input type="checkbox" name="id[]" value="<?=$permission['id']?>"></td>
    <td <?if(strstr($permission['code'], '-')){?> style="padding-left: 30px"<?}?>>
      <?=$permission['code']?>
    </td>
    <td><?echo $permission['is_client'] ? $this->lang->line('type_client') : $this->lang->line('type_admin')?></td>
 
    <td class="col-action">
    <?
      get_actions(array(
        array('display' => $this->permissions['permissions-edit'], 'url' => $lang.'/permissions/edit/'.$permission['id'], 'name' => $this->lang->line('edit')),
        array('display' => $this->permissions['permissions-delete'], 'url' => $lang.'/permissions/delete/'.$permission['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>
    </td>
  </tr>
  <?}
  } else {?>
  <tr>
    <td colspan="4" class="empty-list"><?=$this->lang->line('empty_list');?></td>
  </tr>
<?}?>
  
</table>
</form>

<?if($permissions){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>