<script type="text/javascript">
  var delete_confirm = "<?=$this->lang->line('delete_confirm');?>";
</script>

<form name="feedback" action="<?=$lang?>/feedback/view" method="post">

<div class="action_title">
  <b><?=$this->lang->line('action');?>:</b>
  &nbsp;
<?if ($this->permissions['feedback-delete']) {?>
  <a class="action" href="javascript: if (check_selected(document.feedback, 'id[]')) change_action(document.feedback,'<?=$lang?>/feedback/delete<?=$visual?>');"><?=$this->lang->line('delete');?></a>
<?}?>
</div>

<table cellpadding="3" cellspacing="1" border="0" width="100%" class="listview">
  <tr>
    <th width="30" style="text-align: center">
      <input type="checkbox" name="sample" onclick="javascript:select_all(document.feedback);">
    </th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Message</th>
    <th>Date</th>
    <th width="80" style="text-align: center"><?=$this->lang->line('action');?></th>
  </tr>

  <?foreach($feedback as $post){?>
  <tr>
    <td style="text-align: center">
      <input type="checkbox" name="id[]" value="<?=$post['id']?>">
    </td>
    <td><?=$post['name']?></td>
    <td>
      <a href="mailto:<?=$post['email']?>"><?=$post['email']?></a>
    </td>
    <td><?=$post['phone']?></td>
    <td><?=nl2br($post['message'])?></td>
    <td><?=strftime('%d.%m.%Y %H:%M', strtotime($post['ctime']))?></td>
    <td align="center">
    <?if ($this->permissions['feedback-delete']) {?>
      <a href="<?=$lang?>/feedback/delete/<?=$post['id']?>.html" class="mand"
        onclick="return confirm('The item will be deleted.');">Delete</a>
    <?}?>
    </td>
  </tr>
  <?}?>
</table>

</form>