<? if ($clips) { ?>
	<div id="playOriginal"></div>

	<div id="playPreview"></div>

	<script type="text/javascript">
		lang = "<?=$lang?>";
	  
		function playPreview(preview) {
	        createPlayer(preview, 512, 288, "playPreview", false, true);
	        $("#playPreview").dialog({
	            title: "Clip Preview",
	            width: "auto",
	            height: "auto",
	            position: ["center", "center"],
	            modal: true,
	            resizable: false,
	            buttons: { "Close": function() { $(this).dialog("close"); } },
	            beforeClose: function() {
					removePlayer("playPreview");
	            }
	        });
		}
		
		function playOriginal(path, width, height) {
			var qtContent = QT_GenerateOBJECTText('<?=base_url()?>' + path, width, height + 16, "",
				"controller", "true", "autoplay", "true", "scale", "aspect", "bgcolor",
				"black", "wmode", "opaque",
				"obj#ID", "playOriginal_qtobj", "emb#ID", "playOriginal_qtembed");

			$("#playOriginal").html(qtContent);
			
	        $("#playOriginal").dialog({
	            title: "Original Video",
	            width: "auto",
	            height: "auto",
	            position: ["center", "center"],
	            modal: true,
	            resizable: true,
	            buttons: { "Close": function() { $(this).dialog("close"); } },
	            beforeClose: function() {
					removePlayer("playOriginal");
	            }
	        });
		}
	</script>
<? } ?>

