<?if($menu) echo $menu;?> 

<form action="<?=$lang?>/images/cats<?='/'.$id?>" method="post">

<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b> <input type="hidden" name="filter" value="1"></td>
 <td width="220">
 
   <?if($this->permissions['images-edit']):?>
     <a class="action" href="<?=$lang?>/images/edit<?='/'.$id?>"><?=$this->lang->line('edit');?></a>
   <?endif;?>
   
   <?if($this->permissions['images-thumbs']):?> 
     <a class="action" href="<?=$lang?>/images/thumbs<?='/'.$id?>"><?=$this->lang->line('thumbs');?></a>
   <?endif;?>
   
   <?if($this->permissions['images-resources']):?>
     <a class="action" href="<?=$lang?>/images/resources<?='/'.$id?>"><?=$this->lang->line('resources');?></a>
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
 
  <table border="0"> 
  <tr class="table_title"><td align="center" height="20"><?=$this->lang->line('images_cats_edit');?>:</td></tr>  
  <tr><td>
 
 <table border="0" cellspacing="0" cellpadding="0">

<tr><td valign="top">
    
    <?if($cats):?>
    
    <table>
    
    <?foreach($cats as $val): $i++;?>
    
    <?if($i>=$column_count): $i=0;?>
    </table>
    </td>
    <td width="20">&nbsp;</td><td valign="top">
    <table>
    <?endif;?>
       
    <tr><td><input type="checkbox" <?if($val['checked']) echo "checked";?> name="id[]" value="<?=$val['id']?>"> <?=$val['title']?></td></tr>
    
        <?if($val['child']):?>
        
        <?foreach($val['child'] as $v): $i++;?>
        <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" <?if($v['checked']) echo "checked";?> name="id[]" value="<?=$v['id']?>"> <?=$v['title']?></td></tr>
        <?endforeach;?>  
        
        <?endif;?>  
    
    <?endforeach;?>
    </td></tr></table>
    <?endif;?>  
    
</td></tr>
</table> 
  
  </td></tr>
  <tr><td align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>   
  </table>        
      
</td></tr>
</table>

</form>
