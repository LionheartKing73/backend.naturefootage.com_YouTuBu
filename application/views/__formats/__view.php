<form name="formats" action="<?=$lang?>/formats/<?=$type?>" method="post">

<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b></td>
 <td width="220">
   <? if ($this->permissions['formats-edit']): ?>
     <a class="action" href="<?=$lang?>/formats/<?=$type?>/edit"><?=$this->lang->line('add');?></a>
   <? endif; ?>
   <? if ($this->permissions['formats-delete']): ?>
     <a class="action" href="javascript: if (check_selected(document.formats, 'id[]')) change_action(document.formats,'<?=$lang?>/formats/<?=$type?>/delete');"><?=$this->lang->line('delete');?></a>
   <? endif; ?>
 </td>

<td></td>

</tr>
</table>

<table border="0" width="100%" cellpadding="1" cellspacing="1" class="listview">

<tr height="20">
  <th width="30" align="center">
    <input type="checkbox" name="sample" onclick="javascript:select_all(document.formats);">
  </th>

  <th><a href="<?=$uri?>/sort/title" class="title"><?=$this->lang->line('title');?></a></th>

  <th><?=$this->lang->line('code')?></th>

  <?if ($type == 'original') {?>
  <th>HD/SD</th>

  <th>HD delivery format</th>
  <?}?>

  <?if (($type == 'original') || ($type == 'delivery')) {?>
  <th>Frame size</th>
  <?}?>

  <?if ($type == 'delivery') {?>
  <th>Resolution</th>

  <th>File type</th>

  <th><?=$this->lang->line('price');?></th>
  <?}?>

  <th width="150" style="text-align: center"><?=$this->lang->line('action');?></th>
</tr>

<?
  if($formats) {
    $hd_sd = array('', 'HD', 'SD');
    $res = array('', 'High', 'Medium', 'Low');
    foreach($formats as $format) {
?>
<tr class="tdata1" height="20">
  <td style="text-align: center"><input type="checkbox" name="id[]" value="<?=$format['id']?>"></td>

  <td><?=$format['title']?></td>
  <td><?=$format['code']?></td>

  <?if ($type == 'original') {?>
  <td><?=$hd_sd[$format['hd_sd']]?></td>

  <td><?=$format['hr_df']?></td>
  <?}?>

  <?if (($type == 'original') || ($type == 'delivery')) {?>
  <td><?=$format['width'] . 'x' . $format['height']?></td>
  <?}?>

  <?if ($type == 'delivery') {?>
  <td><?=$res[$format['res']]?></td>

  <td><?=$format['filetype']?></td>

  <td><?=$format['price_code'] . ' (' . $format['price'] . ')'?></td>
  <?}?>

  <td style="text-align: center">

  <?php
    get_actions(array(
      array('display' => $this->permissions['formats-edit'], 'url' => $lang.'/formats/'.$type.'/edit/'.$format['id'], 'name' => $this->lang->line('edit')),
      array('display' => $this->permissions['formats-delete'], 'url' => $lang.'/formats/'.$type.'/delete/'.$format['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
    ));
  ?>
  </td>
</tr>
<? }} else {?>
<tr class="tdata1"><td colspan="3" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?}?>

</table>
</form>