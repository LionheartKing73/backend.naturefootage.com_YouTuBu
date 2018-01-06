<strong class="toolbar-item">
	<?=$this->lang->line('action')?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
      <?if ($this->permissions['highlights-edit']) : ?>
      <a class="btn" href="<?=$lang?>/highlights/edit"><?=$this->lang->line('add')?></a>
      <?endif?>
      <?if ($this->permissions['highlights-visible']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.highlights, 'id[]')) change_action(document.highlights,'<?=$lang?>/highlights/visible')">
		  <?=$this->lang->line('visible')?>
	  </a>
      <?endif?>
      <? if ($this->permissions['highlights-ord']) : ?>
      <a class="btn" href="javascript: change_action(document.highlights,'<?=$lang?>/highlights/ord')">
		  <?=$this->lang->line('save_ord')?></a>
      <?endif?>
      <?if ($this->permissions['highlights-delete']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.highlights, 'id[]')) change_action(document.highlights,'<?=$lang?>/highlights/delete')">
		  <?=$this->lang->line('delete')?>
	  </a>
      <?endif?>	
</div>

<br class="clr">

<form name="highlights" action="<?=$lang?>/highlights/view" method="post">
  <table class="table table-striped">
    <tr>
      <th width="30" align="center">
        <input type="checkbox" name="sample" onclick="javascript:select_all(document.highlights)">
      </th>
      <th><?=$this->lang->line('title')?></th>
      <th><?=$this->lang->line('thumbnail')?></th>
      <th><?=$this->lang->line('order')?></th>
      <th><?=$this->lang->line('status')?></th>
      <th><?=$this->lang->line('date')?></th>
      <th><?=$this->lang->line('action')?></th>
    </tr>

    <?if($highlights):?>

      <?foreach($highlights as $highlight):?>
    <tr>
      <td>
        <input type="checkbox" name="id[]" value="<?=$highlight['id']?>">
      </td>
      <td>
        <?=$highlight['title']?>
      </td>
      <td>
        <img src="<?=$highlight['thumb']?>?date=<?=strftime('%Y%m%d%H%M%S', strtotime($highlight['mtime']))?>" width="70">
        &nbsp;
      </td>
      <td><input type="text" name="ord[<?=$highlight['id']?>]" style="width:30px" value="<?=$highlight['ord']?>"></td>
      <td><?if($highlight['active']) echo 'published'; else echo 'unpublished'?></td>
      <td><?=$highlight['ctime']?></td>
      <td>
            <?
            get_actions(array(
                array('display' => $this->permissions['highlights-edit'], 'url' => $lang.'/highlights/edit/'.$highlight['id'], 'name' => $this->lang->line('edit')),
                array('display' => $this->permissions['highlights-delete'], 'url' => $lang.'/highlights/delete/'.$highlight['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
            ))
            ?>
      </td>
    </tr>

      <?endforeach?>

    </td></tr>
    <?else:?>
    <tr><td colspan="7" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
    <?endif?>
  </table>

</table>
</form>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>