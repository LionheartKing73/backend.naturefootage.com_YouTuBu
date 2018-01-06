<form action="<?=$lang?>/publication/edit<?='/'.$id.(($visual)?'/visual':'')?>"
  method="post" class="form-horizontal well">
  <fieldset>
    <legend>
      <?=$this->lang->line('pages_edit')?> (<?=$this->lang->line('required_fields')?> <span class="mand">*</span>):
    </legend>

    <div class="control-group">
      <label class="control-label" for="title">
        <?=$this->lang->line('title')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="title" id="title" maxlength="255" size="70" value="<?=$title?>">
        <input type="hidden" name="id" value="<?=$id?>">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="body">
        <?=$this->lang->line('content')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <textarea name="body" id="body"><?=$body?></textarea>
        <?=fck(750)?>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="alias1">
        <?=$this->lang->line('pages_alias')?>:
      </label>
      <div class="controls">
        <input type="text" name="alias1" id="alias1" maxlength="255" size="50" value="<?=$alias1?>">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="alias2">
        <?=$this->lang->line('pages_alias_alt')?>:
      </label>
      <div class="controls">
        <input type="text" name="alias2" id="alias2" maxlength="255" size="50" value="<?=$alias2?>">
      </div>
    </div>

<?if(!$visual){?>
    <div class="control-group">
      <label class="control-label" for="meta_title">
        <?=$this->lang->line('meta_title')?>:
      </label>
      <div class="controls">
        <input type="text" name="meta_title" id="meta_title" maxlength="255" size="80" value="<?=$meta_title?>">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="meta_desc">
        <?=$this->lang->line('meta_desc')?>:
      </label>
      <div class="controls">
        <textarea name="meta_desc" id="meta_desc" cols="80" rows="4"><?=$meta_desc?></textarea>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="meta_keys">
        <?=$this->lang->line('meta_keys')?>:
      </label>
      <div class="controls">
        <textarea name="meta_keys" id="meta_keys" cols="80" rows="4"><?=$meta_keys?></textarea>
      </div>
    </div>
<?}?>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>
  </fieldset>
</form>
