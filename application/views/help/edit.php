<form action="<?=$lang?>/help/edit<?='/'.$id?>" method="post" enctype="multipart/form-data">

<table border="0" cellspacing="0" cellpadding="0">
<tr><td>

<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>

    <table border="0">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('help_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>
    <tr>
        <td class="form_label" width="100"><?=$this->lang->line('title');?>: <span class="mand">*</span></td>
        <td><input type="text" name="title" maxlength="255" size="70" value="<?=$title?>" class="field"> <input type="hidden" name="id" value="<?=$id?>"></td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('annotation');?>: <span class="mand">*</span></td>
        <td><textarea name="annotation" class="ta" style="width:400px; height:80px"><?=$annotation?></textarea></td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('order');?>: </td>
        <td><input type="text" name="ord" maxlength="255" size="3" value="<?=$ord?>" class="field"></td>
    </tr>
    
    <?if($parents):?> 
    <tr>
        <td class="form_label"><?=$this->lang->line('parent');?>: </td>
        <td>
        
          <select name="parent_id">
          <option value="0">
          
          <?foreach($parents as $parent):?>
             <option value="<?=$parent['id']?>" <?if($parent['id']==$parent_id) echo 'selected'?>> <?=$parent['title']?><br>
          <?endforeach;?>
          
          </select>
        </td>
    </tr>
    <?endif;?> 

    <tr>
        <td class="form_label"><?=$this->lang->line('pdf');?>: </td>
        <td><input type="file" name="mpdf" class="field" size="57"></td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('video');?>: </td>
        <td><input type="file" name="mvid" class="field" size="57"></td>
    </tr>
    
    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
</td></tr>
</table>

</td>

<td width="10"></td>
<td valign="top">

  <?if($path):?>
  <form action="newsletters/attach/8" method="post">
  <table class="form_table" border="1" cellspacing="0" cellpadding="2">
  <tr><td>

    <table border="0">
    <tr class="table_title"><td colspan="2" align="center"><?=$this->lang->line('help_attach');?>:</td></tr>
    <tr><td colspan="2">
    
      <table border="0" width="100%" cellpadding="3" cellspacing="1">
      <tr class="table_title">
        <td width="150"><?=$this->lang->line('file');?></td>
        <td width="70" align="center"><?=$this->lang->line('video');?></td>
      </tr>
      
      <?if($path['pdfpath']):?> 
      <tr class="tdata1">
        <td><a href="<?=$path['pdfpath']?>">PDF document</a></td>
        <td align="center"><a href="help/delres/pdf/<?=$id?>"><?=$this->lang->line('delete');?></a></td>
      </tr>
      <?endif;?>
      
      <?if($path['vidpath']):?> 
      <tr class="tdata1">
        <td><a href="<?=$path['vidpath']?>">Video file</a></td>
        <td align="center"><a href="help/delres/video/<?=$id?>"><?=$this->lang->line('delete');?></a></td>
      </tr>
      <?endif;?>
      
      </table>

     </td></tr>
    </table>
  <?endif;?> 
  
</td>
</tr>
</table>  
  </form>
  
<?if($picture):?>

<table border="0" cellspacing="0" cellpadding="2">
  <tr><td align="center"><img src="<?=$picture?>" border="0" style="border:solid 1px #efefef"></td></tr>
  <tr><td align="center">
    <input type="hidden" name="sid" value="<?=$sid;?>">
    <input type="submit" value="<?=$this->lang->line('delete');?>" class="sub" name="delete">
  </td></tr>
</table>

<?endif;?>   

</td></tr>
</table>

</form>
