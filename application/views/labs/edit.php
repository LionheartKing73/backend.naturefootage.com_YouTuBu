<form action="<?= $lang ?>/labs/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT'; else echo 'ADD'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="name">
                Lab: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="name" id="name" value="<?=$name?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <!-- Do not delete this, maybe we will need this functionality -->
        <!--<div class="control-group">
            <label class="control-label" for="name">
                Lab User: <span class="mand"></span>
            </label>
            <div class="controls">
                <select name="user_id" id="user_id">
                    <option></option>
                    <? foreach($users as $user){ ?>
                        <option value="<? echo $user['id']; ?>" <? if($user['id'] == $user_id){ ?> selected <? } ?> ><? echo $user['login']?></option>
                    <? } ?>
                </select>
            </div>
        </div>-->

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
