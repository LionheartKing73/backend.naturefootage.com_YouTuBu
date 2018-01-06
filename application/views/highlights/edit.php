<form action="<?= $lang ?>/highlights/edit/<?=$id?>" method="post" enctype="multipart/form-data" class="form-horizontal well">
	<fieldset>
		<legend>
			<?= $this->lang->line('highlight_edit') ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="title">
				<?= $this->lang->line('title') ?>: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="title" id="title" maxlength="255" value="<?=esc($title)?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="subcats">
                <?=$this->lang->line('urls');?>
            </label>
            <div class="controls">
                <select id="subcats" onchange="set_link();">
                    <option value=""></option>
                    <?foreach($pages as $page){?>
                        <option value="<?=$page['alias1']?>"><?=$page['title']?></option>
                    <?}?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="urllink">
                <?=$this->lang->line('link')?>:
            </label>
            <div class="controls">
                <input type="text" name="link" maxlength="255" size="50" value="<?=$link?>" id="urllink">
            </div>
        </div>

		<div class="control-group">
			<label class="control-label" for="ord">
				<?= $this->lang->line('order') ?>:
			</label>
			<div class="controls">
				<input type="text" name="ord" id="ord" maxlength="6" value="<?=$ord?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="mresource">
                <?=$this->lang->line('file')?>:
            </label>
            <div class="controls">
                <input type="file" name="mresource" id="mresource" class="input-file">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

        <?if($resource_file){?>

        <table border="0" cellspacing="0" cellpadding="2">
            <tr>
                <td align="center">
                    <?if($resource_video){?>
                        <div id="thumbPlayer"></div>
                        <script type="text/javascript">
                            $(document).ready(function(){
                                createPlayer('<?=$this->config->item('base_url').$resource_file?>', 200, 112, "thumbPlayer", false, false);
                            });
                        </script>
                    <?}else{?>
                        <img src="<?=$resource_file?>?date=<?=$mtime?>" border="0" style="border:solid 1px #efefef; width: 70px">
                    <?}?>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <input type="hidden" name="sid" value="<?=$id?>">
                    <input type="submit" value="<?=$this->lang->line('delete')?>" class="btn btn-danger" name="delete">
                </td>
            </tr>
        </table>

        <?}?>

	</fieldset>
</form>

<script>
    var subcats = new Array();
    var cats = new Array(<?=$cats?>);

    <?foreach($subs as $k=>$v):?>
    subcats[<?=$k?>] = new Array(<?=$v?>);
        <?endforeach;?>

    update_selects(0, 0);
</script>