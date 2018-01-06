<form name="groups" action="<?=$lang?>/groups/permissions/<?=$this->id?>" method="post" class="well form-inline">
  <fieldset>
    <legend>
      <?=$this->lang->line('permissions')?>
    </legend>

<?if($permissions) {
  foreach($permissions as $code => $permission) {?>
  <label>
    <input type="checkbox" name="id[]" id="chk<?=$permission['class']['id']?>"
      value="<?=$permission['class']['id']?>" <? echo $permission['class']['value'] ? 'checked' : '' ?>>
    <?=$code?>
  </label>
  <br>

    <?if($permission['actions']) {
      ksort($permission['actions']);?>
  <p class="subitem">
      <?foreach($permission['actions'] as $code => $permission) {?>
  <label>
    <input type="checkbox" name="id[]" value="<?=$permission['id']?>" <? echo $permission['value'] ? 'checked' : '' ?>>
    <?
      $label = $this->lang->line($code);
      if (empty($label)) $label = ucfirst($code);
      echo $label;
    ?>
  </label>
      <?}?>
  </p>
    <?}
  }?>

  <div class="form-actions">
    <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
  </div>

<?} else {?>
<?=$this->lang->line('empty_list')?>
<?}?>
  </fieldset>
</form>