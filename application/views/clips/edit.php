<style type="text/css">
	@import url(/data/js/jquery.ui.plupload/css/jquery.ui.plupload.css);
	@import url(/data/css/clips_edit.css);
</style>
<script type="text/javascript" src="/data/js/plupload.full.js"></script>
<script type="text/javascript" src="/data/js/jquery.ui.plupload/jquery.ui.plupload.js"></script>


<strong class="toolbar-item">
	<?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
	<?if($id && $this->permissions['clips-cats']){?>
    <a href="<?=$lang?>/clips/cats<?='/'.$id?>" class="btn">
		Categories
	</a>
    <? } ?>
    <!--
    <?if($id && $this->permissions['clips-sequences']){?>
    <a href="<?=$lang?>/clips/sequences<?='/'.$id?>" class="btn">
        Sequences
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-bins']){?>
    <a href="<?=$lang?>/clips/bins<?='/'.$id?>" class="btn">
        Bins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-galleries']){?>
    <a href="<?=$lang?>/clips/galleries<?='/'.$id?>" class="btn">
        Galleries
    </a>
    <? } ?>-->
    <?if($id && $this->permissions['clips-clipbins']){?>
    <a href="<?=$lang?>/clips/clipbins<?='/'.$id?>" class="btn">
        Clipbins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-resources']){?>
    <a href="<?=$lang?>/clips/resources<?='/'.$id?>" class="btn">
        Resources
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-attachments']){?>
    <a href="<?=$lang?>/clips/attachments<?='/'.$id?>" class="btn">
        Attachments
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-statistics']){?>
    <a href="<?=$lang?>/clips/statistics<?='/'.$id?>" class="btn">
        Access statistics
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-derived']){?>
    <a href="<?=$lang?>/clips/derived<?='/'.$id?>" class="btn">
        Derived
    </a>
    <? } ?>
</div>

<br class="clr">

