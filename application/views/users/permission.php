<form action="<?=$lang?>/users/permission<?='/'.$id?>" method="post" name="perm">

<table class="form_table" border="1" cellspacing="0" cellpadding="2">

<tr><td>

    <table border="0" width="100%" cellpadding="1" cellspacing="1"> 
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('users_perm');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>  
    
    <tr class="table_title">
      <td width="30" align="center"><input type="checkbox" name="sample" onclick="javascript:select_all(document.perm);"></td>
      <td width="300"><?=$this->lang->line('title');?></td>
    </tr>
    
    <?foreach($tree as $item):?>
      <tr class="tdata1">  
        <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><input type="checkbox" name="id[]" value="<?=$item['id']?>" <?if($item['checked']) echo 'checked';?>></td>
        <td onmouseover='light(this);' onmouseout='dark(this);'><b><?=$item['name']?></b></td>
      </tr>  
      
      <?if(count($item['child'])):?>
        <?foreach($item['child'] as $item1):?> 
        <tr class="tdata1">  
          <td onmouseover='light(this);' onmouseout='dark(this);' align="center"><input type="checkbox" name="id[]" value="<?=$item1['id']?>" <?if($item1['checked']) echo 'checked';?>></td>
          <td onmouseover='light(this);' onmouseout='dark(this);'>&nbsp;&nbsp;&nbsp;<?=$item1['name']?></td>
        </tr>
        <?endforeach;?>
      <?endif;?>
    <?endforeach;?>
    
    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
  </table>
    
</td></tr>
</table>
</form>
