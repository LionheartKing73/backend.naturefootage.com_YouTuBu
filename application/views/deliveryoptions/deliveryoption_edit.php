<form action="<?= $lang ?>/deliveryoptions/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT OPTION'; else echo 'ADD OPTION'; ?>
		</legend>

        <div class="halfForm">
		
		<div class="control-group">
			<label class="control-label" for="code">
                Code: <span class="mand">*</span>
			</label>
			<div class="controls">
				<input type="text" name="code" id="code" value="<?=$code?>">
				<input type="hidden" name="id" value="<?=$id?>">
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
            <label class="control-label" for="format">
                Format:
            </label>
            <div class="controls">
                <input type="text" name="format" id="format" value="<?=$format?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="source">
                Source: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="source" id="source">
                    <option value=""></option>
                    <option value="File" <? if($source == 'File') echo 'selected="selected"'; ?>>File</option>
                    <option value="Tape" <? if($source == 'Tape') echo 'selected="selected"'; ?>>Tape</option>
                    <option value="Film" <? if($source == 'Film') echo 'selected="selected"'; ?>>Film</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="destination">
                Destination: <span class="mand">*</span>
            </label>
            <div class="controls">
                <select name="destination" id="destination">
                    <option value=""></option>
                    <option value="File" <? if($destination == 'File') echo 'selected="selected"'; ?>>File</option>
                    <option value="Tape" <? if($destination == 'Tape') echo 'selected="selected"'; ?>>Tape</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="conversion">
                Conversion:
            </label>
            <div class="controls">
                <input type="checkbox" id="conversion" name="conversion" <?if($conversion){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="workflow">
                Workflow:
            </label>
            <div class="controls">
                <input type="text" name="workflow" id="workflow" value="<?=$workflow?>">
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

        </div>

        <div class="halfForm">

            <div class="control-group">
                <label class="control-label" for="price">
                    Price: <span class="mand">*</span>
                </label>
                <div class="controls">
                    <input type="text" name="price" id="price" value="<?=$price?>">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="cost_extra_clips">
                    Cost extra clips:
                </label>
                <div class="controls">
                    <input type="text" name="cost_extra_clips" id="cost_extra_clips" value="<?=$cost_extra_clips?>">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="delivery">
                    Delivery: <span class="mand">*</span>
                </label>
                <div class="controls">
                    <select name="delivery" id="delivery">
                        <?foreach($delivery_methods as $method):?>
                            <option value="<?=$method['code']?>" <?if($delivery == $method['code']) echo 'selected';?>><?=$method['code']?></option>
                        <?endforeach;?>
                    </select>
                </div>
            </div>

            <script>
                jQuery(document).ready(function(){
                    jQuery('#delivery').on('change', function(){
                        var labWrapper = jQuery('.lab-wrapper');
                        if(jQuery(this).val() == 'Lab'){
                            labWrapper.show();
                        }else{
                            labWrapper.hide();
                            jQuery('#lab_id').val('');
                        }
                    });
                });
            </script>

            <!--
            <div class="control-group lab-wrapper" <? if($delivery != 'Lab'){ ?>style="display:none;"<? } ?> >
                <label class="control-label" for="delivery">
                    Lab:
                </label>
                <div class="controls">
                    <select name="lab_id" id="lab_id">
                        <option></option>
                        <?foreach($labs as $lab):?>
                            <option value="<?=$lab['id']?>" <?if($lab_id == $lab['id']) echo 'selected';?>><?=$lab['name']?></option>
                        <?endforeach;?>
                    </select>
                </div>
            </div>-->

            <div class="control-group">
                <label class="control-label" for="timedelay">
                    Timedelay:
                </label>
                <div class="controls">
                    <textarea name="timedelay" id="timedelay" rows="4" cols="70"><?= esc($timedelay) ?></textarea>
                </div>
            </div>

            <!--<div class="control-group">
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
            </div>-->

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

            <div class="control-group">
                <label class="control-label" for="collection">
                    Collection:
                </label>
                <div class="controls">
                    <select name="collection" id="collection">
                        <option value="1">Rights Managed clips</option>
                        <option value="2">NatureFlix clips</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <legend>Transcoding options</legend>

        <div class="control-group">
            <label class="control-label" for="video_codec">
                Video codec:
            </label>
            <div class="controls">
                <select name="video_codec" id="video_codec">
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
                    <option value="rawvideo" <?if($video_codec == 'rawvideo') echo 'selected';?>>Raw video</option>
                    <option value="r210" <?if($video_codec == 'r210') echo 'selected';?>>r210 QuickTime Uncompressed RGB 10-bit</option>
                    <option value="v210" <?if($video_codec == 'v210') echo 'selected';?>>v210 QuickTime uncompressed 4:2:2 10-bit</option>
                    <option value="v308" <?if($video_codec == 'v308') echo 'selected';?>>v308 QuickTime uncompressed 4:4:4</option>
                    <option value="v408" <?if($video_codec == 'v408') echo 'selected';?>>v408 QuickTime uncompressed 4:4:4:4</option>
                    <option value="v410" <?if($video_codec == 'v410') echo 'selected';?>>v410 QuickTime uncompressed 4:4:4 10-bit</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="audio_codec">
                Audio codec:
            </label>
            <div class="controls">
                <select name="audio_codec" id="audio_codec">
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
