<?if($menu) echo $menu;?>   

<form action="<?=$lang?>/images/edit<?='/'.$id?>" method="post">

<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b> <input type="hidden" name="filter" value="1"></td>
 <td width="220">
 
   <?if($this->permissions['images-cats']):?>
     <a class="action" href="<?=$lang?>/images/cats<?='/'.$id?>"><?=$this->lang->line('cats');?></a>
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
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('images_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>
    <tr>
        <td class="form_label" width="100"><?=$this->lang->line('title');?>: <span class="mand">*</span></td>
        <td><input type="text" name="title" maxlength="255" size="70" value="<?=$title?>" class="field"> <input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="client_id" value="<?=$client_id?>"></td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('code');?>: <span class="mand">*</span></td>
        <td><input type="text" name="code" maxlength="255" size="70" value="<?=$code?>" class="field"></td>
    </tr>    
    
    <tr>
        <td class="form_label"><?=$this->lang->line('license');?>: <span class="mand">*</span></td>
        <td>
        
          <select name="license">
            <option value="1" <?if($license==1) echo 'selected';?>>RF
            <option value="2" <?if($license==2) echo 'selected';?>>RM
          </select>
          
        </td>
    </tr>
        
    <tr>
        <td class="form_label"><?=$this->lang->line('color');?>: <span class="mand">*</span></td>
        <td>
          <select name="color">
            <option value="0" <?if($color==0) echo 'selected';?>> <?=$this->lang->line('black_white');?> 
            <option value="1" <?if($color==1) echo 'selected';?>> <?=$this->lang->line('colored');?>
          </select>
          
        </td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('price');?>: <span class="mand">*</span></td>
        <td><input type="text" name="price" maxlength="255" size="10" value="<?=$price?>" class="field"></td>
    </tr>   
    
    <tr>
        <td class="form_label"><?=$this->lang->line('size');?>: <span class="mand">*</span></td>
        <td><input type="text" name="width" maxlength="255" size="5" value="<?=$width?>" class="field"> x <input type="text" name="height" maxlength="255" size="5" value="<?=$height?>" class="field"></td>
    </tr> 

    <tr>
        <td class="form_label"><?=$this->lang->line('desc');?>: </td>
        <td><textarea name="description" class="ta" style="width:475px; height:50px"><?=$description?></textarea></td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('keys');?>: </td>
        <td><textarea name="keywords" class="ta" style="width:475px; height:50px"><?=$keywords?></textarea></td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('cright');?>: </td>
        <td><textarea name="copyright" class="ta" style="width:375px; height:30px"><?=$copyright?></textarea></td>
    </tr>
    <!--    
    <tr>
        <td class="form_label"><?=$this->lang->line('picture');?>: </td>
        <td><input type="file" name="mimg" class="field" size="57"></td>
    </tr>
    -->
    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
</td></tr>
</table>
</form>
