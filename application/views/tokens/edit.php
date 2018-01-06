<form action="<?= $lang ?>/tokens/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT TOKEN'; else echo 'ADD TOKEN'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="token">
                Token: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="token" id="token" value="<?=$token?>"> <img src="/data/img/admin/token.png" width="24" title="Generate" style="cursor: pointer;" class="generate_token">
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="path">
                Path: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="path" id="path" value="<?=$path?>">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
<script type="text/javascript">
    (function ($) {
        var tokenField = $('#token');
        function generateToken(){
            var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
            var string_length = 32;
            var randomstring = '';
            for (var i=0; i<string_length; i++) {
                var rnum = Math.floor(Math.random() * chars.length);
                randomstring += chars.substring(rnum,rnum+1);
            }
            return randomstring;
        }
        $(document).ready(function() {
            $('.generate_token').on('click', function(){
                tokenField.val(generateToken());
            })
        });
    })(jQuery)
</script>
