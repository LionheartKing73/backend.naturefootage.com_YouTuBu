<?if($inquiries) {?>
<table cellpadding="4" cellspacing="1" border="0" width="100%" class="listview">
  <tr>
    <th style="text-align: center" width="100">Date</th>
    <th>Clip reference no.</th>
    <th>Name</th>
    <th>Phone number</th>
    <th>Email address</th>
    <th>Comment</th>
    <th style="text-align: center">Action</th>
  </tr>
<?foreach ($inquiries as $inquiry) {?>
  <tr>
    <td align="right"><?=date('d.m.Y H:i', strtotime($inquiry['ctime']))?></td>
    <td><?=$inquiry['clip_code']?></td>
    <td><?=$inquiry['name']?></td>
    <td><?=$inquiry['phone']?></td>
    <td><?=$inquiry['email']?></td>
    <td><?=nl2br($inquiry['comment'])?></td>
    <td align="center">
      <a href="<?=$lang?>/invoices/inquiries/delete/<?=$inquiry['id']?>"
        onclick="return confirm('The inquiry will be deleted.');" class="mand">Delete</a>
    </td>
  </tr>
<?}?>
</table>
<?} else {?>
<p align="center">There are no enquiries.</p>
<?}?>