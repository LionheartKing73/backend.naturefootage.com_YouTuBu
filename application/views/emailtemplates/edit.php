<form action="<?= $lang ?>/emailtemplates/edit/<?=$id?>.html" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT TEMPLATE'; else echo 'ADD TEMPLATE'; ?>
		</legend>

		<div class="control-group">
			<label class="control-label" for="name">
				Template name: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="name" id="name" value="<?=$name?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="name">
				Format:
			</label>
			<div class="controls radio-format">
				<input type="radio" name="is_html" id="radio-html" value="1" <?= ( $is_html == 1 ) ? 'checked="checked"' : ''; ?>/><label for="radio-html">HTML</label>
				<input type="radio" name="is_html" id="radio-text" value="0" <?= ( $is_html == 0 ) ? 'checked="checked"' : ''; ?>/><label for="radio-text">Text</label>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="subject">
                Subject: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="subject" id="subject" value="<?=$subject?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="description">
                Description:
			</label>
			<div class="controls">
				<input type="text" name="description" id="description" class="span9" value="<?=$description?>">
			</div>
		</div>
        <div class="control-group">
            <label class="control-label" for="to">
                To: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="to" id="to" class="span9" value="<?=$to?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="bcc">
                BCC:
            </label>
            <div class="controls">
                <input type="text" name="bcc" id="bcc" class="span9" value="<?=$bcc?>">
            </div>
        </div>
        <input type="hidden" name="emailtype" value="0">
        <div class="control-group">
            <label class="control-label" for="<?=($is_html)?'body_html':'body'?>">
                Text: <span class="mand">*</span>
            </label>
            <div class="controls">
                <textarea name="<?=($is_html)?'body_html':'body'?>" id="<?=($is_html)?'body_html':'body'?>" style="width: 800px; height: 600px;"><?=($is_html)?$body_html:$body?></textarea>

                <?  if ( $is_html == 1 ) echo /*tinymce();//*/fck( 800, 600, 'body_html' );  ?>
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>



<script> jQuery( function () { $( ".radio-format" ).buttonset(); } ); </script>
