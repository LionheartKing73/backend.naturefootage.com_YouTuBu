<form action="<?=$lang?>/groups/edit<?='/'.$id?>" method="post" class="form-horizontal well">
  <fieldset>
    <legend>
      <?=$this->lang->line('users_group_edit');?> (<?=$this->lang->line('required_fields');?> <span class="mand">*</span>):
    </legend>

    <div class="control-group">
      <label class="control-label" for="title">
        <?=$this->lang->line('title')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="title" id="title" maxlength="255" value="<?=$title?>">
        <input type="hidden" name="id" value="<?=$id?>">
      </div>
    </div>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>
  </fieldset>
</form>
