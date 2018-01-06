<script type="text/javascript">
  function addItem(elem) {
    document.getElementById("addItem").style.display = "";
    elem.style.display = "none";
    document.getElementById("newItem").focus();
  }
</script>

<form method="post" action="/<?=$lang?>/footagetypes/save.html">
	<strong class="toolbar-item">
		<?=$this->lang->line('action')?>:
	</strong>
	
	<div class="btn-group toolbar-item">
<?if ($this->permissions['footagetypes-save']) {?>
		<input type="submit" class="btn" value="Save">
		<input type="button" class="btn" value="<?=$this->lang->line('add')?>" onclick="addItem(this)">
<?}?>
	</div>
	
	<br class="clr">

<table class="table" style="margin-top: 10px; width: auto">
  <tr>
    <th>#</th>
    <th>Name</th>
    <th>Action</th>
  </tr>
  <tr id="addItem" style="display: none">
    <td>
      <input type="hidden" name="id[]" value="0">
    </td>
    <td>
      <input type="text" id="newItem" name="name[]" value="">
    </td>
    <td>
      <input type="submit" class="btn btn-primary" value="Save">
    </td>
  </tr>
<?
  if ($footage_types) {
  $i = 0; foreach ($footage_types as $item) {
?>
  <tr>
    <td style="padding-right: 5px; text-align: right">
      <?=++$i?>
      <input type="hidden" name="id[]" value="<?=$item->id?>">
    </td>
    <td>
      <input type="text" name="name[]" value="<?=$item->name?>">
    </td>
    <td>
    <?if ($this->permissions['footagetypes-delete']) {?>
      <a class="btn btn-primary" href="<?=$lang?>/footagetypes/delete/<?=$item->id?>" onclick="return confirm('The item will be deleted.');">
        <?=$this->lang->line('delete')?>
      </a>
    <?}?>
    </td>
  </tr>
  <?
    }
  }
  ?>
</table>
</form>