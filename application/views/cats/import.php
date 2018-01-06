<div class="action_title">
  <b><?=$this->lang->line('action');?>:</b>
  &nbsp;
  <? if ($this->permissions[$module.'-view']) : ?>
  <a class="action" href="<?=$lang?>/<?=$module?>/view">View</a>
  <?php endif;?>

  <? if ($this->permissions[$module.'-edit']) : ?>
  <a class="action" href="<?=$lang?>/<?=$module?>/edit"><?=$this->lang->line('add');?></a>
  <?php endif;?>
</div>

<form action="<?=$lang?>/collections/import" method="post" enctype="multipart/form-data">
  Upload csv-file:
  <input type="file" name="csv_file" class="field">
  <input type="submit" name="upload" class="sub" value="Upload">
  &nbsp;&nbsp;
  <a href="/data/example/collections.csv" target="_blank">csv-file example</a>
</form>

<?if ($import_result) {?>
<br><br>
Total rows: <?=$import_result['total']?><br>
Imported: <?=$import_result['success']?><br>
Failed: <?=$import_result['failed']?>
<?}?>