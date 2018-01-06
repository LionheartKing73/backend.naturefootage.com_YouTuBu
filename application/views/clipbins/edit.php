<form action="<?= $lang ?>/clipbins/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT CLIPBIN'; else echo 'ADD CLIPBIN'; ?>
		</legend>

        <div class="halfForm">

            <div class="control-group">
                <label class="control-label" for="title">
                    Title: <span class="mand">*</span>
                </label>
                <div class="controls">
                    <input type="text" name="title" id="title" value="<?=$title?>">
                    <input type="hidden" name="id" value="<?=$id?>">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">
                    Date Created:
                </label>
                <div class="controls">
                    <div data-date-format="dd-mm-yyyy" data-date="<?php echo $date; ?>" class="input-append date datepicker">
                        <input class="span2" size="16" type="text" name="ctime" value="<?php echo $date; ?>" readonly><span class="add-on"><i class="icon-calendar"></i></span>
                    </div>
                </div>
            </div>

            <?php if($is_admin) { ?>
            <div class="control-group">
                <label class="control-label" for="sfr">
                    SFR:
                </label>
                <div class="controls">
                    <input type="text" name="sfr" id="sfr" value="<?=$sfr?>">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="category">
                    Category
                </label>
                <div class="controls">
                    <select name="category" id="category">
                        <option value="Client" <?php echo ($category == 'Client') ? 'selected' : ''; ?>>Client</option>
                        <option value="Internal" <?php echo ($category == 'Internal') ? 'selected' : ''; ?>>Internal</option>
                        <option value="Master" <?php echo ($category == 'Master' || !$category) ? 'selected' : ''; ?>>Master</option>
                    </select>
                </div>
            </div>
            <?php } ?>

            <div class="control-group">
                <div class="controls">
                    <label class="radio inline">
                        <input type="radio" name="public" value="1"<?if($public==1) echo ' checked';?>>Public
                    </label>
                    <label class="radio inline">
                        <input type="radio" name="public" value="0"<?if($public==0) echo ' checked';?>>Private
                    </label>
                </div>
            </div>

            <?php if($is_admin) { ?>
            <div class="control-group">
                <div class="controls">
                    <label class="radio inline">
                        <input type="radio" name="display" value="1"<?if($display==1 || !isset($display)) echo ' checked';?>>Display
                    </label>
                    <label class="radio inline">
                        <input type="radio" name="display" value="0"<?if(isset($display) && $display==0) echo ' checked';?>>Hidden
                    </label>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="provider_id">
                    Provider
                </label>
                <div class="controls">
                    <select name="provider_id" id="provider_id">
                        <option value="0"></option>
                        <? foreach ($providers as $item) { ?>
                        <option value="<?=$item['id']?>"<?if ($item['id'] == $provider_id) echo ' selected'?>>
                            <?= $item['fname'] . ' ' . $item['lname']; ?>
                        </option>
                        <?}?>
                    </select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="client_id">
                    Client
                </label>
                <div class="controls">
                    <!--<select name="client_id" id="client_id">
                        <option value="0"></option>
                        <? foreach ($clients as $item) { ?>
                        <option value="<?=$item['id']?>"<?if ($item['id'] == $client_id) echo ' selected'?>>
                            <?= $item['fname'] . ' ' . $item['lname']; ?>
                        </option>
                        <?}?>
                    </select>-->
                    <input type="text" name="client_name" id="client_name" value="">
                    <input type="hidden" name="client_id" id="client_id" value="<?php echo $client_id; ?>">
                </div>
            </div>
            <?php } ?>

        </div>

        <div class="halfForm">

            <?php if($is_admin) { ?>
            <div class="control-group">
                <label class="control-label" for="description">
                    Description:
                </label>
                <div class="controls">
                    <textarea name="description" id="description" rows="4"
                              cols="70"><?= esc($description) ?></textarea>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="keywords">
                    Keywords:
                </label>
                <div class="controls">
                    <textarea name="keywords" id="keywords" rows="4"
                              cols="70"><?= esc($keywords) ?></textarea>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="search_notes">
                    Search Notes:
                </label>
                <div class="controls">
                    <textarea name="search_notes" id="search_notes" rows="4"
                              cols="70"><?= esc($search_notes) ?></textarea>
                </div>
            </div>
            <?php } ?>

            <div class="control-group">
                <label class="control-label" for="comments">
                    Comments:
                </label>
                <div class="controls">
                    <textarea name="comments" id="comments" rows="4"
                              cols="70"><?= esc($comments) ?></textarea>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
<script type="text/javascript">
    $('.datepicker').datepicker();

    $("#client_name").autocomplete({
        source: 'en/clipbins/get_clients',
        minLength: 2,
        select: function( event, ui ) {
            $('#client_id').val(ui.item.client_id);
        },
        search: function() {
            $('#client_id').val('');
        }
    });

</script>