<? if ($id && $this->permissions['clips-upload']) { ?>

	<div id="tabs">
		<ul>
			<li><a href="<?php echo '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>#details">Details</a></li>
			<li><a href="<?php echo '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>#uploaderTab">Upload</a></li>
            <li><a href="<?php echo '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>#ktool">Keywording</a></li>
		</ul>

		<div id="details">
			<h3><?= esc($title) ?></h3>
		<? } ?>

		<? if ($preview) { ?>
			<div id="previewPlayer">
			</div>
			<script type="text/javascript">
				createPlayer("<?=base_url()?>data/upload/resources/clip/preview/<?=$preview['clip_id'] . '.' . $preview['resource']?>",
				512, 288, "previewPlayer", false, false);
			</script>
		<? } else { ?>
			<p>The clip preview not available yet.</p>
		<? } ?>
		<form action="<?= $lang ?>/clips/edit<?= '/' . $id ?>" method="post"
			  class="form-horizontal well" id="clipEditForm"<? if (!$preview) { ?> style="margin-left: 0"<? } ?>>

			<fieldset>
				<div class="halfForm">

					<div class="control-group">
						<label class="control-label" for="code">
							<?= $this->lang->line('code') ?>: <span class="mand">*</span>
						</label>
						<div class="controls">
							<input type="text" name="code" id="code" maxlength="255"
								value="<?= esc($code) ?>">
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="title">
							<?= $this->lang->line('title') ?>: <span class="mand">*</span>
						</label>
						<div class="controls">
							<input type="text" name="title" id="title" maxlength="255"
								value="<?= esc($title) ?>">
							<input type="hidden" name="id" value="<?= $id ?>">
						</div>
					</div>

                    <div class="control-group">
                        <label class="control-label" for="duration">
                            Duration:
                        </label>
                        <div class="controls">
                            <div class="input-append">
                                <input type="text" name="duration" id="duration" maxlength="20"
                                       value="<?=$duration?>" style="width: 180px"><span class="add-on">s</span>
                            </div>
                        </div>
                    </div>

					<!--
                    <div class="control-group">
						<label class="control-label" for="creator">
							Creator:
						</label>
						<div class="controls">
							<input type="text" name="creator" id="creator" maxlength="255"
								value="<?= esc($creator) ?>">
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="rights">
							Rights:
						</label>
						<div class="controls">
							<input type="text" name="rights" id="rights" maxlength="255"
								value="<?= esc($rights) ?>">
						</div>
					</div>

                    <div class="control-group">
                        <label class="control-label" for="subject">
                            Subject:
                        </label>
                        <div class="controls">
                            <input type="text" name="subject" id="subject" maxlength="255"
                                   value="<?= esc($subject) ?>">
                        </div>
                    </div>-->

					<div class="control-group">
						<label class="control-label" for="description">
							<?= $this->lang->line('desc') ?>:
						</label>
						<div class="controls">
							<textarea name="description" id="description" rows="4"
									  cols="70"><?= esc($description) ?></textarea>
						</div>
					</div>

                    <div class="control-group">
						<label class="control-label" for="notes">
                            Notes:
						</label>
						<div class="controls">
							<textarea name="notes" id="notes" rows="4"
									  cols="70"><?= esc($notes) ?></textarea>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="keywords">
							<?= $this->lang->line('keys') ?>:
						</label>
						<div class="controls">
							<textarea name="keywords" id="keywords" rows="4"
									  cols="70"><?= esc($keywords) ?></textarea>
						</div>
					</div>

                    <div class="control-group">
                        <label class="control-label">
                            <?=$this->lang->line('license');?>:
                        </label>
                        <div class="controls">
                            <label class="radio inline">
                                <input type="radio" name="license" value="1"<?if($license==1) echo ' checked';?>>Royalty Free
                            </label>
                            <label class="radio inline">
                                <input type="radio" name="license" value="2"<?if($license==2) echo ' checked';?>>Rights Managed
                            </label>
                            <!--
                            <label class="radio inline">
                                <input type="radio" name="license" value="3"<?if($license==3) echo ' checked';?>>Premium Collection
                            </label>-->
                        </div>
                    </div>

                    <? if($is_admin && $providers) { ?>
                    <div class="control-group">
                        <label class="control-label" for="client_id">
                            Provider
                        </label>
                        <div class="controls">
                            <select name="client_id" id="client_id">
                                <option value="0"></option>
                                <? foreach ($providers as $item) { ?>
                                <option value="<?=$item['id']?>"<?if ($item['id'] == $client_id) echo ' selected'?>>
                                    <?= $item['fname'] . ' ' . $item['lname']; ?>
                                </option>
                                <?}?>
                            </select>
                        </div>
                    </div>
                    <? } ?>

                    <div class="control-group">
                        <label class="control-label" for="creation_date">
                            Date Filmed:
                        </label>
                        <div class="controls">
                            <input type="text" name="creation_date" id="creation_date"
                                   maxlength="30" value="<?=strftime('%Y-%m-%d', strtotime($creation_date))?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="collection">
                            Collection:
                        </label>
                        <div class="controls">
                            <input type="text" name="collection" id="collection" value="<?= $collection ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="add_collections">
                            Collection:
                        </label>
                        <div class="controls">
                            <input type="text" name="add_collections" id="add_collections" value="<?= $add_collections ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="price_level">
                            Price level
                        </label>
                        <div class="controls">
                            <select name="price_level" id="price_level" disabled="disabled">
                                <option value=""></option>
                                <option value="1" <?php echo ($price_level == 1) ? 'selected' : ''; ?>>Budget</option>
                                <option value="2" <?php echo ($price_level == 2) ? 'selected' : ''; ?>>Standard</option>
                                <option value="3" <?php echo ($price_level == 3) ? 'selected' : ''; ?>>Premium</option>
                                <option value="4" <?php echo ($price_level == 4) ? 'selected' : ''; ?>>Exclusive</option>
                                <option value="5" <?php echo ($price_level == 5) ? 'selected' : ''; ?>>3D</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="releases">
                            Releases
                        </label>
                        <div class="controls">
                            <input type="text" name="releases" id="releases" value="<?= $releases ? 'Yes' : 'No' ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="source_format">
                            Source Format
                        </label>
                        <div class="controls">
                            <input type="text" name="source_format" id="source_format" value="<?= $source_format; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="camera_chip_size">
                            Camera Chip Size
                        </label>
                        <div class="controls">
                            <input type="text" name="camera_chip_size" id="camera_chip_size" value="<?= esc($camera_chip_size); ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="bit_depth">
                            Bit Depth
                        </label>
                        <div class="controls">
                            <input type="text" name="bit_depth" id="bit_depth" value="<?= $bit_depth; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="color_space">
                            Color Space
                        </label>
                        <div class="controls">
                            <input type="text" name="color_space" id="color_space" value="<?= $color_space; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="source_frame_size">
                            Source Frame Size
                        </label>
                        <div class="controls">
                            <input type="text" name="source_frame_size" id="source_frame_size" value="<?= $source_frame_size; ?>" disabled="disabled">
                        </div>
                    </div>

				</div>

				<div class="halfForm">



                    <div class="control-group">
                        <label class="control-label" for="source_frame_rate">
                            Source Frame Rate
                        </label>
                        <div class="controls">
                            <input type="text" name="source_frame_rate" id="source_frame_rate" value="<?= $source_frame_rate; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="master_format">
                            Master Format
                        </label>
                        <div class="controls">
                            <input type="text" name="master_format" id="master_format" value="<?= $master_format; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="master_frame_size">
                            Master Frame Size
                        </label>
                        <div class="controls">
                            <input type="text" name="master_frame_size" id="master_frame_size" value="<?= $master_frame_size; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="master_frame_rate">
                            Master Frame Rate
                        </label>
                        <div class="controls">
                            <input type="text" name="master_frame_rate" id="master_frame_rate" value="<?= $master_frame_rate; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="digital_file_format">
                            Digital File Format
                        </label>
                        <div class="controls">
                            <input type="text" name="digital_file_format" id="digital_file_format" value="<?= $digital_file_format; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="digital_file_frame_size">
                            Digital File Frame Size:
                        </label>
                        <div class="controls">
                            <input type="text" name="digital_file_frame_size" id="digital_file_frame_size" value="<?= $digital_file_frame_size; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="digital_file_frame_rate">
                            Digital file frame rate:
                        </label>
                        <div class="controls">
                            <input type="text" name="digital_file_frame_rate" id="digital_file_frame_rate" value="<?= $digital_file_frame_rate; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="source_codec">
                            Digital File Compression:
                        </label>
                        <div class="controls">
                            <input type="text" name="source_codec" id="source_codec" value="<?= $source_codec; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="pricing_category">
                            Delivery Category:
                        </label>
                        <div class="controls">
                            <input type="text" name="pricing_category" id="pricing_category" value="<?= $pricing_category; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="master_lab">
                            Lab:
                        </label>
                        <div class="controls">
                            <input type="text" name="master_lab" id="master_lab" value="<?= $master_lab; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="shot_type">
                            Shot Type:
                        </label>
                        <div class="controls">
                            <input type="text" name="shot_type" id="shot_type" value="<?= $shot_type; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="subject_category">
                            Subject Category:
                        </label>
                        <div class="controls">
                            <input type="text" name="subject_category" id="subject_category" value="<?= $subject_category; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="primary_subject">
                            Primary Subject:
                        </label>
                        <div class="controls">
                            <input type="text" name="primary_subject" id="primary_subject" value="<?= $primary_subject; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="appearance">
                            Appearance:
                        </label>
                        <div class="controls">
                            <input type="text" name="appearance" id="appearance" value="<?= $appearance; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="actions">
                            Actions:
                        </label>
                        <div class="controls">
                            <input type="text" name="actions" id="actions" value="<?= $actions; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="time">
                            Time:
                        </label>
                        <div class="controls">
                            <input type="text" name="time" id="time" value="<?= $time; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="habitat">
                            Habitat:
                        </label>
                        <div class="controls">
                            <input type="text" name="habitat" id="habitat" value="<?= $habitat; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="concept">
                            Concept:
                        </label>
                        <div class="controls">
                            <input type="text" name="concept" id="concept" value="<?= $concept; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="location">
                            Location:
                        </label>
                        <div class="controls">
                            <input type="text" name="location" id="location" value="<?= $location; ?>" disabled="disabled">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="country">
                            Country:
                        </label>
                        <div class="controls">
                            <input type="text" name="country" id="country" value="<?= $country; ?>" disabled="disabled">
                        </div>
                    </div>

					<!--
                    <div class="control-group">
						<label class="control-label" for="width">
							Width:
						</label>
						<div class="controls">
							<input type="text" name="width" id="width" maxlength="5" value="<?= $width ?>" disabled="disabled">
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="height">
							Height:
						</label>
						<div class="controls">
							<input type="text" name="height" id="height" maxlength="5" value="<?= $height ?>" disabled="disabled">
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="aspect">
							Aspect ratio:
						</label>
						<div class="controls">
							<input type="text" name="aspect" id="aspect" maxlength="10" value="<?= $aspect ?>">
						</div>
					</div>

                    <div class="control-group">
                        <label class="control-label" for="frame_rate">
                            Frame rate:
                        </label>
                        <div class="controls">
                            <div class="input-append">
                                <input type="text" name="frame_rate" id="frame_rate"
                                       value="<?= $frame_rate ?>" maxlength="8" style="width: 180px" disabled="disabled"><span class="add-on">fps</span>
                            </div>
                        </div>
                    </div>

					<div class="control-group">
						<label class="control-label" for="codec">
                            Compression:
						</label>
						<div class="controls">
							<input type="text" name="codec" id="codec" value="<?=$codec?>" maxlength="255" disabled="disabled">
						</div>
					</div>

                    <div class="control-group">
                        <label class="control-label" for="color_system">
                            Color system:
                        </label>
                        <div class="controls">
                            <input type="text" name="color_system" id="color_system" value="<?=$color_system?>">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="pricing_category">
                            Pricing category
                        </label>
                        <div class="controls">
                            <input type="text" name="pricing_category" id="pricing_category" value="<?= $pricing_category ?>">
                        </div>
                    </div>
                    -->

                    <!--
                    <div class="control-group">
                        <label class="control-label" for="calc_price_level">
                            Calc price level
                        </label>
                        <div class="controls">
                            <input type="text" name="calc_price_level" id="calc_price_level" value="<?= $calc_price_level ?>">
                        </div>
                    </div>-->

					<!--
                    <div class="control-group">
						<label class="control-label" for="format">
							Format:
						</label>
						<div class="controls">
							<input type="text" name="format" id="format" value="<?= esc($format) ?>" maxlength="255" disabled="disabled">
						</div>
					</div>
					-->

                    <!--
                    <div class="control-group">
                        <label class="control-label" for="of_id">
                            Original file format:
                        </label>
                        <div class="controls">
                            <select name="of_id" id="of_id">
                                <option value="0"></option>
                                <?foreach($of as $item):?>
                                <option value="<?=$item['id']?>" <?if($of_id==$item['id']) echo 'selected';?>> <?=$item['title']?>
                                <?endforeach;?>
                            </select>
                        </div>
                    </div>-->

                    <!--<div class="control-group">
                        <label class="control-label" for="price">
                            Price:
                        </label>
                        <div class="controls">
                            <input type="text" name="price" id="price" maxlength="10"
                                   value="<?= $price ?>">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="price_per_second">
                            Price per second:
                        </label>
                        <div class="controls">
                            <input type="text" name="price_per_second" id="price_per_second" maxlength="10"
                                   value="<?= $price_per_second ?>">
                        </div>
                    </div>-->

                </div>

                <div class="clearfix"></div>

				<div class="form-actions">
					<input type="submit" class="btn btn-primary"
						   value="<?= $this->lang->line('save') ?>" name="save">
				</div>
			</fieldset>
		</form>
