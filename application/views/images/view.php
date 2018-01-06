<?if($menu) echo $menu;?>

<form name="imgs" action="<?=$lang?>/images/view" method="post">

<table width="100%" cellpadding="5" cellspacing="1" border="0">
<tr class="action_title">
 <td width="40"><b><?=$this->lang->line('action');?>:</b> <input type="hidden" name="filter" value="1"></td>
 <td width="220">
   <? if ($this->permissions['images-edit']) : ?>
     <a class="action" href="<?=$lang?>/images/edit"><?=$this->lang->line('add');?></a>
   <? endif; ?>
   <? if ($this->permissions['images-visible']) : ?> 
     <a class="action" href="javascript: if (check_selected(document.imgs, 'id[]')) change_action(document.imgs,'<?=$lang?>/images/visible');"><?=$this->lang->line('visible');?></a>
   <? endif; ?>
   <? if ($this->permissions['images-delete']) : ?>
     <a class="action" href="javascript: if (check_selected(document.imgs, 'id[]')) change_action(document.imgs,'<?=$lang?>/images/delete');"><?=$this->lang->line('delete');?></a>
   <? endif; ?>
 </td>
 
 <td width="40"><b><?=$this->lang->line('search');?>:</b></td>
 <td>
    <input type="text" name="words" size="20" value="<?=$filter['words']?>" class="field"> 
    <input type="submit" value="<?=$this->lang->line('find');?>" class="sub">  
 </td>

 <td width="40"><b><?=$this->lang->line('filter');?>:</b></td>
 <? if($clients): ?>
 <td width="50">
  <select name="client" onchange="javascript: change_action(document.imgs,'')">
    <option value="0" <? if(!$filter['client']) echo 'selected';?>><?=$this->lang->line('all');?>
      <? foreach ($clients as $client): ?>
        <option value="<?=$client['id']?>" <? if($filter['client']==$client['id']) echo 'selected';?>><?=$client['name']?>
      <? endforeach; ?>
  </select>
</td>
<? endif; ?>
 
 <td width="60">
  <select name="active" onchange="javascript: change_action(document.imgs,'')">
    <option value="0" <? if(!$filter['active']) echo 'selected';?>><?=$this->lang->line('all');?>
    <option value="1" <? if($filter['active']==1) echo 'selected';?>><?=$this->lang->line('nothidden');?>
    <option value="2" <? if($filter['active']==2) echo 'selected';?>><?=$this->lang->line('hidden');?>
  </select>
  
</td>

 <td width="50">
  <select name="rights" onchange="javascript: change_action(document.imgs,'')">
    <option value="0" <? if(!$filter['rights']) echo 'selected';?>><?=$this->lang->line('all');?>
    <option value="1" <? if($filter['rights']==1) echo 'selected';?>>RF
    <option value="2" <? if($filter['rights']==2) echo 'selected';?>>RM
  </select>
  
</td>

</tr>
</table>

<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr class="table_title">
    <td width="30" align="center"><input type="checkbox" name="sample" onclick="javascript:select_all(document.imgs);"></td>
    <td width="105"><a href="<?=$uri?>/sort/resource" class="title"><?=$this->lang->line('thumbnail');?></a></td> 
    <td><a href="<?=$uri?>/sort/title" class="title"><?=$this->lang->line('title');?></a></td>
    <td width="100"><a href="<?=$uri?>/sort/code" class="title"><?=$this->lang->line('code');?></a></td>
    <td width="100"><a href="<?=$uri?>/sort/active" class="title"><?=$this->lang->line('status');?></a></td> 
    <td width="120"><a href="<?=$uri?>/sort/ctime" class="title"><?=$this->lang->line('date');?></a></td>
    <td width="200" align="center"><?=$this->lang->line('action');?></td>
</tr>

<?php if($images): foreach($images as $image):?>   
<tr class="tdata1">  
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><input type="checkbox" name="id[]" value="<?=$image['id']?>"></td>
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><img src="<?=$image['thumb']?>"></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$image['title']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$image['code']?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><? if($image['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden');?></td>
    <td onmouseover='light(this);' onmouseout='dark(this);'><?=$image['ctime']?></td>
    
    <td onmouseover='light(this);' onmouseout='dark(this);' align="center">
    <?php
      get_actions(array(
        array('display' => $this->permissions['images-edit'], 'url' => $lang.'/images/edit/'.$image['id'], 'name' => $this->lang->line('edit')),
        array('display' => $this->permissions['images-cats'], 'url' => $lang.'/images/cats/'.$image['id'], 'name' => $this->lang->line('cats')),
        array('display' => $this->permissions['images-delete'], 'url' => $lang.'/images/delete/'.$image['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
      ));
    ?>
    </td>
</tr>
<?php endforeach; else:?>
<tr class="tdata1"><td colspan="7" align="center" height="25"><?=$this->lang->line('empty_list');?></td></tr>
<?php endif;?>
  
</table>
</form>