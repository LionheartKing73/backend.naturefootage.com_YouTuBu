<form action="<?=$lang?>/rm/view" method="post" class="form-horizontal well">
  <fieldset>
    <legend>
      <?=$this->lang->line('rm_edit')?> (<?=$this->lang->line('required_fields')?> <span class="mand">*</span>):
    </legend>


    
<?foreach($sets as $type=>$items){?>
    <h4>
      <?=$this->lang->line('rm_type'.$type)?> <?=$this->lang->line('coefficient')?>
    </h4>
    
  <?foreach($items as $set){?>
    <div class="control-group">
      <label class="control-label" for="sets[<?=$set['id']?>]">
        <?=$set['name']?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="sets[<?=$set['id']?>]" id="sets[<?=$set['id']?>]" maxlength="255" size="30" value="<?=$set['value']?>">
      </div>
    </div>
  <?}
}?>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>
  </fieldset>
</form>
