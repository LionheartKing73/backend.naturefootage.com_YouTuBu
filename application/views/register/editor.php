<?if($menu) echo $menu;?>   
<?if($error):?><span class="mand"><?=$error?></span><br><?endif;?>
<?if($message):?><span class="mes"><?=$message?></span><br>

<?else:?>

<form name="register" action="<?=$lang?>/<?=$action?>" method="post" enctype="multipart/form-data">

<table border="0" cellspacing="0" cellpadding="2" id="formborder">
<tr><td>

    <table border="0">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('register_client_title');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>
    
    <tr>
        <td class="form_label" width="140"><?=$this->lang->line('fname');?>: <span class="mand">*</span></td>
        <td><input type="text" name="fname" maxlength="255" size="50" value="<?=$fname?>" class="field"></td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('lname');?>: <span class="mand">*</span></td>
        <td><input type="text" name="lname" maxlength="255" size="50" value="<?=$lname?>" class="field"></td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('email');?>: <span class="mand">*</span></td>
        <td><input type="text" name="email" maxlength="255" size="50" value="<?=$email?>" class="field"></td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('login');?>: <span class="mand">*</span></td>
        <? if($id): ?>
        <td class="uneditable">
          <?=$login?>
          <input type="hidden" name="login" value="<?=$login?>" class="field">
        </td>
        <? else: ?>
        <td>
          <input type="text" name="login" value="<?=$login?>" class="field">
        </td>
        <? endif; ?>
        
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('password');?>: <span class="mand">*</span></td>
        <td><input type="password" name="pass" maxlength="255" size="50" value="<?=$password?>" class="field"></td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('retype_password');?>: <span class="mand">*</span></td>
        <td><input type="password" name="pass2" maxlength="255" size="50" value="<?=$password?>" class="field"></td>
    </tr>
<?if ($id){?>
    <tr>
        <td class="form_label"><?=$this->lang->line('avatar');?>:</td>
        <td><input type="file" name="avatar" size="35" class="field"></td>
    </tr>
    <input type="hidden" name="id" value="<?=$id?>" class="field">
<?}?>

    <tr>
        <td class="form_label"><?=$this->lang->line('company');?>:</td>
        <td class="uneditable"><?=$company?>&nbsp;</td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('position');?>:</td>
        <td class="uneditable"><?=$position?>&nbsp;</td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('address');?>:</td>
        <td class="uneditable"><?=$address?>&nbsp;</td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('city');?>:</td>
        <td class="uneditable"><?=$city?>&nbsp;</td>
    </tr>
    
    <tr>
        <td class="form_label"><?=$this->lang->line('country');?>:</td>
        <td class="uneditable"><?=$country_name?></td>
    </tr>
    
    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="register"></td></tr>
    </table>
</td>
<td valign="top" align="center" style="padding: 4px;">
 <?if ($id && $avatar){?>
  <img src="<?=$this->config->item('avatar_path').$avatar?>" border="0"><br><br>
  <input type="submit" value="<?=$this->lang->line('delete');?>" class="sub" name="delete_avatar">
<?}?>
</td>
</tr>
</table>
</form>

<?endif;?> 
