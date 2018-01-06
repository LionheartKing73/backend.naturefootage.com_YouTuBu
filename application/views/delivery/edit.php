<form method="post" class="form-horizontal well">
  <fieldset>
    <legend>
      <?=$action?> (<?=$this->lang->line('required_fields')?> <span class="mand">*</span>):
    </legend>

    <div class="control-group">
      <label class="control-label" for="name">
        <?=$this->lang->line('title')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="name" id="name" value="<?=$method['name']?>" maxlength="100">
      </div>
    </div>

    <!--<div class="control-group">
      <label class="control-label" for="cost">
        Cost: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="cost" id="cost" value="<?=$method['cost']?>">
      </div>
    </div>-->

    <div class="control-group">
      <label class="control-label" for="ord">
        Order:
      </label>
      <div class="controls">
        <input type="text" name="ord" id="ord" value="<?=$method['ord']?>">
      </div>
    </div>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>
  </fieldset>
</form>
