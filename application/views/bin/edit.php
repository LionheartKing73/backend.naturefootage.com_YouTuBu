<div class="content_padding typography content_bg">
<?if($client):?>
<form  action="<?=$lang.'/bin/edit/'.$id?>" method="post">

<?if($error):?><span class="err"><?=$error?></span><br><br><?endif;?>

<div id="binEdit">
<table cellspacing="1" cellpadding="4" border="0">
<tr>
  <td width="100"><?=$this->lang->line('name');?>: <span class="mand">*</span></td>
  <td><input type="text" name="title" value="<?=$title?>" class="inp"></td>
</tr>

<tr>
  <td><?=$this->lang->line('desc');?>:</td>
  <td><textarea name="description" style="width:400px; height:80px;" class="inp"><?=$description?></textarea></td>
</tr>

<tr>
  <td>&nbsp;</td>
  <td>
    <button type="submit" name="save" class="action"><?=$this->lang->line('save');?></button>
  </td>
</tr>

</table>
</div>
           
</form>
<?else:?>
<?=$this->lang->line('must_register');?> - <a href="<?=$lang?>/register.html"> <?=$this->lang->line('login_register');?></a>.
<?endif;?>
</div>
