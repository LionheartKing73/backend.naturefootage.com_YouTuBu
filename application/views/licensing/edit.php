<!--
<?php
  if ($license['id'] != 1) {
?>

<div class="action_title">
  <b>Action: </b>
  <a class="action" href="<?=$lang?>/rm/view">View prices</a>
  <a class="action" href="<?=$lang?>/rm/edit">Edit prices</a>
</div>

<?}?>
-->

<form method="post" class="form-horizontal well">

    <fieldset>
        <legend>
            EDIT LICENSE DESCRIPTION
        </legend>

        <div class="control-group">
            <label class="control-label" for="name">
                Name:
            </label>
            <div class="controls">
                <input type="text" name="name" id="name" value="<?=$license['name']?>" disabled="disabled">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="description">
                Description:
            </label>
            <div class="controls">
                <textarea name="description" id="description"><?=$license['description']?></textarea>
                <script type="text/javascript" src="/vendors/fck/fckeditor.js"></script>
                <script type="text/javascript">
                    var oFCKeditor = new FCKeditor("description");
                    oFCKeditor.ToolbarSet="Custom";
                    oFCKeditor.Width=600;
                    oFCKeditor.Height=280;
                    oFCKeditor.BasePath="/vendors/fck/";
                    oFCKeditor.ReplaceTextarea();
                </script>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>">
        </div>

    </fieldset>

</form>

<script type="text/javascript">
   var oFCKeditor = new FCKeditor("description");
   oFCKeditor.ToolbarSet="Basic";
   oFCKeditor.Width=400;
   oFCKeditor.Height=200;
   oFCKeditor.BasePath="/system/plugins/fck/";
   oFCKeditor.ReplaceTextarea();
</script>