<form action="<?=$lang?>/rm/" method="post">

<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>

    <table border="0">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('rm_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>
    
    <?php foreach($sets as $type=>$items):?> 
    <tr height="20" class="tdata1"><td><?=$this->lang->line('rm_type'.$type);?></td><td>Coefficient</td></tr> 
    
    <?php foreach($items as $set):?>
    <tr>
        <td class="form_label" width="160"><?=$set['name']?>: <span class="mand">*</span></td>
        <td><input type="text" name="sets[<?=$set['id']?>]" maxlength="255" size="30" value="<?=$set['value']?>" class="field"></td>
    </tr>
    <?endforeach;?>
      

    <?php endforeach;?>  

    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
</td></tr>
</table>
</form>
