<form action="<?=$lang?>/blocks/edit/<?=$id?>" method="post">
<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>
    <table border="0">
    <tr class="table_title"><td colspan="2" align="center" height="20">EDIT CONTENT</td></tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('content');?>: <span class="mand">*</span></td>
        <td>
          <textarea name="content" id="body" class="ta" style="width:600px; height:200px"><?=$content?></textarea>
          <?=fck(750);?>
        </td>
    </tr>

    <tr>
      <td colspan="2" align="center">
        <input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save">
      </td>
    </tr>
    </table>
</td></tr>
</table>
</form>
