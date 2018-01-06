<form action="<?= $lang ?>/deliveryoptionsrf/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT OPTION'; else echo 'ADD OPTION'; ?>
		</legend>
		
		<div class="control-group">
			<label class="control-label" for="delivery_id">
                Delivery id:
			</label>
			<div class="controls">
				<input type="text" name="delivery_id" id="delivery_id" value="<?=$delivery_id?>">
				<input type="hidden" name="id" value="<?=$id?>">
			</div>
		</div>

        <div class="control-group">
			<label class="control-label" for="price_code">
                Price code:
			</label>
			<div class="controls">
				<input type="text" name="price_code" id="price_code" value="<?=$price_code?>">
			</div>
		</div>

        <div class="control-group">
            <label class="control-label" for="admin_only">
                Admin only:
            </label>
            <div class="controls">
                <input type="checkbox" id="admin_only" name="admin_only" <?if($admin_only){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="categories">
                Categories:
            </label>
            <div class="controls">
                <input type="text" name="categories" id="categories" value="<?=$categories?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="description">
                Description: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="description" id="description" value="<?=$description?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="timedelay">
                Timedelay:
            </label>
            <div class="controls">
                <textarea name="timedelay" id="timedelay" rows="4" cols="70"><?= esc($timedelay) ?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="method">
                Method:
            </label>
            <div class="controls">
                <select name="method" id="method">
                    <option value="Download" <?if($method == 'Download') echo 'selected';?>>Download</option>
                    <option value="Lab" <?if($method == 'Lab') echo 'selected';?>>Lab</option>
                    <option value="Compressor" <?if($method == 'Compressor') echo 'selected';?>>Compressor</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="display_order">
                Display order:
            </label>
            <div class="controls">
                <input type="text" name="display_order" id="display_order" value="<?=$display_order?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="price_factor">
                Delivery factor:
            </label>
            <div class="controls">
                <select name="price_factor" id="price_factor">
                    <option value="0"></option>
                    <?foreach($delivery_price_factors as $price_factor_item):?>
                    <option value="<?=$price_factor_item['id']?>" <?if($price_factor == $price_factor_item['id']) echo 'selected';?>><?=$price_factor_item['format']?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>

        <legend>Transcoding options</legend>

        <div class="control-group">
            <label class="control-label" for="video_codec">
                Video codec:
            </label>
            <div class="controls">
                <select name="video_codec" id="video_codec">
                    <option value=""></option>
                    <option value="libx264" <?if($video_codec == 'libx264') echo 'selected';?>>H.264</option>
                    <option value="mjpeg" <?if($video_codec == 'mjpeg') echo 'selected';?>>Photo JPEG</option>
                    <option value="wmv2" <?if($video_codec == 'wmv2') echo 'selected';?>>Windows Media Video</option>
                    <option value="prores_ks -profile:v 0 -vendor ap10 -pix_fmt yuv422p10le" <?if($video_codec == 'prores_ks -profile:v 0 -vendor ap10 -pix_fmt yuv422p10le') echo 'selected';?>>Apple ProRes 422 (Proxy)</option>
                    <option value="prores_ks -profile:v 1 -vendor ap10 -pix_fmt yuv422p10le" <?if($video_codec == 'prores_ks -profile:v 1 -vendor ap10 -pix_fmt yuv422p10le') echo 'selected';?>>Apple ProRes 422 (LT)</option>
                    <option value="prores_ks -profile:v 2 -vendor ap10 -pix_fmt yuv422p10le" <?if($video_codec == 'prores_ks -profile:v 2 -vendor ap10 -pix_fmt yuv422p10le') echo 'selected';?>>Apple ProRes 422 (SQ)</option>
                    <option value="prores_ks -profile:v 3 -vendor ap10 -pix_fmt yuv422p10le" <?if($video_codec == 'prores_ks -profile:v 3 -vendor ap10 -pix_fmt yuv422p10le') echo 'selected';?>>Apple ProRes 422 (HQ)</option>
                    <option value="prores_ks -profile:v 0 -vendor ap10 -pix_fmt yuv444p10le" <?if($video_codec == 'prores_ks -profile:v 0 -vendor ap10 -pix_fmt yuv444p10le') echo 'selected';?>>Apple ProRes 4444 (Proxy)</option>
                    <option value="prores_ks -profile:v 1 -vendor ap10 -pix_fmt yuv444p10le" <?if($video_codec == 'prores_ks -profile:v 1 -vendor ap10 -pix_fmt yuv444p10le') echo 'selected';?>>Apple ProRes 4444 (LT)</option>
                    <option value="prores_ks -profile:v 2 -vendor ap10 -pix_fmt yuv444p10le" <?if($video_codec == 'prores_ks -profile:v 2 -vendor ap10 -pix_fmt yuv444p10le') echo 'selected';?>>Apple ProRes 4444 (SQ)</option>
                    <option value="prores_ks -profile:v 3 -vendor ap10 -pix_fmt yuv444p10le" <?if($video_codec == 'prores_ks -profile:v 3 -vendor ap10 -pix_fmt yuv444p10le') echo 'selected';?>>Apple ProRes 4444 (HQ)</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="audio_codec">
                Audio codec:
            </label>
            <div class="controls">
                <select name="audio_codec" id="audio_codec">
                    <option value=""></option>
                    <option value="aac" <?if($audio_codec == 'aac') echo 'selected';?>>AAC</option>
                    <option value="wmav2" <?if($audio_codec == 'wmav2') echo 'selected';?>>Windows Media Audio</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="container">
                Container:
            </label>
            <div class="controls">
                <input type="text" name="container" id="container" value="<?=$container?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="resolution">
                Resolution:
            </label>
            <div class="controls">
                <input type="text" name="resolution" id="resolution" value="<?=$resolution?>">
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>
	</fieldset>
</form>
