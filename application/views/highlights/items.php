<?if($id && $this->permissions['cats-edit']){?>
<strong class="toolbar-item">
	<?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
	<a href="<?=$lang?>/cats/edit/<?=$id?>" class="btn">
		<?=$this->lang->line('edit')?>
	</a>
</div>

<br class="clr">
<?}?>

<form action="<?= $lang ?>/cats/items/<?= $id ?>" method="post" class="form-horizontal well">
	<fieldset>
		<label style="display: inline">
			Add clip with code:
			<input type="text" name="code" class="field">
		</label>
		<input type="submit" name="add" value="Add" class="btn btn-primary">
	</fieldset>
</form>


<?if ($items) {?>
<form action="<?=$lang?>/cats/items/<?=$id?>" method="post" class="form well">
	<?foreach ($items as $clip) {?>

	<div class="thumb">
		<img src="<?= $clip['thumb'] ?>" alt="<?= $clip['code'] ?>" width="100">
		<br>
		<label>
			<input type="checkbox" name="id[]" value="<?= $clip['id'] ?>">
			<?=$clip['code']?>
		</label>
		<br>
		<label>
			Order:
			<input type="text" name="ord[<?= $clip['id'] ?>]" value="<?= $clip['ord'] ?>" maxlength="5">
		</label>
	</div>

	<?}?>
  <br class="clr">
  
  <input type="submit" name="del" value="Remove selected" class="btn btn-primary"
    onclick="return confirm('Selected items will be removed.');">
  &nbsp;
  <input type="submit" name="order" value="Save order" class="btn btn-primary">
</form>
<?}?>
