<form action="<?=$lang?>/watermark/edit" method="post">

<br>
<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>

    <table border="0">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('watermark_text_title');?></td></tr>
    <tr>
        <td><input type="text" name="text" maxlength="30" size="70" value="<?=$text?>" class="field"></td>
    </tr>

    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
</td></tr>
</table>
</form>
