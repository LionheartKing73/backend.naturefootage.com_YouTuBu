<form name="rss" action="<?=$lang?>/rss/view" method="post">

<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr class="table_title" height="20">
    <td width="30" align="center">#</td>
    <td><?=$this->lang->line('title');?></td>
    <td width="100" align="center"><?=$this->lang->line('action');?></td>
</tr>

<?php if($items): foreach($items as $k=>$item):?>   
<tr class="tdata1" height="20">  
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><?=$k+1?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$item['title']?></td>

    <td onmouseover='light(this);' onmouseout='dark(this);' align="center">
    <?php
      get_actions(array(
        array('display' => $this->permissions['rss-publish'], 'url' => $lang."/rss/publish/".$channel."/".($k+1), 'name' => $this->lang->line('publish'))
      ));
    ?>     
    </td>                               
</tr>
<?php endforeach; else:?>
<tr class="tdata1"><td colspan="5" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?php endif;?>
  
</table>
</form>