<? if (!$hide_clips_actions) { ?>
	<? if ($menu) echo $menu ?>

	<form name="clips" action="<?=$lang?>/clips/view<?=$this->config->item('url_suffix')?>" method="post">


		<div class="toolbar-item" style="margin: 0">
			<div class="controls-group">
				<label for="words"><?= $this->lang->line('search') ?>:</label>
				<input type="text" name="words" id="words" style="width: 120px"
						value="<?=$filter['words']?>">

				<input type="radio" name="search_mode" id="search_mode_0" value="0"<?if(empty($filter['search_mode'])){?> checked<?}?>>
				<label for="search_mode_0">any word</label>
				&nbsp;
				<input type="radio" name="search_mode" id="search_mode_1" value="1"<?if($filter['search_mode']==1){?> checked<?}?>>
				<label for="search_mode_1">all words</label>
				&nbsp;
				<input type="radio" name="search_mode" id="search_mode_2" value="2"<?if($filter['search_mode']==2){?> checked<?}?>>
				<label for="search_mode_2">exact phrase</label>
			</div>

			<div class="controls-group">
				<label for="cat_id">Category:</label>
				<select name="cat_id" id="cat_id" style="width: auto">
					<option value="0">-- all --</option>
				<? foreach ($categories as $cat) { ?>
					<option value="<?=$cat['id']?>"<?if ($cat['id']==$filter['cat_id']) echo ' selected'?>>
						<?=$cat['title']?>
					</option>
					<? if ($cat['child']) {
						foreach ($cat['child'] as $subcat) {?>
					<option value="<?=$cat['id']?>"<?if ($subcat['id']==$filter['cat_id']) echo ' selected'?>>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$subcat['title']?>
					</option>
						<?}
					}
				}?>
				</select>
			</div>

            <div class="controls-group">
                <label for="sequence_id">Sequence:</label>
                <select name="sequence_id" id="sequence_id" style="width: auto">
                    <option value="0">-- all --</option>
                    <? foreach ($sequences as $item) { ?>
                    <option value="<?=$item['id']?>"<?if ($item['id']==$filter['sequence_id']) echo ' selected'?>>
                        <?=$item['title']?>
                    </option>
                    <?}?>
                </select>
            </div>

            <? if($is_admin) { ?>
            <div class="controls-group">
                <label for="client_id">Provider:</label>
                <select name="client_id" id="client_id" style="width: auto">
                    <option value="0">-- all --</option>
                    <? foreach ($providers as $item) { ?>
                    <option value="<?=$item['id']?>"<?if ($item['id']==$filter['client_id']) echo ' selected'?>>
                        <?= $item['fname'] . ' ' . $item['lname']; ?>
                    </option>
                    <?}?>
                </select>
            </div>
            <? } ?>

            <div class="controls-group">
                <label for="bin_id">Bin:</label>
                <select name="bin_id" id="bin_id" style="width: auto">
                    <option value="0">-- all --</option>
                    <? foreach ($bins as $item) { ?>
                    <option value="<?=$item['id']?>"<?if ($item['id']==$filter['bin_id']) echo ' selected'?>>
                        <?=$item['title']?>
                    </option>
                    <?}?>
                </select>
            </div>

            <div class="controls-group">
                <label for="gallery_id">Gallery:</label>
                <select name="gallery_id" id="gallery_id" style="width: auto">
                    <option value="0">-- all --</option>
                    <? foreach ($galleries as $item) { ?>
                    <option value="<?=$item['id']?>"<?if ($item['id']==$filter['gallery_id']) echo ' selected'?>>
                        <?=$item['title']?>
                    </option>
                    <?}?>
                </select>
            </div>

            <div class="controls-group">
                <label for="submission_id">Submission:</label>
                <select name="submission_id" id="submission_id" style="width: auto">
                    <option value="0">-- all --</option>
                    <? foreach ($submissions as $item) { ?>
                    <option value="<?=$item['id']?>"<?if ($item['id']==$filter['submission_id']) echo ' selected'?>>
                        <?=$item['code']?>
                    </option>
                    <?}?>
                </select>
            </div>

			<div class="controls-group">
				<label for="frame_rate">Frame rate:</label>
				<select name="frame_rate" id="frame_rate" style="width: auto">
					<option value="0">-- all --</option>
				<?foreach ($frame_rates as $frame_rate) {?>
					<option value="<?=$frame_rate?>"<?if ($frame_rate==$filter['frame_rate']) echo ' selected'?>>
						<?=$frame_rate?>
					</option>
				<?}?>
				</select>
			</div>

			<input type="submit" name="apply_filters" class="btn find" value="<?=$this->lang->line('find')?>">
		</div>
		<br class="clr">



		<input type="hidden" name="filter" value="1">
		<? if ($this->permissions['clips-edit'] || $this->permissions['clips-visible']
			|| $this->permissions['clips-delete']) {
			?>

			<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
		<?= $this->lang->line('action') ?>:
			</strong>

			<div class="btn-group toolbar-item"<? if ($paging) { ?> style="margin-top: 5px;"<? } ?>>
				<? if ($this->permissions['clips-upload']) { ?>
					<a class="btn" href="<?= $lang ?>/clips/upload"><?= $this->lang->line('add'); ?></a>
				<? } ?>
				<? if ($this->permissions['clips-visible']) { ?>
					<a class="btn" href="javascript: if (check_selected(document.clips, 'id[]')) change_action(document.clips,'<?= $lang ?>/clips/visible');"><?= $this->lang->line('visible'); ?></a>
				<? } ?>
				<? if ($this->permissions['clips-delete']) { ?>
					<a class="btn" href="javascript: if (check_selected(document.clips, 'id[]')) change_action(document.clips,'<?= $lang ?>/clips/delete');"><?= $this->lang->line('delete'); ?></a>
			    <? } ?>
                <? if ($this->permissions['cliplog-edit']) { ?>
                    <a class="btn" href="javascript: if (check_selected(document.clips, 'id[]')) change_action(document.clips,'<?= $lang ?>/cliplog/edit');">ClipLog</a>
                <? } ?>
			</div>
		<? } ?>

		<? if ($paging) { ?>
			<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
	<? } ?>



<? } ?>
<br class="clr">
	<table class="table table-striped">
		<tr>
