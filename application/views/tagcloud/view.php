<form name="phrase" action="<?=$lang?>/tagcloud/view" method="post">

<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b> <input type="hidden" name="filter" value="1"></td>
 <td>
   <? if ($this->permissions['tagcloud-edit']) : ?>
     <a class="action" href="<?=$lang?>/tagcloud/edit"><?=$this->lang->line('add');?></a>
   <? endif; ?>
   <? if ($this->permissions['tagcloud-delete']) : ?>
     <a class="action" href="javascript: if (check_selected(document.phrase, 'id[]')) change_action(document.phrase,'<?=$lang?>/tagcloud/delete');"><?=$this->lang->line('delete');?></a>
   <? endif; ?>
 </td>
 
 <?
 if ($filter['type']!=2) {
   switch ($filter['type']) {
     case 1: $param2 = 'weight'; break;
     default: $param2 = 'times'; break;
   }
 ?>
 <td width="60"><b>Sorted&nbsp;by:</b></td>
 <td width="80">
  <select name="sort" onchange="javascript: change_action(document.phrase,'')">
    <option value="0"<? if(!$filter['sort']) echo ' selected' ?>>phrase</option>
    <option value="1"<? if($filter['sort']==1) echo ' selected' ?>>phrase, descending</option>
    <option value="2"<? if($filter['sort']==2) echo ' selected' ?>><?=$param2?></option>
    <option value="3"<? if($filter['sort']==3) echo ' selected' ?>><?=$param2?>, descending</option>
  </select>
 <?}?>
 
 <td width="40"><b><?=$this->lang->line('filter');?>:</b></td>
 <td width="60">
  <select name="type" onchange="javascript: change_action(document.phrase,'')">
    <option value="0" <? if(!$filter['type']) echo 'selected';?>><?=$this->lang->line('natural');?>
    <option value="1" <? if($filter['type']==1) echo 'selected';?>><?=$this->lang->line('manual');?>
    <option value="2" <? if($filter['type']==2) echo 'selected';?>><?=$this->lang->line('stop');?>
  </select>
  
</td>

</tr>
</table>

<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr class="table_title">
    <td width="30" align="center"><input type="checkbox" name="sample" onclick="javascript:select_all(document.phrase);"></td>
    <td><?=$this->lang->line('phrase');?></td>
    <?if(!$filter['type']){?><td width="60"><?=$this->lang->line('times')?></td><?}?>
    <?if($filter['type']=='1'){?><td width="60"><?=$this->lang->line('weight');?></td><?}?>
    <td width="200" align="center"><?=$this->lang->line('action');?></td>
</tr>

<?php if($phrases) { foreach($phrases as $phrase) { ?>   
<tr class="tdata1">  
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><input type="checkbox" name="id[]" value="<?=$phrase['id']?>"></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$phrase['phrase']?></td>
    <?if(!$filter['type']):?><td onmouseover='light(this);' onmouseout='dark(this);'><?=$phrase['times']?></td><?endif;?>
    <?if($filter['type']=='1'):?><td onmouseover='light(this);' onmouseout='dark(this);'><?=$phrase['weight']?></td><?endif;?> 
    
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center">
    <?php
      $actions = array();
      $actions[] = array('display' => $this->permissions['tagcloud-edit'], 'url' => $lang.'/tagcloud/edit/'.$phrase['id'], 'name' => $this->lang->line('edit'));
      if(!$filter['type']){
        $actions[] = array(
          'display' => $this->permissions['tagcloud-delete'],
          'url' => $lang.'/tagcloud/move/'.$phrase['id'],
          'name' => 'Move to stop tags',
          'confirm' => 'The phrase will be moved to stop tags.');
      }      
      $actions[] = array('display' => $this->permissions['tagcloud-delete'], 'url' => $lang.'/tagcloud/delete/'.$phrase['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'));
      get_actions($actions);
    ?>
    </td>
</tr>
<?php }} else { ?>
<tr class="tdata1"><td colspan="6" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?php } ?>

</table>
</form>