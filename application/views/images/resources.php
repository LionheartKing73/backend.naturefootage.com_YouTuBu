<?if($menu) echo $menu;?> 

<form action="<?=$lang?>/images/resources<?='/'.$id?>" method="post" enctype="multipart/form-data">
<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b> <input type="hidden" name="filter" value="1"></td>
 <td width="250">
   <?if($this->permissions['images-edit']):?>
     <a class="action" href="<?=$lang?>/images/edit<?='/'.$id?>"><?=$this->lang->line('edit');?></a>
   <?endif;?>
   
   <?if($this->permissions['images-cats']):?>
     <a class="action" href="<?=$lang?>/images/cats<?='/'.$id?>"><?=$this->lang->line('cats');?></a>
   <?endif;?>
   
   <?if($this->permissions['images-thumbs']):?> 
     <a class="action" href="<?=$lang?>/images/thumbs<?='/'.$id?>"><?=$this->lang->line('thumbs');?></a>
   <?endif;?>
   
   <?if($this->permissions['images-disks']):?>
     <a class="action" href="<?=$lang?>/images/disks<?='/'.$id?>"><?=$this->lang->line('disks');?></a>
   <?endif;?>
 </td>
 <td>&nbsp;</td>
</tr>
</table>
                                   
<br>

<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>

<table border="0" cellpadding="1" cellspacing="1" width="468">
<tr class="table_title" height="22">
    <td width="20" align="center">#</td>
    <td><?=$this->lang->line('resource');?></td>
    <td width="105"><?=$this->lang->line('date');?></td>
    <td width="80" align="center"><?=$this->lang->line('action');?></td>
</tr>

<?php if($resources): foreach($resources as $k=>$item):?>   
<tr class="tdata1" height="22">  
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><?=$k+1?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$item['resource']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$item['ctime']?></td>
    
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center">
    <?php
      get_actions(array(
        array('display' => $this->permissions['images-resources'], 'url' => $lang.'/images/resources/'.$id.'/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
        array('display' => $this->permissions['images-resources'], 'url' => $lang.'/images/resources/'.$id.'/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>
    </td>
</tr>
<?php endforeach; else:?>
<tr class="tdata1"><td colspan="6" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?php endif;?>
</table>

</td></tr>
</table>

<br>
<? if (!$resources || $res):?>
<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>
    
    <table border="0" width="468">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('images_res_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>             
    <tr>
        <td class="form_label"><?=$this->lang->line('picture');?>: </td>
        <td><input type="file" name="mimg" class="field" size="57"></td>
    </tr>

    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
    
</td></tr>
</table>
<?endif;?>
<br>   

</form>
