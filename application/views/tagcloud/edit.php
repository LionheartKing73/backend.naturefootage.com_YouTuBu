<form action="<?=$lang?>/tagcloud/edit<?='/'.$id?>" method="post">

<table border="0" cellspacing="0" cellpadding="0">
<tr><td>

<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>

    <table border="0">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('tagcloud_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>
    <tr>
        <td class="form_label" width="100"><?=$this->lang->line('phrase');?>: <span class="mand">*</span></td>
        <td><input type="text" name="phrase" maxlength="255" size="70" value="<?=$phrase?>" class="field"> <input type="hidden" name="id" value="<?=$id?>"></td>
    </tr>
    
    <?if($filter=='1'):?>    
    <tr>
        <td class="form_label"><?=$this->lang->line('weight');?>: </td>
        <td><input type="text" name="weight" maxlength="255" size="10" value="<?=$weight?>" class="field"></td>
    </tr>
    <?endif;?>
    
    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
</td></tr>
</table>

</td>

<td width="10"></td>
<td valign="top">

<?if($picture):?>

<table border="0" cellspacing="0" cellpadding="2">
  <tr><td align="center"><img src="<?=$picture?>" border="0" style="border:solid 1px #efefef"></td></tr>
  <tr><td align="center">
    <input type="hidden" name="sid" value="<?=$sid;?>">
    <input type="submit" value="<?=$this->lang->line('delete');?>" class="sub" name="delete">
  </td></tr>
</table>

<?endif;?>   

</td></tr>
</table>

</form>
