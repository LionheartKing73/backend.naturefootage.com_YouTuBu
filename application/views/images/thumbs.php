<?if($menu) echo $menu;?> 

<form action="<?=$lang?>/images/thumbs<?='/'.$id?>" method="post" enctype="multipart/form-data">
<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b> <input type="hidden" name="filter" value="1"></td>
 <td width="220">
 
   <?if($this->permissions['images-edit']):?>
     <a class="action" href="<?=$lang?>/images/edit<?='/'.$id?>"><?=$this->lang->line('edit');?></a>
   <?endif;?>
   
   <?if($this->permissions['images-cats']):?>
     <a class="action" href="<?=$lang?>/images/cats<?='/'.$id?>"><?=$this->lang->line('cats');?></a>
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
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('images_thumbs_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>
    <tr>
        <td class="form_label"><?=$this->lang->line('picture');?>: </td>
        <td><input type="file" name="mimg" class="field" size="57"></td>
    </tr>

    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
    
</td></tr>
</table>

<br>  

<?if($res):?>  

<table class="form_table" border="1" cellspacing="0" cellpadding="2" width="434">
<tr><td>
  
  <b><?=$this->lang->line('thumbnail');?>:</b><br> 
  <img src="<?=$res['thumb']?>"> <br><br>
  <b><?=$this->lang->line('preview');?>:</b><br>
  <img src="<?=$res['preview']?>"><br><br>

  <div align="center"><input type="submit" value="<?=$this->lang->line('delete_images');?>" class="sub" name="delete"></div> 
  
</td></tr>                 
</table>
<?endif;?>   

</form>
