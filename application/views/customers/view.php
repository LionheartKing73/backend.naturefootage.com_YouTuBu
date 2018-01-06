<form name="customers" action="<?=$lang?>/customers/view" method="post">
  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">

 <div class="btn-group toolbar-item">
   <? if ($this->permissions['customers-visible']) : ?> 
     <a class="btn" href="javascript: if (check_selected(document.customers, 'id[]')) change_action(document.customers,'<?=$lang?>/customers/visible');"><?=$this->lang->line('visible')?></a>
   <? endif; ?>
   <? if ($this->permissions['customers-delete']) : ?>
     <a class="btn" href="javascript: if (check_selected(document.customers, 'id[]')) change_action(document.customers,'<?=$lang?>/customers/delete');"><?=$this->lang->line('delete')?></a>
   <? endif; ?>  
 </div>

  <div class="toolbar-item">
    <label for="words"><?=$this->lang->line('search')?>:</label>
    <input type="text" name="words" id="words" value="<?=$filter['words']?>">
    <input type="submit" value="<?=$this->lang->line('find')?>" class="btn find">
 </div>

  <div class="toolbar-item">
    <label for="active"><?=$this->lang->line('filter')?>:</label>
    <select name="active" id="active" onchange="change_action(document.customers,'')" style="width: auto">
      <option value="0" <? if(!$filter['active']) echo 'selected'?>><?=$this->lang->line('all')?>
      <option value="1" <? if($filter['active']==1) echo 'selected'?>><?=$this->lang->line('nothidden')?>
      <option value="2" <? if($filter['active']==2) echo 'selected'?>><?=$this->lang->line('hidden')?>
    </select>
  </div>
  <br class="clr">

<table class="table table-striped">
  <tr>
    <th><input type="checkbox" name="sample" onclick="javascript:select_all(document.customers);"></th>
    <th><a href="<?=$uri?>/sort/fname"><?=$this->lang->line('fname')?></a></th>
    <th><a href="<?=$uri?>/sort/lname"><?=$this->lang->line('lname')?></a></th>
    <th><a href="<?=$uri?>/sort/email"><?=$this->lang->line('email')?></a></th> 
    <!--<th><a href="<?=$uri?>/sort/company"><?=$this->lang->line('company')?></a></th>
    <th><a href="<?=$uri?>/sort/city"><?=$this->lang->line('city')?></a></th>
    <th><a href="<?=$uri?>/sort/country"><?=$this->lang->line('country')?></a></th>-->
    <th><a href="<?=$uri?>/sort/active"><?=$this->lang->line('status')?></a></th>
    <th><?=$this->lang->line('last_login')?></th>
    <th><a href="<?=$uri?>/sort/ctime"><?=$this->lang->line('date')?></a></th>
    <th><?=$this->lang->line('action')?></th>
  </tr>

<?if($customers): foreach($customers as $customer):?>
  <tr>
    <td><input type="checkbox" name="id[]" value="<?=$customer['id']?>"></td>
    <td><?=$customer['fname']?></td>
    <td><?=$customer['lname']?></td>
    <td><?=$customer['email']?></td>
    <!--<td><?=$customer['company']?></td>
    <td><?=$customer['city']?></td>
    <td><?=$customer['country']?></td>-->
    <td><?if($customer['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden')?></td>
    <td><? if($customer['last_login'] != '00.00.0000 00:00:00') echo $customer['last_login']; ?></td>
    <td><?=$customer['ctime']?></td>
    
    <td align="center">
    <?
      get_actions(array(
        array('display' => $this->permissions['customers-details'], 'url' => $lang.'/customers/details/'.$customer['id'], 'name' => $this->lang->line('details')),
        array('display' => $this->permissions['customers-delete'], 'url' => $lang.'/customers/delete/'.$customer['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>
    </td>
  </tr>
<? endforeach; else:?>
  <tr><td colspan="10" class="empty-list"><?=$this->lang->line('empty_list')?></td></tr>
<?endif?>
  
</table>
</form>

<?if($customers){?>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>