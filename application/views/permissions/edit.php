<form action="<?=$lang?>/permissions/edit/<?=$this->id?>" method="post" class="form-horizontal well">
  <fieldset>
    <legend>
      <?=$this->lang->line('permissions_add')?>
    </legend>

    <div class="control-group">
      <label class="control-label" for="code">
        <?=$this->lang->line('permissions_code')?>
      </label>
      <div class="controls">
        <input type="text" name="code" id="code" maxlength="255" value="<?=$code?>">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="is_client">
        <?=$this->lang->line('permissions_group_type')?>
      </label>
      <div class="controls">
        <select name="is_client" id="is_client">
          <option value="0"><?=$this->lang->line('type_admin')?></option>
          <option value="1" <?echo $is_client ? 'selected' : ''?>><?=$this->lang->line('type_client')?></option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('add')?>" name="save">
    </div>
  </fieldset>
</form>
