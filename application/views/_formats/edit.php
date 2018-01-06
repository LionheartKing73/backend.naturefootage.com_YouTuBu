<form action="<?=$lang?>/formats/<?=$type?>/edit/<?=$this->id?>" method="post">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td>

<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>

    <table border="0">
      <tr class="table_title">
        <td colspan="2" align="center" height="20"><?=$this->lang->line('formats_edit');?></td>
      </tr>
      
      <tr>
        <td class="form_label" width="100"><?=$this->lang->line('title');?>: <span class="mand">*</span></td>
        <td>
          <input type="text" name="title" maxlength="255" size="70" value="<?=$title?>" class="field">
          <input type="hidden" name="type" value="<?=$type?>">
        </td>
      </tr>
      
<?if ($type == 'delivery') {?>
      <tr>
        <td class="form_label" width="100">Resolution:</td>
        <td>
          <select name="res">
            <option value="1"<?if ($res==1) echo ' selected="selected"'?>>High</option>
            <option value="2"<?if ($res==2) echo ' selected="selected"'?>>Medium</option>
            <option value="3"<?if ($res==3) echo ' selected="selected"'?>>Low</option>
          </select>
        </td>
      </tr>
      
      <tr>
        <td class="form_label" width="100">File type:</td>
        <td>
          <input type="text" name="filetype" maxlength="8" value="<?=$filetype?>" class="field" style="width: 25px">
        </td>
      </tr>
<?}?>

<?if ($type == 'original') {?>
      <tr>
        <td class="form_label" width="100">Code:</td>
        <td>
          <input type="text" name="code" value="<?=$code?>" class="field">
        </td>
      </tr>
      
      <tr>
        <td class="form_label" width="100">HD or SD:</td>
        <td>
          <select name="hd_sd">
            <option value="1"<?if ($hd_sd==1) echo ' selected="selected"'?>>HD</option>
            <option value="2"<?if ($hd_sd==2) echo ' selected="selected"'?>>SD</option>
          </select>
        </td>
      </tr>
<?}?>

<?if ($type == 'delivery' || $type == 'original') {?>
      <tr>
        <td class="form_label" width="100">Frame size:</td>
        <td>
          <input name="width" maxlength="4" value="<?=$width?>" style="width: 32px; text-align: right">x<input name="height" maxlength="4" value="<?=$height?>" style="width: 32px; text-align: right">
        </td>
      </tr>
<?}?>

<?if ($type == 'original') {?>
      <tr>
        <td class="form_label" width="100">High resolution delivery format:</td>
        <td>
          <select name="hr_id">
            <? foreach ($hrs as $hr) { ?>
            <option value="<?=$hr['id']?>"<?if ($hr_id==$hr['id']) echo ' selected="selected"'?>>
              <?=$hr['title']?>
            </option>
            <?}?>
          </select>
        </td>
      </tr>
<?}?>

<?if ($type == 'delivery') {?>
      <tr>
        <td class="form_label" width="100">Price:</td>
        <td>
          <select name="price_id">
<?foreach ($prices as $price) {?>
            <option value="<?=$price['id']?>"<?if ($price_id==$price['id']) echo ' selected="selected"'?>>
              <?=$price['code']?>
            </option>
<?}?>
          </select>
        </td>
      </tr>
<?}?>

      <tr>
        <td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td>
      </tr>
    </table>
</td></tr>
</table>

</td></tr>
</table>

</form>
