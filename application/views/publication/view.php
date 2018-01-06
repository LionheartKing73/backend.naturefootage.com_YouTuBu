<form name="pages" action="<?=$lang?>/publication/view" method="post">

  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">
 
  <div class="btn-group toolbar-item">
  <? if ($this->permissions['publication-edit']) : ?>
    <a class="btn" href="<?=$lang?>/publication/edit"><?=$this->lang->line('add')?></a>
  <? endif; ?>
  <? if ($this->permissions['publication-visible']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.pages, 'id[]')) change_action(document.pages,'<?=$lang?>/publication/visible');"><?=$this->lang->line('visible');?></a>
  <? endif; ?>
  <? if ($this->permissions['publication-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.pages, 'id[]')) change_action(document.pages,'<?=$lang?>/publication/delete');"><?=$this->lang->line('delete');?></a>
  <? endif; ?>
  </div>

  <div class="toolbar-item">

      <div class="controls-group">
      <label for="words"><?=$this->lang->line('search')?>:</label>
    <input type="text" name="words" value="<?=$filter['words']?>">
          </div>
      <div class="controls-group">
    <input type="submit" value="<?=$this->lang->line('find')?>" class="btn find">
          </div>

      <div class="controls-group">
      <label for="active"><?=$this->lang->line('filter')?>:</label>
    <select name="active" id="active" onchange="javascript: change_action(document.pages,'')" style="width: auto">
      <option value="0" <? if(!$filter['active']) echo 'selected';?>><?=$this->lang->line('all');?>
      <option value="1" <? if($filter['active']==1) echo 'selected';?>><?=$this->lang->line('nothidden');?>
      <option value="2" <? if($filter['active']==2) echo 'selected';?>><?=$this->lang->line('hidden');?>
    </select>
          </div>
  </div>
  
  <br class="clr">

<table class="table table-striped">
<tr>
    <th><input type="checkbox" name="sample" onclick="select_all(document.pages)"></th>
    <th><a href="<?=$uri?>/sort/title"><?=$this->lang->line('title')?></a></th>
    <th><a href="<?=$uri?>/sort/alias1"><?=$this->lang->line('pages_alias')?></a></th>
    <th><a href="<?=$uri?>/sort/alias2"><?=$this->lang->line('pages_alias_alt')?></a></th>
    <th><a href="<?=$uri?>/sort/active"><?=$this->lang->line('status')?></a></th>
    <th><a href="<?=$uri?>/sort/ctime"><?=$this->lang->line('date')?></a></th>
    <th class="col-action"><?=$this->lang->line('action')?></th>
</tr>

<? if($pages): foreach($pages as $page):?>   
<tr class="tdata1">  
    <td><input type="checkbox" name="id[]" value="<?=$page['id']?>"></td>
    <td><?=$page['title']?></td>
    <td><? if($page['alias1']) echo $page['alias1']?></td>
    <td><? if($page['alias2']) echo $page['alias2']?></td>
    <td><? if($page['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
    <td><?=$page['ctime']?></td>
    
    <td>
    <?
      get_actions(array(
        array('display' => $this->permissions['publication-edit'], 'url' => $lang.'/publication/edit/'.$page['id'], 'name' => $this->lang->line('edit')),
        (($page['predefined'] == 1)? array() : array('display' => $this->permissions['publication-delete'], 'url' => $lang.'/publication/delete/'.$page['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm')))
      ));
    ?>
    </td>
</tr>
<? endforeach; else:?>
<tr><td colspan="7" class="empty-list"><?=$this->lang->line('empty_list');?></td></tr>
<? endif;?>
  
</table>
</form>

<?if($pages){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>