<form method="post" class="form-horizontal well">
  <fieldset>
    <legend>
      Edit Location
    </legend>

<?if($parent_type) {?>
    <div class="control-group">
      <label class="control-label" for="parent_id">
        <?=$parent_type?>:
      </label>
      <div class="controls">
        <select name="parent_id" id="parent_id">
        <?foreach ($parents as $parent) {?>
          <option value="<?=$parent['id']?>"<?if ($parent['id']==$parent_id) echo 'selected="selected"';?>>
            <?=$parent['name']?>
          </option>
        <?}?>
        </select>
      </div>
    </div>
<?}?>

    <div class="control-group">
      <label class="control-label" for="name">
        <?=$location_type?> name:
      </label>
      <div class="controls">
        <input type="text" name="name" id="name" value="<?=$location['name']?>">
      </div>
    </div>

      <!--<?if(!$parent_type) {?>
      <div class="control-group">
          <label class="control-label" for="clip_code">
              Clip code:
          </label>
          <div class="controls">
              <input type="text" name="clip_code" id="clip_code" value="<?=$location['clip_code']?>">
          </div>
      </div>
      <?}?>-->

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>

  </fieldset>
</form>