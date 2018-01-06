<form action="<?=$lang?>/rss/edit<?='/'.$id?>" method="post">

<table class="form_table" border="1" cellspacing="0" cellpadding="2">
<tr><td>

    <table border="0">
    <tr class="table_title"><td colspan="2" align="center" height="20"><?=$this->lang->line('news_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):</td></tr>
    <tr>
        <td class="form_label" width="100"><?=$this->lang->line('title');?>: <span class="mand">*</span></td>
        <td><input type="text" name="title" maxlength="255" size="70" value="<?=$title?>" class="field"> <input type="hidden" name="id" value="<?=$id?>"></td>
    </tr>
    
    <tr>
        <td class="form_label" width="100"><?=$this->lang->line('link');?>: <span class="mand">*</span></td>
        <td><input type="text" name="url" maxlength="255" size="70" value="<?=$url?>" class="field"></td>
    </tr>

    <tr>
        <td class="form_label"><?=$this->lang->line('language');?>: <span class="mand">*</span></td>
        <td>
          <select name="lang">
          <?foreach($lgs as $k=>$v):?>
             <option value="<?=$k?>" <?if($lg==$k) echo 'selected'?>> <?=$v?><br>
          <?endforeach;?>
          </select>
          
        </td>
    </tr>
    <tr><td colspan="2" align="center"><input type="submit" value="<?=$this->lang->line('save');?>" class="sub" name="save"></td></tr>
    </table>
</td></tr>
</table>
</form>
