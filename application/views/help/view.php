<form name="help" action="<?=$lang?>/help/view" method="post">

<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b> <input type="hidden" name="filter" value="1"></td>
 <td>
   <? if ($this->permissions['help-edit']) : ?>
     <a class="action" href="<?=$lang?>/help/edit"><?=$this->lang->line('add');?></a>
   <? endif; ?>
   <? if ($this->permissions['help-ord']) : ?> 
     <a class="action" href="javascript: if (check_selected(document.help, 'id[]')) change_action(document.help,'<?=$lang?>/help/ord');"><?=$this->lang->line('save_ord');?></a>
   <? endif; ?>
   <? if ($this->permissions['help-delete']) : ?>
     <a class="action" href="javascript: if (check_selected(document.help, 'id[]')) change_action(document.help,'<?=$lang?>/help/delete');"><?=$this->lang->line('delete');?></a>
   <? endif; ?>
 </td>
 <td></td>

</tr>
</table>

<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr class="table_title">
    <td width="30" align="center"><input type="checkbox" name="sample" onclick="javascript:select_all(document.help);"></td>
    <td><?=$this->lang->line('title');?></td>
    <td width="80"><?=$this->lang->line('order');?></td>
    <td width="80"><?=$this->lang->line('pdf');?></td> 
    <td width="80"><?=$this->lang->line('video');?></td>
    <td width="150" align="center"><?=$this->lang->line('action');?></td>
</tr>

<?if($topics):?>

  <?foreach($topics as $topic):?>
  <tr class="tdata1">  
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><input type="checkbox" name="id[]" value="<?=$topic['id']?>"></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><? if(!$topic['parent_id'] && $topic['child']):?><img src="data/img/admin/arrow.gif"><?endif;?>&nbsp;<?=$topic['title']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><input type="text" class="field" name="ord[<?=$topic['id']?>]" style="width:30px" value="<?=$topic['ord']?>"></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$topic['pdfplus']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$topic['videoplus']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center">
    <?php
      get_actions(array(
        array('display' => $this->permissions['help-edit'], 'url' => $lang.'/help/edit/'.$topic['id'], 'name' => $this->lang->line('edit')),
        array('display' => $this->permissions['help-delete'], 'url' => $lang.'/help/delete/'.$topic['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>    
    </td>
  </tr>
  
  <?if($topic['child']):?>
    <?foreach($topic['child'] as $item):?>
    <tr class="tdata1">  
        <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><input type="checkbox" name="id[]" value="<?=$item['id']?>"></td>
        <td onmouseover='light(this);' onmouseout='dark(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$item['title']?></td>
        <td onmouseover='light(this);' onmouseout='dark(this);'><input type="text" class="field" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['ord']?>"></td>
        <td onmouseover='light(this);' onmouseout='dark(this);'><?=$item['pdf']?></td>
        <td onmouseover='light(this);' onmouseout='dark(this);'><?=$item['video']?></td>
        <td onmouseover='light(this);' onmouseout='dark(this);' align="center">
        <?php
          get_actions(array(
            array('display' => $this->permissions['help-edit'], 'url' => $lang.'/help/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
            array('display' => $this->permissions['help-delete'], 'url' => $lang.'/help/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
          ));
        ?>
        </td>
    </tr>
    <?endforeach;?>
  <?endif;?>
  
  <?endforeach;?> 
  
</td></tr>
<?else:?>
  <tr class="tdata1"><td colspan="6" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?endif;?>
</table>
  
</table>
</form>