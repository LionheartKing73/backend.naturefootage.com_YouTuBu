<form method="post" enctype="multipart/form-data">

<table cellpadding="2" cellspacing="2" border="0" class="itemview">
<tr class="table_title">
  <td colspan="2">PRODUCTION SERVICES</td>
</tr>

<tr>
  <th width="100">Clip file:</th>
  <td>
    <input type="file" name="clip" class="field" style="width:300px">
  </td>
</tr>

<tr>
  <th>File name:</th>
  <td>
    <?=$this->config->item('production_path')?>
    <input type="text" name="filename" class="field" value="<?=$production['filename']?>">
    <?if ($production['filename']) {?>
    &nbsp;
    <?if ($production['file_exists']){?>file is OK<?}else{?><span class="mand">file is absent!</span><?}}?>
  </td>
</tr>

<tr>
  <th>Content:</th>
  <td>
    <textarea name="body"><?=$production['content']?></textarea>
    <?=fck(750);?>
  </td>
</tr>

<tr>
  <th>Bottom content:</th>
  <td>
    <textarea name="bottom_body"><?=$production['bottom_content']?></textarea>
    <?=fck(750,'','bottom_body');?>
  </td>
</tr>

<tr>
  <td colspan="2" align="center">
    <input type="submit" name="save" value="Save" class="sub">
  </td>
</tr>

</table>
</form>