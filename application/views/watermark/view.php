<form action="<?=$lang?>/watermark/view<?='/'.$id?>" method="post" enctype="multipart/form-data">

<table class="form_table" border="1" cellspacing="0" cellpadding="2" width="500">
<tr><td>
    
    <table border="0" width="100%">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('watermark_image_title');?>:</td></tr>
    
    <?php if($image): ?>
    <tr class="tdata1">
        <td class="bg_blue" width="400"><?php echo "<img src='".$image."' border='1'>" ?></td>
        <td class="bg_blue" width="100" align="center">
        <?php
          get_actions(array(
            array('display' => $this->permissions['watermark-delete'], 'url' => $lang.'/watermark/delete', 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
          ));
        ?>  
        </td>
    </tr>
    <?php endif; ?>
    <? if ($this->permissions['watermark-edit']) : ?>
    <tr>
        <td align="center" colspan="2"><input type="file" name="image" class="field" size="62"> <input type="submit" value="<?=$this->lang->line('upload');?>" class="sub" name="upload"></td>
    </tr>
    <? endif; ?>
    </table>
    
</td></tr>
</table>

<br>  

<table class="form_table" border="1" cellspacing="0" cellpadding="2" width="500">
<tr><td>
    
    <table border="0" width="100%">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('watermark_text_title');?>:</td></tr>
    <tr class="tdata1">
        <td><?=$text?></td>
        <td width="100" align="center">
        <?php
          get_actions(array(
            array('display' => $this->permissions['watermark-edit'], 'url' => $lang.'/watermark/edit', 'name' => $this->lang->line('edit'))
          ));
        ?>  
        </td>
    </tr>

    </table>
    
</td></tr>
</table>
<br>
</form>
