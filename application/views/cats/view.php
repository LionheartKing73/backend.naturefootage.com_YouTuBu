<strong class="toolbar-item">
	<?=$this->lang->line('action')?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
      <?if ($this->permissions['cats-edit']) : ?>
      <a class="btn" href="<?=$lang?>/cats/edit"><?=$this->lang->line('add')?></a>
      <?endif?>
      <?if ($this->permissions['cats-visible']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.cats, 'id[]')) change_action(document.cats,'<?=$lang?>/cats/visible')">
		  <?=$this->lang->line('visible')?>
	  </a>
      <?endif?>
      <? if ($this->permissions['cats-ord']) : ?>
      <a class="btn" href="javascript: change_action(document.cats,'<?=$lang?>/cats/ord')">
		  <?=$this->lang->line('save_ord')?></a>
      <?endif?>
      <?if ($this->permissions['cats-delete']) : ?>
      <a class="btn" href="javascript: if (check_selected(document.cats, 'id[]')) change_action(document.cats,'<?=$lang?>/cats/delete')">
		  <?=$this->lang->line('delete')?>
	  </a>
      <?endif?>	
</div>

<br class="clr">

<form name="cats" action="<?=$lang?>/cats/view" method="post">
  <table class="table table-striped">
    <tr>
      <th width="30" align="center">
        <input type="checkbox" name="sample" onclick="javascript:select_all(document.cats)">
      </th>
      <th><?=$this->lang->line('title')?></th>
      <th><?=$this->lang->line('code')?></th>
      <th><?=$this->lang->line('thumbnail')?></th>
      <th><?=$this->lang->line('order')?></th>
      <th><?=$this->lang->line('status')?></th>
      <th><?=$this->lang->line('date')?></th>
      <th><?=$this->lang->line('action')?></th>
    </tr>

    <?if($cats):?>

      <?foreach($cats as $cat):?>
    <tr>
      <td>
        <input type="checkbox" name="id[]" value="<?=$cat['id']?>">
      </td>
      <td>
        <?if(!$cat['parent_id'] && $cat['child']):?>
          <img src="data/img/admin/arrow.gif">
        <?endif?>
        &nbsp;
        <?=$cat['title']?>
      </td>
      <td><?=$cat['code']?></td>
      <td>
        <img src="<?=$cat['thumb']?>?date=<?=strftime('%Y%m%d%H%M%S', strtotime($cat['mtime']))?>" width="100">
        &nbsp;
      </td>
      <td><input type="text" name="ord[<?=$cat['id']?>]" style="width:30px" value="<?=$cat['ord']?>"></td>
      <td><?if($cat['active']) echo 'published'; else echo 'unpublished'?></td>
      <td><?=$cat['ctime']?></td>
      <td>
            <?
            get_actions(array(
                array('display' => $this->permissions['cats-edit'], 'url' => $lang.'/cats/edit/'.$cat['id'], 'name' => $this->lang->line('edit')),
                array('display' => $this->permissions['cats-items'], 'url' => $lang.'/cats/items/'.$cat['id'], 'name' => 'Items'),
                array('display' => $this->permissions['cats-delete'], 'url' => $lang.'/cats/delete/'.$cat['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
            ))
            ?>
      </td>
    </tr>

        <?if($cat['child']):?>
          <?foreach($cat['child'] as $item):?>
    <tr>  
      <td><input type="checkbox" name="id[]" value="<?=$item['id']?>"></td>
      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$item['title']?></td>
      <td><?=$item['code']?></td>
      <td>
        <img src="<?=$item['thumb']?>?date=<?=$item['mtime']?>" width="100">
        &nbsp;
      </td>
      <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['ord']?>"></td>
      <td><?if($item['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
      <td><?=$item['ctime']?></td>
      <td align="center">
                <?
                get_actions(array(
                    array('display' => $this->permissions['cats-edit'], 'url' => $lang.'/cats/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                    array('display' => $this->permissions['cats-items'], 'url' => $lang.'/cats/items/'.$item['id'], 'name' => 'Items'),
                    array('display' => $this->permissions['cats-delete'], 'url' => $lang.'/cats/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                ));
                ?>
      </td>
    </tr>
          <?endforeach?>
        <?endif?>

      <?endforeach?>

    </td></tr>
    <?else:?>
    <tr><td colspan="8" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
    <?endif?>
  </table>

</table>
</form>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>