<? if ($id && $this->permissions['clips-upload']) { ?>
			<br class="clr">
		</div>

		<div id="uploaderTab">
			<label for="resourceType" style="display: inline">Resource type:</label>
			<select id="resourceType" onchange="changeResourceType()">
				<option value="res">original</option>
				<option value="preview">preview</option>
				<option value="thumb">thumbnail</option>
			</select>
			<div id="uploader"></div>
		</div>

        <div id="ktool">
            <? if ($preview) { ?>
            <script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
            <div id="clipsList" style="display: none;"></div>
            <iframe width="1130" height="750" frameborder="0" src="/ui_Web/KToolUi.html">
            </iframe>
            <? } else { ?>
                <p>The clip preview not available yet.</p>
            <? } ?>
        </div>

	</div>

	<script type="text/javascript">
		$("#uploader").plupload({
	<? if (!empty($runtimes)) { ?>"runtimes" : "<?= $runtimes ?>",<? } ?>
			url : "<?=base_url()?><?=$lang?>/upload/index/<?=$id?>",
			chunk_size : "512kb",
			unique_names : false,
			filters : [
				{title : "Videos and images", extensions : "mov,mp4,avi,r3d,jpg,jpeg,png,gif"}
			]<? if (!empty($runtimes) && (strpos($runtimes, 'flash') !== FALSE)) { ?>,
				flash_swf_url : "<?= base_url() ?>data/js/plupload.flash.swf?date=<?=date('Y-m-d-H-i-s')?>"
	<? } ?>
		});

		var up = $("#uploader").plupload("getUploader");
		up.bind('FilesAdded', function() {
			if (up.files.length > 1) {
				alert("You may upload only one file for the existing asset.");
				for (var i = up.files.length - 1; i > 0; --i) {
					up.removeFile(up.files[i]);
				}
			}
		});

		function changeResourceType() {
			var resourceType = $("#resourceType").val();
			up.settings.url = "<?=base_url()?><?=$lang?>/upload/index/<?=$id?>/"
				+ resourceType;
		}

		$("#tabs").tabs();
	</script>
<? } ?>

<? if ($error) { ?>
	<script type="text/javascript">
		alert("<?= $error ?>");
	</script>
<? } ?>
