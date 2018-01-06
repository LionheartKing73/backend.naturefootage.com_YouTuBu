<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr class="table_title">
    <td style="padding:3px;"><?=$this->lang->line('action');?></td> 
    <td width="120"><?=$this->lang->line('date');?></td>
</tr>

<?php if($logs): foreach($logs as $log):?>   
<tr class="tdata1">  
    <td style="padding:3px;" onmouseover='light(this);' onmouseout='dark(this);'><?=$log['action']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$log['ctime']?></td>
</tr>
<?php endforeach; else:?>
<tr class="tdata1"><td colspan="2" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?php endif;?>
  
</table>
