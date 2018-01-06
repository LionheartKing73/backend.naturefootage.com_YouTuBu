<form name="editors" action="<?=$lang?>/editors/view" method="post">

  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">

  <div class="btn-group toolbar-item">
   <? if ($this->permissions['editors-visible']) : ?>
     <a class="btn" href="javascript: if (check_selected(document.editors, 'id[]')) change_action(document.editors,'<?=$lang?>/editors/visible');"><?=$this->lang->line('visible')?></a>
   <? endif; ?>
   <? if ($this->permissions['editors-delete']) : ?>
     <a class="btn" href="javascript: if (check_selected(document.editors, 'id[]')) change_action(document.editors,'<?=$lang?>/editors/delete');"><?=$this->lang->line('delete')?></a>
   <? endif; ?>
  </div>

  <div class="toolbar-item">
    <label for="words"><?=$this->lang->line('search')?>:</label>
    <input type="text" name="words" value="<?=$filter['words']?>">
    <input type="submit" value="<?=$this->lang->line('find')?>" class="btn find">
  </div>
 
  <div class="toolbar-item">
    <label for="active"><?=$this->lang->line('filter')?>:</label>
    <select name="active" id="active" onchange="change_action(document.editors,'')" style="width: auto">
      <option value="0" <? if(!$filter['active']) echo 'selected'?>><?=$this->lang->line('all')?>
      <option value="1" <? if($filter['active']==1) echo 'selected'?>><?=$this->lang->line('nothidden')?>
      <option value="2" <? if($filter['active']==2) echo 'selected'?>><?=$this->lang->line('hidden')?>
    </select>
  </div>
  <br class="clr">

<table class="table table-striped">
  <tr>
    <th><input type="checkbox" name="sample" onclick="select_all(document.editors)"></th>
    <th><a href="<?=$uri?>/sort/fname" class="title"><?=$this->lang->line('fname')?></a></th>
    <th><a href="<?=$uri?>/sort/lname" class="title"><?=$this->lang->line('lname')?></a></th>
    <th><a href="<?=$uri?>/sort/email" class="title"><?=$this->lang->line('email')?></a></th> 
    <th><a href="<?=$uri?>/sort/company" class="title"><?=$this->lang->line('company')?></a></th> 
    <th><a href="<?=$uri?>/sort/city" class="title"><?=$this->lang->line('city')?></a></th>
    <th><a href="<?=$uri?>/sort/country" class="title"><?=$this->lang->line('country')?></a></th>   
    <th><a href="<?=$uri?>/sort/active" class="title"><?=$this->lang->line('status')?></a></th>
    <th><a href="<?=$uri?>/sort/ctime" class="title"><?=$this->lang->line('date')?></a></th>
    <th><?=$this->lang->line('action')?></th>
  </tr>

<?if($editors): foreach($editors as $editor):?>
  <tr class="tdata1">
    <td align="center"><input type="checkbox" name="id[]" value="<?=$editor['id']?>"></td>
    <td><?=$editor['fname']?></td>
    <td><?=$editor['lname']?></td>
    <td><?=$editor['email']?></td>
    <td><?=$editor['company']?></td>
    <td><?=$editor['city']?></td>
    <td><?=$editor['country']?></td>
    <td><? if($editor['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
    <td><?=strftime('%d.%m.%Y', strtotime($editor['ctime']))?></td>    
    <td>
    <?
      get_actions(array(
        array('display' => $this->permissions['editors-details'], 'url' => $lang.'/editors/details/'.$editor['id'], 'name' => $this->lang->line('details')),
        array('display' => $this->permissions['editors-delete'], 'url' => $lang.'/editors/delete/'.$editor['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>
    </td>
  </tr>
<?endforeach; else:?>
<tr><td colspan="10" class="empty-list"><?=$this->lang->line('empty_list')?></td></tr>
<?endif?>
  
</table>
</form>

<?if($editors){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>