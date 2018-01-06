<form name="curs" action="<?=$lang?>/currencies/edit/<?=$id?>" method="post" class="form-horizontal well">

  <fieldset>
    <legend>
      <?=$this->lang->line('currencies_edit')?>
    </legend>

    <div class="control-group">
      <label class="control-label" for="title">
        <?=$this->lang->line('title')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="title" id="title" maxlength="255" value="<?=$title?>">
        <input type="hidden" name="item_id" value="<?=$res['id']?>">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="code">
        <?=$this->lang->line('code')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="code" id="code" maxlength="255" value="<?=$code?>">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="rate">
        <?=$this->lang->line('rate')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="rate" id="rate" maxlength="255" value="<?=$rate?>">
      </div>
    </div>

  
<?if(!$set_default || ($set_default && $is_default)){?>
    <div class="control-group">
      <label class="control-label" for="is_default">
        <?=$this->lang->line('default_currency')?>:
      </label>
      <div class="controls">
        <input type="checkbox" name="is_default" id="is_default" value="1" <?if($is_default) echo "checked"?>>
      </div>
    </div>
<?}?>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>
  </fieldset>
</form>