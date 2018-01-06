<form name="rss" action="<?=$lang?>/rss/view" method="post">

<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b></td>
 <td width="220">
   <? if ($this->permissions['rss-edit']) : ?>
     <a class="action" href="<?=$lang?>/rss/edit"><?=$this->lang->line('add');?></a>
   <? endif; ?>
   <? if ($this->permissions['rss-delete']) : ?>
     <a class="action" href="javascript: if (check_selected(document.rss, 'id[]')) change_action(document.rss,'<?=$lang?>/rss/delete');"><?=$this->lang->line('delete');?></a>
   <? endif; ?>
 </td>
 <td>&nbsp;</td>
</tr>
</table>

<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr class="table_title">
    <td width="30" align="center"><input type="checkbox" name="sample" onclick="javascript:select_all(document.rss);"></td>
    <td><?=$this->lang->line('title');?></td>
    <td width="100"><?=$this->lang->line('link');?></td> 
     <td width="100"><?=$this->lang->line('language');?></td> 
    <td width="150" align="center"><?=$this->lang->line('action');?></td>
</tr>

<?php if($channels): foreach($channels as $channel):?>   
<tr class="tdata1">  
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><input type="checkbox" name="id[]" value="<?=$channel['id']?>"></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$channel['title']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$channel['url']?></td>  
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$channel['lang']?></td>  

    <td onmouseover='light(this);' onmouseout='dark(this);' align="center">
    <?php
      get_actions(array(
        array('display' => $this->permissions['rss-edit'], 'url' => $lang.'/rss/edit/'.$channel['id'], 'name' => $this->lang->line('edit')),
        array('display' => $this->permissions['rss-items'], 'url' => $lang.'/rss/items/'.$channel['id'], 'name' => $this->lang->line('items')),
        array('display' => $this->permissions['rss-delete'], 'url' => $lang.'/rss/delete/'.$channel['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>    
    </td>
</tr>
<?php endforeach; else:?>
<tr class="tdata1"><td colspan="5" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?php endif;?>
  
</table>
</form>