<? if ($hide_clips_actions) { ?>
				<th><?= $this->lang->line('thumbnail') ?></th>
				<th><?= $this->lang->line('title') ?></th>
				<th><?= $this->lang->line('code') ?></th>
                <th>Original filename</th>
				<? if($is_admin) { ?>
                    <th>Provider</th>
                <? } ?>
				<th><?= $this->lang->line('status') ?></th>
                <th>Transcoding status</th>
				<th><?= $this->lang->line('date') ?></th>
				<th> </th>
<? } else { ?>
				<th><input type="checkbox" name="sample" onclick="select_all(document.clips)"></th>
				<th><?= $this->lang->line('thumbnail') ?></th>
				<th><a href="<?= $uri ?>/sort/title" class="title"><?= $this->lang->line('title') ?></a></th>
				<th><a href="<?= $uri ?>/sort/code" class="title"><?= $this->lang->line('code') ?></a></th>
                <th>Original filename</th>
                <? if($is_admin) { ?>
                    <th><a href="<?= $uri ?>/sort/client_id" class="title">Provider</a></th>
                <? } ?>
				<th><a href="<?= $uri ?>/sort/active" class="title"><?= $this->lang->line('status') ?></a></th>
				<th>Transcoding status</th>
				<th><a href="<?= $uri ?>/sort/activity" class="title">Last activity</a></th>
				<th><a href="<?= $uri ?>/sort/ctime" class="title"><?= $this->lang->line('date') ?></a></th>
				<th class="col-action"><?= $this->lang->line('action') ?></th>
<? } ?>
		</tr>

			<? if ($clips) {
				foreach ($clips as $clip) { ?>   
				<tr class="tdata1">
					<? if (!$hide_clips_actions) { ?>
						<td><input type="checkbox" name="id[]" value="<?= $clip['id'] ?>"></td>
						<? } ?>
					<td>      
							<? if ($this->permissions['clips-edit']) { ?>
							<a href="<?= $lang ?>/clips/edit/<?= $clip['id'] ?>">
							<? } ?>
							<img src="<?= ($clip['thumb']) ? $clip['thumb'] : $default_img ?>" width="100">
						<? if ($this->permissions['clips-edit']) { ?>
							</a>
		<? } ?>
					</td>
					<td><?= esc($clip['title']) ?></td>
					<td><?= esc($clip['code']) ?></td>
					<td><?= esc($clip['original_filename']) ?></td>
                    <? if($is_admin) { ?>
					    <td><?= $clip['fname'] . ' ' . $clip['lname'] ?></td>
                    <? } ?>
					<td><? if ($clip['active'] == 1) echo 'published'; else echo 'unpublished'; ?></td>
					<td>
                        Preview: <?php echo $clip['preview'] ? 'Ready' : 'Not ready'; ?><br>
                        Thumbnail: <?php echo $clip['thumb'] ? 'Ready' : 'Not ready'; ?><br>
                        Motion thumbnail: <?php echo $clip['motion_thumb'] ? 'Ready' : 'Not ready'; ?><br>
					</td>
					<td><?= $clip['activity'] ?></td>
					<td><?= $clip['ctime'] ?></td>
					<td>
						
            <?
            get_actions(array(
                array(
					'display' => TRUE,
					'url' => "javascript:playOriginal('" . $clip['res'] . "', "
						. $clip['width'] . ", " . $clip['height'] . ")",
					'name' => 'Play original'
				),
                array(
					'display' => $clip['preview'],
					'url' => "javascript:playPreview('" . $clip['preview'] . "')",
					'name' => 'Preview'
				),
//                array(
//					'display' => $this->permissions['clips-edit'],
//					'url' => $lang . '/clips/edit/' . $clip['id'],
//					'name' => $this->lang->line('edit')
//				),
                array(
					'display' => $this->permissions['cliplog-edit'],
					'url' => $lang . '/cliplog/edit/' . $clip['id'],
					'name' => 'ClipLog'
				),
                array(
					'display' => $this->permissions['clips-cats'],
					'url' => $lang . '/clips/cats/' . $clip['id'],
					'name' => 'Categories'
				),
//                array(
//                    'display' => $this->permissions['clips-sequences'],
//                    'url' => $lang . '/clips/sequences/' . $clip['id'],
//                    'name' => 'Sequences'
//                ),
//                array(
//                    'display' => $this->permissions['clips-bins'],
//                    'url' => $lang . '/clips/bins/' . $clip['id'],
//                    'name' => 'Bins'
//                ),
//                array(
//                    'display' => $this->permissions['clips-galleries'],
//                    'url' => $lang . '/clips/galleries/' . $clip['id'],
//                    'name' => 'Galleries'
//                ),
                array(
                    'display' => $this->permissions['clips-clipbins'],
                    'url' => $lang . '/clips/clipbins/' . $clip['id'],
                    'name' => 'Clipbins'
                ),
                array(
                    'display' => $this->permissions['clips-statistics'],
                    'url' => $lang . '/clips/statistics/' . $clip['id'],
                    'name' => 'Statistics'
                ),
                array(
					'display' => $this->permissions['clips-delete'],
					'url' => $lang . '/clips/delete/' . $clip['id'],
					'name' => $this->lang->line('delete'),
					'confirm' => $this->lang->line('delete_confirm'))
            ))
            ?>
					</td>
				</tr>
	<? }
} else { ?>
			<tr><td colspan="7" class="empty-list"><?= $this->lang->line('empty_list') ?></td></tr>
	<? } ?>

	</table>

<? if ($paging) { ?>
		<div class="pagination"><?= $paging ?></div>
<? } ?>

<? if (!$hide_clips_actions) { ?>
	</form>
<? } ?>

<? if ($clips) { ?>
	<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?
}?>

<div class="clr"></div>